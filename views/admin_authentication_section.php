<?php // Admin login view shown when no admin session is active. ?>
<h1>Admin Login</h1>
<?php if ($login_error): ?>
  <!-- Login errors returned by admin auth handler. -->
  <p style="color:darkred"><?php echo htmlspecialchars($login_error); ?></p>
<?php endif; ?>
<!-- Admin login form posts action=login to admin.php. -->
<form method="post">
  <input type="hidden" name="action" value="login">
  <label>Username: <input name="username" required value="admin"></label><br>
  <label>Password: <input name="password" type="password" required></label><br>
  <button type="submit">Login</button>
</form>
