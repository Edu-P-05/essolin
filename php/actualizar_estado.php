<?php
// Archivo: php/actualizar_estado.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

// Leemos los datos enviados por JavaScript
$datos = json_decode(file_get_contents('php://input'), true);

$id_trabajo = $datos['id_trabajo'] ?? '';
$nuevo_estado = $datos['estado'] ?? '';

// Validar que no lleguen vacíos
if (empty($id_trabajo) || empty($nuevo_estado)) {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos para actualizar."]);
    exit;
}

try {
    $pdo = getConexion();
    // Preparamos la actualización en la base de datos
    $stmt = $pdo->prepare("UPDATE trabajos SET estado = ? WHERE id_trabajo = ?");
    
    if ($stmt->execute([$nuevo_estado, $id_trabajo])) {
        echo json_encode(["success" => true, "mensaje" => "Estado actualizado a " . $nuevo_estado]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "No se pudo actualizar el registro."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error SQL: " . $e->getMessage()]);
}
?>