<?php
session_start();

// ========== SECURITY ENHANCEMENTS ==========
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Initialize login attempts counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
}

// Database configuration
$host = 'localhost';
$dbname = 'sneakysheets';
$username = 'root';
$password = '';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("System error. Please try again later.");
}

// ========== SECURE LOGIN SYSTEM ==========
// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {

    // ========== BRUTE FORCE PROTECTION ==========
    if ($_SESSION['login_attempts'] >= 5) {
        $lockout_time = 900; // 15 minutes
        if (time() - $_SESSION['first_attempt_time'] < $lockout_time) {
            $remaining = $lockout_time - (time() - $_SESSION['first_attempt_time']);
            $error = "Too many failed attempts. Please try again in " . ceil($remaining / 60) . " minutes.";
        } else {
            // Reset after lockout period
            $_SESSION['login_attempts'] = 0;
            $_SESSION['first_attempt_time'] = time();
        }
    }

    // Check admin login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {

        // ========== CSRF PROTECTION ==========
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Security token invalid. Please refresh the page.";
        } else {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            // Check if admins table exists
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'admins'")->fetch();

            if ($tableCheck) {
                // Admins table exists - use database authentication
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active'");
                $stmt->execute([$email]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    // ========== SUCCESSFUL LOGIN ==========
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = htmlspecialchars($admin['name']);
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['login_attempts'] = 0;

                    session_regenerate_id(true);

                    header('Location: admin.php');
                    exit;
                } else {
                    $error = "Invalid email or password!";
                    $_SESSION['login_attempts']++;
                }
            } else {
                // Admins table doesn't exist, use default credentials
                $default_email = "admin@sneakyplay.com";
                $default_password = "admin123";

                if ($email === $default_email && $password === $default_password) {
                    $_SESSION['admin_id'] = 1;
                    $_SESSION['admin_name'] = "Admin";
                    $_SESSION['admin_email'] = $default_email;
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['login_attempts'] = 0;

                    session_regenerate_id(true);

                    header('Location: admin.php');
                    exit;
                } else {
                    $error = "Invalid email or password!";
                    $_SESSION['login_attempts']++;
                }
            }

            if ($_SESSION['login_attempts'] == 1) {
                $_SESSION['first_attempt_time'] = time();
            }
        }
    }

    // ========== GENERATE CSRF TOKEN ==========
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Show admin login form
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - SneakyPlay</title>
        <link rel="stylesheet" href="../assets/css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="icon" type="image/png" href="../assets/image/logo.png">
    </head>

    <body class="admin-login-page">
        <div class="admin-login-container">
            <div class="admin-login-box">
                <div class="admin-login-header">
                    <i class="fas fa-shield-alt"></i>
                    <h2>Admin Login</h2>
                    <p>SneakyPlay Admin Dashboard</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($_SESSION['login_attempts'] >= 3): ?>
                    <div class="security-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo (5 - $_SESSION['login_attempts']); ?> attempts remaining
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="admin_login" value="1">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Admin Email" required value="admin@sneakyplay.com">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" required value="admin123">
                    </div>
                    <button type="submit" class="btn-admin-login">
                        <i class="fas fa-sign-in-alt"></i> Login as Admin
                    </button>
                </form>


                <div class="admin-login-footer">
                    <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Main Site</a></p>
                </div>
            </div>
        </div>
    </body>

    </html>
<?php
    exit;
}

// ========== ADMIN DASHBOARD - LOGGED IN ==========

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'] ?? 0,
    'total_products' => $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'] ?? 0,
    'total_orders' => $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'] ?? 0,
    'total_revenue' => $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'paid'")->fetch()['total'] ?? 0,
    'total_reviews' => $pdo->query("SELECT COUNT(*) as count FROM reviews")->fetch()['count'] ?? 0,
];

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, u.name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 8
");
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Get low stock products
$stmt = $pdo->prepare("
    SELECT p.*, s.quantity 
    FROM products p 
    JOIN stock s ON p.product_id = s.product_id 
    WHERE s.quantity < 20 
    ORDER BY s.quantity ASC 
    LIMIT 8
");
$stmt->execute();
$lowStock = $stmt->fetchAll();

// Get recent users
$recentUsers = $pdo->query("
    SELECT * FROM users 
    ORDER BY date_registered DESC 
    LIMIT 8
")->fetchAll();

// Get top selling products
$stmt = $pdo->prepare("
    SELECT p.product_name, COUNT(oi.order_item_id) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->execute();
$topProducts = $stmt->fetchAll();

// Get latest reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, p.product_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN products p ON r.product_id = p.product_id
    ORDER BY r.review_date DESC
    LIMIT 5
");
$stmt->execute();
$recentReviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SneakyPlay</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
</head>

<body class="admin-dashboard">
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="admin-nav-container">
            <div class="admin-logo">
                <i class="fas fa-gamepad"></i>
                <span>SneakyPlay Admin</span>
            </div>

            <div class="admin-nav-menu">
                <a href="admin.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="product.php" class="nav-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i> Users
                </a>

            </div>

            <div class="admin-user">
                <div class="user-circle">
                    <span class="user-initial"><?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?></span>
                </div>
                <span class="user-welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening with your store.</p>
                <small style="color: #666;">
                    <i class="fas fa-shield-alt"></i> Last login: <?php echo date('Y-m-d H:i:s'); ?>
                </small>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Users</h3>
                        <p class="stat-value"><?php echo htmlspecialchars($stats['total_users']); ?></p>
                        <p class="stat-change">+<?php echo max(0, $stats['total_users'] - 14); ?> this month</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Products</h3>
                        <p class="stat-value"><?php echo htmlspecialchars($stats['total_products']); ?></p>
                        <p class="stat-change"><?php echo htmlspecialchars($stats['total_products']); ?> in stock</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Orders</h3>
                        <p class="stat-value"><?php echo htmlspecialchars($stats['total_orders']); ?></p>
                        <p class="stat-change">₱<?php echo number_format($stats['total_revenue'], 2); ?> revenue</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon reviews">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Reviews</h3>
                        <p class="stat-value"><?php echo htmlspecialchars($stats['total_reviews']); ?></p>
                        <p class="stat-change">+<?php echo max(0, $stats['total_reviews'] - 15); ?> this month</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Recent Orders</h2>
                    <a href="orders.php" class="view-all">View All</a>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['order_id'] ?? ''); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['name'] ?? ''); ?></strong><br>
                                            <small><?php echo htmlspecialchars($order['email'] ?? ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['order_date']))); ?></td>
                                        <td>₱<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo htmlspecialchars($order['status'] ?? ''); ?>">
                                                <?php echo htmlspecialchars(ucfirst($order['status'] ?? '')); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No orders yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock & Recent Users -->
            <div class="dashboard-grid">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h2>
                    </div>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lowStock)): ?>
                                    <?php foreach ($lowStock as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></td>
                                            <td>₱<?php echo number_format($product['price'] ?? 0, 2); ?></td>
                                            <td>
                                                <span class="stock-badge <?php echo ($product['quantity'] ?? 0) < 10 ? 'critical' : 'low'; ?>">
                                                    <?php echo htmlspecialchars($product['quantity'] ?? 0); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="product.php?restock=<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>" class="btn-small">
                                                    Restock
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">All products are well stocked! 🎉</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-plus"></i> Recent Users</h2>
                    </div>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentUsers)): ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($user['date_registered']))); ?></td>
                                            <td>
                                                <span class="status-badge active">Active</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No users yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Products & Recent Reviews -->
            <div class="dashboard-grid">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-chart-line"></i> Top Selling Products</h2>
                    </div>
                    <div class="top-products">
                        <?php if (!empty($topProducts)): ?>
                            <?php foreach ($topProducts as $index => $product): ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <div class="product-rank">#<?php echo $index + 1; ?></div>
                                        <div class="product-name"><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></div>
                                    </div>
                                    <div class="product-sales">
                                        <?php echo htmlspecialchars($product['total_sold'] ?? 0); ?> sold
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <p>No sales data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-section">
                    <div class="section-header">
                        <h2><i class="fas fa-comment"></i> Recent Reviews</h2>
                    </div>
                    <div class="reviews-list">
                        <?php if (!empty($recentReviews)): ?>
                            <?php foreach ($recentReviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-user">
                                            <strong><?php echo htmlspecialchars($review['user_name'] ?? ''); ?></strong>
                                            <small>on <?php echo htmlspecialchars($review['product_name'] ?? ''); ?></small>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= ($review['rating'] ?? 0) ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="review-comment">
                                        <?php echo htmlspecialchars($review['comment'] ?? ''); ?>
                                    </div>
                                    <div class="review-date">
                                        <?php echo htmlspecialchars(date('M d, Y', strtotime($review['review_date']))); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <p>No reviews yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-logout after 30 minutes of inactivity
        let idleTimeout = 30 * 60 * 1000; // 30 minutes
        let idleTimer;

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                if (confirm('Session expired. Logout?')) {
                    window.location.href = 'logout.php';
                }
            }, idleTimeout);
        }

        // Reset timer on user activity
        ['click', 'mousemove', 'keypress'].forEach(event => {
            document.addEventListener(event, resetIdleTimer);
        });

        resetIdleTimer();
    </script>
</body>

</html>