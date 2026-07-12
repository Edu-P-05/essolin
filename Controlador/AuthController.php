<?php
session_start();
require_once '../DAO/UsuarioDAO.php';

$datos = json_decode(file_get_contents('php://input'), true);
$email = trim($datos['email'] ?? '');
$password = trim($datos['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "mensaje" => "Correo y contraseña son obligatorios."]);
    exit;
}

// Usamos el DAO para buscar
$usuarioDAO = new UsuarioDAO();
$usuario = $usuarioDAO->buscarPorEmail($email);

if (!$usuario) {
    echo json_encode(["success" => false, "mensaje" => "Usuario no encontrado."]);
    exit;
}

if ($usuario->estado === 'Suspendido') {
    echo json_encode(["success" => false, "mensaje" => "Acceso denegado. Cuenta suspendida."]);
    exit;
}

if (!password_verify($password, $usuario->password)) {
    echo json_encode(["success" => false, "mensaje" => "Contraseña incorrecta."]);
    exit;
}

// Login exitoso
$_SESSION['usuario_logueado'] = true;
$_SESSION['id_usuario'] = $usuario->id_usuario; 
$_SESSION['nombre_usuario'] = $usuario->nombre_completo;
$_SESSION['id_rol'] = $usuario->id_rol; 

echo json_encode([
    "success" => true,
    "nombre"  => $usuario->nombre_completo,
    "rol"     => $usuario->nombre_rol
]);
?>