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

    // Cambiamos la firma de la función para recibir datos de sesión
    public function listarTrabajosFiltrados($busqueda, $estado, $tipo, $id_rol, $id_usuario_actual, $id_cuadrilla_actual) {
        
        // Mantenemos tus JOINs originales
        $sql = "SELECT t.id_trabajo, t.codigo_trabajo, t.elemento, t.prioridad, t.ubicacion, t.estado, t.descripcion, t.id_cuadrilla,
                    tt.nombre_tipo, cp.codigo_padre as contrato,
                    u.nombre_completo as supervisor
                FROM trabajos t
                LEFT JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo
                LEFT JOIN contratos_pluz cp ON t.id_contrato = cp.id_contrato
                LEFT JOIN usuarios u ON t.id_supervisor = u.id_usuario
                WHERE 1=1";

        $parametros = [];

        // --- SEGURIDAD: FILTRO POR ROL (Añadido antes de los otros filtros) ---
        // 1: Admin, 4: Secretaria -> No ponemos filtro (ven todo)
        // 2: Supervisor -> Filtramos por supervisor
        if ($id_rol == 2) {
            $sql .= " AND t.id_supervisor = ?";
            $parametros[] = $id_usuario_actual; 
        }
        // 3: Técnico -> Filtramos por su cuadrilla
        elseif ($id_rol == 3) {
            $sql .= " AND t.id_cuadrilla = ?";
            $parametros[] = $id_cuadrilla_actual;
        }
        // ---------------------------------------------------------------------

        // Filtro 1: Buscador de texto
        if (!empty($busqueda)) {
            $sql .= " AND (t.codigo_trabajo LIKE ? OR t.elemento LIKE ? OR t.ubicacion LIKE ?)";
            $parametros[] = "%$busqueda%";
            $parametros[] = "%$busqueda%";
            $parametros[] = "%$busqueda%";
        }

        // Filtro 2: Estado
        if (!empty($estado) && $estado !== 'Todos los estados') {
            $sql .= " AND t.estado = ?";
            $parametros[] = $estado;
        }

        // Filtro 3: Tipo de Trabajo
        if (!empty($tipo) && $tipo !== 'Todos los tipos') {
            $sql .= " AND tt.nombre_tipo = ?"; 
            $parametros[] = $tipo;
        }

        $sql .= " ORDER BY t.fecha_registro DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. NUEVA FUNCIÓN: GUARDAR TRABAJO
    public function guardar(Trabajo $trabajo) {
        // 1. Insertamos los datos usando las propiedades del objeto
        $sql = "INSERT INTO trabajos (id_contrato, id_tipo, elemento, prioridad, ubicacion, descripcion, id_supervisor, fecha_programada, estado) 
                VALUES (?, (SELECT id_tipo FROM tipos_trabajo WHERE nombre_tipo = ? LIMIT 1), ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            $trabajo->id_contrato, 
            $trabajo->tipo, // Buscamos el ID del tipo basado en el texto del selector ("Poste", "SCP", etc.)
            $trabajo->elemento, 
            $trabajo->prioridad, 
            $trabajo->ubicacion, 
            $trabajo->descripcion,
            $trabajo->id_supervisor, 
            $trabajo->fecha_programada,
            $trabajo->estado
        ]);

        // 2. Obtenemos el ID que la base de datos le acaba de asignar
        $id_nuevo = $this->conexion->lastInsertId();

        // 3. Generamos el código corporativo (Ej. ESS-TRAB-0004)
        $codigo_generado = "ESS-TRAB-" . str_pad($id_nuevo, 4, "0", STR_PAD_LEFT);
        
        $sqlUpdate = "UPDATE trabajos SET codigo_trabajo = ? WHERE id_trabajo = ?";
        $stmtUpdate = $this->conexion->prepare($sqlUpdate);
        
        return $stmtUpdate->execute([$codigo_generado, $id_nuevo]);
    }

    // Obtiene solo el estado actual de un trabajo específico
    public function obtenerEstadoActual($id_trabajo) {
        try {
            $sql = "SELECT estado FROM trabajos WHERE id_trabajo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_trabajo]);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error al obtener estado actual: " . $e->getMessage());
            return null;
        }
    }

    // Actualiza el estado de un trabajo
    public function actualizarEstado($id_trabajo, $estado) {
        try {
            $sql = "UPDATE trabajos SET estado = ? WHERE id_trabajo = ?";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([$estado, $id_trabajo]);
        } catch (Exception $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }

    // Obtener los combos para el formulario
    public function obtenerContratosActivos() {
        $stmt = $this->conexion->query("SELECT id_contrato, codigo_padre, descripcion FROM contratos_pluz WHERE estado = 'Activo'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSupervisores() {
        // Asumiendo que el ID de rol supervisor es 2
        $stmt = $this->conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE id_rol = 2 AND estado = 'Activo'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function asignarCuadrilla($id_trabajo, $id_cuadrilla) {
        try {
            // Preparamos la consulta para actualizar solo el id_cuadrilla
            $sql = "UPDATE trabajos SET id_cuadrilla = ? WHERE id_trabajo = ?";
            $stmt = $this->conexion->prepare($sql);
            
            // Ejecutamos pasándole los dos parámetros en orden
            return $stmt->execute([$id_cuadrilla, $id_trabajo]);
            
        } catch (Exception $e) {
            // Opcional: registrar el error en el log de PHP para depuración
            error_log("Error en asignarCuadrilla: " . $e->getMessage());
            return false;
        }
    }

    // Función para eliminar un trabajo y todas sus dependencias (Cascada)
    public function eliminarTrabajo($id_trabajo) {
        try {
            // 1. Iniciamos una transacción. Si algo falla, se revierte todo.
            $this->conexion->beginTransaction();

            // ----------------------------------------------------------------
            // ELIMINAR ARCHIVOS FÍSICOS (Evidencias)
            // Leemos la 'ruta_archivo' de la tabla 'evidencias'
            $stmtEvidencias = $this->conexion->prepare("SELECT ruta_archivo FROM evidencias WHERE id_trabajo = ?");
            $stmtEvidencias->execute([$id_trabajo]);
            $evidencias = $stmtEvidencias->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($evidencias as $ev) {
                $ruta = $ev['ruta_archivo'];
                // Si la ruta no está vacía y el archivo físico existe en el servidor, lo borramos.
                if (!empty($ruta) && is_string($ruta) && file_exists($ruta)) {
                    unlink($ruta); 
                }
            }
            // ----------------------------------------------------------------

            // 2. Borrar registros de la tabla 'evidencias' (Hija 1)
            $stmt1 = $this->conexion->prepare("DELETE FROM evidencias WHERE id_trabajo = ?");
            $stmt1->execute([$id_trabajo]);

            // 3. Borrar registros de la tabla 'bitacora_trabajos' (Hija 2)
            $stmt2 = $this->conexion->prepare("DELETE FROM bitacora_trabajos WHERE id_trabajo = ?");
            $stmt2->execute([$id_trabajo]);

            // 4. Finalmente, borrar el registro de la tabla 'trabajos' (Padre)
            $stmt3 = $this->conexion->prepare("DELETE FROM trabajos WHERE id_trabajo = ?");
            $stmt3->execute([$id_trabajo]);

            // 5. Si los 3 DELETE fueron exitosos, confirmamos los cambios en la BD
            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            // Si salta el error de integridad u otro fallo, deshacemos todo
            $this->conexion->rollBack();
            error_log("Error al eliminar trabajo en cascada: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDetalleParaPDF($id_trabajo) {
        try {
            $sql = "SELECT 
                        t.id_trabajo, t.elemento, t.prioridad, t.ubicacion, t.descripcion, 
                        t.fecha_registro, t.fecha_finalizacion,
                        tipos.nombre_tipo,
                        c.codigo_padre AS codigo_contrato, c.descripcion AS desc_contrato,
                        u.nombre_completo AS nombre_supervisor,
                        cuad.nombre_cuadrilla
                    FROM trabajos t
                    LEFT JOIN tipos_trabajo tipos ON t.id_tipo = tipos.id_tipo
                    LEFT JOIN contratos_pluz c ON t.id_contrato = c.id_contrato
                    LEFT JOIN usuarios u ON t.id_supervisor = u.id_usuario
                    LEFT JOIN cuadrillas cuad ON t.id_cuadrilla = cuad.id_cuadrilla
                    WHERE t.id_trabajo = ?";
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_trabajo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerDetalleParaPDF: " . $e->getMessage());
            return false;
        }
    }

    // Obtener las evidencias por Trabajo
    public function obtenerEvidenciasPorTrabajo($id_trabajo) {
        try {
            $sql = "SELECT ruta_archivo FROM evidencias WHERE id_trabajo = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_trabajo]);
            $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $evidenciasBase64 = [];
            foreach ($evidencias as $ev) {
                // Si el BLOB no está vacío, lo convertimos a texto base64
                if (!empty($ev['ruta_archivo'])) {
                    $evidenciasBase64[] = [
                        // Le pasamos el binario a la función de PHP
                        'base64' => base64_encode($ev['ruta_archivo'])
                    ];
                }
            }
            return $evidenciasBase64;
        } catch (Exception $e) {
            error_log("Error en obtenerEvidencias: " . $e->getMessage());
            return [];
        }
    }


}
?>