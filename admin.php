<?php
require_once __DIR__ . '/config.php';

// Admin Panel - Appointment Management

$maxAppointmentsPerMechanic = 4;
$mechanics = [];
// Load mechanics and booking count (date ignored) to compute available slots.
$mechStmt = $conn->prepare('SELECT m.id, m.name, (
        SELECT COUNT(*) FROM appointments a
        WHERE a.mechanic_id = m.id
    ) as booked_count
FROM mechanics m');
$mechStmt->execute();
$mechResult = $mechStmt->get_result();
while ($row = $mechResult->fetch_assoc()) {
    $row['booked_count'] = (int) $row['booked_count'];
    $row['slots_left'] = max(0, $maxAppointmentsPerMechanic - $row['booked_count']);
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
        <h1>Mechanic and slots availability</h1>
        <?php if (empty($mechanics)): ?>
            <p>No mechanics are registered yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Mechanic</th>
                        <th>Booked</th>
                        <th>Slots Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mechanics as $mech): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mech['name']); ?></td>
                            <td><?php echo (int)$mech['booked_count']; ?></td>
                            <td><?php echo (int)$mech['slots_left']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
