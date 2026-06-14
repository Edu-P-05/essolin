<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data['nombre'] ?? '';
$email = $data['usuario'] ?? ''; // El frontend manda 'usuario', pero lo guardamos en 'email'
$password = $data['password'] ?? '';
$rolTexto = $data['rol'] ?? '';

if (empty($nombre) || empty($email) || empty($password) || empty($rolTexto)) {
    echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Convertimos el texto del rol a su respectivo número (id_rol)
$id_rol = 3; // Por defecto Técnico
if ($rolTexto === 'Administrador') $id_rol = 1;
else if ($rolTexto === 'Supervisor') $id_rol = 2;
else if ($rolTexto === 'Tecnico') $id_rol = 3;
else if ($rolTexto === 'Secretaria') $id_rol = 4;

try {
    $pdo = getConexion();
    
    // 1. Verificamos que el email no exista ya en la tabla
    $stmtCheck = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        echo json_encode(["success" => false, "mensaje" => "Este Email ya está registrado en el sistema."]);
        exit;
    }

    // 2. ENCRIPTACIÓN: Hasheamos la contraseña por seguridad
    $passHash = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insertamos en la BD usando tus columnas exactas
    $sql = "INSERT INTO usuarios (nombre_completo, email, password, id_rol) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $email, $passHash, $id_rol]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>