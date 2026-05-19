<?php
// Archivo: php/guardar_trabajo.php
require_once 'conexion.php';

// Cabeceras para recibir JSON
header("Content-Type: application/json; charset=utf-8");

// Obtener los datos enviados por JavaScript (Fetch)
$datos = json_decode(file_get_contents('php://input'), true);

$id_tipo = $datos['id_tipo'] ?? '';
$id_cuadrilla = $datos['id_cuadrilla'] ?? '';
$ubicacion = trim($datos['ubicacion'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$id_usuario = $datos['id_usuario'] ?? 1; // Asumimos el usuario 1 si no se envía

// Validación básica: Que no lleguen campos vacíos
if (empty($id_tipo) || empty($id_cuadrilla) || empty($ubicacion) || empty($descripcion)) {
    echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios."]);
    exit;
}

try {
    $pdo = getConexion();
    
    // Preparar la consulta SQL para insertar
    $sql = "INSERT INTO trabajos (id_cuadrilla, id_tipo, ubicacion, descripcion, id_usuario) 
            VALUES (?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    
    // Ejecutar inyectando los datos de forma segura
    if ($stmt->execute([$id_cuadrilla, $id_tipo, $ubicacion, $descripcion, $id_usuario])) {
        echo json_encode(["success" => true, "mensaje" => "Trabajo registrado correctamente."]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "No se pudo insertar el registro en la base de datos."]);
    }
    
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()]);
}
?>