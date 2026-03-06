<?php // Booking view (authenticated users only). ?>
<div class="booking-topbar">
    <div>
        <h1>Book Your Mechanic</h1>
        <p>Signed in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.</p>
    </div>

    <!-- Sign-out action uses POST and CSRF protection. -->
    <form method="post" class="logout-user-form">
        <input type="hidden" name="action" value="logout_user">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <button type="submit">Sign Out</button>
    </form>
</div>

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

<div class="user-appointments-panel">
    <div class="panel-header-row">
        <div>
            <h2>Your Appointments</h2>
            <p>All appointments created from this account are shown here.</p>
        </div>
    </div>

    <?php if (empty($userAppointments)): ?>
        <p class="empty-state">No appointments found for your account yet.</p>
    <?php else: ?>
        <div class="appointment-card-list">
            <?php foreach ($userAppointments as $appointment): ?>
                <article class="appointment-card">
                    <div class="appointment-card-top">
                        <h3><?php echo htmlspecialchars($appointment['name']); ?></h3>
                        <span class="appointment-date-badge"><?php echo htmlspecialchars(date('d M Y', strtotime($appointment['appointment_date']))); ?></span>
                    </div>
                    <ul class="appointment-meta-list">
                        <li><strong>Mechanic:</strong> <?php echo htmlspecialchars($appointment['mechanic_name'] ?: 'Not assigned'); ?></li>
                        <li><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['phone']); ?></li>
                        <li><strong>Address:</strong> <?php echo htmlspecialchars($appointment['address']); ?></li>
                        <li><strong>Car License:</strong> <?php echo htmlspecialchars($appointment['car_license']); ?></li>
                        <li><strong>Engine:</strong> <?php echo htmlspecialchars($appointment['car_engine']); ?></li>
                    </ul>
                    <form method="post" class="cancel-appointment-form">
                        <input type="hidden" name="action" value="cancel_appointment">
                        <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <button type="submit" class="cancel-btn">Cancel Appointment</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="appointment-actions-row">
        <button
            type="button"
            id="toggle-appointment-form-btn"
            class="new-appointment-btn"
            aria-expanded="<?php echo $errors ? 'true' : 'false'; ?>"
            aria-controls="appointment-modal"
        >
            New Appointment
        </button>
    </div>
</div>

<div class="appointment-modal" id="appointment-modal" aria-hidden="false" data-open-on-load="<?php echo $errors ? 'true' : 'false'; ?>">
    <div class="appointment-modal-backdrop" data-close-appointment-modal="true"></div>
    <div class="booking-form-panel appointment-modal-dialog" id="appointment-form" role="dialog" aria-modal="true" aria-labelledby="appointment-modal-title">
        <div class="panel-header-row appointment-modal-header">
            <div>
                <h2 id="appointment-modal-title">New Appointment</h2>
                <p>Fill in the form below and press the button to send the appointment form.</p>
            </div>
            <button type="button" class="modal-close-btn" id="close-appointment-form-btn" aria-label="Close appointment form">×</button>
        </div>

        <div class="help-box" id="booking-help">
            <strong>How to book:</strong>
            <ul>
                <li>Fields marked with <strong>*</strong> are required.</li>
                <li>Select a mechanic with available slots.</li>
                <li>Need technical help? Contact: +880-1700-000000</li>
            </ul>
        </div>

        <!-- Appointment request form includes action + CSRF token. -->
        <form method="post" action="">
            <input type="hidden" name="action" value="request_appointment">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <label>
                Name *
                <input type="text" name="name" required placeholder="Enter full name" value="<?php echo htmlspecialchars($formValues['name']); ?>">
            </label>
            <label>
                Address *
                <input type="text" name="address" required placeholder="Enter address" value="<?php echo htmlspecialchars($formValues['address']); ?>">
            </label>
            <label>
                Phone *
                <input type="tel" name="phone" required placeholder="Enter phone number" value="<?php echo htmlspecialchars($formValues['phone']); ?>">
            </label>
            <label>
                Car License *
                <input type="text" name="car_license" required placeholder="e.g. DHAKA-METRO-GA-12-3456" value="<?php echo htmlspecialchars($formValues['car_license']); ?>">
            </label>
            <label>
                Car Engine *
                <input type="text" name="car_engine" required placeholder="Enter car engine number" value="<?php echo htmlspecialchars($formValues['car_engine']); ?>">
            </label>
            <label>
                Appointment Date *
                <input type="date" name="appointment_date" required value="<?php echo htmlspecialchars($formValues['appointment_date']); ?>">
            </label>
            <label>
                Choose Mechanic *
                <select name="mechanic_id" required>
                    <option value="">Select a mechanic</option>
                    <?php foreach ($mechanics as $mechanic): ?>
                        <option value="<?php echo $mechanic['id']; ?>" <?php echo ((string)$mechanic['id'] === (string)$formValues['mechanic_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mechanic['name']); ?> - <?php echo (int)$mechanic['slots_available_today']; ?> available slots (<?php echo htmlspecialchars($mechanic['availability_status']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="button-row">
                <button type="submit">Send Appointment Form</button>
                <button type="reset" class="secondary-btn">Clear</button>
            </div>
        </form>
    </div>
</div>
