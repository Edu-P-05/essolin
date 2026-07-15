<?php
require_once '../php/conexion.php';

class BitacoraDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion();
    }

    // 1. Obtener la lista de comentarios
    public function obtenerPorTrabajo($id_trabajo) {
        // Aquí aplicamos el truco: b.id_comentario AS id_bitacora
        $sql = "SELECT b.id_comentario AS id_bitacora, b.comentario, DATE_FORMAT(b.fecha_comentario, '%d/%m/%Y %H:%i') as fecha, u.nombre_completo as autor 
                FROM bitacora_trabajos b
                INNER JOIN usuarios u ON b.id_usuario = u.id_usuario
                WHERE b.id_trabajo = ? 
                ORDER BY b.fecha_comentario ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_trabajo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Guardar un nuevo comentario
    public function guardarComentario($id_trabajo, $id_usuario, $comentario) {
        $sql = "INSERT INTO bitacora_trabajos (id_trabajo, id_usuario, comentario) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id_trabajo, $id_usuario, $comentario]);
    }

    // 3. Eliminar un comentario
    public function eliminarComentario($id_bitacora) {
        // Corregimos el WHERE con el nombre real de tu columna
        $sql = "DELETE FROM bitacora_trabajos WHERE id_comentario = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id_bitacora]);
    }
}
?>