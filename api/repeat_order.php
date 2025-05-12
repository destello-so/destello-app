<?php
// api/repeat_order.php
require '../config/db.php';
header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId']) || !isset($data['orderId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$data['userId'];
$orderId = (int)$data['orderId'];

try {
    // Verificar que el pedido pertenece al usuario
    $orderStmt = $conn->prepare("
        SELECT id FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $orderStmt->execute([$orderId, $userId]);
    
    if ($orderStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Pedido no encontrado o no pertenece al usuario'
        ]);
        exit;
    }
    
    // Obtener carrito actual del usuario o crear uno nuevo
    $cartStmt = $conn->prepare("
        SELECT id FROM carts 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $cartStmt->execute([$userId]);
    
    if ($cartStmt->rowCount() === 0) {
        // Crear carrito nuevo
        $createCartStmt = $conn->prepare("
            INSERT INTO carts (user_id, created_at, updated_at)
            VALUES (?, NOW(), NOW())
        ");
        $createCartStmt->execute([$userId]);
        $cartId = $conn->lastInsertId();
    } else {
        $cartId = $cartStmt->fetchColumn();
    }
    
    // Obtener productos del pedido original
    $itemsStmt = $conn->prepare("
        SELECT oi.product_id, oi.quantity, p.stock_quantity 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    $addedItems = 0;
    $noStockItems = 0;
    
    foreach ($items as $item) {
        // Verificar si hay stock suficiente
        if ($item['stock_quantity'] < $item['quantity']) {
            $noStockItems++;
            continue; // Saltar este producto
        }
        
        // Verificar si el producto ya está en el carrito
        $checkCartStmt = $conn->prepare("
            SELECT id, quantity FROM cart_items 
            WHERE cart_id = ? AND product_id = ?
        ");
        $checkCartStmt->execute([$cartId, $item['product_id']]);
        
        if ($checkCartStmt->rowCount() > 0) {
            // Actualizar cantidad
            $cartItem = $checkCartStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = min($cartItem['quantity'] + $item['quantity'], 10); // Máximo 10 unidades
            
            $updateCartStmt = $conn->prepare("
                UPDATE cart_items SET quantity = ?, added_at = NOW()
                WHERE id = ?
            ");
            $updateCartStmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Añadir producto al carrito
            $addCartStmt = $conn->prepare("
                INSERT INTO cart_items (cart_id, product_id, quantity, added_at)
                VALUES (?, ?, ?, NOW())
            ");
            $addCartStmt->execute([$cartId, $item['product_id'], $item['quantity']]);
        }
        
        $addedItems++;
    }
    
    // Actualizar timestamp del carrito
    $updateCartStmt = $conn->prepare("
        UPDATE carts SET updated_at = NOW() WHERE id = ?
    ");
    $updateCartStmt->execute([$cartId]);
    
    $conn->commit();
    
    // Preparar mensaje de respuesta
    $message = 'Productos añadidos al carrito correctamente';
    if ($noStockItems > 0) {
        $message .= ". $noStockItems productos no pudieron añadirse por falta de stock.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'addedItems' => $addedItems,
        'noStockItems' => $noStockItems,
        'cartId' => $cartId
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al repetir el pedido: ' . $e->getMessage()
    ]);
}
?>
