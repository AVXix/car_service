<?php // Admin login view shown when no admin session is active. ?>
<!--
    Notes:
    - Login feedback comes from `$login_error` set by admin auth handler.
    - CSRF token is required for admin login POST requests.
-->
<h1>Admin Login</h1>
<?php if ($login_error): ?>
  <!-- Login errors returned by admin auth handler. -->
  <p style="color:darkred"><?php echo htmlspecialchars($login_error); ?></p>
<?php endif; ?>
<!-- Admin login form posts action=login to admin.php. -->
<form method="post">
  <input type="hidden" name="action" value="login">
  <!-- CSRF token defends against cross-site request forgery attacks. -->
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminCsrfToken); ?>">
  <!-- Username/password are validated server-side in admin_auth_request_handler.php. -->
  <label>Username: <input name="username" required></label><br>
  <label>Password: <input name="password" type="password" required></label><br>
  <button type="submit">Login</button>
</form>
