<?php
require '../config/db.php';
header('Content-Type: application/json');

// Registrar la solicitud para depuración
file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Solicitud recibida - " . json_encode($_POST) . PHP_EOL, FILE_APPEND);

if (!isset($_POST['cartItemId'])) {
    file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Error - Falta el ID del ítem\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Falta el ID del ítem del carrito'
    ]);
    exit;
}

$cartItemId = (int)$_POST['cartItemId'];
file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Procesando eliminación del ítem ID: $cartItemId\n", FILE_APPEND);

try {
    // 1. Verificar que el ítem existe antes de intentar eliminarlo
    $checkStmt = $conn->prepare("SELECT ci.id, ci.cart_id, ci.product_id, p.name FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = ?");
    $checkStmt->execute([$cartItemId]);
    
    if ($checkStmt->rowCount() === 0) {
        file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Error - Ítem $cartItemId no existe\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'El ítem no existe en el carrito'
        ]);
        exit;
    }
    
    $cartItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $cartId = $cartItem['cart_id'];
    $productName = $cartItem['name'];
    
    file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Ítem encontrado - CartID: $cartId, Producto: $productName\n", FILE_APPEND);
    
    // 2. Eliminar el ítem explícitamente
    $conn->beginTransaction();
    
    $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
    $result = $deleteStmt->execute([$cartItemId]);
    
    if (!$result || $deleteStmt->rowCount() === 0) {
        $conn->rollBack();
        file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Error - La eliminación no afectó ninguna fila\n", FILE_APPEND);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el ítem del carrito'
        ]);
        exit;
    }
    
    // 3. Actualizar la fecha del carrito
    $updateCartStmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $updateCartStmt->execute([$cartId]);
    
    $conn->commit();
    
    // 4. Verificar si el carrito quedó vacío
    $checkEmptyStmt = $conn->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
    $checkEmptyStmt->execute([$cartId]);
    $itemCount = $checkEmptyStmt->fetchColumn();
    
    file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": Eliminación exitosa - Quedan $itemCount ítems en el carrito\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado del carrito',
        'cartItemId' => $cartItemId,
        'productName' => $productName,
        'cartId' => $cartId,
        'cartIsEmpty' => ($itemCount == 0)
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    file_put_contents('delete_cart_log.txt', date('Y-m-d H:i:s') . ": ERROR PDO - " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
