<?php
// Archivo: php/generar_reporte.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

// Recibimos los filtros enviados por la URL (GET)
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

try {
    $pdo = getConexion();
    
    // Consulta base
    // Consulta base ampliada para el reporte gerencial
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

    // Si el usuario eligió un estado, lo agregamos a la consulta
    // Si el usuario eligió un estado, lo agregamos a la consulta
    if ($estado !== '') {
        if ($estado === 'Pendientes') {
            // Agrupación lógica: Filtra ambos estados operativos que aún no terminan
            $sql .= " AND (t.estado = 'Programado' OR t.estado = 'En Proceso')";
        } else {
            // Filtro individual común para los demás estados
            $sql .= " AND t.estado = ?";
            $parametros[] = $estado;
        }
    }
    // Si puso fecha de inicio
    if ($fecha_inicio !== '') {
        $sql .= " AND DATE(t.fecha_registro) >= ?";
        $parametros[] = $fecha_inicio;
    }
    // Si puso fecha de fin
    if ($fecha_fin !== '') {
        $sql .= " AND DATE(t.fecha_registro) <= ?";
        $parametros[] = $fecha_fin;
    }

    $sql .= " ORDER BY t.fecha_registro DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sanitización básica contra XSS por seguridad
    foreach ($resultados as &$fila) {
        $fila['ubicacion'] = htmlspecialchars($fila['ubicacion'], ENT_QUOTES, 'UTF-8');
    }
    unset($fila);

    echo json_encode(["success" => true, "data" => $resultados]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>