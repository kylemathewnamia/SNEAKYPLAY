<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit();
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'sneakysheets';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$product_id = (int)$_POST['product_id'];
$user_id = (int)$_SESSION['user_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Check if product exists
$stmt = $pdo->prepare("SELECT p.*, s.quantity as stock_qty 
                       FROM products p 
                       LEFT JOIN stock s ON p.product_id = s.product_id 
                       WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

if (($product['stock_qty'] ?? 0) < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit();
}

// Get or create cart
$stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch();

if (!$cart) {
    $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $pdo->lastInsertId();
} else {
    $cart_id = $cart['cart_id'];
}

// Check if item already in cart
$stmt = $pdo->prepare("SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?");
$stmt->execute([$cart_id, $product_id]);
$existing_item = $stmt->fetch();

if ($existing_item) {
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_item_id = ?");
    $stmt->execute([$quantity, $existing_item['cart_item_id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$cart_id, $product_id, $quantity]);
}

// Get updated cart count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart_items WHERE cart_id = ?");
$stmt->execute([$cart_id]);
$cart_count = $stmt->fetch()['count'];

echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'cart_count' => $cart_count,
    'product_name' => $product['product_name']
]);
