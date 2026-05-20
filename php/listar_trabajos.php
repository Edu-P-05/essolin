<?php
// Archivo: php/listar_trabajos.php
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();
    // Añadimos t.estado a la consulta SELECT
    $sql = "SELECT t.id_trabajo, tt.nombre_tipo AS actividad, t.ubicacion, t.descripcion, t.estado 
            FROM trabajos t
            INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo
            ORDER BY t.fecha_registro DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Obtenemos los datos como un arreglo asociativo
    $trabajos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($trabajos as &$t) {
        $t['ubicacion'] = htmlspecialchars($t['ubicacion'], ENT_QUOTES, 'UTF-8');
        $t['descripcion'] = htmlspecialchars($t['descripcion'], ENT_QUOTES, 'UTF-8');
    }
    unset($t); // Rompemos la referencia por seguridad en memoria

    echo json_encode(["success" => true, "data" => $trabajos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error al consultar: " . $e->getMessage()]);
}
?>