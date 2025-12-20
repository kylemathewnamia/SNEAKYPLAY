<?php
// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'sneakysheets';
$username = 'root';
$password = '';

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_no = trim($_POST['contact_no']);
    $address = trim($_POST['address']);

    // Validate form data
    $errors = [];

    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists";
        }
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    // Validate confirm password
    if (empty($confirm_password)) {
        $errors[] = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Validate contact number
    if (!empty($contact_no) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $contact_no)) {
        $errors[] = "Invalid contact number format";
    }

    // Validate address
    if (!empty($address) && strlen($address) < 5) {
        $errors[] = "Address must be at least 5 characters";
    }

    // Validate terms
    if (!isset($_POST['terms'])) {
        $errors[] = "You must agree to the terms and conditions";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare insert statement - updated with all fields
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, contact_no, address, date_registered) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $hashed_password, $contact_no, $address]);

            // Registration successful
            $_SESSION['registration_success'] = true;
            header("Location: register.php?success=1");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage(); // Show error for debugging
        }
    }

    // If there are errors, store them in session
    $_SESSION['registration_errors'] = $errors;
}

// Check for registration success
$registration_success = isset($_GET['success']) && $_GET['success'] == 1;

// Get any registration errors
$registration_errors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];
unset($_SESSION['registration_errors']);

// Pre-populate form data
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$contact_no = isset($_POST['contact_no']) ? $_POST['contact_no'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SneakyPlay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="icon" type="image/png" href="assets/image/logo.png">

</head>

<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">SneakyPlay</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Register Content -->
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-container">
                    <div class="auth-header text-center">
                        <h2 class="auth-title">Create Account</h2>
                        <p class="auth-subtitle">Join the SneakyPlay community today</p>
                    </div>

                    <!-- Display Errors -->
                    <?php if (!empty($registration_errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading">Registration Failed</h5>
                            <ul class="mb-0">
                                <?php foreach ($registration_errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Display Success -->
                    <?php if ($registration_success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading">Registration Successful!</h5>
                            <p class="mb-0">Your account has been created. You can now <a href="login.php" class="alert-link">login here</a>.</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="auth-body">
                        <!-- Register Form -->
                        <form id="register-form" class="auth-form" action="register.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="register-name" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required>
                                <label for="register-name">Full Name</label>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="register-email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                                <label for="register-email">Email address</label>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <!-- NEW: Contact Number Field -->
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" id="register-contact" name="contact_no" placeholder="Contact Number" value="<?php echo htmlspecialchars($contact_no); ?>">
                                <label for="register-contact">Contact Number (Optional)</label>
                                <div class="invalid-feedback">Please enter a valid contact number.</div>
                            </div>

                            <!-- Address Field -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="register-address" name="address" placeholder="Address" style="height: 100px"><?php echo htmlspecialchars($address); ?></textarea>
                                <label for="register-address">Address (Optional)</label>
                                <div class="invalid-feedback">Please enter a valid address.</div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="register-password" name="password" placeholder="Password" required>
                                <label for="register-password">Password</label>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('register-password')"></i>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" placeholder="Confirm Password" required>
                                <label for="register-confirm-password">Confirm Password</label>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('register-confirm-password')"></i>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">You must agree to the terms.</div>
                            </div>

                            <button type="submit" class="auth-button w-100">Create Account</button>
                        </form>

                        <div class="divider mt-4">
                            <span>or continue with</span>
                        </div>

                        <div class="social-login mt-4">
                            <a href="#" class="social-btn">
                                <i class="fab fa-google"></i>
                                <span>Google</span>
                            </a>
                            <a href="#" class="social-btn">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </a>
                            <a href="#" class="social-btn">
                                <i class="fab fa-twitter"></i>
                                <span>Twitter</span>
                            </a>
                        </div>
                    </div>

                    <div class="auth-footer text-center mt-4">
                        <p>Already have an account? <a href="login.php">Sign in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/register.js"></script>
</body>

</html>