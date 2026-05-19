<?php
// Archivo: php/listar_trabajos.php
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();
    // Añadimos t.descripcion a la consulta
    $sql = "SELECT t.id_trabajo, tt.nombre_tipo AS actividad, t.ubicacion, t.descripcion 
            FROM trabajos t
            INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo
            ORDER BY t.fecha_registro DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $trabajos = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $trabajos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error al consultar: " . $e->getMessage()]);
}
?>