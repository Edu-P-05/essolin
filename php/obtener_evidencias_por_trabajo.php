<?php
// Archivo: php/obtener_evidencias_por_trabajo.php
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "mensaje" => "ID no proporcionado."]);
    exit;
}

$id_trabajo = $_GET['id'];

try {
    $pdo = getConexion();
    // Filtramos solo las fotos que pertenezcan a este ID
    $sql = "SELECT ruta_archivo, fecha_subida FROM evidencias WHERE id_trabajo = ? ORDER BY fecha_subida DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trabajo]);
    
    $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transformar a Base64 para que el navegador lo dibuje
    foreach ($evidencias as &$evidencia) {
        if (!empty($evidencia['ruta_archivo'])) {
            $base64 = base64_encode($evidencia['ruta_archivo']);
            $evidencia['ruta_archivo'] = 'data:image/jpeg;base64,' . $base64;
        }
    }

    echo json_encode(["success" => true, "data" => $evidencias]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>