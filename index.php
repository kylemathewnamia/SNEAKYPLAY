<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SneakyPlay - Premium Gaming</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/image/logo.png">
</head>

<body>
    <!-- Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="logo-container">
                <div class="logo">SneakyPlay</div>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Shop/Products</a>
            </div>
            <div class="auth-buttons">
                <a href="login.php" class="login-btn">Login</a>
                <a href="register.php" class="register-btn">Register</a>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Unleash Your Gaming Potential</h1>
            <p class="hero-description">Discover premium gaming designed for champions. Elevate your gameplay with
                SneakyPlay's curated collection.</p>
            <button class="cta-button">Explore Now</button>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80"
                alt="Gaming Setup">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <h3>Fast Delivery</h3>
            <p>Get your gaming gear delivered to your door in record time with our express shipping options.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>Easy Shopping</h3>
            <p>Browse our intuitive catalog and find exactly what you need with our streamlined shopping experience.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Secure Payment</h3>
            <p>Shop with confidence using our encrypted payment system that protects your sensitive information.</p>
        </div>
    </section>

    <!-- Product Highlights -->
    <section id="shop" class="product-highlights">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <div class="product-card">
                <div class="product-image">
                    <img src="assets/image/mouse.jpg" alt="Gaming Mouse">
                </div>
                <h3>Pro Gaming Mouse</h3>
                <p>Precision engineered for competitive gameplay with customizable RGB lighting.</p>
                <div class="product-price"> ₱299.99</div>
                <button class="product-btn">Add to Cart</button>
            </div>
            <div class="product-card">
                <div class="product-image">
                    <img src="assets/image/mboard.jpg" alt="Mechanical Keyboard">
                </div>
                <h3>Mechanical Keyboard</h3>
                <p>Tactile switches with customizable backlighting for the ultimate typing experience.</p>
                <div class="product-price"> ₱599.99</div>
                <button class="product-btn">Add to Cart</button>
            </div>
            <div class="product-card">
                <div class="product-image">
                    <img src="assets/image/jbl.jpg" alt="Gaming Headset">
                </div>
                <h3>Pro Gaming Headset</h3>
                <p>Immersive 7.1 surround sound with noise-canceling microphone for crystal clear communication.</p>
                <div class="product-price"> ₱359.99</div>
                <button class="product-btn">Add to Cart</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: sneakymail@gmail.com</p>
                <p>Phone: +63 9322355510</p>
                <p>Address: 123 Tech, Biringan City</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe for exclusive offers and new product launches.</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Your email address">
                    <button>Subscribe</button>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 SneakyPlay. All rights reserved.</p>
        </div>
        <!-- navigation or footer -->
        <div class="admin-access">
            <a href="admin0/admin.php" class="admin-link">
                <i class="fas fa-shield-alt"></i> Admin Access
            </a>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>

</html>