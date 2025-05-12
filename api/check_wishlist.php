<?php
require '../config/db.php';
header('Content-Type: application/json');

// Parámetros requeridos
if (!isset($_GET['userId']) || !isset($_GET['productIds'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$_GET['userId'];
$productIds = explode(',', $_GET['productIds']); // Convertir string de IDs a array

// Validar que haya productos para verificar
if (empty($productIds)) {
    echo json_encode([
        'success' => true,
        'wishlistItems' => []
    ]);
    exit;
}

try {
    // Buscar la wishlist del usuario o crearla si no existe
    $wishlistStmt = $conn->prepare("
        SELECT id FROM wishlists 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $wishlistStmt->execute([$userId]);
    
    if ($wishlistStmt->rowCount() === 0) {
        // No hay wishlist, devolver array vacío
        echo json_encode([
            'success' => true,
            'wishlistItems' => []
        ]);
        exit;
    }
    
    $wishlistId = $wishlistStmt->fetchColumn();
    
    // Preparar placeholders para la consulta SQL
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    // Buscar los productos en la wishlist
    $stmt = $conn->prepare("
        SELECT product_id 
        FROM wishlist_items 
        WHERE wishlist_id = ? AND product_id IN ($placeholders)
    ");
    
    // Preparar parámetros para la consulta
    $params = [$wishlistId];
    foreach ($productIds as $id) {
        $params[] = (int)$id;
    }
    
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear resultados
    $wishlistItems = [];
    foreach ($results as $item) {
        $wishlistItems[] = (int)$item['product_id'];
    }
    
    echo json_encode([
        'success' => true,
        'wishlistItems' => $wishlistItems
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar la lista de deseos: ' . $e->getMessage()
    ]);
}
?>
