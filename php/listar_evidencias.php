<?php
// Archivo: php/listar_evidencias.php
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();
    // Unimos evidencias con trabajos, y también con tipos_trabajo para obtener el nombre de la actividad
    $sql = "SELECT e.id_evidencia, e.ruta_archivo, e.fecha_subida, 
                   t.id_trabajo, t.ubicacion, 
                   tt.nombre_tipo AS actividad
            FROM evidencias e
            INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
            INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo
            ORDER BY e.fecha_subida DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Obtener todas las filas
    $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recorrer las filas para traducir el código binario a una imagen visible (Base64)
    foreach ($evidencias as &$evidencia) {
        if (!empty($evidencia['ruta_archivo'])) {
            $base64 = base64_encode($evidencia['ruta_archivo']);
            $evidencia['ruta_archivo'] = 'data:image/jpeg;base64,' . $base64;
        }
    }

    echo json_encode(["success" => true, "data" => $evidencias]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()]);
}
?>