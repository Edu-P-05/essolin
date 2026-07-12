<?php
class Trabajo {
    public $id_trabajo;
    public $id_cuadrilla;
    public $id_tipo;
    public $ubicacion;
    public $descripcion;
    public $id_usuario;
    public $fecha_registro;
    public $estado;
    public $fecha_finalizacion;
    public $fecha_programada;
    
    // Variables extra para mostrar nombres en lugar de números (IDs) en la tabla
    public $nombre_cuadrilla;
    public $nombre_tipo;
    public $nombre_usuario;
}
?>