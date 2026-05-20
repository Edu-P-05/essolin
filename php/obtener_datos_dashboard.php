<?php
// Archivo: php/obtener_datos_dashboard.php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

try {
    $pdo = getConexion();

    // 1. Tarjetas Superiores: Contar trabajos por Estado
    $stmtResumen = $pdo->query("SELECT estado, COUNT(*) as total FROM trabajos GROUP BY estado");
    $filasResumen = $stmtResumen->fetchAll(PDO::FETCH_ASSOC);
    
    $resumen = ['Programado' => 0, 'En Proceso' => 0, 'Finalizado' => 0];
    foreach($filasResumen as $fila) {
        $resumen[$fila['estado']] = $fila['total']; // Asignamos el valor real de la BD
    }

    // 2. Gráfico de Barras: Contar trabajos por Tipo de Actividad
    $stmtTipos = $pdo->query("
        SELECT tt.nombre_tipo, COUNT(t.id_trabajo) as cantidad 
        FROM trabajos t 
        INNER JOIN tipos_trabajo tt ON t.id_tipo = tt.id_tipo 
        GROUP BY tt.id_tipo
    ");
    $graficoBarras = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Últimas 4 fotos para la galería rápida
    $stmtFotos = $pdo->query("
        SELECT e.ruta_archivo, t.ubicacion 
        FROM evidencias e 
        INNER JOIN trabajos t ON e.id_trabajo = t.id_trabajo 
        ORDER BY e.fecha_subida DESC LIMIT 4
    ");
    $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
    
    // Transformar binarios a Base64 para mostrarlos
    foreach ($fotos as &$foto) {
        if (!empty($foto['ruta_archivo'])) {
            $base64 = base64_encode($foto['ruta_archivo']);
            $foto['ruta_archivo'] = 'data:image/jpeg;base64,' . $base64;
        }
    }

    // Enviar todo en un solo "paquete" JSON
    echo json_encode([
        "success" => true,
        "tarjetas" => [
            "programados" => $resumen['Programado'],
            "en_proceso" => $resumen['En Proceso'],
            "finalizados" => $resumen['Finalizado']
        ],
        "grafico_barras" => $graficoBarras,
        "fotos_recientes" => $fotos
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>