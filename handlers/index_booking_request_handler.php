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
    $mechStmt = $conn->prepare('SELECT m.id, m.name, COALESCE(ms.total_slots, 4) AS total_slots, (
            SELECT COUNT(*) FROM appointments a
            WHERE a.mechanic_id = m.id AND a.appointment_date = CURDATE()
        ) AS booked_today
    FROM mechanics m
    LEFT JOIN mechanic_slots ms ON ms.mechanic_id = m.id
    ORDER BY m.name');
    $mechStmt->execute();
    $mechResult = $mechStmt->get_result();
    while ($mechanic = $mechResult->fetch_assoc()) {
        $mechanic['total_slots'] = (int)($mechanic['total_slots'] ?? 4);
        $mechanic['booked_today'] = (int)($mechanic['booked_today'] ?? 0);
        $mechanic['slots_available_today'] = max(0, $mechanic['total_slots'] - $mechanic['booked_today']);
        $mechanic['availability_status'] = ($mechanic['slots_available_today'] > 0) ? 'Available' : 'Fully booked';
        $mechanics[] = $mechanic;
    }
    $mechStmt->close();

    if (!empty($_SESSION['username'])) {
        $userAppointmentsStmt = $conn->prepare('SELECT a.id, a.name, a.address, a.phone, a.car_license, a.car_engine, a.appointment_date, a.created_at, m.name AS mechanic_name
            FROM appointments a
            LEFT JOIN mechanics m ON m.id = a.mechanic_id
            WHERE a.username = ?
            ORDER BY a.appointment_date ASC, a.created_at DESC');
        $userAppointmentsStmt->bind_param('s', $_SESSION['username']);
        $userAppointmentsStmt->execute();
        $userAppointmentsResult = $userAppointmentsStmt->get_result();

        while ($appointment = $userAppointmentsResult->fetch_assoc()) {
            $userAppointments[] = $appointment;
        }

        $userAppointmentsStmt->close();
    }
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
            $errors[] = 'The appointment date is not valid. Please use the picker to select a date.';
        }

        if (!ctype_digit((string)$formValues['mechanic_id']) || (int)$formValues['mechanic_id'] <= 0) {
            $errors[] = 'Please choose a valid mechanic.';
        }

        if (!$errors) {
            // Convert submitted date into DB-friendly date format.
            $formattedDate = $dateValue->format('Y-m-d');

            // Rule 1: same user account cannot book multiple appointments on same date.
            $dupStmt = $conn->prepare('SELECT COUNT(*) as count FROM appointments WHERE username = ? AND DATE(appointment_date) = DATE(?)');
            $dupStmt->bind_param('ss', $_SESSION['username'], $formattedDate);
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
            $insertStmt = $conn->prepare('INSERT INTO appointments (username, name, address, phone, car_license, car_engine, appointment_date, mechanic_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $insertStmt->bind_param(
                'sssssssi',
                $_SESSION['username'],
                $formValues['name'],
                $formValues['address'],
                $formValues['phone'],
                $formValues['car_license'],
                $formValues['car_engine'],
                $formattedDate,
                $formValues['mechanic_id']
            );

            if ($insertStmt->execute()) {
                $selectedMechanicName = 'Selected mechanic';
                foreach ($mechanics as $mechanic) {
                    if ((int)$mechanic['id'] === (int)$formValues['mechanic_id']) {
                        $selectedMechanicName = $mechanic['name'];
                        break;
                    }
                }

                index_page_set_flash_message('booking', 'Appointment confirmed for ' . $formattedDate . ' with ' . $selectedMechanicName . '.');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }

            $errors[] = 'Unable to save the appointment right now. Please try again.';
            $insertStmt->close();
        }
    }
}

// Process cancel appointment.
if (!empty($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && $bookingAction === 'cancel_appointment') {
    if (!index_page_has_valid_csrf_token()) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    } else {
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);
        if ($appointmentId <= 0) {
            $errors[] = 'Invalid appointment selected.';
        } else {
            // Check if appointment belongs to user and delete.
            $deleteStmt = $conn->prepare('DELETE FROM appointments WHERE id = ? AND username = ?');
            $deleteStmt->bind_param('is', $appointmentId, $_SESSION['username']);
            if ($deleteStmt->execute() && $deleteStmt->affected_rows > 0) {
                index_page_set_flash_message('booking', 'Appointment cancelled successfully.');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $errors[] = 'Unable to cancel the appointment. It may not exist or belong to you.';
            }
            $deleteStmt->close();
        }
    }
}
