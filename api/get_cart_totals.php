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
    // Buscar el carrito del usuario
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
            'subtotal' => '0.00',
            'discount' => '0.00',
            'total' => '0.00',
            'items_count' => 0
        ]);
        exit;
    }
    
    $cartId = $cartStmt->fetchColumn();
    
    // Obtener el subtotal y cantidad de ítems del carrito
    $itemsStmt = $conn->prepare("
        SELECT ci.quantity, p.price, p.name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $itemsStmt->execute([$cartId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $itemCount = count($items);
    
    $subtotal = 0;
    $itemsCount = 0;
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        $itemsCount += $item['quantity'];
    }
    
    // Aquí implementarías la lógica para calcular descuentos
    $discount = 0;
    
    $total = $subtotal - $discount;
    
    echo json_encode([
        'success' => true,
        'subtotal' => number_format($subtotal, 2),
        'discount' => number_format($discount, 2),
        'total' => number_format($total, 2),
        'items_count' => $itemsCount
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los totales: ' . $e->getMessage()
    ]);
}
?>