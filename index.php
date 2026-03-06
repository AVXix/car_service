<?php
// ------------------------------------------------------------
// index.php
// ------------------------------------------------------------
// This is the public page users open first.
//
// - It only wires pieces together (config, session, handlers, views).
// - Business logic lives in `handlers/` so this page stays easy to read.
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/handlers/index_page_request_handler.php';
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
            <!--
                Page state switch:
                - If no `user_id` in session -> show sign-in/create-account view.
                - If `user_id` exists         -> show booking form view.
            -->
            <?php if (empty($_SESSION['user_id'])): ?>
                <?php require __DIR__ . '/views/authentication_section.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/views/appointment_booking_section.php'; ?>
            <?php endif; ?>
        </section>
    </main>
    <script src="js/index-page-interactions.js"></script>
</body>
</html>
