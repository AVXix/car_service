<?php
// ------------------------------------------------------------
// index_page_request_handler.php
// ------------------------------------------------------------
// Purpose:
// - Prepare all shared variables needed by index views.
// - Provide small helper functions (CSRF + flash messages).
// - Delegate action processing to focused handlers:
//   - index_auth_request_handler.php
//   - index_booking_request_handler.php
//
// Think of this file as the "orchestrator" for index page requests.
// Shared state used by index page views.
$authErrors = [];
$authNotification = '';
$authMode = (($_GET['view'] ?? '') === 'create') ? 'create' : 'signin';

$errors = [];
$notification = '';
$mechanics = [];
$userAppointments = [];
$formValues = [
    // Defaults make rendering and validation predictable on first page load.
    'name' => '',
    'address' => '',
    'phone' => '',
    'car_license' => '',
    'car_engine' => '',
    'appointment_date' => '',
    'mechanic_id' => '',
];

if (empty($_SESSION['csrf_token'])) {
    // Create one CSRF token per session.
    // Every POST form includes this token so we can verify request origin.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if (!function_exists('index_page_has_valid_csrf_token')) {
    // Returns true only when submitted token matches session token.
    // `hash_equals` helps prevent timing attacks.
    function index_page_has_valid_csrf_token(): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $postedToken = $_POST['csrf_token'] ?? '';

        if ($sessionToken === '' || $postedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $postedToken);
    }
}

if (!function_exists('index_page_set_flash_message')) {
    // Save a one-time message in session.
    // Used with redirect flows so feedback still appears after page reload.
    function index_page_set_flash_message(string $target, string $message): void
    {
        $_SESSION['index_page_flash'] = [
            'target' => $target,
            'message' => $message,
        ];
    }
}

if (!empty($_SESSION['index_page_flash']) && is_array($_SESSION['index_page_flash'])) {
    // Read one-time message into display variables, then delete it.
    $flashTarget = $_SESSION['index_page_flash']['target'] ?? '';
    $flashMessage = $_SESSION['index_page_flash']['message'] ?? '';

    if ($flashMessage !== '') {
        if ($flashTarget === 'auth') {
            $authNotification = $flashMessage;
        } elseif ($flashTarget === 'booking') {
            $notification = $flashMessage;
        }
    }

    unset($_SESSION['index_page_flash']);
}

require __DIR__ . '/index_auth_request_handler.php';
require __DIR__ . '/index_booking_request_handler.php';
