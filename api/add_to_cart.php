<?php
require '../config/db.php';
header('Content-Type: application/json');

if (!isset($_POST['userId']) || !isset($_POST['productId']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$_POST['userId'];
$productId = (int)$_POST['productId'];
$quantity = (int)$_POST['quantity'];

// Validar que los valores sean razonables
if ($quantity < 1) $quantity = 1;
if ($quantity > 10) $quantity = 10;

try {
    // Verificar si el producto existe y tiene stock
    $productStmt = $conn->prepare("SELECT id, stock_quantity FROM products WHERE id = ?");
    $productStmt->execute([$productId]);
    
    if ($productStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El producto no existe'
        ]);
        exit;
    }
    
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay suficiente stock disponible'
        ]);
        exit;
    }
    
    // Buscar el carrito del usuario o crear uno nuevo
    $cartStmt = $conn->prepare("
        SELECT id FROM carts 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $cartStmt->execute([$userId]);
    
    if ($cartStmt->rowCount() === 0) {
        // Crear un nuevo carrito
        $createCartStmt = $conn->prepare("
            INSERT INTO carts (user_id, created_at, updated_at) 
            VALUES (?, NOW(), NOW())
        ");
        $createCartStmt->execute([$userId]);
        $cartId = $conn->lastInsertId();
    } else {
        $cartId = $cartStmt->fetchColumn();
    }
    
    // Verificar si el producto ya está en el carrito
    $existingItemStmt = $conn->prepare("
        SELECT id, quantity FROM cart_items 
        WHERE cart_id = ? AND product_id = ?
    ");
    $existingItemStmt->execute([$cartId, $productId]);
    
    if ($existingItemStmt->rowCount() > 0) {
        // Actualizar la cantidad del producto existente
        $existingItem = $existingItemStmt->fetch(PDO::FETCH_ASSOC);
        $newQuantity = min($existingItem['quantity'] + $quantity, 10); // Máximo 10 unidades
        
        $updateStmt = $conn->prepare("
            UPDATE cart_items 
            SET quantity = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([$newQuantity, $existingItem['id']]);
        
        // Actualizar la fecha del carrito
        $updateCartStmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $updateCartStmt->execute([$cartId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cantidad actualizada en el carrito',
            'cartItemId' => $existingItem['id'],
            'quantity' => $newQuantity
        ]);
    } else {
        // Añadir nuevo item al carrito
        $addItemStmt = $conn->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, added_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $addItemStmt->execute([$cartId, $productId, $quantity]);
        $cartItemId = $conn->lastInsertId();
        
        // Actualizar la fecha del carrito
        $updateCartStmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
        $updateCartStmt->execute([$cartId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto añadido al carrito',
            'cartItemId' => $cartItemId,
            'quantity' => $quantity
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al añadir al carrito: ' . $e->getMessage()
    ]);
}
?>
