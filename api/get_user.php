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
        SELECT id, first_name, last_name, email, phone
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del usuario: ' . $e->getMessage()
    ]);
}
?>
