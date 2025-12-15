// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation
document.getElementById('login-form').addEventListener('submit', function (event) {
    const email = document.getElementById('login-email');
    const password = document.getElementById('login-password');

    // Reset validation
    email.classList.remove('is-invalid');
    password.classList.remove('is-invalid');

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        email.classList.add('is-invalid');
        event.preventDefault();
    }

    // Password validation
    if (password.value.length < 6) {
        password.classList.add('is-invalid');
        event.preventDefault();
    }
});

// Remember me functionality
document.getElementById('remember-me').addEventListener('change', function () {
    if (this.checked) {
        localStorage.setItem('rememberEmail', document.getElementById('login-email').value);
    } else {
        localStorage.removeItem('rememberEmail');
    }
});

// Load remembered email
window.onload = function () {
    const rememberedEmail = localStorage.getItem('rememberEmail');
    if (rememberedEmail) {
        document.getElementById('login-email').value = rememberedEmail;
        document.getElementById('remember-me').checked = true;
    }
};  