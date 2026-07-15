<?php
require_once '../php/conexion.php';
require_once '../Modelos/Evidencia.php';

class EvidenciaDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = getConexion();
    }

    // Unimos los resultados con las tablas "trabajos" y "tipos_trabajo" para tener los nombres reales
    // Actualiza esta función en tu EvidenciaDAO.php
    private function mapearResultados($resultados) {
        $lista = [];
        foreach ($resultados as $row) {
            $ev = new Evidencia();
            $ev->id_evidencia = $row['id_evidencia'];
            $ev->id_trabajo = $row['id_trabajo'];
            
            // ¡ESTA ES LA CLAVE! Convertimos el binario BLOB a Base64 al vuelo
            // 'data:image/jpeg;base64,' es el prefijo que necesita la etiqueta <img>
            $ev->ruta_archivo = 'data:image/jpeg;base64,' . base64_encode($row['ruta_archivo']);
            
            $ev->fecha_subida = $row['fecha_subida'];
            $ev->actividad = $row['actividad'];
            $ev->ubicacion = $row['ubicacion'];
            $lista[] = $ev;
        }
        return $lista;
    }

    public function guardar($evidencia) {
        $query = "INSERT INTO evidencias (id_trabajo, ruta_archivo, fecha_subida) VALUES (?, ?, NOW())";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$evidencia->id_trabajo, $evidencia->ruta_archivo]);
    }

    public function listarTodas() {
        $query = "SELECT e.*, t.ubicacion, tp.nombre_tipo as actividad 
                  FROM evidencias e
                  INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
                  INNER JOIN tipos_trabajo tp ON t.id_tipo = tp.id_tipo
                  ORDER BY e.fecha_subida DESC";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $this->mapearResultados($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function listarPorTrabajo($id_trabajo) {
        $query = "SELECT e.*, t.ubicacion, tp.nombre_tipo as actividad 
                  FROM evidencias e
                  INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
                  INNER JOIN tipos_trabajo tp ON t.id_tipo = tp.id_tipo
                  WHERE e.id_trabajo = ? ORDER BY e.fecha_subida DESC";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute([$id_trabajo]);
        return $this->mapearResultados($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function listarRecientes($limite = 5) {
        $query = "SELECT e.*, t.ubicacion, tp.nombre_tipo as actividad 
                  FROM evidencias e
                  INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo
                  INNER JOIN tipos_trabajo tp ON t.id_tipo = tp.id_tipo
                  ORDER BY e.fecha_subida DESC LIMIT ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $this->mapearResultados($stmt->fetchAll(PDO::FETCH_ASSOC));
        
    }
}
?>