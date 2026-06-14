<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();
    $sql = "SELECT e.id_evidencia, e.ruta_archivo, e.fecha_subida, t.id_trabajo, t.ubicacion 
            FROM evidencias e
            INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
            ORDER BY e.id_evidencia DESC 
            LIMIT 6";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // EL SECRETO: Traducir el binario (BLOB) a texto (Base64)
    foreach ($fotos as &$foto) {
        // Detectamos si la información es binaria pura (las imágenes contienen caracteres nulos \0)
        if (!empty($foto['ruta_archivo']) && strpos($foto['ruta_archivo'], "\0") !== false) {
            // Convertimos la imagen a un formato seguro de texto que HTML puede dibujar directamente
            $foto['ruta_archivo'] = 'data:image/jpeg;base64,' . base64_encode($foto['ruta_archivo']);
        }
    }
    unset($foto); // Buena práctica para limpiar la memoria

    echo json_encode(["success" => true, "data" => $fotos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error SQL: " . $e->getMessage()]);
}
?>