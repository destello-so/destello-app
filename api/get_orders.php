<?php
require '../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['userId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Falta el ID de usuario'
    ]);
    exit;
}

$userId = (int)$_GET['userId'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Pedidos por página
$offset = ($page - 1) * $limit;

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$date = isset($_GET['date']) ? $_GET['date'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Construir la consulta con filtros
    $query = "
        SELECT 
            o.id, o.total_amount, o.status, o.created_at, o.updated_at,
            COUNT(oi.id) as items_count,
            a.street, a.city, a.state, a.country, a.zip_code
        FROM 
            orders o
        JOIN 
            addresses a ON o.address_id = a.id
        LEFT JOIN 
            order_items oi ON o.id = oi.order_id
        WHERE 
            o.user_id = :userId
    ";
    
    $params = [':userId' => $userId];
    
    // Aplicar filtro de estado
    if ($status !== 'all') {
        $query .= " AND o.status = :status";
        $params[':status'] = $status;
    }
    
    // Aplicar filtro de fecha
    if ($date !== 'all') {
        $dateFilter = '';
        switch ($date) {
            case 'today':
                $dateFilter = "DATE(o.created_at) = CURDATE()";
                break;
            case 'week':
                $dateFilter = "o.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $dateFilter = "o.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case '6month':
                $dateFilter = "o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
                break;
            case 'year':
                $dateFilter = "o.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
        
        if ($dateFilter) {
            $query .= " AND $dateFilter";
        }
    }
    
    // Aplicar búsqueda por número de pedido
    if ($search) {
        $query .= " AND o.id LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    // Agrupar y ordenar
    $query .= " GROUP BY o.id, o.total_amount, o.status, o.created_at, o.updated_at, a.street, a.city, a.state, a.country, a.zip_code";
    $query .= " ORDER BY o.created_at DESC";
    
    // Obtener el total de registros para la paginación
    $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as filtered_orders";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalOrders = $countStmt->fetchColumn();
    
    // CAMBIO AQUÍ: Usar LIMIT y OFFSET directamente en la SQL sin marcadores
    $query .= " LIMIT $limit OFFSET $offset";
    
    // Ejecutar la consulta principal
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular el total de páginas
    $totalPages = ceil($totalOrders / $limit);
    
    // Para cada pedido, obtener sus items
    $orderData = [];
    foreach ($orders as $order) {
        $orderId = $order['id'];
        $orderNumber = 'DE-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
        
        // Obtener los items del pedido
        $itemsStmt = $conn->prepare("
            SELECT 
                oi.id, oi.product_id, oi.quantity, oi.unit_price, oi.discount_amount,
                p.name, p.sku,
                pi.url as image_url, pi.alt_text
            FROM 
                order_items oi
            JOIN 
                products p ON oi.product_id = p.id
            LEFT JOIN 
                (SELECT product_id, url, alt_text FROM product_images WHERE is_primary = 1) as pi
                ON p.id = pi.product_id
            WHERE 
                oi.order_id = :orderId
        ");
        $itemsStmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
        $itemsStmt->execute();
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Estructurar la información del pedido
        $orderData[] = [
            'id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $order['total_amount'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at'],
            'items_count' => $order['items_count'],
            'address' => [
                'street' => $order['street'],
                'city' => $order['city'],
                'state' => $order['state'],
                'country' => $order['country'],
                'zip_code' => $order['zip_code']
            ],
            'items' => $items
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orderData,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener pedidos: ' . $e->getMessage()
    ]);
}
?>
