// resources/js/admin/settings/password-change.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Password change settings scripts loaded.');

    // Aquí puedes añadir lógica específica para el formulario de cambio de contraseña si es necesario.
    // Por ejemplo, validación en tiempo real de la coincidencia de contraseñas.

    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('new_password_confirmation');

    function validateNewPasswords() {
        if (newPasswordInput.value !== confirmPasswordInput.value && confirmPasswordInput.value !== '') {
            confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden.');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
        confirmPasswordInput.reportValidity(); // Dispara la validación del navegador
    }

    if (newPasswordInput && confirmPasswordInput) {
        newPasswordInput.addEventListener('input', validateNewPasswords);
        confirmPasswordInput.addEventListener('input', validateNewPasswords);
    }
});