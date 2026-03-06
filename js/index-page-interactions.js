// Small client-side helpers for index.php auth and booking views.
document.addEventListener('DOMContentLoaded', function () {
	// Auto-focus username on login/create-account screens.
	var usernameInput = document.querySelector('input[name="username"]');
	if (usernameInput) {
		usernameInput.focus();
	}

	// Prevent selecting past dates for appointment booking.
	var appointmentDateInput = document.querySelector('input[name="appointment_date"]');
	if (appointmentDateInput) {
		var today = new Date();
		var month = String(today.getMonth() + 1).padStart(2, '0');
		var day = String(today.getDate()).padStart(2, '0');
		var minDate = today.getFullYear() + '-' + month + '-' + day;
		appointmentDateInput.setAttribute('min', minDate);
	}

	// Confirm sign-out to avoid accidental logout.
	var logoutForm = document.querySelector('.logout-user-form');
	if (logoutForm) {
		logoutForm.addEventListener('submit', function (event) {
			var confirmed = window.confirm('Are you sure you want to sign out?');
			if (!confirmed) {
				event.preventDefault();
			}
		});
	}

	// Prevent accidental double-submit on auth/booking forms.
	var forms = document.querySelectorAll('form');
	forms.forEach(function (form) {
		form.addEventListener('submit', function () {
			var submitButton = form.querySelector('button[type="submit"]');
			if (!submitButton) {
				return;
			}

			submitButton.disabled = true;
			if (submitButton.textContent) {
				submitButton.dataset.originalText = submitButton.textContent;
				submitButton.textContent = 'Please wait...';
			}
		});
	});
});
