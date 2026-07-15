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

if ($usuario->id_rol == 3) {
    echo json_encode([
        "success" => false, 
        "mensaje" => "Acceso denegado: El perfil de Técnico no tiene permisos para ingresar a este sistema web."
    ]);
    exit;
}

// Login exitoso
$_SESSION['usuario_logueado'] = true;
$_SESSION['id_usuario'] = $usuario->id_usuario; 
$_SESSION['nombre_usuario'] = $usuario->nombre_completo;
$_SESSION['id_rol'] = $usuario->id_rol; 
// Eliminamos la línea de $_SESSION['id_cuadrilla']

echo json_encode([
    "success" => true,
    "nombre"  => $usuario->nombre_completo,
    "rol"     => $usuario->nombre_rol,
    "id_rol"  => $usuario->id_rol
    // Eliminamos la línea de "id_cuadrilla" del JSON
]);
?>