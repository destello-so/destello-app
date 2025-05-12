<?php
require '../config/db.php';
header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId']) || !isset($data['address']) || !isset($data['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$data['userId'];
$addressData = $data['address'];
$cartData = $data['cart'];
$paymentInfo = $data['paymentInfo'] ?? null;
$discountCode = $data['discountCode'] ?? null;

try {
    $conn->beginTransaction();
    
    // 1. Guardar la dirección si se solicitó
    $addressId = null;
    
    if ($addressData['save_address']) {
        // Verificar si la dirección ya existe
        $checkAddressStmt = $conn->prepare("
            SELECT id FROM addresses 
            WHERE user_id = ? AND street = ? AND city = ? AND country = ?
            LIMIT 1
        ");
        $checkAddressStmt->execute([
            $userId, 
            $addressData['street'], 
            $addressData['city'], 
            $addressData['country']
        ]);
        
        if ($checkAddressStmt->rowCount() > 0) {
            // Si existe, usar esa dirección
            $addressId = $checkAddressStmt->fetchColumn();
        } else {
            // Si no existe, crear nueva dirección
            $insertAddressStmt = $conn->prepare("
                INSERT INTO addresses (user_id, street, city, state, country, zip_code, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            $insertAddressStmt->execute([
                $userId,
                $addressData['street'],
                $addressData['city'],
                $addressData['state'],
                $addressData['country'],
                $addressData['zip_code']
            ]);
            $addressId = $conn->lastInsertId();
        }
    } else {
        // Crear dirección temporal para esta orden
        $insertAddressStmt = $conn->prepare("
            INSERT INTO addresses (user_id, street, city, state, country, zip_code, is_default, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $insertAddressStmt->execute([
            $userId,
            $addressData['street'],
            $addressData['city'],
            $addressData['state'],
            $addressData['country'],
            $addressData['zip_code']
        ]);
        $addressId = $conn->lastInsertId();
    }
    
    // 2. Crear la orden
    $total = $cartData['total'] ?? 0;
    
    $insertOrderStmt = $conn->prepare("
        INSERT INTO orders (user_id, address_id, total_amount, status, created_at, updated_at)
        VALUES (?, ?, ?, 'pending', NOW(), NOW())
    ");
    $insertOrderStmt->execute([
        $userId,
        $addressId,
        $total
    ]);
    $orderId = $conn->lastInsertId();
    
    // Generar número de orden para mostrar al cliente
    $orderNumber = 'DE-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    
    // 3. Registrar los items de la orden
    foreach ($cartData['items'] as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $discountAmount = 0; // Por ítem, si aplicara
        
        // Insertar item en la orden
        $insertItemStmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, discount_amount)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertItemStmt->execute([
            $orderId,
            $productId,
            $quantity,
            $price,
            $discountAmount
        ]);
        
        // Actualizar inventario
        $updateInventoryStmt = $conn->prepare("
            UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?
        ");
        $updateInventoryStmt->execute([$quantity, $productId]);
        
        // Registrar transacción de inventario
        $insertTransactionStmt = $conn->prepare("
            INSERT INTO inventory_transactions (product_id, quantity_change, transaction_type, occurred_at, note)
            VALUES (?, ?, 'order', NOW(), ?)
        ");
        $insertTransactionStmt->execute([
            $productId,
            -$quantity, // Negativo porque es salida de inventario
            "Venta - Orden #$orderNumber"
        ]);
    }
    
    // 4. Si se aplicó descuento, registrarlo en la orden
    if ($discountCode) {
        $discountStmt = $conn->prepare("
            UPDATE discount_redemptions 
            SET order_id = ? 
            WHERE user_id = ? AND discount_code_id = (SELECT id FROM discount_codes WHERE code = ?) AND order_id IS NULL
        ");
        $discountStmt->execute([$orderId, $userId, $discountCode]);
        
        // Incrementar contador de usos
        $updateUsesStmt = $conn->prepare("
            UPDATE discount_codes 
            SET uses_count = uses_count + 1 
            WHERE code = ?
        ");
        $updateUsesStmt->execute([$discountCode]);
    }
    
    // 5. Vaciar el carrito del usuario
    $cartId = $cartData['cartId'] ?? null;
    if ($cartId) {
        $emptyCartStmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $emptyCartStmt->execute([$cartId]);
        
        // Actualizar fecha de actualización del carrito
        $updateCartStmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $updateCartStmt->execute([$cartId]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Orden creada exitosamente',
        'orderNumber' => $orderNumber,
        'orderId' => $orderId
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear la orden: ' . $e->getMessage()
    ]);
}
?>
