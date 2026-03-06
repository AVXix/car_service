<?php
// Load DB connection (mysqli `$conn` from config.php) and start session.
// This page implements a very small authentication layer for a single
// admin account stored in the `admins` table. Passwords are stored
// hashed with PHP's `password_hash()` and verified with `password_verify()`.
require_once __DIR__ . '/config.php';
session_start();

// Handle logout action: POST with action=logout will clear the session.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    // Clear session data and destroy session cookie.
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// --- Authentication overview ---
// 1) When the page receives a POST with action=login it looks up the
//    `admins` table for the provided username and fetches `password_hash`.
// 2) `password_verify($submitted, $stored_hash)` checks the credential.
// 3) On success: `$_SESSION['admin_id']` is set and the page reloads.
// 4) When not authenticated, the script renders a simple login form and exits.
// 5) All code below the authentication check is only executed for logged-in admins.

// Simple admin authentication using the `admins` table.
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $login_error = 'Username and password are required.';
    } else {
        // Lookup the admin row (safe with prepared statement).
        $stmt = $conn->prepare('SELECT id, password_hash FROM admin WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Verify submitted password against stored hash.
        if ($row && password_verify($password, $row['password_hash'])) {
            // Successful login: prevent session fixation and store admin id.
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$row['id'];
            // Reload to enter the authenticated branch below.
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = 'Invalid username or password.';
        }
    }
}

// If not logged in, show login form and stop further processing.
if (empty($_SESSION['admin_id'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link rel="stylesheet" href="css/admin.css">
    </head>
        <body>
                <main>
        <h1>Admin Login</h1>
        <?php if ($login_error): ?>
          <p style="color:darkred"><?php echo htmlspecialchars($login_error); ?></p>
        <?php endif; ?>
        <form method="post">
          <input type="hidden" name="action" value="login">
          <label>Username: <input name="username" required value="admin"></label><br>
          <label>Password: <input name="password" type="password" required></label><br>
          <button type="submit">Login</button>
        </form>
      </main>
    </body>
    </html>
    <?php
    exit;
}

// Admin is authenticated beyond this point. Load admin-only data.

// Note: Everything below this comment is visible only to authenticated admins.
// If you add other admin pages later, require the same `$_SESSION['admin_id']`
// check at the top of those pages to protect them.

// Admin Panel - Appointment Management (loads mechanics and appointments)

// Handle total slot updates from inline admin input (auto-submit, no save button).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_total_slots') {
    $mechanicId = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;
    $totalSlots = isset($_POST['total_slots']) ? (int)$_POST['total_slots'] : -1;

    if ($mechanicId > 0 && $totalSlots >= 0) {
        // Keep available_slots consistent with appointments currently assigned.
        $bookedStmt = $conn->prepare('SELECT COUNT(*) AS booked_count FROM appointments WHERE mechanic_id = ?');
        $bookedStmt->bind_param('i', $mechanicId);
        $bookedStmt->execute();
        $bookedRow = $bookedStmt->get_result()->fetch_assoc();
        $bookedStmt->close();

        $bookedCount = (int)($bookedRow['booked_count'] ?? 0);
        $availableSlots = max(0, $totalSlots - $bookedCount);

        $updateSlotStmt = $conn->prepare('UPDATE mechanic_slots SET total_slots = ?, available_slots = ?, updated_at = NOW() WHERE mechanic_id = ?');
        $updateSlotStmt->bind_param('iii', $totalSlots, $availableSlots, $mechanicId);
        $updateSlotStmt->execute();
        $updatedRows = $updateSlotStmt->affected_rows;
        $updateSlotStmt->close();

        if ($updatedRows === 0) {
            $insertSlotStmt = $conn->prepare('INSERT INTO mechanic_slots (mechanic_id, total_slots, available_slots, updated_at) VALUES (?, ?, ?, NOW())');
            $insertSlotStmt->bind_param('iii', $mechanicId, $totalSlots, $availableSlots);
            $insertSlotStmt->execute();
            $insertSlotStmt->close();
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$mechanics = [];
// Load mechanics and booking count (date ignored) to compute available slots.
$mechStmt = $conn->prepare('SELECT m.id, m.name, COALESCE(ms.total_slots, 4) AS total_slots, (
        SELECT COUNT(*) FROM appointments a
        WHERE a.mechanic_id = m.id
    ) as booked_count
FROM mechanics m
LEFT JOIN mechanic_slots ms ON ms.mechanic_id = m.id');
$mechStmt->execute();
$mechResult = $mechStmt->get_result();
while ($row = $mechResult->fetch_assoc()) {
    $row['total_slots'] = (int) $row['total_slots'];
    $row['booked_count'] = (int) $row['booked_count'];
    $row['slots_left'] = max(0, $row['total_slots'] - $row['booked_count']);
    $mechanics[] = $row;
}
$mechStmt->close();

$appointments = [];
$appStmt = $conn->prepare('SELECT a.id, a.name, a.phone, a.car_license, a.appointment_date, m.name AS mechanic_name FROM appointments a JOIN mechanics m ON a.mechanic_id = m.id ORDER BY a.appointment_date ASC');
$appStmt->execute();
$appResult = $appStmt->get_result();
while ($row = $appResult->fetch_assoc()) {
    $appointments[] = $row;
}
$appStmt->close();

// Handle mechanic assignment update from admin (small, safe handler)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_mechanic') {
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $new_mechanic = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;

    if ($appointment_id > 0 && $new_mechanic > 0) {
        $update = $conn->prepare('UPDATE appointments SET mechanic_id = ? WHERE id = ?');
        $update->bind_param('ii', $new_mechanic, $appointment_id);
        $update->execute();
        $update->close();
    }

    // Redirect to avoid form resubmission and return to admin list
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// If admin requested to edit an appointment, load that appointment for the form
$editingAppointment = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $eStmt = $conn->prepare('SELECT id, mechanic_id, name FROM appointments WHERE id = ? LIMIT 1');
        $eStmt->bind_param('i', $editId);
        $eStmt->execute();
        $eRes = $eStmt->get_result();
        $editingAppointment = $eRes->fetch_assoc();
        $eStmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php if (!empty($_SESSION['admin_id'])): ?>
        <!-- Top-right logout button for authenticated admin -->
        <form method="post" style="position:fixed; right:16px; top:16px;">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Logout</button>
        </form>
    <?php endif; ?>
    <main>
        <h1>Mechanic and slots availability</h1>
        <?php if (empty($mechanics)): ?>
            <p>No mechanics are registered yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Mechanic</th>
                        <th>Total Slots</th>
                        <th>Booked</th>
                        <th>Slots Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mechanics as $mech): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mech['name']); ?></td>
                            <td>
                                <form method="post" class="slot-inline-form">
                                    <input type="hidden" name="action" value="update_total_slots">
                                    <input type="hidden" name="mechanic_id" value="<?php echo (int)$mech['id']; ?>">
                                    <input
                                        type="number"
                                        name="total_slots"
                                        min="0"
                                        value="<?php echo (int)$mech['total_slots']; ?>"
                                        class="slot-total-input"
                                        aria-label="Total slots for <?php echo htmlspecialchars($mech['name']); ?>"
                                    >
                                </form>
                            </td>
                            <td><?php echo (int)$mech['booked_count']; ?></td>
                            <td><?php echo (int)$mech['slots_left']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Booked Appointments</h2>
        <?php if ($editingAppointment): ?>
            <section style="border:1px solid #ccc;padding:12px;margin-bottom:12px;">
                <h3>Edit mechanic for: <?php echo htmlspecialchars($editingAppointment['name']); ?></h3>
                <form method="post">
                    <input type="hidden" name="action" value="update_mechanic">
                    <input type="hidden" name="appointment_id" value="<?php echo (int)$editingAppointment['id']; ?>">
                    <label>Mechanic:
                        <select name="mechanic_id" required>
                            <option value="">-- Select mechanic --</option>
                            <?php foreach ($mechanics as $mech): ?>
                                <option value="<?php echo (int)$mech['id']; ?>" <?php echo ((int)$mech['id'] === (int)$editingAppointment['mechanic_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mech['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit">Save</button>
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="margin-left:8px;">Cancel</a>
                </form>
            </section>
        <?php endif; ?>
        <?php if (empty($appointments)): ?>
            <p>No appointments yet.</p>
        <?php else: ?>
            <!-- Display the persisted appointments so admins can see which slots are taken and by whom. -->
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Phone</th>
                        <th>Car</th>
                        <th>Date</th>
                        <th>Mechanic</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['car_license']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($appointment['mechanic_name']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?edit=<?php echo (int)$appointment['id']; ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <script src="js/amdin.js"></script>
</body>
</html>
