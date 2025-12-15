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
document.getElementById('register-form').addEventListener('submit', function (event) {
    const name = document.getElementById('register-name');
    const email = document.getElementById('register-email');
    const password = document.getElementById('register-password');
    const confirmPassword = document.getElementById('register-confirm-password');
    const terms = document.getElementById('terms');

    // Reset validation
    name.classList.remove('is-invalid');
    email.classList.remove('is-invalid');
    password.classList.remove('is-invalid');
    confirmPassword.classList.remove('is-invalid');
    terms.classList.remove('is-invalid');

    let isValid = true;

    // Name validation
    if (name.value.trim().length < 2) {
        name.classList.add('is-invalid');
        isValid = false;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    }

    // Password validation
    if (password.value.length < 6) {
        password.classList.add('is-invalid');
        isValid = false;
    }

    // Confirm password validation
    if (password.value !== confirmPassword.value) {
        confirmPassword.classList.add('is-invalid');
        isValid = false;
    }

    // Terms validation
    if (!terms.checked) {
        terms.classList.add('is-invalid');
        isValid = false;
    }

    if (!isValid) {
        event.preventDefault();
    }
});

// Password strength indicator (optional enhancement)
document.getElementById('register-password').addEventListener('input', function () {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    updatePasswordStrengthIndicator(strength);
});

function calculatePasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    // Implement password strength indicator UI
    // This is a placeholder for implementation
}