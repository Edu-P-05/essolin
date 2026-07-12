<?php
session_start();
require_once '../DAO/TrabajoDAO.php';

// 1. Capturamos los datos que vengan por POST (JSON)
$input = file_get_contents('php://input');
$datos = json_decode($input, true);

// SOLUCIÓN: Si no envían un JSON (ej. petición GET), creamos un array vacío para que PHP no arroje errores
if (!is_array($datos)) {
    $datos = [];
}

// 2. Buscamos la acción, dando prioridad a la URL (GET) y luego al JSON (POST)
$accion = $_GET['accion'] ?? $datos['accion'] ?? '';

$trabajoDAO = new TrabajoDAO();

// 3. Forzamos a que PHP devuelva estrictamente un JSON
header('Content-Type: application/json');

switch ($accion) {
    case 'listar':
        $trabajos = $trabajoDAO->listarTodos();
        echo json_encode(["success" => true, "data" => $trabajos]);
        break;

    case 'guardar':
        $trabajo = new Trabajo();
        $trabajo->id_tipo = $datos['id_tipo'];
        $trabajo->id_cuadrilla = $datos['id_cuadrilla'];
        $trabajo->ubicacion = $datos['ubicacion'];
        $trabajo->descripcion = $datos['descripcion'];
        $trabajo->fecha_programada = $datos['fecha_programada'];
        
        $trabajo->id_usuario = $_SESSION['id_usuario'] ?? 1;

        if ($trabajoDAO->guardar($trabajo)) {
            echo json_encode(["success" => true, "mensaje" => "Trabajo guardado correctamente"]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al guardar en la base de datos"]);
        }
        break;

    case 'actualizar_estado':
        $id_trabajo = $datos['id_trabajo'];
        $estado = $datos['estado'];
        
        if ($trabajoDAO->actualizarEstado($id_trabajo, $estado)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "No se pudo actualizar el estado"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no reconocida"]);
        break;
}
?>