document.addEventListener("DOMContentLoaded", function() {
    const toggles = document.querySelectorAll('[data-show-password]');

    toggles.forEach(toggle => {
        const inputId = toggle.dataset.showPassword;
        const passwordInput = document.getElementById(inputId);

        toggle.addEventListener('change', () => {
            passwordInput.type = toggle.checked ? 'text' : 'password';
        });
    });
});
