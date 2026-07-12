<?php
session_start();
require_once '../DAO/EvidenciaDAO.php';

$input = file_get_contents('php://input');
$datosJSON = json_decode($input, true);
$datos = is_array($datosJSON) ? $datosJSON : $_POST;

$accion = $_GET['accion'] ?? $datos['accion'] ?? '';

$evidenciaDAO = new EvidenciaDAO();
header('Content-Type: application/json');

switch ($accion) {
    case 'listar':
        $evidencias = $evidenciaDAO->listarTodas();
        echo json_encode(["success" => true, "data" => $evidencias]);
        break;

    case 'listar_por_trabajo':
        $id = $_GET['id'] ?? 0;
        $evidencias = $evidenciaDAO->listarPorTrabajo($id);
        echo json_encode(["success" => true, "data" => $evidencias]);
        break;

    case 'listar_recientes':
        $evidencias = $evidenciaDAO->listarRecientes(5);
        echo json_encode(["success" => true, "data" => $evidencias]);
        break;

    case 'subir':
        // 1. Validamos que la imagen llegó
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] != UPLOAD_ERR_OK) {
            echo json_encode(["success" => false, "mensaje" => "Error al recibir la imagen."]);
            exit;
        }

        $id_trabajo = $datos['id_trabajo'] ?? 0;

        // 2. Leemos el archivo binario directamente desde la memoria temporal
        $datosImagen = file_get_contents($_FILES['foto']['tmp_name']);
        
        // 3. Creamos el objeto Evidencia
        $evidencia = new Evidencia();
        $evidencia->id_trabajo = $id_trabajo;
        
        // AQUÍ VA LA LÍNEA: Guardamos el binario crudo en el objeto
        $evidencia->ruta_archivo = $datosImagen; 

        // 4. Enviamos el objeto al DAO para que haga el INSERT en la BD
        if ($evidenciaDAO->guardar($evidencia)) {
            echo json_encode(["success" => true, "mensaje" => "Foto guardada en BD correctamente."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error en el DAO al guardar."]);
        }
        break;

    case 'eliminar':
        $id_evidencia = $datos['id_evidencia'] ?? 0;

        // Como está en la BD, ya no necesitamos buscar carpetas ni usar unlink()
        // Solo lanzamos la orden de eliminar el registro al DAO
        if ($evidenciaDAO->eliminar($id_evidencia)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar de la BD"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no reconocida"]);
        break;
}
?>