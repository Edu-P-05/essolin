<?php
/* ============================================================
   ESSOLIN - Gestión Industrial Eléctrica
   Archivo: php/obtener_trabajos.php
   Descripción: Devuelve todos los trabajos registrados en la BD

   Retorna (JSON array):
     [ { "id":1, "codigo":"T-01", "actividad":"...", "ubicacion":"...", "estado":"..." }, ... ]
   ============================================================ */

require_once 'conexion.php';

$pdo   = getConexion();
$stmt  = $pdo->query("SELECT id, codigo, actividad, ubicacion, estado FROM trabajos ORDER BY id DESC");
$datos = $stmt->fetchAll();

echo json_encode($datos);
?>
