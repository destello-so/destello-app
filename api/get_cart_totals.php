<?php
require '../config/db.php';
header('Content-Type: application/json');

// Para debugging
file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Solicitud recibida: " . json_encode($_GET) . PHP_EOL, FILE_APPEND);

if (!isset($_GET['userId'])) {
    file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Error - Falta el ID de usuario\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Falta el ID de usuario'
    ]);
    exit;
}

$userId = (int)$_GET['userId'];
file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Procesando para userId: $userId\n", FILE_APPEND);

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
        file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": No se encontró carrito para el usuario $userId\n", FILE_APPEND);
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
    file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Carrito encontrado: $cartId\n", FILE_APPEND);
    
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
    file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Encontrados $itemCount tipos de productos\n", FILE_APPEND);
    
    $subtotal = 0;
    $itemsCount = 0;
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        $itemsCount += $item['quantity'];
        file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Producto: {$item['name']}, Cantidad: {$item['quantity']}, Precio: {$item['price']}, Total: $itemTotal\n", FILE_APPEND);
    }
    
    // Aquí implementarías la lógica para calcular descuentos
    $discount = 0;
    
    $total = $subtotal - $discount;
    
    file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": Totales calculados - Subtotal: $subtotal, Descuento: $discount, Total: $total, Items: $itemsCount\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'subtotal' => number_format($subtotal, 2),
        'discount' => number_format($discount, 2),
        'total' => number_format($total, 2),
        'items_count' => $itemsCount
    ]);
} catch (PDOException $e) {
    file_put_contents('cart_totals_log.txt', date('Y-m-d H:i:s') . ": ERROR PDO - " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los totales: ' . $e->getMessage()
    ]);
}
?>