<?php
require_once '../php/conexion.php';
require_once '../Modelos/Trabajo.php';

class TrabajoDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion();
    }

    // 1. LISTAR TRABAJOS (Esto ya lo tenías)
    public function listarTodos() {
        $query = "
            SELECT t.*, c.nombre_cuadrilla, tp.nombre_tipo, u.nombre_completo as nombre_usuario
            FROM trabajos t
            INNER JOIN cuadrillas c ON t.id_cuadrilla = c.id_cuadrilla
            INNER JOIN tipos_trabajo tp ON t.id_tipo = tp.id_tipo
            INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
            ORDER BY t.fecha_registro DESC
        ";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $listaTrabajos = [];
        foreach ($resultados as $row) {
            $trabajo = new Trabajo();
            $trabajo->id_trabajo = $row['id_trabajo'];
            $trabajo->ubicacion = $row['ubicacion'];
            $trabajo->descripcion = $row['descripcion'];
            $trabajo->estado = $row['estado'];
            $trabajo->nombre_cuadrilla = $row['nombre_cuadrilla'];
            $trabajo->nombre_tipo = $row['nombre_tipo'];
            $trabajo->nombre_usuario = $row['nombre_usuario'];
            
            $listaTrabajos[] = $trabajo;
        }
        
        return $listaTrabajos;
    }

    // 2. NUEVA FUNCIÓN: GUARDAR TRABAJO
    public function guardar($trabajo) {
        $query = "INSERT INTO trabajos (id_cuadrilla, id_tipo, ubicacion, descripcion, id_usuario, fecha_programada) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([
            $trabajo->id_cuadrilla,
            $trabajo->id_tipo,
            $trabajo->ubicacion,
            $trabajo->descripcion,
            $trabajo->id_usuario,
            $trabajo->fecha_programada
        ]);
    }

    // 3. NUEVA FUNCIÓN: ACTUALIZAR ESTADO
    public function actualizarEstado($id_trabajo, $estado) {
        // Si lo marcan como Finalizado, guardamos la fecha y hora exacta actual (NOW)
        if ($estado === 'Finalizado') {
            $query = "UPDATE trabajos SET estado = ?, fecha_finalizacion = NOW() WHERE id_trabajo = ?";
        } else {
            // Si lo regresan a Programado o En Proceso, limpiamos la fecha de finalización
            $query = "UPDATE trabajos SET estado = ?, fecha_finalizacion = NULL WHERE id_trabajo = ?";
        }
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$estado, $id_trabajo]);
    }
}
?>