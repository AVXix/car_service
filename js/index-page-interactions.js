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

	// Show or hide the appointment form on demand.
	var toggleAppointmentFormButton = document.getElementById('toggle-appointment-form-btn');
	var appointmentModal = document.getElementById('appointment-modal');
	var appointmentFormPanel = document.getElementById('appointment-form');
	var closeAppointmentFormButton = document.getElementById('close-appointment-form-btn');
	var openAppointmentModal = function () {
		if (!appointmentModal || !appointmentFormPanel || !toggleAppointmentFormButton) {
			return;
		}

		appointmentModal.classList.remove('is-hidden');
		appointmentModal.setAttribute('aria-hidden', 'false');
		toggleAppointmentFormButton.setAttribute('aria-expanded', 'true');
		var firstInput = appointmentFormPanel.querySelector('input:not([type="hidden"]), select, textarea');
		if (firstInput) {
			firstInput.focus();
		}
	};
	var closeAppointmentModal = function () {
		if (!appointmentModal || !toggleAppointmentFormButton) {
			return;
		}

		appointmentModal.classList.add('is-hidden');
		appointmentModal.setAttribute('aria-hidden', 'true');
		toggleAppointmentFormButton.setAttribute('aria-expanded', 'false');
		toggleAppointmentFormButton.focus();
	};
	if (toggleAppointmentFormButton && appointmentModal && appointmentFormPanel) {
		var shouldOpenOnLoad = appointmentModal.getAttribute('data-open-on-load') === 'true';
		if (!shouldOpenOnLoad) {
			appointmentModal.classList.add('is-hidden');
			appointmentModal.setAttribute('aria-hidden', 'true');
			toggleAppointmentFormButton.setAttribute('aria-expanded', 'false');
		}

		toggleAppointmentFormButton.addEventListener('click', function () {
			openAppointmentModal();
		});

		if (closeAppointmentFormButton) {
			closeAppointmentFormButton.addEventListener('click', function () {
				closeAppointmentModal();
			});
		}

		appointmentModal.addEventListener('click', function (event) {
			if (event.target && event.target.getAttribute('data-close-appointment-modal') === 'true') {
				closeAppointmentModal();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !appointmentModal.classList.contains('is-hidden')) {
				closeAppointmentModal();
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
