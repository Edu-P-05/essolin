<?php
class ContratoDAO {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    public function listar() {
        // Agregamos COUNT para contar los trabajos asociados
        $sql = "SELECT c.*, COUNT(t.id_trabajo) as total_trabajos 
                FROM contratos_pluz c 
                LEFT JOIN trabajos t ON c.id_contrato = t.id_contrato 
                GROUP BY c.id_contrato";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($id, $codigo, $desc, $estado) {
        try {
            if (empty($id)) {
                // Corregido: contratos_pluz
                $sql = "INSERT INTO contratos_pluz (codigo_padre, descripcion, estado) VALUES (?, ?, ?)";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([$codigo, $desc, $estado]);
            } else {
                // Corregido: contratos_pluz
                $sql = "UPDATE contratos_pluz SET codigo_padre = ?, descripcion = ?, estado = ? WHERE id_contrato = ?";
                $stmt = $this->conexion->prepare($sql);
                return $stmt->execute([$codigo, $desc, $estado, $id]);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    // Verifica si el contrato tiene trabajos pendientes (cualquier estado diferente a 'Finalizado')
    public function contarTrabajosPendientes($id_contrato) {
        try {
            // Buscamos trabajos del contrato cuyo estado NO sea Finalizado
            $sql = "SELECT COUNT(*) FROM trabajos WHERE id_contrato = ? AND estado != 'Finalizado'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_contrato]);
            
            // Retorna el número de trabajos encontrados
            return $stmt->fetchColumn(); 
        } catch (Exception $e) {
            error_log("Error al contar trabajos pendientes: " . $e->getMessage());
            return -1; // En caso de error de BD
        }
    }

    // Función para actualizar el estado del contrato
    public function actualizarEstado($id_contrato, $estado) {
        try {
            $sql = "UPDATE contratos_pluz SET estado = ? WHERE id_contrato = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$estado, $id_contrato]);
        } catch (Exception $e) {
            return false;
        }
    }

    
}
?>