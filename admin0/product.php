<?php
session_start();

// Get user name
$user_name = 'Admin';

// Check different session variables
if (isset($_SESSION['admin_name']) && !empty($_SESSION['admin_name'])) {
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $user_name = htmlspecialchars($_SESSION['username']);
    } elseif (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
        $user_name = htmlspecialchars($_SESSION['user_name']);
    } elseif (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
        $user_name = htmlspecialchars($_SESSION['email']);
    } else {
        $user_name = 'User'; // Default
    }
}

ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products | SneakyPlay</title>
    <link rel="stylesheet" href="../assets/css/admin_shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../assets/image/logo.png">
</head>

<body>
    <!-- Admin Dashboard Header -->
    <nav class="admin-nav">
        <div class="admin-nav-container">
            <!-- Logo -->
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
                <a href="product" class="nav-link active">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                </a>
                <a href="orders.php" class="nav-link">
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
                    <span class="user-initial"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></span>
                </div>
                <span class="user-welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-container">
            <!-- Database Connection -->
            <?php
            $host = "127.0.0.1";
            $username = "root";
            $password = "";
            $database = "sneakysheets";

            $conn = mysqli_connect($host, $username, $password, $database);

            if (!$conn) {
                echo "<div class='error'>Database connection failed</div>";
                exit();
            }
            ?>

            <!-- Search and Filter Section -->
            <section class="shop-controls">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search products..." onkeyup="searchProducts()">
                    <button onclick="searchProducts()"><i class="fas fa-search"></i></button>
                </div>

                <!-- Organized Category Filter -->
                <div class="category-filter-section">
                    <h3><i class="fas fa-filter"></i> Filter by Category</h3>
                    <div class="category-filter">
                        <button class="filter-btn active" data-category="all">
                            <i class="fas fa-th-large"></i> All Products
                        </button>
                        <?php
                        $cat_query = "SELECT * FROM categories ORDER BY category_name";
                        $cat_result = mysqli_query($conn, $cat_query);

                        if (mysqli_num_rows($cat_result) > 0) {
                            while ($cat = mysqli_fetch_assoc($cat_result)) {
                                echo '<button class="filter-btn" data-category="' . $cat['categories_id'] . '">
                                    <i class="fas fa-tag"></i> '
                                    . htmlspecialchars($cat['category_name']) .
                                    '</button>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Products Grid -->
            <section class="products-section">
                <h2><i class="fas fa-list"></i> Available Products</h2>

                <div class="products-grid" id="productsGrid">
                    <?php
                    $sql = "SELECT p.*, s.quantity as stock_qty, c.category_name 
                        FROM products p 
                        LEFT JOIN stock s ON p.product_id = s.product_id
                        LEFT JOIN categories c ON p.categories_id = c.categories_id
                        WHERE s.quantity > 0
                        ORDER BY p.product_id DESC";

                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($product = mysqli_fetch_assoc($result)) {
                            // Stock status based on quantity
                            $stock_qty = $product['stock_qty'];
                            $stock_class = "in-stock";

                            if ($stock_qty > 20) {
                                $stock_status = "Available: $stock_qty units";
                            } elseif ($stock_qty > 10) {
                                $stock_status = "Available: $stock_qty units";
                                $stock_class = "medium-stock";
                            } elseif ($stock_qty > 0) {
                                $stock_status = "Only $stock_qty left";
                                $stock_class = "low-stock";
                            } else {
                                $stock_status = "Out of Stock";
                                $stock_class = "out-stock";
                            }

                            $formatted_price = "₱" . number_format($product['price'], 2);

                            // Get image filename from database - FIXED PATH
                            $image_filename = $product['image'];
                            $has_image = false;
                            $actual_image_path = "";

                            if (!empty($image_filename)) {
                                // Clean the filename
                                $image_filename = basename(trim($image_filename));

                                // Try different possible locations
                                $possible_locations = [
                                    "../assets/image/" . $image_filename,      // Your actual folder
                                    "assets/image/" . $image_filename,         // Relative path
                                    "../images/" . $image_filename,            // Alternative
                                    "images/" . $image_filename                // Another alternative
                                ];

                                // Check each location
                                foreach ($possible_locations as $location) {
                                    if (file_exists($location)) {
                                        $has_image = true;
                                        $actual_image_path = $location;
                                        break;
                                    }
                                }
                            }

                            echo '
                        <div class="product-card" data-category="' . $product['categories_id'] . '">
                            <div class="product-image">
                                <span class="category-badge">' . htmlspecialchars($product['category_name']) . '</span>
                                <div class="image-placeholder">';

                            if ($has_image && !empty($actual_image_path)) {
                                echo '<img src="' . $actual_image_path . '" 
                                 alt="' . htmlspecialchars($product['product_name']) . '"
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onerror="this.style.display=\'none\'; this.parentNode.innerHTML=\'<i class=\"fas fa-gamepad\"></i>\';">';
                            } else {
                                // Show gamepad icon with debug info
                                echo '<div style="text-align: center; padding: 20px 0;">';
                                echo '<i class="fas fa-gamepad" style="font-size: 40px; color: #ccc; margin-bottom: 10px;"></i>';
                                if (!empty($image_filename)) {
                                    echo '<div style="font-size: 11px; color: #999;">';
                                    echo 'Image: ' . htmlspecialchars($image_filename);
                                    echo '</div>';
                                }
                                echo '</div>';
                            }

                            echo '
                                </div>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name">' . htmlspecialchars($product['product_name']) . '</h3>
                                <p class="product-desc">' . substr(htmlspecialchars($product['description']), 0, 100) . '...</p>
                                
                                <div class="stock-display">
                                    <div class="stock-info">
                                        <span class="stock-label">Stock Quantity:</span>
                                        <span class="stock-quantity ' . $stock_class . '">
                                            <i class="fas fa-box"></i> ' . $stock_status . '
                                        </span>
                                    </div>
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width: ' . min(100, ($stock_qty / 50) * 100) . '%"></div>
                                    </div>
                                </div>
                                
                                <div class="price-display">
                                    <span class="price-label">Price:</span>
                                    <span class="price">' . $formatted_price . '</span>
                                </div>
                            </div>
                        </div>';
                        }
                    } else {
                        echo '<div class="no-products">
                            <i class="fas fa-gamepad"></i>
                            <h3>No products available</h3>
                            <p>Check back soon for new arrivals!</p>
                          </div>';
                    }

                    // Close first connection for summary stats
                    mysqli_close($conn);

                    // Reconnect for summary statistics
                    $conn = mysqli_connect($host, $username, $password, $database);
                    ?>
                </div>
            </section>

            <!-- Product Summary -->
            <section class="summary-section">
                <div class="summary-card">
                    <h3><i class="fas fa-chart-bar"></i> Inventory Summary</h3>
                    <?php
                    // Get total products
                    $total_query = "SELECT COUNT(*) as total FROM products";
                    $total_result = mysqli_query($conn, $total_query);
                    $total = mysqli_fetch_assoc($total_result);

                    // Get categories count
                    $cats_query = "SELECT COUNT(*) as total FROM categories";
                    $cats_result = mysqli_query($conn, $cats_query);
                    $cats = mysqli_fetch_assoc($cats_result);

                    // Get in stock count
                    $in_stock_query = "SELECT COUNT(DISTINCT p.product_id) as total 
                                  FROM products p 
                                  JOIN stock s ON p.product_id = s.product_id 
                                  WHERE s.quantity > 0";
                    $in_stock_result = mysqli_query($conn, $in_stock_query);
                    $in_stock = mysqli_fetch_assoc($in_stock_result);

                    // Get products with images count
                    $with_images_query = "SELECT COUNT(*) as total FROM products WHERE image IS NOT NULL AND image != ''";
                    $with_images_result = mysqli_query($conn, $with_images_query);
                    $with_images = mysqli_fetch_assoc($with_images_result);

                    mysqli_close($conn);
                    ?>
                    <div class="summary-stats">
                        <div class="stat-item">
                            <i class="fas fa-boxes"></i>
                            <span class="stat-number"><?php echo $total['total']; ?></span>
                            <span class="stat-label">Total Products</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-check-circle"></i>
                            <span class="stat-number"><?php echo $in_stock['total']; ?></span>
                            <span class="stat-label">In Stock</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-tags"></i>
                            <span class="stat-number"><?php echo $cats['total']; ?></span>
                            <span class="stat-label">Categories</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-image"></i>
                            <span class="stat-number"><?php echo $with_images['total']; ?></span>
                            <span class="stat-label">With Images</span>
                        </div>
                    </div>
                </div>
            </section>
    </main>

    <!-- Footer -->
    <footer class="shop-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SneakyPlay Gaming Store. All rights reserved.</p>
            <p>Browse our latest gaming products and accessories</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function searchProducts() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');

            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productDesc = card.querySelector('.product-desc').textContent.toLowerCase();

                if (productName.includes(searchInput) || productDesc.includes(searchInput)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Filter by category
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');

                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filter products
                const productCards = document.querySelectorAll('.product-card');

                productCards.forEach(card => {
                    if (category === 'all' || card.getAttribute('data-category') === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>

    <?php ob_end_flush(); ?>
</body>


</html>
