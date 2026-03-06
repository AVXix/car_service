// Admin page behavior:
// - Auto-submit slot updates when values change.
// - Prevent negative/invalid slot values in the input.
// - Ask for confirmation before logging out.
document.addEventListener('DOMContentLoaded', function () {
    var slotInputs = document.querySelectorAll('.slot-total-input');
    var logoutForm = document.querySelector('.logout-form');

    // Submit each inline slot form as soon as its value changes.
    slotInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var parsedValue = parseInt(input.value, 10);
            if (Number.isNaN(parsedValue) || parsedValue < 0) {
                input.value = '0';
            }

            if (input.form) {
                input.form.submit();
            }
        });
    });

    // Prevent accidental logout with a confirmation prompt.
    if (logoutForm) {
        logoutForm.addEventListener('submit', function (event) {
            var confirmed = window.confirm('Are you sure you want to log out?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    }
});
