<?php
// Archivo: php/eliminar_evidencia.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

// BARRERA DE SEGURIDAD: Si no eres Admin (1), te rechaza de inmediato
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(["success" => false, "mensaje" => "No tienes permisos de Administrador para realizar esta acción."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id_evidencia = $data['id_evidencia'] ?? '';
$ruta_archivo = $data['ruta'] ?? '';

if (empty($id_evidencia)) {
    echo json_encode(["success" => false, "mensaje" => "Falta el ID de la foto."]);
    exit;
}

try {
    $pdo = getConexion();
    
    // 1. Borramos el registro de la Base de Datos
    $stmt = $pdo->prepare("DELETE FROM evidencias WHERE id_evidencia = ?");
    $stmt->execute([$id_evidencia]);

    // 2. Borramos el archivo físico del disco duro (si existe)
    if (!empty($ruta_archivo) && file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>