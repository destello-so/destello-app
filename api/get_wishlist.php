<?php
require '../config/db.php';
header('Content-Type: application/json');

// Verificar el parámetro userId
if (!isset($_GET['userId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Falta el parámetro userId'
    ]);
    exit;
}

$userId = (int)$_GET['userId'];

try {
    // Buscar la wishlist del usuario
    $wishlistStmt = $conn->prepare("
        SELECT id FROM wishlists 
        WHERE user_id = ? 
        LIMIT 1
    ");
    $wishlistStmt->execute([$userId]);
    
    // Si no existe la wishlist, devolver array vacío
    if ($wishlistStmt->rowCount() === 0) {
        echo json_encode([
            'success' => true,
            'items' => []
        ]);
        exit;
    }
    
    $wishlistId = $wishlistStmt->fetchColumn();
    
    // Obtener los items de la wishlist con toda la información necesaria
    $itemsStmt = $conn->prepare("
        SELECT 
            wi.id as wishlist_item_id,
            wi.product_id as id,
            wi.added_at,
            p.name,
            p.description,
            p.price,
            p.stock_quantity,
            p.sku,
            pi.url as image_url,
            pi.alt_text,
            c.name as category_name
        FROM 
            wishlist_items wi
        INNER JOIN 
            products p ON wi.product_id = p.id
        LEFT JOIN 
            (SELECT product_id, url, alt_text FROM product_images WHERE is_primary = 1) pi 
            ON p.id = pi.product_id
        LEFT JOIN 
            product_categories pc ON p.id = pc.product_id
        LEFT JOIN 
            categories c ON pc.category_id = c.id
        WHERE 
            wi.wishlist_id = ?
        GROUP BY 
            wi.id, wi.product_id, wi.added_at, p.name, p.description, 
            p.price, p.stock_quantity, p.sku, pi.url, pi.alt_text, c.name
        ORDER BY 
            wi.added_at DESC
    ");
    
    $itemsStmt->execute([$wishlistId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar la lista de deseos: ' . $e->getMessage()
    ]);
}
?>
