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
$id_rol = $_SESSION['id_rol'] ?? null;
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_cuadrilla = $_SESSION['id_cuadrilla'] ?? null;
$trabajoDAO = new TrabajoDAO();

// 3. Forzamos a que PHP devuelva estrictamente un JSON
header('Content-Type: application/json');



switch ($accion) {
    case 'listar':
        // Recibimos los filtros del frontend
        $busqueda = $_GET['busqueda'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $tipo = $_GET['tipo'] ?? '';

        try {
            // === AQUÍ ESTÁ EL CAMBIO ===
            // Pasamos las 3 variables de filtro + las 3 variables de sesión (rol, usuario, cuadrilla)
            $resultados = $trabajoDAO->listarTrabajosFiltrados(
                $busqueda, 
                $estado, 
                $tipo, 
                $id_rol, 
                $id_usuario, 
                $id_cuadrilla
            );
            
            // Sanitización contra XSS
            foreach ($resultados as &$fila) {
                $fila['ubicacion'] = htmlspecialchars($fila['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8');
                $fila['supervisor'] = htmlspecialchars($fila['supervisor'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8');
            }
            unset($fila);

            echo json_encode(["success" => true, "data" => $resultados]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
        }
        break;

    case 'guardar':
        $datos = json_decode(file_get_contents("php://input"), true);
        require_once '../Modelos/Trabajo.php'; // Aseguramos que el molde esté cargado

        $trabajo = new Trabajo();
        
        // Asignamos los nuevos datos del formulario corporativo
        $trabajo->id_contrato = $datos['id_contrato'] ?? null;
        $trabajo->tipo = $datos['tipo'] ?? ''; 
        $trabajo->elemento = $datos['elemento'] ?? '';
        $trabajo->prioridad = $datos['prioridad'] ?? 'Media';
        $trabajo->ubicacion = $datos['ubicacion'] ?? '';
        $trabajo->descripcion = $datos['descripcion'] ?? '';
        // Tomamos el ID del usuario logueado, o por defecto el 1 si se pierde la sesión
        $trabajo->id_usuario = $_SESSION['id_usuario'] ?? 1;
        $trabajo->id_supervisor = $datos['id_supervisor'] ?? null;
        $trabajo->fecha_programada = $datos['fecha_programada'] ?? '';
        
        // Estado por defecto al crear un trabajo nuevo
        $trabajo->estado = 'Asignado'; 

        // Le pasamos el objeto completo al DAO
        if ($trabajoDAO->guardar($trabajo)) {
            echo json_encode(["success" => true, "mensaje" => "Trabajo creado y asignado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al guardar en la base de datos."]);
        }
        break;

    case 'actualizar_estado':
        $id_trabajo = $datos['id_trabajo'] ?? null;
        $estado = $datos['estado'] ?? null;
        
        // 1. VALIDACIÓN DE ROL: Solo Admin, Secretaria o Supervisor pueden cambiar estados
        if ($id_rol > 2 && $id_rol != 4) { // Asumiendo que 1=Admin, 2=Sup, 4=Sec
             echo json_encode(["success" => false, "mensaje" => "No tienes permisos."]);
             break;
        }

        // 2. CANDADO DE ESTADO: No modificar finalizados (Aplica a todos)
        $estado_actual = $trabajoDAO->obtenerEstadoActual($id_trabajo); 
        if ($estado_actual === 'Finalizado') {
            echo json_encode(["success" => false, "mensaje" => "Acción denegada: El trabajo ya fue cerrado."]);
            break;
        }
        
        if ($trabajoDAO->actualizarEstado($id_trabajo, $estado)) {
            echo json_encode(["success" => true, "mensaje" => "Estado actualizado."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar."]);
        }
        break;

    case 'datos_formulario':
        try {
            $contratos = $trabajoDAO->obtenerContratosActivos();
            $supervisores = $trabajoDAO->obtenerSupervisores();
            echo json_encode(["success" => true, "contratos" => $contratos, "supervisores" => $supervisores]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
        }
        break;

    case 'asignar_cuadrilla':
        $id_trabajo = $datos['id_trabajo'] ?? null;
        $id_cuadrilla = $datos['id_cuadrilla'] ?? null;
        
        if (!$id_trabajo || !$id_cuadrilla) {
            echo json_encode(["success" => false, "mensaje" => "Faltan datos para la asignación"]);
            break;
        }

        if ($trabajoDAO->asignarCuadrilla($id_trabajo, $id_cuadrilla)) {
            echo json_encode(["success" => true, "mensaje" => "Cuadrilla asignada correctamente"]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "No se pudo asignar la cuadrilla"]);
        }
        break;

    case 'eliminar':
        // 1. VALIDACIÓN DE ROL: Solo Admin (1) o Secretaria (4) pueden borrar
        if ($id_rol != 1 && $id_rol != 4) {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para eliminar."]);
            break;
        }
        
        $id_trabajo = $datos['id_trabajo'] ?? null;
        if ($trabajoDAO->eliminarTrabajo($id_trabajo)) {
            echo json_encode(["success" => true, "mensaje" => "Trabajo eliminado."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "No se pudo eliminar."]);
        }
        break;

    case 'obtener_detalle_pdf':
        $id_trabajo = $_GET['id_trabajo'] ?? null;
        
        if (!$id_trabajo) {
            echo json_encode(["success" => false, "mensaje" => "ID de trabajo no proporcionado"]);
            break;
        }

        // Llamamos a las funciones del DAO en lugar de hacer el SQL aquí
        $trabajo = $trabajoDAO->obtenerDetalleParaPDF($id_trabajo);
        $evidencias = $trabajoDAO->obtenerEvidenciasPorTrabajo($id_trabajo);

        if ($trabajo) {
            echo json_encode([
                "success" => true, 
                "trabajo" => $trabajo,
                "evidencias" => $evidencias
            ]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "No se pudo extraer la información de la base de datos."]);
        }
        break;
    
    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no reconocida"]);
        break;
}
?>