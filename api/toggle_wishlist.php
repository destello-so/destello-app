<?php
require '../config/db.php';
header('Content-Type: application/json');

// Verificar parámetros requeridos
if (!isset($_POST['userId']) || !isset($_POST['productId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$_POST['userId'];
$productId = (int)$_POST['productId'];
$action = isset($_POST['action']) ? $_POST['action'] : 'toggle'; // toggle, add, remove

try {
    // Verificar que el producto existe
    $productStmt = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
    $productStmt->execute([$productId]);
    
    if ($productStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El producto no existe'
        ]);
        exit;
    }
    
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
    $productName = $product['name'];
    
    // Buscar la wishlist del usuario o crearla si no existe
    $wishlistStmt = $conn->prepare("
        SELECT id FROM wishlists 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $wishlistStmt->execute([$userId]);
    
    $conn->beginTransaction();
    
    if ($wishlistStmt->rowCount() === 0) {
        // Crear nueva wishlist para el usuario
        $createWishlistStmt = $conn->prepare("
            INSERT INTO wishlists (user_id, created_at) 
            VALUES (?, NOW())
        ");
        $createWishlistStmt->execute([$userId]);
        $wishlistId = $conn->lastInsertId();
    } else {
        $wishlistId = $wishlistStmt->fetchColumn();
    }
    
    // Verificar si el producto ya está en la wishlist
    $checkItemStmt = $conn->prepare("
        SELECT id FROM wishlist_items 
        WHERE wishlist_id = ? AND product_id = ?
    ");
    $checkItemStmt->execute([$wishlistId, $productId]);
    $itemExists = $checkItemStmt->rowCount() > 0;
    
    // Determinar la acción a realizar
    $added = false;
    
    if ($action === 'toggle') {
        if ($itemExists) {
            // Quitar de la lista
            $deleteStmt = $conn->prepare("
                DELETE FROM wishlist_items 
                WHERE wishlist_id = ? AND product_id = ?
            ");
            $deleteStmt->execute([$wishlistId, $productId]);
        } else {
            // Añadir a la lista
            $insertStmt = $conn->prepare("
                INSERT INTO wishlist_items (wishlist_id, product_id, added_at) 
                VALUES (?, ?, NOW())
            ");
            $insertStmt->execute([$wishlistId, $productId]);
            $added = true;
        }
    } elseif ($action === 'add' && !$itemExists) {
        // Solo añadir si no existe
        $insertStmt = $conn->prepare("
            INSERT INTO wishlist_items (wishlist_id, product_id, added_at) 
            VALUES (?, ?, NOW())
        ");
        $insertStmt->execute([$wishlistId, $productId]);
        $added = true;
    } elseif ($action === 'remove' && $itemExists) {
        // Solo quitar si existe
        $deleteStmt = $conn->prepare("
            DELETE FROM wishlist_items 
            WHERE wishlist_id = ? AND product_id = ?
        ");
        $deleteStmt->execute([$wishlistId, $productId]);
    }
    
    $conn->commit();
    
    // Contar total de items en la wishlist
    $countStmt = $conn->prepare("
        SELECT COUNT(*) FROM wishlist_items 
        WHERE wishlist_id = ?
    ");
    $countStmt->execute([$wishlistId]);
    $totalItems = $countStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'added' => $added,
        'message' => $added 
            ? "'{$productName}' añadido a tu lista de deseos" 
            : "'{$productName}' eliminado de tu lista de deseos",
        'productId' => $productId,
        'totalItems' => $totalItems
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
