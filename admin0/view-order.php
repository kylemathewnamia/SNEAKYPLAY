<?php
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

$admin_name = htmlspecialchars($admin['name']);

// Get order ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);

// Get order details
$order_stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email, u.contact_no, u.address
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

// Get order items
$items_stmt = $pdo->prepare("
    SELECT oi.*, p.product_name, p.price, (oi.quantity * p.price) as subtotal
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$items_total = 0;
foreach ($order_items as $item) {
    $items_total += $item['subtotal'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    // Update order status
    $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $update_stmt->execute([$new_status, $order_id]);

    // Log the action (if admin_audit_log table exists)
    try {
        $log_stmt = $pdo->prepare("INSERT INTO admin_audit_log (admin_id, action, details, ip_address, user_agent) 
                                  VALUES (?, ?, ?, ?, ?)");
        $log_stmt->execute([
            $admin_id,
            'UPDATE_ORDER_STATUS',
            "Updated order #{$order_id} from '{$order['status']}' to '{$new_status}'",
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (Exception $e) {
        // Table might not exist, ignore error
    }

    $_SESSION['success_message'] = "Order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . " status updated to " . ucfirst($new_status) . "!";
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?> | SneakyPlay</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
    <style>
        .edit-order-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 16px;
        }

        .order-info-section {
            background: #f9fafb;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item h4 {
            color: #374151;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-item p {
            color: #111827;
            font-size: 16px;
            font-weight: 500;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0 30px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .order-items-table th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .order-items-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        select.form-control {
            cursor: pointer;
            background: white;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f3f4f6;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Admin Navigation - Same as orders.php -->
    <nav class="admin-nav">
        <div class="admin-nav-container">
            <div class="admin-logo">
                <i class="fas fa-gamepad"></i>
                <span>SneakyPlay Admin</span>
            </div>

            <!-- Navigation Menu -->
            <div class="admin-nav-menu">
                <a href="admin.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="product.php" class="nav-link">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                </a>
                <a href="orders.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </div>

            <!-- User & Logout -->
            <div class="admin-user">
                <div class="user-circle">
                    <span class="user-initial"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></span>
                </div>
                <span class="user-welcome-text">Welcome, <?php echo $admin_name; ?></span>
                <a href="logout.php" class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-container">
            <div class="edit-order-container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-edit"></i> Edit Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h1>
                    <p>Update order status and manage order information</p>
                </div>

                <!-- Order Information -->
                <div class="order-info-section">
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <h4>Customer Name</h4>
                            <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <h4>Email Address</h4>
                            <p><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                        <div class="info-item">
                            <h4>Contact Number</h4>
                            <p><?php echo !empty($order['contact_no']) ? htmlspecialchars($order['contact_no']) : 'Not provided'; ?></p>
                        </div>
                        <div class="info-item">
                            <h4>Order Date</h4>
                            <p><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
                        </div>
                        <div class="info-item">
                            <h4>Shipping Address</h4>
                            <p><?php echo !empty($order['address']) ? htmlspecialchars($order['address']) : 'Not provided'; ?></p>
                        </div>
                        <div class="info-item">
                            <h4>Current Status</h4>
                            <p><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo strtoupper($order['status']); ?></span></p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <h3><i class="fas fa-box"></i> Ordered Products</h3>
                <div class="table-container">
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($order_items)): ?>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><strong>₱<?php echo number_format($item['subtotal'], 2); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: #f9fafb;">
                                    <td colspan="3" style="text-align: right; font-weight: 600;">Items Total:</td>
                                    <td><strong>₱<?php echo number_format($items_total, 2); ?></strong></td>
                                </tr>
                                <tr style="background: #f9fafb;">
                                    <td colspan="3" style="text-align: right; font-weight: 600;">Shipping Fee:</td>
                                    <td>₱<?php echo number_format($order['shipping_fee'] ?? 0, 2); ?></td>
                                </tr>
                                <tr style="background: #f9fafb;">
                                    <td colspan="3" style="text-align: right; font-weight: 600;">Tax:</td>
                                    <td>₱<?php echo number_format($order['tax'] ?? 0, 2); ?></td>
                                </tr>
                                <tr style="background: #f3f4f6; font-size: 16px;">
                                    <td colspan="3" style="text-align: right; font-weight: 700;">Total Amount:</td>
                                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No products found for this order.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                        <textarea name="notes" id="notes" class="form-control"
                            placeholder="Add any notes about this status change, shipping updates, or special instructions..."></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Order Status
                        </button>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

</html>