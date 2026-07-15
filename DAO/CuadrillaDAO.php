<?php
class CuadrillaDAO {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    public function listarTodas() {
        $sql = "SELECT 
                    c.id_cuadrilla, 
                    c.nombre_cuadrilla AS nombre, 
                    c.id_supervisor, 
                    s.nombre_completo AS supervisor_nombre,
                    GROUP_CONCAT(t.id_usuario SEPARATOR ',') AS tecnicos_ids,
                    GROUP_CONCAT(t.nombre_completo SEPARATOR ', ') AS tecnicos_nombres
                FROM cuadrillas c
                LEFT JOIN usuarios s ON c.id_supervisor = s.id_usuario
                LEFT JOIN cuadrilla_tecnicos ct ON c.id_cuadrilla = ct.id_cuadrilla
                LEFT JOIN usuarios t ON ct.id_usuario = t.id_usuario
                GROUP BY c.id_cuadrilla";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as &$fila) {
            $fila['tecnicos_ids'] = $fila['tecnicos_ids'] ? explode(',', $fila['tecnicos_ids']) : [];
            $fila['tecnicos_nombres'] = $fila['tecnicos_nombres'] ? explode(', ', $fila['tecnicos_nombres']) : [];
        }
        return $resultados;
    }

    public function guardar($id, $nombre, $id_supervisor, $tecnicos) {
        try {
            $this->conexion->beginTransaction();

            if (empty($id)) {
                // INSERT: Nueva cuadrilla
                $sql = "INSERT INTO cuadrillas (nombre_cuadrilla, id_supervisor) VALUES (?, ?)";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$nombre, $id_supervisor]);
                $id = $this->conexion->lastInsertId();
            } else {
                // UPDATE: Cuadrilla existente
                $sql = "UPDATE cuadrillas SET nombre_cuadrilla = ?, id_supervisor = ? WHERE id_cuadrilla = ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([$nombre, $id_supervisor, $id]);
                
                // Borramos los técnicos actuales para reasignar los nuevos
                $stmtDel = $this->conexion->prepare("DELETE FROM cuadrilla_tecnicos WHERE id_cuadrilla = ?");
                $stmtDel->execute([$id]);
            }

            // INSERTAR NUEVA RELACIÓN DE TÉCNICOS
            if (!empty($tecnicos)) {
                $sqlTec = "INSERT INTO cuadrilla_tecnicos (id_cuadrilla, id_usuario) VALUES (?, ?)";
                $stmtTec = $this->conexion->prepare($sqlTec);
                foreach ($tecnicos as $id_usuario) {
                    $stmtTec->execute([$id, $id_usuario]);
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $this->conexion->beginTransaction();
            $stmt1 = $this->conexion->prepare("DELETE FROM cuadrilla_tecnicos WHERE id_cuadrilla = ?");
            $stmt1->execute([$id]);
            $stmt2 = $this->conexion->prepare("DELETE FROM cuadrillas WHERE id_cuadrilla = ?");
            $stmt2->execute([$id]);
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    public function asignarCuadrilla($id_trabajo, $id_cuadrilla) {
        try {
            $stmt = $this->conexion->prepare("UPDATE trabajos SET id_cuadrilla = ? WHERE id_trabajo = ?");
            return $stmt->execute([$id_cuadrilla, $id_trabajo]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>