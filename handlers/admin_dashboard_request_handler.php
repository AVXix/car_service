<?php
// ------------------------------------------------------------
// admin_dashboard_request_handler.php
// ------------------------------------------------------------
// Handles admin-only dashboard behavior:
// - action updates (slot totals, mechanic reassignment),
// - loading mechanics + appointments for display,
// - optional edit-mode lookup from query string.
$adminAction = $_POST['action'] ?? '';

// 1) Update total slots for one mechanic.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminAction === 'update_total_slots') {
    $mechanicId = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;
    $totalSlots = isset($_POST['total_slots']) ? (int)$_POST['total_slots'] : -1;

    if ($mechanicId > 0 && $totalSlots >= 0) {
        // Count current bookings so `available_slots` stays consistent.
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
            // If mechanic has no slot row yet, create it.
            $insertSlotStmt = $conn->prepare('INSERT INTO mechanic_slots (mechanic_id, total_slots, available_slots, updated_at) VALUES (?, ?, ?, NOW())');
            $insertSlotStmt->bind_param('iii', $mechanicId, $totalSlots, $availableSlots);
            $insertSlotStmt->execute();
            $insertSlotStmt->close();
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 2) Reassign existing appointment to a different mechanic.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminAction === 'update_mechanic') {
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $new_mechanic = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;

    if ($appointment_id > 0 && $new_mechanic > 0) {
        $update = $conn->prepare('UPDATE appointments SET mechanic_id = ? WHERE id = ?');
        $update->bind_param('ii', $new_mechanic, $appointment_id);
        $update->execute();
        $update->close();
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 3) Load mechanics summary for dashboard table and edit dropdown.
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

// 4) Load booked appointments list.
$appStmt = $conn->prepare('SELECT a.id, a.name, a.phone, a.car_license, a.appointment_date, m.name AS mechanic_name FROM appointments a JOIN mechanics m ON a.mechanic_id = m.id ORDER BY a.appointment_date ASC');
$appStmt->execute();
$appResult = $appStmt->get_result();
while ($row = $appResult->fetch_assoc()) {
    $appointments[] = $row;
}
$appStmt->close();

// 5) Optional edit mode selected by query string (`?edit={id}`).
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
