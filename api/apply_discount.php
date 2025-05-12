<?php
require '../config/db.php';
header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId']) || !isset($data['couponCode']) || !isset($data['subtotal'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan parámetros requeridos'
    ]);
    exit;
}

$userId = (int)$data['userId'];
$couponCode = trim($data['couponCode']);
$subtotal = (float)$data['subtotal'];

try {
    // Verificar si el código de descuento existe y es válido
    $stmt = $conn->prepare("
        SELECT id, code, percentage_off, fixed_amount_off, min_purchase_amount, 
               max_uses, uses_count, valid_from, valid_until
        FROM discount_codes 
        WHERE code = ? 
        LIMIT 1
    ");
    $stmt->execute([$couponCode]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Código de descuento no válido'
        ]);
        exit;
    }
    
    $discount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Validar fecha de vigencia
    $now = new DateTime();
    $validFrom = new DateTime($discount['valid_from']);
    $validUntil = new DateTime($discount['valid_until']);
    
    if ($now < $validFrom || $now > $validUntil) {
        echo json_encode([
            'success' => false,
            'message' => 'Este código ha expirado o aún no está vigente'
        ]);
        exit;
    }
    
    // Validar número máximo de usos
    if ($discount['max_uses'] > 0 && $discount['uses_count'] >= $discount['max_uses']) {
        echo json_encode([
            'success' => false,
            'message' => 'Este código ya alcanzó su límite de usos'
        ]);
        exit;
    }
    
    // Validar monto mínimo de compra
    if ($discount['min_purchase_amount'] > 0 && $subtotal < $discount['min_purchase_amount']) {
        echo json_encode([
            'success' => false,
            'message' => 'El subtotal debe ser de al menos S/ ' . number_format($discount['min_purchase_amount'], 2) . ' para usar este código'
        ]);
        exit;
    }
    
    // Verificar si el usuario ya usó este código (opcional)
    $redemptionStmt = $conn->prepare("
        SELECT id FROM discount_redemptions
        WHERE discount_code_id = ? AND user_id = ? AND order_id IS NULL
        LIMIT 1
    ");
    $redemptionStmt->execute([$discount['id'], $userId]);
    
    if ($redemptionStmt->rowCount() === 0) {
        // Registrar uso pendiente del código (se confirmará al crear la orden)
        $insertStmt = $conn->prepare("
            INSERT INTO discount_redemptions (discount_code_id, user_id, redeemed_at)
            VALUES (?, ?, NOW())
        ");
        $insertStmt->execute([$discount['id'], $userId]);
    }
    
    // Calcular el descuento y enviar respuesta
    $discountAmount = 0;
    $discountMessage = '';
    
    if ($discount['percentage_off'] > 0) {
        $discountAmount = $subtotal * ($discount['percentage_off'] / 100);
        $discountMessage = $discount['percentage_off'] . '% de descuento';
    } else if ($discount['fixed_amount_off'] > 0) {
        $discountAmount = $discount['fixed_amount_off'];
        $discountMessage = 'S/ ' . number_format($discountAmount, 2) . ' de descuento';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $discountMessage,
        'discount' => [
            'id' => $discount['id'],
            'code' => $discount['code'],
            'percentage_off' => $discount['percentage_off'],
            'fixed_amount_off' => $discount['fixed_amount_off'],
            'amount' => $discountAmount
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al aplicar el código de descuento: ' . $e->getMessage()
    ]);
}
?>
