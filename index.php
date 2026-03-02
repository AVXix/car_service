<?php
require_once __DIR__ . '/config.php';

// Client Appointment Booking Page

$mechanics = [];
/*
 * Load mechanic records so we can show them in the dropdown.
 * - Prepare makes the query safe and reusable.
 * - Execute runs it against the connection we got from config.php.
 * - get_result() hands back the rows returned from the database.
 * - fetch_assoc() reads one mechanic at a time as an associative array.
 */
$mechStmt = $conn->prepare('SELECT id, name, specialty FROM mechanics ORDER BY name');
$mechStmt->execute();
$mechResult = $mechStmt->get_result();
while ($mechanic = $mechResult->fetch_assoc()) {
    $mechanics[] = $mechanic;
}
$mechStmt->close();

$errors = [];
$notification = '';
$formValues = [
    // Initialize form values to empty strings for each field.
    'name' => '',
    'address' => '',
    'phone' => '',
    'car_license' => '',
    'car_engine' => '',
    'appointment_date' => '',
    'mechanic_id' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the form submission when the client posts data.
    // Trim and memorize every incoming field so we can re-populate the form if validation fails.
    foreach ($formValues as $key => $value) {
        $formValues[$key] = trim($_POST[$key] ?? '');
    }

    // Ensure nothing is left blank before we run more expensive checks.
    if (in_array('', $formValues, true)) {
        $errors[] = 'Please provide every detail so we can process your request.';
    }

    // Convert the datetime-local string to a format MySQL understands.
    $dateValue = DateTime::createFromFormat('Y-m-d\TH:i', $formValues['appointment_date']);
    if (!$dateValue) {
        $errors[] = 'The appointment date/time is not valid. Please use the picker to select a slot.';
    }

    if (!$errors) {
        $formattedDate = $dateValue->format('Y-m-d H:i:s');

        // Guard against clients booking the same date more than once.
        $dupStmt = $conn->prepare('SELECT COUNT(*) as count FROM appointments WHERE phone = ? AND DATE(appointment_date) = DATE(?)');
        $dupStmt->bind_param('ss', $formValues['phone'], $formattedDate);
        $dupStmt->execute();
        $dupResult = $dupStmt->get_result()->fetch_assoc();
        $dupStmt->close();

        if ((int) $dupResult['count'] > 0) {
            $errors[] = 'It looks like you already have an appointment on that date.';
        }
    }

    if (!$errors) {
        // Count how many bookings the selected mechanic already has on that day.
        $slotStmt = $conn->prepare('SELECT COUNT(*) as taken FROM appointments WHERE mechanic_id = ? AND DATE(appointment_date) = DATE(?)');
        $slotStmt->bind_param('is', $formValues['mechanic_id'], $formattedDate);
        $slotStmt->execute();
        $slotTaken = $slotStmt->get_result()->fetch_assoc();
        $slotStmt->close();

        if ((int) $slotTaken['taken'] >= 4) {
            $errors[] = 'The mechanic you selected is fully booked for that day. Choose another date or mechanic.';
        }
    }

    if (!$errors) {
        // Insert the validated appointment.
        $insertStmt = $conn->prepare('INSERT INTO appointments (name, address, phone, car_license, car_engine, appointment_date, mechanic_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertStmt->bind_param(
            'ssssssi',
            $formValues['name'],
            $formValues['address'],
            $formValues['phone'],
            $formValues['car_license'],
            $formValues['car_engine'],
            $formattedDate,
            $formValues['mechanic_id']
        );

        if ($insertStmt->execute()) {
            $notification = 'Appointment requested! We will confirm shortly.';
            foreach ($formValues as &$value) {
                $value = '';
            }
            unset($value);
        } else {
            $errors[] = 'Unable to save the appointment right now. Please try again.';
        }

        $insertStmt->close();
    }
}
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
            <!-- Display any messages that result from the form submission. -->
            <?php if ($notification): ?>
                <div class="notification success"><?php echo htmlspecialchars($notification); ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="notification error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <!-- Form posts to this same page so we can validate before writing to the database. -->
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
