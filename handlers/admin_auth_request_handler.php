<?php
// ------------------------------------------------------------
// admin_auth_request_handler.php
// ------------------------------------------------------------
// Handles admin authentication actions:
// - logout
// - login
//
// This file updates session auth state, then redirects to avoid
// accidental form re-submission on refresh.
$adminAction = $_POST['action'] ?? '';

// 1) Logout admin.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminAction === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 2) Login admin.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminAction === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $login_error = 'Username and password are required.';
    } else {
        // Load admin credentials by username and verify password hash.
        $stmt = $conn->prepare('SELECT id, password_hash FROM admin WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$row['id'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        $login_error = 'Invalid username or password.';
    }
}
