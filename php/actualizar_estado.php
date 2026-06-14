<?php
// Archivo: php/actualizar_estado.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
$id_trabajo = $data['id_trabajo'] ?? '';
$estado = $data['estado'] ?? '';

if (empty($id_trabajo) || empty($estado)) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

try {
    $pdo = getConexion();
    
    // Si el estado es Finalizado, actualizamos también la fecha de finalización con la hora actual (NOW)
    if ($estado === 'Finalizado') {
        $sql = "UPDATE trabajos SET estado = ?, fecha_finalizacion = NOW() WHERE id_trabajo = ?";
    } else {
        // Si lo regresan a En Proceso (antes de bloquearlo), borramos la fecha de finalización
        $sql = "UPDATE trabajos SET estado = ?, fecha_finalizacion = NULL WHERE id_trabajo = ?";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$estado, $id_trabajo]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>