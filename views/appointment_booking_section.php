<?php // Booking view (authenticated users only). ?>
<h1>Book Your Mechanic</h1>
<p>Signed in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>

<!-- Sign-out action uses POST and CSRF protection. -->
<form method="post" class="logout-user-form">
    <input type="hidden" name="action" value="logout_user">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
    <button type="submit">Sign Out</button>
</form>

<?php if ($notification): ?>
    <!-- Flash success message after PRG redirect. -->
    <div class="notification success"><?php echo htmlspecialchars($notification); ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <!-- Validation/business-rule errors from booking handler. -->
    <div class="notification error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Appointment request form includes action + CSRF token. -->
<form method="post" action="">
    <input type="hidden" name="action" value="request_appointment">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
    <label>
        Name
        <input type="text" name="name" required>
    </label>
    <label>
        Address
        <input type="text" name="address" required>
    </label>
    <label>
        Phone
        <input type="tel" name="phone" required>
    </label>
    <label>
        Car License
        <input type="text" name="car_license" required>
    </label>
    <label>
        Car Engine
        <input type="text" name="car_engine" required>
    </label>
    <label>
        Appointment Date
        <input type="date" name="appointment_date" required>
    </label>
    <label>
        Choose Mechanic
        <select name="mechanic_id" required>
            <option value="">Select a mechanic</option>
            <?php foreach ($mechanics as $mechanic): ?>
                <option value="<?php echo $mechanic['id']; ?>"><?php echo htmlspecialchars($mechanic['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Request Appointment</button>
</form>
