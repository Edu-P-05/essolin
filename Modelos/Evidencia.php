<?php
class Evidencia {
    public $id_evidencia;
    public $id_trabajo;
    public $ruta_archivo;
    public $fecha_subida;
    
    // Campos extra (para no mostrar solo IDs, sino el nombre de la actividad y ubicación)
    public $actividad;
    public $ubicacion;
}
?>