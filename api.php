<?php
require_once 'includes/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get action from POST
$action = $_POST['action'] ?? '';

try {
    switch($action) {
        case 'add':
            $stmt = $pdo->prepare("INSERT INTO products (sku, name, category, quantity, price, reorder_level, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['sku'],
                $_POST['name'],
                $_POST['category'],
                $_POST['quantity'],
                $_POST['price'],
                $_POST['reorder_level'],
                $_POST['description'] ?? ''
            ]);
            echo json_encode(['success' => true, 'message' => 'Product added successfully!']);
            break;
            
        case 'update':
            $stmt = $pdo->prepare("UPDATE products SET sku=?, name=?, category=?, quantity=?, price=?, reorder_level=?, description=? WHERE id=?");
            $stmt->execute([
                $_POST['sku'],
                $_POST['name'],
                $_POST['category'],
                $_POST['quantity'],
                $_POST['price'],
                $_POST['reorder_level'],
                $_POST['description'] ?? '',
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Product updated successfully!']);
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>