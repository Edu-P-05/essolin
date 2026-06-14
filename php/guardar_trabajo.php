<?php
// Archivo: php/guardar_trabajo.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);

// Recibimos todos los datos, incluyendo la fecha programada
$id_tipo = $data['id_tipo'] ?? '';
$id_cuadrilla = $data['id_cuadrilla'] ?? '';
$ubicacion = $data['ubicacion'] ?? '';
$descripcion = $data['descripcion'] ?? '';
$fecha_programada = $data['fecha_programada'] ?? '';
$id_usuario = $data['id_usuario'] ?? 1; 

if (empty($id_tipo) || empty($ubicacion) || empty($descripcion) || empty($fecha_programada)) {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios"]);
    exit;
}

try {
    $pdo = getConexion();
    
    // Insertamos incluyendo la fecha_programada. Por defecto el estado inicial será 'Programado'
    $sql = "INSERT INTO trabajos (id_tipo, id_cuadrilla, ubicacion, descripcion, fecha_programada, estado) 
            VALUES (?, ?, ?, ?, ?, 'Programado')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tipo, $id_cuadrilla, $ubicacion, $descripcion, $fecha_programada]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>