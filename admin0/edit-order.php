<?php
// edit-order.php
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

// Get order details
$order_stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ?
");
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    // Update order status
    $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $update_stmt->execute([$new_status, $order_id]);

    // Log the action
    $log_stmt = $pdo->prepare("INSERT INTO admin_audit_log (admin_id, action, details, ip_address, user_agent) 
                              VALUES (?, ?, ?, ?, ?)");
    $log_stmt->execute([
        $admin_id,
        'UPDATE_ORDER_STATUS',
        "Updated order #{$order_id} from '{$order['status']}' to '{$new_status}'",
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);

    $_SESSION['success_message'] = "Order #{$order_id} status updated to " . ucfirst($new_status) . "!";
    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?> - SneakyPlay</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <style>
        .edit-order-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #14141e;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666;
        }

        .edit-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
        }

        .order-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item label {
            font-weight: 600;
            color: #666;
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }

        .info-item span {
            color: #14141e;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b6b;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
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

        .btn-primary {
            background: #ff6b6b;
            color: white;
        }

        .btn-primary:hover {
            background: #ff5252;
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

    <main class="edit-order-container">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Edit Order</h1>
            <p>Update order status and information</p>
        </div>

        <div class="edit-card">
            <!-- Order Information -->
            <div class="order-info">
                <h3><i class="fas fa-info-circle"></i> Order Details</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Order ID</label>
                        <span>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Customer Name</label>
                        <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Customer Email</label>
                        <span><?php echo htmlspecialchars($order['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Order Date</label>
                        <span><?php echo date('F d, Y', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Amount</label>
                        <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Current Status</label>
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="status"><i class="fas fa-exchange-alt"></i> Update Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes"><i class="fas fa-sticky-note"></i> Admin Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" placeholder="Add any notes about this status change..."></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Order
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