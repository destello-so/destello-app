<?php
session_start();
require 'config/db.php';
header('Content-Type: application/json');

// Obtener datos del formulario
$firstName = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$lastName = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['celular']) ? trim($_POST['celular']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validación básica
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, complete todos los campos'
    ]);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'Las contraseñas no coinciden'
    ]);
    exit;
}

// Validar que el correo tenga un formato válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'El formato del correo electrónico no es válido'
    ]);
    exit;
}

try {
    // Verificar si el correo ya está registrado
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Este correo electrónico ya está registrado'
        ]);
        exit;
    }
    
    // Rol por defecto (cliente)
    $roleId = 2; // Asumiendo que 2 es el rol de cliente normal
    
    // Hashear la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Registrar el usuario
    $insertStmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, password_hash, phone, role_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $insertStmt->execute([
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $phone,
        $roleId
    ]);
    
    // Obtener el ID del usuario recién creado
    $userId = $conn->lastInsertId();
    
    // Crear un carrito para el usuario
    $cartStmt = $conn->prepare("
        INSERT INTO carts (user_id, created_at, updated_at)
        VALUES (?, NOW(), NOW())
    ");
    $cartStmt->execute([$userId]);
    
    // Crear una lista de deseos para el usuario
    $wishlistStmt = $conn->prepare("
        INSERT INTO wishlists (user_id, created_at)
        VALUES (?, NOW())
    ");
    $wishlistStmt->execute([$userId]);
    
    // Iniciar sesión
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $roleId;
    
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso',
        'userId' => $userId,
        'userName' => $firstName . ' ' . $lastName,
        'redirectUrl' => 'home.php'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
