<?php
// ------------------------------------------------------------
// index_booking_request_handler.php
// ------------------------------------------------------------
// Handles booking-related data and actions for index page.
//
// Responsibilities:
// - Load mechanics list for booking dropdown (logged-in users only).
// - Validate appointment requests.
// - Enforce duplicate and capacity rules.
// - Save appointment and redirect with flash feedback.

// Load mechanics only when user is authenticated.
if (!empty($_SESSION['user_id'])) {
    $mechStmt = $conn->prepare('SELECT id, name FROM mechanics ORDER BY name');
    $mechStmt->execute();
    $mechResult = $mechStmt->get_result();
    while ($mechanic = $mechResult->fetch_assoc()) {
        $mechanics[] = $mechanic;
    }
    $mechStmt->close();
}

$bookingAction = $_POST['action'] ?? '';
// Process booking form submit.
if (!empty($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && $bookingAction === 'request_appointment') {
    if (!index_page_has_valid_csrf_token()) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    } else {
        // Normalize incoming form values.
        foreach ($formValues as $key => $value) {
            $formValues[$key] = trim($_POST[$key] ?? '');
        }

        if (in_array('', $formValues, true)) {
            $errors[] = 'Please provide every detail so we can process your request.';
        }

        $dateValue = DateTime::createFromFormat('Y-m-d', $formValues['appointment_date']);
        if (!$dateValue) {
            $errors[] = 'The appointment date/time is not valid. Please use the picker to select a slot.';
        }

        if (!$errors) {
            // Convert submitted date into DB-friendly datetime format.
            $formattedDate = $dateValue->format('Y-m-d H:i:s');

            // Rule 1: same phone cannot book multiple appointments on same date.
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
            // Read configured capacity for selected mechanic.
            $capacityStmt = $conn->prepare('SELECT COALESCE(total_slots, 4) AS total_slots FROM mechanic_slots WHERE mechanic_id = ? LIMIT 1');
            $capacityStmt->bind_param('i', $formValues['mechanic_id']);
            $capacityStmt->execute();
            $capacityRow = $capacityStmt->get_result()->fetch_assoc();
            $capacityStmt->close();

            $totalSlotsForMechanic = (int)($capacityRow['total_slots'] ?? 4);

            // Count existing bookings for same mechanic/date.
            $slotStmt = $conn->prepare('SELECT COUNT(*) as taken FROM appointments WHERE mechanic_id = ? AND DATE(appointment_date) = DATE(?)');
            $slotStmt->bind_param('is', $formValues['mechanic_id'], $formattedDate);
            $slotStmt->execute();
            $slotTaken = $slotStmt->get_result()->fetch_assoc();
            $slotStmt->close();

            if ((int) $slotTaken['taken'] >= $totalSlotsForMechanic) {
                $errors[] = 'The mechanic you selected is fully booked for that day. Choose another date or mechanic.';
            }
        }

        if (!$errors) {
            // Save appointment only after all checks pass.
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
                index_page_set_flash_message('booking', 'Appointment requested! We will confirm shortly.');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }

            $errors[] = 'Unable to save the appointment right now. Please try again.';
            $insertStmt->close();
        }
    }
}
