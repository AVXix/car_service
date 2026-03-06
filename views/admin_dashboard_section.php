<?php // Admin dashboard view shown after successful admin authentication. ?>
<!-- Quick logout action. -->
<form method="post" class="logout-form" style="position:fixed; right:16px; top:16px;">
    <input type="hidden" name="action" value="logout">
    <button type="submit">Logout</button>
</form>

<main>
    <!-- Mechanic capacity and current booking usage table. -->
    <h1>Mechanic and slots availability</h1>
    <?php if (empty($mechanics)): ?>
        <p>No mechanics are registered yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mechanic</th>
                    <th>Total Slots</th>
                    <th>Booked</th>
                    <th>Slots Left</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mechanics as $mech): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mech['name']); ?></td>
                        <td>
                            <!-- Inline form for changing one mechanic's total slots. -->
                            <form method="post" class="slot-inline-form">
                                <input type="hidden" name="action" value="update_total_slots">
                                <input type="hidden" name="mechanic_id" value="<?php echo (int)$mech['id']; ?>">
                                <input
                                    type="number"
                                    name="total_slots"
                                    min="0"
                                    value="<?php echo (int)$mech['total_slots']; ?>"
                                    class="slot-total-input"
                                    aria-label="Total slots for <?php echo htmlspecialchars($mech['name']); ?>"
                                >
                            </form>
                        </td>
                        <td><?php echo (int)$mech['booked_count']; ?></td>
                        <td><?php echo (int)$mech['slots_left']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Booked Appointments</h2>
    <?php if ($editingAppointment): ?>
        <!-- Appears when `?edit={id}` is present. -->
        <section style="border:1px solid #ccc;padding:12px;margin-bottom:12px;">
            <h3>Edit mechanic for: <?php echo htmlspecialchars($editingAppointment['name']); ?></h3>
            <form method="post">
                <input type="hidden" name="action" value="update_mechanic">
                <input type="hidden" name="appointment_id" value="<?php echo (int)$editingAppointment['id']; ?>">
                <label>Mechanic:
                    <select name="mechanic_id" required>
                        <option value="">-- Select mechanic --</option>
                        <?php foreach ($mechanics as $mech): ?>
                            <option value="<?php echo (int)$mech['id']; ?>" <?php echo ((int)$mech['id'] === (int)$editingAppointment['mechanic_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mech['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Save</button>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="margin-left:8px;">Cancel</a>
            </form>
        </section>
    <?php endif; ?>
    <?php if (empty($appointments)): ?>
        <p>No appointments yet.</p>
    <?php else: ?>
        <!-- Full appointment list with edit links for reassignment. -->
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Car</th>
                    <th>Date</th>
                    <th>Mechanic</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['car_license']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'])); ?></td>
                        <td><?php echo htmlspecialchars($appointment['mechanic_name']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?edit=<?php echo (int)$appointment['id']; ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
