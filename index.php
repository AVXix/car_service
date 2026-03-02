<?php
require_once __DIR__ . '/config.php';

// Client Appointment Booking Page

$mechanics = [];
// Load mechanic records to present them in the dropdown below.
$mechStmt = $conn->prepare('SELECT id, name, specialty FROM mechanics ORDER BY name');
$mechStmt->execute();
$mechResult = $mechStmt->get_result();
while ($mechanic = $mechResult->fetch_assoc()) {
    $mechanics[] = $mechanic;
}
$mechStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service - Appointment Booking</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
        <section>
            <h1>Book Your Mechanic</h1>
            <p>Fill in the form below so we can reserve a slot with your preferred mechanic.</p>
            <!-- Form posts to this same page; we will add validation logic later. -->
            <form method="post" action="">
                <!-- Text inputs for client contact and vehicle details -->
                <label>
                    Name
                    <input type="text" name="name" required>
                </label>
                <label>
                    Address
                    <input type="text" name="address" required>
                </label>
                <label>
                    Phone
                    <input type="tel" name="phone" required>
                </label>
                <label>
                    Car License
                    <input type="text" name="car_license" required>
                </label>
                <label>
                    Car Engine
                    <input type="text" name="car_engine" required>
                </label>
                <label>
                    Appointment Date &amp; Time
                    <input type="datetime-local" name="appointment_date" required>
                </label>
                <label>
                    Choose Mechanic
                    <select name="mechanic_id" required>
                        <!-- Mechanics dropdown populated from the database -->
                        <option value="">Select a mechanic</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                            <option value="<?php echo $mechanic['id']; ?>"><?php echo htmlspecialchars($mechanic['name'] . ' — ' . $mechanic['specialty']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Request Appointment</button>
            </form>
        </section>
    </main>
    <script src="js/script.js"></script>
</body>
</html>
