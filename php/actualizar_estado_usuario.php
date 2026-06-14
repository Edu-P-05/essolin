<?php
// Archivo: php/actualizar_estado_usuario.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
$id_usuario = $data['id_usuario'] ?? '';
$nuevo_estado = $data['estado'] ?? '';

if (empty($id_usuario) || empty($nuevo_estado)) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

try {
    $pdo = getConexion();
    
    // Actualizamos el estado del usuario
    $sql = "UPDATE usuarios SET estado = ? WHERE id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nuevo_estado, $id_usuario]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>