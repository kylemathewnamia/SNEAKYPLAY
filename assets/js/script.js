// Mobile menu toggle
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const navLinks = document.querySelector('.nav-links');
const authButtons = document.querySelector('.auth-buttons');

mobileMenuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    authButtons.classList.toggle('active');

    // Change icon between menu and close
    const icon = mobileMenuToggle.querySelector('i');
    if (icon.classList.contains('fa-bars')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            // Close mobile menu if open
            navLinks.classList.remove('active');
            authButtons.classList.remove('active');

            // Reset mobile menu icon
            const icon = mobileMenuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');

            // Scroll to target
            const offsetTop = targetElement.offsetTop - 80; // Account for fixed header
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// Add scroll effect to header
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.style.backgroundColor = 'rgba(20, 20, 30, 0.98)';
    } else {
        header.style.backgroundColor = 'rgba(20, 20, 30, 0.95)';
    }
});

// Product add to cart functionality
const productButtons = document.querySelectorAll('.product-btn');
productButtons.forEach(button => {
    button.addEventListener('click', function () {
        const productCard = this.closest('.product-card');
        const productName = productCard.querySelector('h3').textContent;

        // Visual feedback
        this.textContent = 'Added!';
        this.style.backgroundColor = '#4caf50';

        setTimeout(() => {
            this.textContent = 'Add to Cart';
            this.style.backgroundColor = '#ff6b6b';
        }, 2000);

        // Here you would typically add the product to a cart
        console.log(`Added ${productName} to cart`);
    });
});

// Newsletter form submission
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const emailInput = this.querySelector('input[type="email"]');
        const email = emailInput.value;

        if (email) {
            // Visual feedback
            const button = this.querySelector('button');
            const originalText = button.textContent;
            button.textContent = 'Subscribed!';
            button.style.backgroundColor = '#4caf50';

            // Clear input
            emailInput.value = '';

            // Reset button after delay
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '#ff6b6b';
            }, 2000);

            // Here you would typically send the email to your backend
            console.log(`Subscribed ${email} to newsletter`);
        }
    });
}

// Intersection Observer for fade-in animations
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Apply fade-in animation to sections
document.querySelectorAll('.feature-card, .product-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// Add this to your existing script
document.querySelector('.login-btn').addEventListener('click', function () {
    document.getElementById('authModal').classList.add('active');
});

document.querySelector('.register-btn').addEventListener('click', function () {
    document.getElementById('authModal').classList.add('active');
    toggleForm(); // Switch to register form
});