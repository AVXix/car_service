<?php // Authentication view (guest users only). ?>
<?php if ($authMode === 'create'): ?>
    <h1>Create Account</h1>
    <p>Choose a username and password.</p>
    <div class="help-box">
        <strong>Create account:</strong>
        <ul>
            <li>Username must be unique and at least 3 characters.</li>
            <li>Password must be at least 6 characters.</li>
            <li>Technical support: +880-1700-000000</li>
        </ul>
    </div>
<?php else: ?>
    <h1>User Login</h1>
    <p>Sign in to continue.</p>
    <div class="help-box">
        <strong>Login help:</strong>
        <ul>
            <li>Enter your username and password to continue.</li>
            <li>Technical support: +880-1700-000000</li>
        </ul>
    </div>
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
            Username *
            <input type="text" name="username" required minlength="3" placeholder="Enter username" title="Minimum 3 characters">
        </label>
        <label>
            Password *
            <input type="password" name="password" required minlength="6" placeholder="Enter password" title="Minimum 6 characters">
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
            Username *
            <input type="text" name="username" required placeholder="Enter username">
        </label>
        <label>
            Password *
            <input type="password" name="password" required placeholder="Enter password">
        </label>
        <button type="submit">Sign In</button>
    </form>
    <p class="auth-link-row">Don't have an account? <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?view=create">Create account here</a></p>
<?php endif; ?>
