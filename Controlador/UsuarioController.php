<?php
session_start();
require_once '../DAO/UsuarioDAO.php';
require_once '../php/conexion.php';
require_once '../Modelos/Usuarios.php';

// FORZAMOS A QUE PHP DEVUELVA SIEMPRE UN JSON
header('Content-Type: application/json');

// --- PREPARAMOS DATOS ---
$input = file_get_contents('php://input');
$datosJSON = json_decode($input, true);
$datos = is_array($datosJSON) ? $datosJSON : $_POST;
$accion = $_GET['accion'] ?? $datos['accion'] ?? '';

// --- 🔓 EXCEPCIÓN: CERRAR SESIÓN ---
// Esta acción debe ir ANTES del candado de seguridad, para que todos puedan salir.
if ($accion === 'logout') {
    session_unset(); 
    session_destroy(); 
    header("Location: ../Vistas/index2.html"); 
    exit;
}

// --- 🔒 CANDADO DE SEGURIDAD MÁXIMA ---
// A partir de aquí, solo entra el Administrador (rol 1)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(["success" => false, "mensaje" => "Acceso denegado. Solo el administrador puede realizar esta acción."]);
    exit; 
}

// Envolvemos todo en un Try-Catch para que si la BD falla, nos dé un mensaje exacto
try {
    $usuarioDAO = new UsuarioDAO();

    switch ($accion) {
        case 'listar':
            $usuarios = $usuarioDAO->listarTodos();
            $listaFinal = [];
            
            foreach ($usuarios as $u) {
                $listaFinal[] = [
                    "id_usuario" => $u->id_usuario,
                    "nombre_completo" => $u->nombre_completo,
                    "usuario" => $u->email, 
                    "rol" => $u->nombre_rol, 
                    "estado" => $u->estado,
                    "es_yo" => ($u->id_usuario == $_SESSION['id_usuario']) 
                ];
            }
            
            echo json_encode(["success" => true, "data" => $listaFinal]);
            break;

        case 'guardar':
            $existeCorreo = $usuarioDAO->buscarPorEmail($datos['usuario']);
            if ($existeCorreo) {
                echo json_encode(["success" => false, "mensaje" => "El correo '" . $datos['usuario'] . "' ya está en uso."]);
                exit; 
            }

            $usuario = new Usuario();
            $usuario->nombre_completo = $datos['nombre'];
            $usuario->email = $datos['usuario'];
            $usuario->password = password_hash($datos['password'], PASSWORD_DEFAULT); 
            $usuario->id_rol = $datos['rol'];

            if ($usuarioDAO->guardar($usuario)) {
                echo json_encode(["success" => true, "mensaje" => "Usuario registrado"]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "No se pudo insertar en MySQL."]);
            }
            break;

        case 'editar':
            $existeCorreo = $usuarioDAO->buscarPorEmail($datos['usuario']);
            if ($existeCorreo && $existeCorreo->id_usuario != $datos['id_usuario']) {
                echo json_encode(["success" => false, "mensaje" => "El correo '" . $datos['usuario'] . "' ya le pertenece a otro usuario."]);
                exit;
            }

            $usuario = new Usuario();
            $usuario->id_usuario = $datos['id_usuario'];
            $usuario->nombre_completo = $datos['nombre'];
            $usuario->email = $datos['usuario'];
            $usuario->id_rol = $datos['rol'];

            $edicionCorrecta = $usuarioDAO->editar($usuario);

            if (!empty($datos['password'])) {
                $passEncriptada = password_hash($datos['password'], PASSWORD_DEFAULT);
                $usuarioDAO->actualizarPassword($usuario->id_usuario, $passEncriptada);
            }

            if ($edicionCorrecta) {
                echo json_encode(["success" => true, "mensaje" => "Usuario actualizado"]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Error al actualizar el usuario"]);
            }
            break;

        case 'actualizar_estado':
            $id_usuario = $datos['id_usuario'];
            $estado = $datos['estado'];

            if ($id_usuario == $_SESSION['id_usuario'] && $estado == 'Suspendido') {
                echo json_encode(["success" => false, "mensaje" => "No puedes suspender tu propia cuenta."]);
                exit;
            }

            if ($usuarioDAO->actualizarEstado($id_usuario, $estado)) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "No se pudo cambiar el estado"]);
            }
            break;
            
        default:
            echo json_encode(["success" => false, "mensaje" => "Acción '$accion' no reconocida"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
}
?>