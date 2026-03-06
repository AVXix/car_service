<?php
// ------------------------------------------------------------
// admin_page_request_handler.php
// ------------------------------------------------------------
// This is the admin-page orchestrator.
// It prepares shared variables and delegates processing to focused handlers.
//
// Shared variables consumed by admin views:
// - `$login_error` for login feedback,
// - `$mechanics` / `$appointments` for dashboard tables,
// - `$editingAppointment` for edit mode.
$login_error = '';
$mechanics = [];
$appointments = [];
$editingAppointment = null;

// Always process auth actions first (login/logout).
require __DIR__ . '/admin_auth_request_handler.php';

// Only authenticated admins can run dashboard logic.
if (!empty($_SESSION['admin_id'])) {
    require __DIR__ . '/admin_dashboard_request_handler.php';
}
