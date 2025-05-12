<?php
// api/cancel_order.php
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
    // Verificar que el pedido pertenece al usuario y está en estado pendiente
    $orderStmt = $conn->prepare("
        SELECT id, status FROM orders 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $orderStmt->execute([$orderId, $userId]);
    
    if ($orderStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Pedido no encontrado, no pertenece al usuario o no se puede cancelar'
        ]);
        exit;
    }
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Actualizar estado del pedido
    $updateStmt = $conn->prepare("
        UPDATE orders SET status = 'cancelled', updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$orderId]);
    
    // Restaurar el inventario
    $itemsStmt = $conn->prepare("
        SELECT product_id, quantity FROM order_items 
        WHERE order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        // Restaurar stock
        $restoreStmt = $conn->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity + ? 
            WHERE id = ?
        ");
        $restoreStmt->execute([$item['quantity'], $item['product_id']]);
        
        // Registrar transacción de inventario
        $transactionStmt = $conn->prepare("
            INSERT INTO inventory_transactions 
            (product_id, quantity_change, transaction_type, occurred_at, note)
            VALUES (?, ?, 'return', NOW(), ?)
        ");
        $transactionStmt->execute([
            $item['product_id'],
            $item['quantity'],
            "Pedido #$orderId cancelado"
        ]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido cancelado exitosamente'
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al cancelar el pedido: ' . $e->getMessage()
    ]);
}
?>
