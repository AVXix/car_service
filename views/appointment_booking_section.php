<?php // Booking view (authenticated users only). ?>
<h1>Book Your Mechanic</h1>
<p>Signed in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>

<div class="help-box" id="booking-help">
    <strong>How to book:</strong>
    <ul>
        <li>Fields marked with <strong>*</strong> are required.</li>
        <li>Select a mechanic with available slots.</li>
        <li>Need technical help? Contact: +880-1700-000000</li>
    </ul>
</div>

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
        Name *
        <input type="text" name="name" required placeholder="Enter full name">
    </label>
    <label>
        Address *
        <input type="text" name="address" required placeholder="Enter address">
    </label>
    <label>
        Phone *
        <input type="tel" name="phone" required placeholder="Enter phone number">
    </label>
    <label>
        Car License *
        <input type="text" name="car_license" required placeholder="e.g. DHAKA-METRO-GA-12-3456">
    </label>
    <label>
        Car Engine *
        <input type="text" name="car_engine" required placeholder="Enter car engine number">
    </label>
    <label>
        Appointment Date *
        <input type="date" name="appointment_date" required>
    </label>
    <label>
        Choose Mechanic *
        <select name="mechanic_id" required>
            <option value="">Select a mechanic</option>
            <?php foreach ($mechanics as $mechanic): ?>
                <option value="<?php echo $mechanic['id']; ?>">
                    <?php echo htmlspecialchars($mechanic['name']); ?> - <?php echo (int)$mechanic['slots_available_today']; ?> available slots (<?php echo htmlspecialchars($mechanic['availability_status']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="button-row">
        <button type="submit">Request Appointment</button>
        <button type="reset" class="secondary-btn">Clear</button>
    </div>
</form>
