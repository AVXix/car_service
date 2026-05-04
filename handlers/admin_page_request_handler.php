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
$adminNotification = '';
$adminError = '';
$mechanics = [];
$appointments = [];
$editingAppointment = null;

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}
$adminCsrfToken = $_SESSION['admin_csrf_token'];

if (!function_exists('admin_has_valid_csrf_token')) {
    function admin_has_valid_csrf_token(): bool
    {
        $sessionToken = $_SESSION['admin_csrf_token'] ?? '';
        $postedToken = $_POST['csrf_token'] ?? '';

        if ($sessionToken === '' || $postedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $postedToken);
    }
}

if (!function_exists('admin_set_flash_message')) {
    function admin_set_flash_message(string $type, string $message): void
    {
        $_SESSION['admin_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!empty($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flashType = $_SESSION['admin_flash']['type'] ?? '';
    $flashMessage = $_SESSION['admin_flash']['message'] ?? '';

    if ($flashMessage !== '') {
        if ($flashType === 'error') {
            $adminError = $flashMessage;
        } else {
            $adminNotification = $flashMessage;
        }
    }

    unset($_SESSION['admin_flash']);
}

// Always process auth actions first (login/logout).
require __DIR__ . '/admin_auth_request_handler.php';

// Only authenticated admins can run dashboard logic.
if (!empty($_SESSION['admin_id'])) {
    require __DIR__ . '/admin_dashboard_request_handler.php';
}
