<?php
require '../config/db.php';
header('Content-Type: application/json');

// Para debugging
error_log("Solicitud recibida en update_cart_quantity.php: " . json_encode($_POST));

if (!isset($_POST['cartItemId']) || !isset($_POST['quantity'])) {
    error_log("Error: Faltan parámetros");
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$cartItemId = (int)$_POST['cartItemId'];
$quantity = (int)$_POST['quantity'];

// Validar que los valores sean razonables
if ($quantity < 1) $quantity = 1;
if ($quantity > 10) $quantity = 10;

error_log("Procesando: cartItemId=$cartItemId, quantity=$quantity");

try {
    // Verificar que el ítem existe
    $checkStmt = $conn->prepare("
        SELECT ci.id, ci.cart_id, ci.product_id, ci.quantity as current_quantity, 
               p.stock_quantity, p.price, p.name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ?
    ");
    $checkStmt->execute([$cartItemId]);
    
    if ($checkStmt->rowCount() === 0) {
        error_log("Error: Ítem $cartItemId no encontrado");
        echo json_encode([
            'success' => false,
            'message' => 'El ítem no existe en el carrito'
        ]);
        exit;
    }
    
    $item = $checkStmt->fetch(PDO::FETCH_ASSOC);
    error_log("Ítem encontrado: " . json_encode($item));
    
    // Verificar que haya suficiente stock
    if ($item['stock_quantity'] < $quantity) {
        error_log("Error: Stock insuficiente. Solicitado: $quantity, Disponible: " . $item['stock_quantity']);
        echo json_encode([
            'success' => false,
            'message' => 'No hay suficiente stock disponible. Solo quedan ' . $item['stock_quantity'] . ' unidades.'
        ]);
        exit;
    }
    
    // Si la cantidad no cambió, no hacer nada
    if ($item['current_quantity'] == $quantity) {
        error_log("La cantidad no cambió: $quantity");
        echo json_encode([
            'success' => true,
            'message' => 'La cantidad no requiere actualización',
            'unit_price' => number_format($item['price'], 2),
            'quantity' => $quantity
        ]);
        exit;
    }
    
    // Actualizar la cantidad
    $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $result = $updateStmt->execute([$quantity, $cartItemId]);
    
    if (!$result) {
        error_log("Error al ejecutar la consulta SQL. Error: " . json_encode($updateStmt->errorInfo()));
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la base de datos'
        ]);
        exit;
    }
    
    // Actualizar la fecha del carrito
    $updateCartStmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $updateCartStmt->execute([$item['cart_id']]);
    
    // Calcular nuevo subtotal
    $newItemTotal = $item['price'] * $quantity;
    
    error_log("Actualización exitosa. Cantidad: $quantity, Total: $newItemTotal");
    
    echo json_encode([
        'success' => true,
        'message' => 'Cantidad actualizada correctamente',
        'quantity' => $quantity,
        'unit_price' => number_format($item['price'], 2),
        'item_total' => number_format($newItemTotal, 2),
        'product_name' => $item['name'],
        'cart_id' => $item['cart_id'],
        'stock_available' => $item['stock_quantity']
    ]);
} catch (PDOException $e) {
    error_log("Error PDO: " . $e->getMessage() . "\nTraza: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la cantidad: ' . $e->getMessage()
    ]);
}
?>