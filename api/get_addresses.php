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

try {
    $stmt = $conn->prepare("
        SELECT id, user_id, street, city, state, country, zip_code, is_default
        FROM addresses 
        WHERE user_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$userId]);
    
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'addresses' => $addresses
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener direcciones: ' . $e->getMessage()
    ]);
}
?>
