<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

// BARRERA DE SEGURIDAD: Solo Administradores (Rol 1) pueden hacer esto
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(["success" => false, "mensaje" => "Acceso denegado. Permisos insuficientes."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id_usuario = $data['id_usuario'] ?? '';
$nombre     = trim($data['nombre'] ?? '');
$usuario    = trim($data['usuario'] ?? '');
$rol        = trim($data['rol'] ?? '');
$password   = trim($data['password'] ?? ''); // Puede venir vacía

if (empty($id_usuario) || empty($nombre) || empty($usuario) || empty($rol)) {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios."]);
    exit;
}

// Convertimos el texto del rol a su ID numérico
$id_rol_nuevo = 3; // Por defecto Técnico
if ($rol === 'Administrador') $id_rol_nuevo = 1;
if ($rol === 'Supervisor') $id_rol_nuevo = 2;
if ($rol === 'Secretaria') $id_rol_nuevo = 4;

try {
    $pdo = getConexion();
    
    // Si el Admin escribió una contraseña nueva, la actualizamos junto con el resto
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, id_rol = ?, password = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $usuario, $id_rol_nuevo, $hash, $id_usuario]);
    } else {
        // Si dejó la contraseña en blanco, actualizamos solo los demás datos
        $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, id_rol = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $usuario, $id_rol_nuevo, $id_usuario]);
    }

    echo json_encode(["success" => true, "mensaje" => "Usuario actualizado correctamente."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>