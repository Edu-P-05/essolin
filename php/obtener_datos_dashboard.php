<?php
// Archivo: php/obtener_datos_dashboard.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();
    $filtrado = [];

    // 1. Conteo de Estados Fijos
    $sqlEstados = "SELECT 
                    SUM(CASE WHEN estado = 'Programado' THEN 1 ELSE 0 END) as programados,
                    SUM(CASE WHEN estado = 'En Proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'Finalizado' THEN 1 ELSE 0 END) as finalizados
                   FROM trabajos";
    $stmt = $pdo->query($sqlEstados);
    $conteos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. NUEVO KPI: Tiempo Promedio de Resolución (Lead Time en días)
    $sqlLeadTime = "SELECT AVG(DATEDIFF(fecha_finalizacion, fecha_registro)) as promedio_dias 
                    FROM trabajos 
                    WHERE estado = 'Finalizado' AND fecha_finalizacion IS NOT NULL";
    $stmtLT = $pdo->query($sqlLeadTime);
    $resLT = $stmtLT->fetch(PDO::FETCH_ASSOC);
    $promedioDias = isset($resLT['promedio_dias']) ? round($resLT['promedio_dias'], 1) : 0;

    // 3. Gráfico de Actividades
    $sqlActividades = "SELECT tt.nombre_tipo as actividad, COUNT(t.id_trabajo) as cantidad 
                       FROM trabajos t 
                       INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo 
                       GROUP BY t.id_tipo";
    $stmtAct = $pdo->query($sqlActividades);
    $actividades = $stmtAct->fetchAll(PDO::FETCH_ASSOC);

    // 4. NUEVO GRÁFICO: Productividad por Cuadrilla (Solo finalizados)
    $sqlCuadrillas = "SELECT 
                        CASE 
                            WHEN id_cuadrilla = 1 THEN 'Cuadrilla Alpha'
                            WHEN id_cuadrilla = 2 THEN 'Cuadrilla Beta'
                            WHEN id_cuadrilla = 3 THEN 'Cuadrilla Gamma'
                            ELSE 'Sin Asignar'
                        END as cuadrilla,
                        COUNT(id_trabajo) as cantidad
                      FROM trabajos 
                      WHERE estado = 'Finalizado'
                      GROUP BY id_cuadrilla";
    $stmtCua = $pdo->query($sqlCuadrillas);
    $cuadrillas = $stmtCua->fetchAll(PDO::FETCH_ASSOC);

    // Armando la respuesta final consolidada
    echo json_encode([
        "success" => true,
        "programados" => $conteos['programados'] ?? 0,
        "en_proceso"  => $conteos['en_proceso'] ?? 0,
        "finalizados" => $conteos['finalizados'] ?? 0,
        "tiempo_promedio" => $promedioDias,
        "actividades" => $actividades,
        "cuadrillas"  => $cuadrillas
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}
?>