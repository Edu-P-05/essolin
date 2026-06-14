<?php
// 1. INICIAMOS EL MOTOR DE SESIONES (Debe ser la primera línea)
session_start();

require_once 'conexion.php'; // Asegúrate de que la ruta sea correcta

// Leer datos enviados desde el formulario de login (JSON)
$datos = json_decode(file_get_contents('php://input'), true);

$email    = trim($datos['email']    ?? '');
$password = trim($datos['password'] ?? '');

// Validación básica de campos
if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "mensaje" => "Correo y contraseña son obligatorios."]);
    exit;
}

// Buscar usuario en la base de datos
// SE AÑADIÓ: u.estado a la consulta para saber si está activo o suspendido
$pdo  = getConexion();
$stmt = $pdo->prepare("
    SELECT u.id_usuario, u.nombre_completo, u.password, r.nombre_rol, u.id_rol, u.estado 
    FROM usuarios u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.email = ? LIMIT 1
");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo json_encode(["success" => false, "mensaje" => "Usuario no encontrado."]);
    exit;
}

// --- NUEVA VALIDACIÓN: CANDADO DE SUSPENSIÓN ---
if ($usuario['estado'] === 'Suspendido') {
    echo json_encode(["success" => false, "mensaje" => "Acceso denegado. Tu cuenta se encuentra suspendida. Contacta a un Administrador."]);
    exit;
}
// ------------------------------------------------

// Verificar contraseña (hash bcrypt guardado en BD)
if (!password_verify($password, $usuario['password'])) {
    echo json_encode(["success" => false, "mensaje" => "Contraseña incorrecta."]);
    exit;
}

// 2. CREAMOS EL "GAFETE" VIP PARA EL USUARIO
$_SESSION['usuario_logueado'] = true;
$_SESSION['id_usuario'] = $usuario['id_usuario']; 
$_SESSION['nombre_usuario'] = $usuario['nombre_completo'];
$_SESSION['id_rol'] = $usuario['id_rol']; 

// Login exitoso
echo json_encode([
    "success" => true,
    "nombre"  => $usuario['nombre_completo'],
    "rol"     => $usuario['nombre_rol'],
    "mensaje" => "Bienvenido, " . $usuario['nombre_completo']
]);
?>