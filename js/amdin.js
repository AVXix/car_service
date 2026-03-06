document.addEventListener('DOMContentLoaded', function () {
    var slotInputs = document.querySelectorAll('.slot-total-input');

    slotInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.form) {
                input.form.submit();
            }
        });
    });
});
