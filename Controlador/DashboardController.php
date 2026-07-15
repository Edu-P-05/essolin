<?php
session_start();
require_once '../DAO/DashboardDAO.php';
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "Sesión no válida"]);
    exit;
}

$accion = $_GET['accion'] ?? '';
$dashboardDAO = new DashboardDAO();

// Capturamos identidad del usuario
$id_rol = $_SESSION['id_rol'] ?? null;
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_cuadrilla = $_SESSION['id_cuadrilla'] ?? null;

switch ($accion) {
    case 'obtener_datos':
        try {
            // Solicitamos los datos pasando los filtros de seguridad
            $conteos = $dashboardDAO->getConteoEstados($id_rol, $id_usuario);
            $promedioDias = $dashboardDAO->getTiempoPromedio($id_rol, $id_usuario);
            $actividades = $dashboardDAO->getActividades($id_rol, $id_usuario);
            $cuadrillas = $dashboardDAO->getProductividadCuadrillas($id_rol, $id_usuario);
            $kpisExtra = $dashboardDAO->obtenerKPIsExtra($id_rol, $id_usuario);
            $fotosRecientes = $dashboardDAO->getFotosRecientes(6, $id_rol, $id_usuario);
            echo json_encode([
                "success" => true,
                "programados" => $conteos['programados'] ?? 0,
                "en_proceso"  => $conteos['en_proceso'] ?? 0,
                "finalizados" => $conteos['finalizados'] ?? 0,
                "tiempo_promedio" => $promedioDias,
                "actividades" => $actividades,
                "cuadrillas"  => $cuadrillas,
                "contratos_activos" => $kpisExtra['contratos_activos'] ?? 0,
                "cuadrillas_libres" => $kpisExtra['cuadrillas_libres'] ?? 0,
                "fotos" => $fotosRecientes
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error al obtener métricas: " . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida en el Dashboard"]);
        break;
}
?>