<?php
require_once '../php/conexion.php';
require_once '../Modelos/Usuarios.php';

class UsuarioDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion(); // Llama a tu conexión actual
    }

    public function buscarPorEmail($email) {
        $stmt = $this->conexion->prepare("
            SELECT u.id_usuario, u.nombre_completo, u.password, r.nombre_rol, u.id_rol, u.estado 
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $usuario = new Usuario();
            $usuario->id_usuario = $row['id_usuario'];
            $usuario->nombre_completo = $row['nombre_completo'];
            $usuario->password = $row['password'];
            $usuario->nombre_rol = $row['nombre_rol'];
            $usuario->id_rol = $row['id_rol'];
            $usuario->estado = $row['estado'];
            return $usuario;
        }
        return null; // Si no lo encuentra, devuelve vacío
    }
}
?>