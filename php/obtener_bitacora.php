<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$id_trabajo = $_GET['id'] ?? '';

try {
    $pdo = getConexion();
    $sql = "SELECT b.comentario, DATE_FORMAT(b.fecha_comentario, '%d/%m/%Y %H:%i') as fecha, u.nombre_completo as autor 
            FROM bitacora_trabajos b
            INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
            WHERE b.id_trabajo = ? 
            ORDER BY b.fecha_comentario ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trabajo]);
    
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sanitizar salidas (Protección XSS)
    foreach ($historial as &$nota) {
        $nota['comentario'] = htmlspecialchars($nota['comentario'], ENT_QUOTES, 'UTF-8');
    }
    unset($nota);

    echo json_encode(["success" => true, "data" => $historial]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
}
?>