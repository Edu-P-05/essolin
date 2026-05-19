<?php
/* ============================================================
   ESSOLIN - Gestión Industrial Eléctrica
   Archivo: php/actualizar_estado.php
   Descripción: Actualiza el estado de un trabajo en la BD

   Recibe (JSON POST):
     { "id": "T-01", "estado": "proceso" }

   Retorna (JSON):
     { "success": true/false, "mensaje": "..." }
   ============================================================ */

require_once 'conexion.php';

$datos  = json_decode(file_get_contents('php://input'), true);
$codigo = trim($datos['id']     ?? '');
$estado = trim($datos['estado'] ?? '');

$estadosValidos = ['programado', 'proceso', 'finalizado'];

if (empty($codigo) || !in_array($estado, $estadosValidos)) {
    echo json_encode(["success" => false, "mensaje" => "Datos inválidos."]);
    exit;
}

$pdo  = getConexion();
$stmt = $pdo->prepare("UPDATE trabajos SET estado = ? WHERE codigo = ?");
$stmt->execute([$estado, $codigo]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => true, "mensaje" => "Estado actualizado a '$estado'."]);
} else {
    echo json_encode(["success" => false, "mensaje" => "No se encontró el trabajo con código $codigo."]);
}
?>
