<?php
session_start();
require 'config/db.php';
header('Content-Type: application/json');

// Obtener datos del formulario
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validación básica
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, complete todos los campos'
    ]);
    exit;
}

try {
    // Buscar el usuario por email
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, role_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Correo electrónico o contraseña incorrectos'
        ]);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar la contraseña
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Correo electrónico o contraseña incorrectos'
        ]);
        exit;
    }
    
    // Iniciar sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role_id'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'userId' => $user['id'],
        'userName' => $user['first_name'] . ' ' . $user['last_name'],
        'redirectUrl' => 'home.php'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}