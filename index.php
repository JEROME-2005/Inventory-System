<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

// Calculate statistics
$totalProducts = count($products);
$totalValue = 0;
$lowStockCount = 0;

foreach ($products as $product) {
    $totalValue += $product['price'] * $product['quantity'];
    if ($product['quantity'] <= $product['reorder_level']) {
        $lowStockCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📦 Inventory</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link active">
                    <span>📊</span> Dashboard
                </a>
                <a href="#" class="nav-link">
                    <span>📦</span> Products
                </a>
                <a href="#" class="nav-link">
                    <span>📈</span> Reports
                </a>
                <a href="#" class="nav-link">
                    <span>⚙️</span> Settings
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="btn btn-danger btn-block">
                    🚪 Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="page-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>👤 Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                </div>
            </header>

            <!-- Low Stock Alert -->
            <?php if ($lowStockCount > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Low Stock Alert!</strong> 
                <?php echo $lowStockCount; ?> product(s) need reordering.
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">📦</div>
                    <div class="stat-details">
                        <h3>Total Products</h3>
                        <p class="stat-number"><?php echo $totalProducts; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f093fb;">💰</div>
                    <div class="stat-details">
                        <h3>Total Value</h3>
                        <p class="stat-number">$<?php echo number_format($totalValue, 2); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fa709a;">⚠️</div>
                    <div class="stat-details">
                        <h3>Low Stock Items</h3>
                        <p class="stat-number"><?php echo $lowStockCount; ?></p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-bar">
                <button class="btn btn-primary" onclick="showAddModal()">
                    ➕ Add New Product
                </button>
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="🔍 Search products..." onkeyup="searchProducts()">
            </div>

            <!-- Products Table -->
            <div class="table-container">
                <table id="productTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['sku']); ?></strong></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($product['category']); ?></span></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>$<?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                            <td>
                                <?php if ($product['quantity'] <= $product['reorder_level']): ?>
                                    <span class="badge badge-danger">🔴 Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✅ In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-action btn-edit" 
                                        onclick='editProduct(<?php echo json_encode($product); ?>)'>
                                    ✏️ Edit
                                </button>
                                <button class="btn-action btn-delete" 
                                        onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="productForm">
                <input type="hidden" id="productId" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" id="sku" name="sku" 
                               placeholder="e.g., SKU001" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food">Food & Beverages</option>
                            <option value="Stationery">Stationery</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" 
                           placeholder="Enter product name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" 
                               min="0" placeholder="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" 
                               step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reorder_level">Reorder Level *</label>
                    <input type="number" id="reorder_level" name="reorder_level" 
                           min="0" placeholder="e.g., 10" required>
                    <small>You'll be alerted when stock falls to this level</small>
                </div>

                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Enter product description"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>