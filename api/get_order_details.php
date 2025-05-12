<?php
require '../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['orderId']) || !isset($_GET['userId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$orderId = (int)$_GET['orderId'];
$userId = (int)$_GET['userId'];

try {
    // Verificar que el pedido pertenece al usuario
    $orderStmt = $conn->prepare("
        SELECT 
            o.id, o.user_id, o.address_id, o.total_amount, o.status, o.created_at, o.updated_at,
            u.first_name, u.last_name, u.email, u.phone,
            a.street, a.city, a.state, a.country, a.zip_code
        FROM 
            orders o
        JOIN 
            users u ON o.user_id = u.id
        JOIN 
            addresses a ON o.address_id = a.id
        WHERE 
            o.id = ? AND o.user_id = ?
    ");
    $orderStmt->execute([$orderId, $userId]);
    
    if ($orderStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Pedido no encontrado o no pertenece al usuario'
        ]);
        exit;
    }
    
    $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
    $orderNumber = 'DE-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    
    // Obtener los items del pedido
    $itemsStmt = $conn->prepare("
        SELECT 
            oi.id, oi.product_id, oi.quantity, oi.unit_price, oi.discount_amount,
            p.name, p.sku, p.description,
            pi.url as image_url, pi.alt_text
        FROM 
            order_items oi
        JOIN 
            products p ON oi.product_id = p.id
        LEFT JOIN 
            (SELECT product_id, url, alt_text FROM product_images WHERE is_primary = 1) as pi
            ON p.id = pi.product_id
        WHERE 
            oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener información de envío si existe
    $shipmentStmt = $conn->prepare("
        SELECT 
            s.id, s.carrier, s.tracking_number, s.status, s.shipped_at, s.delivered_at
        FROM 
            shipments s
        WHERE 
            s.order_id = ?
    ");
    $shipmentStmt->execute([$orderId]);
    $shipments = $shipmentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener descuentos aplicados
    $discountStmt = $conn->prepare("
        SELECT 
            dr.id, dc.code, dc.percentage_off, dc.fixed_amount_off
        FROM 
            discount_redemptions dr
        JOIN 
            discount_codes dc ON dr.discount_code_id = dc.id
        WHERE 
            dr.order_id = ?
    ");
    $discountStmt->execute([$orderId]);
    $discounts = $discountStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estructurar la respuesta
    $orderDetails = [
        'id' => $orderData['id'],
        'order_number' => $orderNumber,
        'total_amount' => $orderData['total_amount'],
        'status' => $orderData['status'],
        'created_at' => $orderData['created_at'],
        'updated_at' => $orderData['updated_at'],
        'user' => [
            'id' => $orderData['user_id'],
            'first_name' => $orderData['first_name'],
            'last_name' => $orderData['last_name'],
            'email' => $orderData['email'],
            'phone' => $orderData['phone']
        ],
        'address' => [
            'id' => $orderData['address_id'],
            'street' => $orderData['street'],
            'city' => $orderData['city'],
            'state' => $orderData['state'],
            'country' => $orderData['country'],
            'zip_code' => $orderData['zip_code']
        ],
        'items' => $items,
        'shipments' => $shipments,
        'discounts' => $discounts
    ];
    
    echo json_encode([
        'success' => true,
        'order' => $orderDetails
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalles del pedido: ' . $e->getMessage()
    ]);
}
?>
