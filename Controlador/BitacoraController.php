<?php
session_start();
require_once '../DAO/BitacoraDAO.php';

header("Content-Type: application/json; charset=utf-8");

// 1. SEGURIDAD: Validar sesión activa antes de procesar cualquier acción
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "Acceso denegado: Sesión no válida."]);
    exit;
}

$accion = $_GET['accion'] ?? ($_POST['accion'] ?? '');
$bitacoraDAO = new BitacoraDAO();

// Identificamos al usuario y su rol desde la sesión
$id_usuario_actual = $_SESSION['id_usuario'];
$rol_usuario = $_SESSION['id_rol'] ?? 0; 

try {
    switch ($accion) {
        case 'listar':
            $id_trabajo = $_GET['id'] ?? '';
            
            if (empty($id_trabajo)) {
                echo json_encode(["success" => false, "mensaje" => "ID de trabajo no proporcionado."]);
                exit;
            }

            $historial = $bitacoraDAO->obtenerPorTrabajo($id_trabajo);
            
            // Sanitizar salidas (Protección XSS)
            foreach ($historial as &$nota) {
                $nota['comentario'] = htmlspecialchars($nota['comentario'], ENT_QUOTES, 'UTF-8');
            }
            unset($nota);

            // Verificamos si el usuario actual es Admin (1) o Supervisor (2) para el botón de eliminar
            $puede_eliminar = ($rol_usuario == 1 || $rol_usuario == 2);

            echo json_encode([
                "success" => true, 
                "data" => $historial, 
                "puede_eliminar" => $puede_eliminar
            ]);
            break;

        case 'guardar':
            $data = json_decode(file_get_contents("php://input"), true);
            $id_trabajo = $data['id_trabajo'] ?? '';
            $comentario = $data['comentario'] ?? '';

            if (empty($id_trabajo) || empty($comentario)) {
                echo json_encode(["success" => false, "mensaje" => "Datos incompletos para guardar el comentario."]);
                exit;
            }

            if ($bitacoraDAO->guardarComentario($id_trabajo, $id_usuario_actual, $comentario)) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Error al guardar en base de datos."]);
            }
            break;

        case 'eliminar':
            // Protección de backend: Solo Admin (1) y Supervisor (2) pueden eliminar
            if ($rol_usuario != 1 && $rol_usuario != 2) {
                echo json_encode(["success" => false, "mensaje" => "No tienes permisos para eliminar comentarios."]);
                exit;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $id_bitacora = $data['id_bitacora'] ?? '';

            if (empty($id_bitacora)) {
                echo json_encode(["success" => false, "mensaje" => "ID de comentario no recibido."]);
                exit;
            }

            if ($bitacoraDAO->eliminarComentario($id_bitacora)) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Error al eliminar el comentario."]);
            }
            break;

        default:
            echo json_encode(["success" => false, "mensaje" => "Acción '$accion' no válida."]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error interno del servidor: " . $e->getMessage()]);
}
?>