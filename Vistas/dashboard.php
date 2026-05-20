<?php
session_start(); // Inicia el motor de sesiones

// Si el usuario no tiene su "gafete" de sesión iniciada, lo devolvemos al login
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: index2.html");
    exit; // Detiene la carga del resto de la página por seguridad
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESSOLIN - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f7f6; color: #333; }
        .app-wrapper { display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR */
        .sidebar { width: 250px; background-color: #1a2b4c; color: white; display: flex; flex-direction: column; justify-content: space-between; padding: 20px 0; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .sidebar-header h2 { color: #4CAF50; letter-spacing: 2px; }
        .sidebar-header p { font-size: 0.8rem; color: #aaa; }
        .nav-links { display: flex; flex-direction: column; gap: 5px; }
        .nav-item { padding: 15px 20px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px; }
        .nav-item:hover, .nav-item.active { background-color: #2c3e50; border-left: 4px solid #4CAF50; }
        .sidebar-footer { padding: 20px; border-top: 1px solid #2c3e50; text-align: center; }
        .logout-sidebar { margin-top: 15px; width: 100%; padding: 10px; background-color: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; }
        
        /* CONTENIDO PRINCIPAL */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; position: relative; }
        .page-panel { display: none; }
        .page-panel.active-panel { display: block; }
        .page-title { font-size: 1.8rem; margin-bottom: 20px; color: #1a2b4c; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        
        /* Tarjetas */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #4CAF50; }
        .module-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;}
        .form-group { flex: 1; display: flex; flex-direction: column; min-width: 200px;}
        .form-group label { font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-primary { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .btn-secondary { background-color: #95a5a6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }

        /* Estilo para los botones de descarga */
        .btn-download-img { display: inline-block; margin-top: 8px; background-color: #2c7da0; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; transition: 0.3s; width: 100%; }
        .btn-download-img:hover { background-color: #1f5c77; }
    </style>
</head>
<body>

<div id="appWrapper" class="app-wrapper">
    <div class="sidebar">
        <div>
            <div class="sidebar-header">
                <h2>ESSOLIN</h2>
                <p>Gestión de Operaciones</p>
            </div>
            <nav class="nav-links">
                <div class="nav-item active" data-page="inicio"><i class="fas fa-tachometer-alt"></i> <span>Inicio</span></div>
                <div class="nav-item" data-page="trabajos"><i class="fas fa-clipboard-list"></i> <span>Trabajos</span></div>
                <div class="nav-item" data-page="evidencias"><i class="fas fa-bolt"></i> <span>Evidencias</span></div>
                <div class="nav-item" data-page="reportes"><i class="fas fa-chart-line"></i> <span>Reportes</span></div>
                <div class="nav-item" data-page="usuarios"><i class="fas fa-users"></i> <span>Usuarios</span></div>
            </nav>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <strong id="sidebarUserName">Usuario</strong><br>
                <span id="sidebarUserRol">Cargando...</span>
            </div>
            <button id="logoutSidebarBtn" class="logout-sidebar"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
    </div>

    <div class="main-content">
        <div id="page-inicio" class="page-panel active-panel">
            <div class="page-title"><i class="fas fa-chart-simple"></i> Panel Principal ESSOLIN</div>
            
            <div class="stats-grid">
                <div class="stat-card" style="border-left: 5px solid #0c5460;">
                    <div class="stat-number" id="dash-prog" style="color: #0c5460;">0</div>
                    <div style="font-weight: bold; color: #555;">Programados</div>
                </div>
                <div class="stat-card" style="border-left: 5px solid #856404;">
                    <div class="stat-number" id="dash-proc" style="color: #856404;">0</div>
                    <div style="font-weight: bold; color: #555;">En Proceso</div>
                </div>
                <div class="stat-card" style="border-left: 5px solid #155724;">
                    <div class="stat-number" id="dash-fin" style="color: #155724;">0</div>
                    <div style="font-weight: bold; color: #555;">Finalizados</div>
                </div>
            </div>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <div class="module-card" style="flex: 1; min-width: 300px;">
                    <h3 style="margin-bottom: 15px; color: #1a2b4c; text-align: center;">Distribución Operativa</h3>
                    <div style="position: relative; height:250px; width:100%; display: flex; justify-content: center;">
                        <canvas id="graficoEstados"></canvas>
                    </div>
                </div>
                <div class="module-card" style="flex: 2; min-width: 400px;">
                    <h3 style="margin-bottom: 15px; color: #1a2b4c;">Trabajos por Tipo de Actividad</h3>
                    <div style="position: relative; height:250px; width:100%;">
                        <canvas id="graficoActividades"></canvas>
                    </div>
                </div>
            </div>

            <div class="module-card">
                <h3 style="margin-bottom: 15px; color: #1a2b4c;"><i class="fas fa-camera-retro"></i> Últimas Evidencias Registradas</h3>
                <div id="dashFotosRecientes" style="display: flex; gap: 15px; overflow-x: auto; padding-bottom: 10px;">
                    <p style="color: #888; font-style: italic;">Cargando fotografías recientes...</p>
                </div>
            </div>
        </div>

        <div id="page-trabajos" class="page-panel">
            <div class="page-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-briefcase"></i> Gestión de Trabajos</div>
                <button onclick="abrirModalTrabajo()" class="btn-primary" style="background-color: #2c7da0;"><i class="fas fa-plus"></i> Nuevo Trabajo</button>
            </div>
            <div class="module-card">
                <div style="overflow-x:auto;">
                    <table id="tablaTrabajosPrincipal">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Actividad</th>
                                <th>Ubicación</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Evidencias</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="page-evidencias" class="page-panel">
            <div class="page-title"><i class="fas fa-bolt"></i> Evidencias de Campo</div>
            <div class="module-card">
                <h3 style="margin-bottom: 15px;"><i class="fas fa-upload"></i> Subir Nueva Evidencia</h3>
                <form id="formSubirEvidencia" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label>Seleccionar Trabajo (ID)</label>
                            <select name="id_trabajo" id="selectTrabajoEvidencia" required>
                                <option value="">Cargando trabajos...</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label>Actividad Registrada</label>
                            <input type="text" id="evidenciaActividad" placeholder="Se llenará automáticamente" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label>Ubicación del Trabajo</label>
                            <input type="text" id="evidenciaUbicacion" placeholder="Se llenará automáticamente" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>
                    <div class="form-row" style="align-items: flex-end; margin-top: 10px;">
                        <div class="form-group" style="flex: 3;">
                            <label>Seleccionar Fotografía</label>
                            <input type="file" name="foto" accept="image/png, image/jpeg, image/jpg" required>
                        </div>
                        <div>
                            <button type="submit" class="btn-primary"><i class="fas fa-cloud-upload-alt"></i> Subir Archivo</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="module-card">
                <h3><i class="fas fa-images"></i> Galería de Fotos Recientes</h3>
                <div id="galeriaEvidencias" style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px;"></div>
            </div>
        </div>

        <div id="page-reportes" class="page-panel"><div class="page-title"><i class="fas fa-chart-pie"></i> Reporte Operativo</div></div>
        <div id="page-usuarios" class="page-panel"><div class="page-title"><i class="fas fa-users"></i> Gestión de Usuarios</div></div>
    </div>
</div>

<div id="modalTrabajo" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:90%; max-width:600px;">
        <h3 style="margin-bottom:20px;"><i class="fas fa-plus-circle"></i> Registrar Trabajo en Campo</h3>
        <form id="formModalNuevoTrabajo">
            <div class="form-row">
                <div class="form-group"><label>Tipo Actividad</label><select id="modalTipoActividad" required><option value="1">Mantenimiento Preventivo</option><option value="2">Reparación de Avería</option><option value="3">Instalación de Tableros</option><option value="4">Diagnóstico de Fallas</option></select></div>
                <div class="form-group"><label>Cuadrilla Asignada</label><select id="modalCuadrilla" required><option value="1">Cuadrilla Alpha</option><option value="2">Cuadrilla Beta</option><option value="3">Cuadrilla Gamma</option></select></div>
            </div>
            <div class="form-group" style="margin-bottom: 15px;"><label>Ubicación / Lugar</label><input type="text" id="modalUbicacion" required></div>
            <div class="form-group" style="margin-bottom: 20px;"><label>Descripción del Trabajo</label><textarea id="modalDescripcion" rows="3" required></textarea></div>
            <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="cerrarModalTrabajo()" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<div id="modalVerFotos" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:90%; max-width:800px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <h3 style="color:#1a2b4c;"><i class="fas fa-images"></i> Evidencias del Trabajo ID: <span id="tituloModalFotosID"></span></h3>
            <button onclick="cerrarModalFotos()" style="background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;"><i class="fas fa-times"></i> Cerrar</button>
        </div>
        <div id="contenedorFotosModal" style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
            <p>Buscando fotos...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/Dashboard.js"></script>

</body>
</html>