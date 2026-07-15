<?php
require_once '../php/conexion.php';

class DashboardDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion();
    }

    // Filtro directo por supervisor
    private function generarFiltro($id_rol, $id_usuario) {
        if ($id_rol == 2) { 
            return " AND id_supervisor = " . (int)$id_usuario;
        }
        return ""; 
    }

    public function getConteoEstados($id_rol, $id_usuario) {
        $sql = "SELECT 
                    SUM(CASE WHEN estado = 'Asignado' THEN 1 ELSE 0 END) as programados,
                    SUM(CASE WHEN estado = 'En Proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'Finalizado' THEN 1 ELSE 0 END) as finalizados
                FROM trabajos WHERE 1=1";
        
        $parametros = [];
        
        // Si es supervisor, agregamos el parámetro de forma segura
        if ($id_rol == 2) { 
            $sql .= " AND id_supervisor = ?";
            $parametros[] = $id_usuario;
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActividades($id_rol, $id_usuario) {
        // En los INNER JOIN necesitamos el alias de la tabla trabajos (t)
        $filtro = ($id_rol == 2) ? " AND t.id_supervisor = " . (int)$id_usuario : "";
        $sql = "SELECT tt.nombre_tipo as actividad, COUNT(t.id_trabajo) as cantidad 
                FROM trabajos t 
                INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo 
                WHERE 1=1" . $filtro . " 
                GROUP BY t.id_tipo";
        
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductividadCuadrillas($id_rol, $id_usuario) {
        $filtro = ($id_rol == 2) ? " AND t.id_supervisor = " . (int)$id_usuario : "";
        $sql = "SELECT COALESCE(c.nombre_cuadrilla, 'Sin Asignar') as cuadrilla, COUNT(t.id_trabajo) as cantidad
                FROM trabajos t
                LEFT JOIN cuadrillas c ON t.id_cuadrilla = c.id_cuadrilla
                WHERE t.estado = 'Finalizado'" . $filtro . "
                GROUP BY t.id_cuadrilla";
        
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerKPIsExtra($id_rol, $id_usuario) {
        $metricas = ['contratos_activos' => 0, 'cuadrillas_libres' => 0];
        
        // Contratos activos es global
        $sqlC = "SELECT COUNT(*) FROM contratos_pluz WHERE estado = 'Activo'";
        $metricas['contratos_activos'] = $this->conexion->query($sqlC)->fetchColumn();

        // Cuadrillas libres filtrado por supervisor (si aplica)
        $filtro = ($id_rol == 2) ? " AND id_cuadrilla IN (SELECT id_cuadrilla FROM trabajos WHERE id_supervisor = " . (int)$id_usuario . ")" : "";
        $sqlQ = "SELECT COUNT(*) FROM cuadrillas WHERE id_cuadrilla NOT IN (
                    SELECT DISTINCT id_cuadrilla FROM trabajos 
                    WHERE estado IN ('Programado', 'En Proceso') AND id_cuadrilla IS NOT NULL
                 )" . $filtro;
        
        $metricas['cuadrillas_libres'] = $this->conexion->query($sqlQ)->fetchColumn();
        return $metricas;
    }

    public function getFotosRecientes($limite = 6, $id_rol, $id_usuario) {
        $filtro = ($id_rol == 2) ? " AND t.id_supervisor = " . (int)$id_usuario : "";
        $sql = "SELECT e.id_evidencia, e.ruta_archivo, e.fecha_subida, t.elemento 
                FROM evidencias e
                INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
                WHERE 1=1" . $filtro . "
                ORDER BY e.fecha_subida DESC LIMIT " . (int)$limite;
        
        $stmt = $this->conexion->query($sql);
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fotosBase64 = [];
        foreach ($fotos as $f) {
            if (!empty($f['ruta_archivo'])) {
                $fotosBase64[] = [
                    'id' => $f['id_evidencia'], 'elemento' => $f['elemento'], 
                    'fecha' => $f['fecha_subida'], 'base64' => base64_encode($f['ruta_archivo'])
                ];
            }
        }
        return $fotosBase64;
    }

    public function getTiempoPromedio($id_rol, $id_usuario) {
        $filtro = ($id_rol == 2) ? " AND id_supervisor = " . (int)$id_usuario : "";
        $sql = "SELECT AVG(DATEDIFF(fecha_finalizacion, fecha_registro)) as promedio_dias 
                FROM trabajos WHERE estado = 'Finalizado' AND fecha_finalizacion IS NOT NULL" . $filtro;
        
        $res = $this->conexion->query($sql)->fetch(PDO::FETCH_ASSOC);
        return isset($res['promedio_dias']) ? round($res['promedio_dias'], 1) : 0;
    }
}
?>