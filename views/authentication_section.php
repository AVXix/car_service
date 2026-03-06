<?php // Authentication view (guest users only). ?>
<?php if ($authMode === 'create'): ?>
    <h1>Create Account</h1>
    <p>Choose a username and password.</p>
<?php else: ?>
    <h1>User Login</h1>
    <p>Sign in to continue.</p>
<?php endif; ?>

<?php if ($authErrors): ?>
    <!-- Validation and auth errors from request handlers. -->
    <div class="notification error">
        <ul>
            <?php foreach ($authErrors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($authNotification): ?>
    <!-- Flash success/info message after redirect. -->
    <div class="notification success"><?php echo htmlspecialchars($authNotification); ?></div>
<?php endif; ?>

<?php if ($authMode === 'create'): ?>
    <!-- Create-account form posts action + CSRF token. -->
    <form method="post" action="">
        <input type="hidden" name="action" value="create_account">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Create Account</button>
    </form>
    <p class="auth-link-row">Already have an account? <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Sign in here</a></p>
<?php else: ?>
    <!-- Sign-in form posts action + CSRF token. -->
    <form method="post" action="">
        <input type="hidden" name="action" value="sign_in">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Sign In</button>
    </form>
    <p class="auth-link-row">Don't have an account? <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?view=create">Create account here</a></p>
<?php endif; ?>
