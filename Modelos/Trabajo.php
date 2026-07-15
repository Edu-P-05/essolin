<?php
class Trabajo {
    // --- NUEVOS DATOS (Diseño Corporativo ESSOLIN) ---
    public $codigo_trabajo; // Ej. ESS-TRAB-0001
    public $id_contrato;    // ID del catálogo Pluz
    public $tipo;           // Texto del selector ("Poste", "SCP", etc.)
    public $elemento;       // Código del poste/subestación (Ej. POSTE-123)
    public $prioridad;      // Alta, Media, Baja
    public $id_supervisor;  // Reemplaza la asignación de cuadrilla directa

    // --- DATOS ORIGINALES (Los mantenemos para compatibilidad) ---
    public $id_trabajo;
    public $id_cuadrilla;
    public $id_tipo;
    public $ubicacion;
    public $descripcion;
    public $id_usuario;
    
    // --- FECHAS Y ESTADO ---
    public $fecha_registro;
    public $fecha_programada;
    public $fecha_finalizacion;
    public $estado;
    
    // --- ALIAS PARA TABLAS (Variables extra para los JOINs) ---
    public $nombre_cuadrilla;
    public $nombre_tipo;
    public $nombre_usuario;
    public $contrato;       // Muestra "PLUZ-2026-045" en lugar del ID
    public $supervisor;     // Muestra "Carlos Mendoza" en lugar del ID
}
?>