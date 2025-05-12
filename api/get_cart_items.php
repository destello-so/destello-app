<?php
require '../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['userId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Falta el ID de usuario'
    ]);
    exit;
}

$userId = (int)$_GET['userId'];

try {
    // Primero buscar el carrito activo del usuario
    $cartStmt = $conn->prepare("
        SELECT id FROM carts 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $cartStmt->execute([$userId]);
    
    if ($cartStmt->rowCount() === 0) {
        echo json_encode([
            'success' => true,
            'items' => []
        ]);
        exit;
    }
    
    $cartId = $cartStmt->fetchColumn();
    
    // Obtener items del carrito con información del producto
    $itemsStmt = $conn->prepare("
        SELECT ci.id as cart_item_id, ci.product_id, ci.quantity, 
               p.name, p.price, p.stock_quantity, 
               pi.url as image_url, pi.alt_text
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE ci.cart_id = ?
    ");
    $itemsStmt->execute([$cartId]);
    
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cartId' => $cartId,
        'items' => $items
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener items del carrito: ' . $e->getMessage()
    ]);
}
?>
