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
            <div class="page-title"><i class="fas fa-chart-simple"></i> Panel ESSOLIN</div>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number">12</div><div>Programados</div></div>
                <div class="stat-card"><div class="stat-number">5</div><div>En Proceso</div></div>
                <div class="stat-card"><div class="stat-number">28</div><div>Finalizados</div></div>
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

<script>
    // === LÓGICA DE NAVEGACIÓN ===
    const navItems = document.querySelectorAll('.nav-item');
    const pagePanels = document.querySelectorAll('.page-panel');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            navItems.forEach(nav => nav.classList.remove('active'));
            pagePanels.forEach(panel => panel.classList.remove('active-panel'));
            item.classList.add('active');
            const targetPage = item.getAttribute('data-page');
            document.getElementById('page-' + targetPage).classList.add('active-panel');

            if (targetPage === 'trabajos') { cargarTrabajosAlMuro(); } 
            else if (targetPage === 'evidencias') { cargarSelectTrabajos(); cargarGaleriaEvidencias(); }
        });
    });

    // === PESTAÑA TRABAJOS ===
    function cargarTrabajosAlMuro() {
        const tbody = document.querySelector('#tablaTrabajosPrincipal tbody');
        fetch('../php/listar_trabajos.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    tbody.innerHTML = ''; 
                    result.data.forEach(t => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${t.id_trabajo}</td>
                                <td>${t.actividad}</td>
                                <td>${t.ubicacion}</td>
                                <td style="max-width: 250px; font-size: 0.85rem; color: #555;">${t.descripcion}</td>
                                <td><span style="background:#d4edda; color:#155724; padding:4px 8px; border-radius:4px; font-size:0.85rem;">Registrado</span></td>
                                <td>
                                    <button onclick="verEvidenciasModal(${t.id_trabajo})" style="background-color: #f39c12; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.3s;">
                                        <i class="fas fa-camera"></i> Ver Fotos
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
            });
    }

    // === FUNCIONES PARA VER FOTOS EN MODAL (AHORA CON DESCARGA) ===
    function verEvidenciasModal(id_trabajo) {
        document.getElementById('modalVerFotos').style.display = 'flex';
        document.getElementById('tituloModalFotosID').innerText = id_trabajo;
        const contenedor = document.getElementById('contenedorFotosModal');
        contenedor.innerHTML = '<p>Buscando evidencias...</p>';

        fetch(`../php/obtener_evidencias_por_trabajo.php?id=${id_trabajo}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    contenedor.innerHTML = '';
                    if (result.data.length === 0) {
                        contenedor.innerHTML = '<p style="color: #888;">No hay fotos registradas para este trabajo.</p>';
                        return;
                    }
                    result.data.forEach((foto, index) => {
                        // Se añadió el <a download> que funciona como botón de descarga
                        const imgCard = `
                            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 8px; width: 200px; text-align: center; background: #f9f9f9;">
                                <img src="${foto.ruta_archivo}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                                <p style="font-size: 0.75rem; color: #666; margin-top: 8px;"><i class="far fa-clock"></i> ${foto.fecha_subida}</p>
                                <a href="${foto.ruta_archivo}" download="ID${id_trabajo}_Evidencia${index + 1}.jpg" class="btn-download-img">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        `;
                        contenedor.innerHTML += imgCard;
                    });
                } else {
                    contenedor.innerHTML = `<p style="color: red;">Error: ${result.mensaje}</p>`;
                }
            })
            .catch(error => { console.error(error); contenedor.innerHTML = '<p style="color: red;">Error de red.</p>'; });
    }

    function cerrarModalFotos() {
        document.getElementById('modalVerFotos').style.display = 'none';
    }

    // === CREAR NUEVO TRABAJO ===
    function abrirModalTrabajo() { document.getElementById('modalTrabajo').style.display = 'flex'; }
    function cerrarModalTrabajo() { document.getElementById('modalTrabajo').style.display = 'none'; document.getElementById('formModalNuevoTrabajo').reset(); }
    
    document.getElementById('formModalNuevoTrabajo').addEventListener('submit', function(e) {
        e.preventDefault(); 
        const datos = {
            id_tipo: document.getElementById('modalTipoActividad').value,
            id_cuadrilla: document.getElementById('modalCuadrilla').value,
            ubicacion: document.getElementById('modalUbicacion').value,
            descripcion: document.getElementById('modalDescripcion').value,
            id_usuario: 1 
        };
        fetch('../php/guardar_trabajo.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
        .then(response => response.json()).then(data => {
            if(data.success) { alert("Registrado!"); cerrarModalTrabajo(); cargarTrabajosAlMuro(); } 
        });
    });

    // === EVIDENCIAS GENERALES (AHORA CON DESCARGA) ===
    function cargarSelectTrabajos() {
        fetch('../php/listar_trabajos.php').then(r => r.json()).then(res => {
            if(res.success) {
                const select = document.getElementById('selectTrabajoEvidencia');
                select.innerHTML = '<option value="">-- Seleccione un Trabajo --</option>';
                res.data.forEach(t => select.innerHTML += `<option value="${t.id_trabajo}" data-actividad="${t.actividad}" data-ubicacion="${t.ubicacion}">ID: ${t.id_trabajo} - ${t.actividad}</option>`);
            }
        });
    }

    document.getElementById('selectTrabajoEvidencia').addEventListener('change', function() {
        const op = this.options[this.selectedIndex];
        document.getElementById('evidenciaActividad').value = this.value ? op.getAttribute('data-actividad') : "";
        document.getElementById('evidenciaUbicacion').value = this.value ? op.getAttribute('data-ubicacion') : "";
    });

    document.getElementById('formSubirEvidencia').addEventListener('submit', function(e) {
        e.preventDefault(); 
        fetch('../php/subir_evidencia.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json()).then(data => {
            if(data.success) { alert("¡Evidencia subida!"); this.reset(); cargarGaleriaEvidencias(); } 
        });
    });

    function cargarGaleriaEvidencias() {
        fetch('../php/listar_evidencias.php').then(r => r.json()).then(res => {
            if (res.success) {
                const gal = document.getElementById('galeriaEvidencias');
                gal.innerHTML = res.data.length ? '' : '<p>No hay evidencias.</p>';
                res.data.forEach((e, index) => {
                    // Se añadió el <a download> que funciona como botón de descarga
                    gal.innerHTML += `
                        <div style="border:1px solid #ddd; padding:10px; border-radius:8px; width:220px; text-align:center;">
                            <img src="${e.ruta_archivo}" style="width:100%; height:160px; object-fit:cover;">
                            <div style="margin-top:10px;">
                                <span style="background:#1a2b4c; color:white; padding:3px 8px; border-radius:12px; font-size:0.75rem;">ID: ${e.id_trabajo}</span>
                                <p style="font-size:0.85rem; font-weight:bold; margin-top:5px;">${e.actividad}</p>
                                <p style="font-size:0.75rem;">📍 ${e.ubicacion}</p>
                                <a href="${e.ruta_archivo}" download="Evidencia_Trabajo_${e.id_trabajo}_v${index}.jpg" class="btn-download-img">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        </div>
                    `;
                });
            }
        });
    }

    // === USUARIO ===
    document.getElementById('sidebarUserName').innerText = localStorage.getItem('usuarioNombre') || 'Usuario';
    document.getElementById('logoutSidebarBtn').addEventListener('click', () => { localStorage.clear(); window.location.href = 'index2.html'; });

    cargarTrabajosAlMuro();
</script>
</body>
</html>