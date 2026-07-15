<?php
session_start();
require_once '../DAO/ReporteDAO.php';

header("Content-Type: application/json; charset=utf-8");

// === CANDADO DE SEGURIDAD ===
// Solo Admin (1) y Secretaria (4) pueden acceder a los reportes
$id_rol = $_SESSION['id_rol'] ?? 0;

if ($id_rol != 1 && $id_rol != 4) {
    echo json_encode([
        "success" => false, 
        "mensaje" => "Acceso denegado: No tienes permisos para ver reportes."
    ]);
    exit; // Detenemos la ejecución inmediatamente
}
// ============================

// Recibimos la acción
$accion = $_GET['accion'] ?? 'generar';

switch ($accion) {
    case 'generar':
        $estado = $_GET['estado'] ?? '';
        $fecha_inicio = $_GET['fecha_inicio'] ?? '';
        $fecha_fin = $_GET['fecha_fin'] ?? '';

        try {
            $reporteDAO = new ReporteDAO();
            $resultados = $reporteDAO->obtenerDatosReporte($estado, $fecha_inicio, $fecha_fin);

            // Sanitización básica contra XSS
            foreach ($resultados as &$fila) {
                $fila['ubicacion'] = htmlspecialchars($fila['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8');
            }
            unset($fila);

            echo json_encode(["success" => true, "data" => $resultados]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida en Reportes"]);
        break;
}
?>