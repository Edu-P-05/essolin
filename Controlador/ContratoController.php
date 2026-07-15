<?php
session_start();
require_once '../php/conexion.php';
require_once '../DAO/ContratoDAO.php';

// Verificamos si hay sesión activa
if (!isset($_SESSION['id_rol'])) {
    echo json_encode(["success" => false, "mensaje" => "Sesión no iniciada"]);
    exit;
}

$id_rol = $_SESSION['id_rol']; // 1: Admin, 2: Supervisor, 4: Secretaria
$datosJSON = json_decode(file_get_contents('php://input'), true);
$datos = is_array($datosJSON) ? $datosJSON : $_POST;
$accion = $_GET['accion'] ?? '';

$pdo = getConexion();
$contratoDAO = new ContratoDAO($pdo);

switch ($accion) {
    case 'listar':
        echo json_encode(["success" => true, "data" => $contratoDAO->listar()]);
        break;

    case 'guardar':
        // VALIDACIÓN: Solo Admin(1) o Secretaria(4) pueden crear/editar
        if ($id_rol != 1 && $id_rol != 4) {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos de edición."]);
            break;
        }
        
        $id = $datos['id_contrato'] ?? '';
        $res = $contratoDAO->guardar($id, $datos['codigo_padre'], $datos['descripcion'], $datos['estado']);
        echo json_encode(["success" => $res]);
        break;

    case 'cambiar_estado':
        // VALIDACIÓN: Solo Admin(1) o Secretaria(4) pueden cambiar estado
        if ($id_rol != 1 && $id_rol != 4) {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para modificar contratos."]);
            break;
        }

        $id_contrato = $datos['id_contrato'] ?? null;
        $nuevo_estado = $datos['nuevo_estado'] ?? null;
        
        if (!$id_contrato || !$nuevo_estado) {
            echo json_encode(["success" => false, "mensaje" => "Faltan datos para la operación"]);
            break;
        }

        // REGLA: Si queremos pasarlo a 'Inactivo', primero revisamos trabajos
        if ($nuevo_estado === 'Inactivo') {
            $pendientes = $contratoDAO->contarTrabajosPendientes($id_contrato);
            
            if ($pendientes > 0) {
                echo json_encode([
                    "success" => false, 
                    "mensaje" => "No se puede inactivar. Este contrato tiene $pendientes trabajo(s) que aún no están finalizados."
                ]);
                break;
            } elseif ($pendientes === -1) {
                echo json_encode(["success" => false, "mensaje" => "Error de base de datos."]);
                break;
            }
        }

        if ($contratoDAO->actualizarEstado($id_contrato, $nuevo_estado)) {
            echo json_encode(["success" => true, "mensaje" => "El contrato ahora está $nuevo_estado"]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "No se pudo actualizar el contrato"]);
        }
        break;
}
?>