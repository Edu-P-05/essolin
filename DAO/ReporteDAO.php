<?php
require_once '../php/conexion.php'; 

class ReporteDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion();
    }

    public function obtenerDatosReporte($estado, $fecha_inicio, $fecha_fin) {
        // Consulta base
        $sql = "SELECT t.id_trabajo, 
                       DATE(t.fecha_registro) as fecha_registro, 
                       t.fecha_programada, 
                       t.fecha_finalizacion, 
                       tt.nombre_tipo AS actividad, 
                       t.ubicacion, 
                       t.descripcion, 
                       t.estado 
                FROM trabajos t
                INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo
                WHERE 1=1"; 
                
        $parametros = [];

        // Filtro de estado
        if ($estado !== '') {
            if ($estado === 'Pendientes') {
                // Agrupación lógica
                $sql .= " AND (t.estado = 'Programado' OR t.estado = 'En Proceso')";
            } else {
                $sql .= " AND t.estado = ?";
                $parametros[] = $estado;
            }
        }

        // Filtro de fecha inicio
        if ($fecha_inicio !== '') {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $parametros[] = $fecha_inicio;
        }

        // Filtro de fecha fin
        if ($fecha_fin !== '') {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $parametros[] = $fecha_fin;
        }

        $sql .= " ORDER BY t.fecha_registro DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>