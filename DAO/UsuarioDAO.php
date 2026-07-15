<?php
require_once '../php/conexion.php';
require_once '../Modelos/Usuarios.php'; // Asegúrate de que el nombre del archivo sea correcto

class UsuarioDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion(); 
    }

    // 1. (El que ya tenías) Para el Login
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
        return null; 
    }

    // 2. NUEVO: Listar todos los usuarios para la tabla
    public function listarTodos() {
        $query = "
            SELECT u.id_usuario, u.nombre_completo, u.email, r.nombre_rol, u.estado 
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC
        ";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $lista = [];
        foreach ($resultados as $row) {
            $u = new Usuario();
            $u->id_usuario = $row['id_usuario'];
            $u->nombre_completo = $row['nombre_completo'];
            $u->email = $row['email']; // En tu JS lo llamas 'usuario', pero en BD suele ser 'email'
            $u->nombre_rol = $row['nombre_rol'];
            $u->estado = $row['estado'];
            $lista[] = $u;
        }
        return $lista;
    }

    // 3. NUEVO: Registrar un usuario nuevo
    public function guardar($usuario) {
        try {
            $query = "INSERT INTO usuarios (nombre_completo, email, password, id_rol, estado) VALUES (?, ?, ?, ?, 'Activo')";
            $stmt = $this->conexion->prepare($query);
            return $stmt->execute([
                $usuario->nombre_completo,
                $usuario->email,
                $usuario->password,
                $usuario->id_rol
            ]);
        } catch (PDOException $e) {
            // Si hay un error (como un correo duplicado), lo atrapamos y devolvemos false silenciosamente
            return false; 
        }
    }

    // 4. Modificar datos de un usuario existente (CON PROTECCIÓN)
    public function editar($usuario) {
        try {
            $query = "UPDATE usuarios SET nombre_completo = ?, email = ?, id_rol = ? WHERE id_usuario = ?";
            $stmt = $this->conexion->prepare($query);
            return $stmt->execute([
                $usuario->nombre_completo,
                $usuario->email,
                $usuario->id_rol,
                $usuario->id_usuario
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 5. NUEVO: Cambiar la contraseña (separado de editar para mayor seguridad)
    public function actualizarPassword($id_usuario, $password_encriptada) {
        $query = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$password_encriptada, $id_usuario]);
    }

    // 6. NUEVO: Suspender o Activar un usuario
    public function actualizarEstado($id_usuario, $estado) {
        $query = "UPDATE usuarios SET estado = ? WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$estado, $id_usuario]);
    }
}
?>