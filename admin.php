<?php
require_once __DIR__ . '/config.php';

// Admin Panel - Appointment Management

$maxAppointmentsPerMechanic = 4;
$todayDate = date('Y-m-d');
$mechanics = [];
// Load mechanics along with the number of bookings they already have for today.
$mechStmt = $conn->prepare('SELECT m.id, m.name, m.specialty, (
        SELECT COUNT(*)
        FROM appointments a
        WHERE a.mechanic_id = m.id
            AND DATE(a.appointment_date) = ?) as booked_today
FROM mechanics m');
$mechStmt->bind_param('s', $todayDate);
$mechStmt->execute();
$mechResult = $mechStmt->get_result();
while ($row = $mechResult->fetch_assoc()) {
    $row['slots_left'] = max(0, $maxAppointmentsPerMechanic - (int) $row['booked_today']);
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
        <h1>Mechanic availability today</h1>
        <?php if (empty($mechanics)): ?>
            <p>No mechanics are registered yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($mechanics as $mech): ?>
                    <li>
                        <?php echo htmlspecialchars($mech['name']); ?> (<?php echo htmlspecialchars($mech['specialty']); ?>) — <?php echo $mech['slots_left']; ?> slots left
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Booked Appointments</h2>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <script src="js/script.js"></script>
</body>
</html>
