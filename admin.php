<?php
// ------------------------------------------------------------
// admin.php
// ------------------------------------------------------------
//
// - It loads shared setup (config + session).
// - It runs admin request handlers.
// - It selects the correct view (login or dashboard).
//
// Real processing logic lives in `handlers/`.
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/handlers/admin_page_request_handler.php';
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
    <!--
        View switch:
        - If admin is not logged in -> show login view.
        - If admin is logged in     -> show dashboard view.
    -->
    <?php if (empty($_SESSION['admin_id'])): ?>
        <main>
            <?php require __DIR__ . '/views/admin_authentication_section.php'; ?>
        </main>
    <?php else: ?>
        <?php require __DIR__ . '/views/admin_dashboard_section.php'; ?>
    <?php endif; ?>

    <script src="js/admin.js"></script>
</body>
</html>
