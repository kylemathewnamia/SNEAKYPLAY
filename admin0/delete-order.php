<?php
// delete-order.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'sneakysheets';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get admin data
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: ../login.php');
    exit();
}

// Get order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);

// Check if order exists
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // First delete order items
        $delete_items_stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $delete_items_stmt->execute([$order_id]);

        // Then delete the order
        $delete_order_stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
        $delete_order_stmt->execute([$order_id]);

        // Log the action
        $log_stmt = $pdo->prepare("INSERT INTO admin_audit_log (admin_id, action, details, ip_address, user_agent) 
                                  VALUES (?, ?, ?, ?, ?)");
        $log_stmt->execute([
            $admin_id,
            'DELETE_ORDER',
            "Deleted order #{$order_id} (Total: ₱" . number_format($order['total_amount'], 2) . ")",
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        $_SESSION['success_message'] = "Order #{$order_id} deleted successfully!";
    }

    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?> - SneakyPlay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <style>
        .delete-container {
            max-width: 600px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .delete-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: center;
        }

        .warning-icon {
            font-size: 4rem;
            color: #f44336;
            margin-bottom: 1rem;
        }

        .delete-card h1 {
            color: #14141e;
            margin-bottom: 1rem;
        }

        .delete-card p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .order-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .order-details h3 {
            color: #14141e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: #14141e;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="logo-container">
                <div class="logo">SneakyPlay Admin</div>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="orders.php" class="nav-link active"><i class="fas fa-shopping-bag"></i> Orders</a>
                <a href="products.php" class="nav-link"><i class="fas fa-gamepad"></i> Products</a>
                <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a>
                <a href="reviews.php" class="nav-link"><i class="fas fa-star"></i> Reviews</a>
            </div>
            <div class="auth-buttons">
                <span class="welcome-user"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin['name']); ?></span>
                <a href="../logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <main class="delete-container">
        <div class="delete-card">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <h1>Confirm Deletion</h1>
            <p>Are you sure you want to delete this order? This action cannot be undone and will permanently remove all order data including order items.</p>

            <div class="order-details">
                <h3><i class="fas fa-shopping-bag"></i> Order Information</h3>
                <div class="detail-item">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <form method="POST" action="">
                <div class="btn-group">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete Order
                    </button>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Admin Panel</h3>
                <p>SneakyPlay Admin Dashboard</p>
                <p>Version 1.0.0</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="index.php" style="color: #ff6b6b;">Dashboard</a></p>
                <p><a href="orders.php" style="color: #ff6b6b;">Orders</a></p>
                <p><a href="products.php" style="color: #ff6b6b;">Products</a></p>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <p>Admin: <?php echo htmlspecialchars($admin['name']); ?></p>
                <p>Role: <?php echo ucfirst($admin['role']); ?></p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> SneakyPlay Admin Panel. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>