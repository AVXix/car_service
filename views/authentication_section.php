<?php // Authentication view (guest users only). ?>
<?php if ($authMode === 'create'): ?>
    <h1>Create Account</h1>
    <p>Choose a username and password.</p>
<?php else: ?>
    <h1>User Login</h1>
    <p>Sign in to continue.</p>
<?php endif; ?>

<p class="support-line">Technical support: +880-1700-000000</p>

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
            Username * <span class="help-icon" title="Choose a unique username (minimum 3 characters).">?</span>
            <input type="text" name="username" required minlength="3" placeholder="Enter username" title="Minimum 3 characters">
        </label>
        <label>
            Password * <span class="help-icon" title="Password must be at least 6 characters.">?</span>
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
