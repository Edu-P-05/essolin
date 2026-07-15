<?php
session_start();

if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: index2.html");
    exit;
}
$id_rol = $_SESSION['id_rol'] ?? 1; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESSOLIN - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
        /* Estilos modernos para la tabla */
        .tabla-moderna {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
        }

        .tabla-moderna thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
        }

        .tabla-moderna tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
            color: #333;
            font-size: 0.95rem;
        }

        .tabla-moderna tbody tr:hover {
            background-color: #f8f9fc; /* Efecto sutil al pasar el mouse */
        }

        /* Diseño "Píldora" para el Select de Estado */
        .select-pildora {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            appearance: none; /* Oculta la flecha por defecto en algunos navegadores */
            text-align: center;
        }

        /* Botón de acción minimalista */
        .btn-accion-linea {
            background: transparent;
            color: #3498db;
            border: 1px solid #3498db;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-accion-linea:hover {
            background: #3498db;
            color: white;
        }

        .modal-moderno {
        background: white; 
        padding: 25px; 
        border-radius: 12px; 
        width: 400px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        font-family: 'Segoe UI', sans-serif;
        }
        .input-moderno {
            width: 100%; 
            padding: 10px; 
            margin-top: 5px; 
            margin-bottom: 15px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box;
        }
        .btn-save { background: #2563eb; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-save:hover { background: #1d4ed8; }
        .scroll-box { height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-active { background-color: #d1fae5; color: #065f46; }
        .badge-inactive { background-color: #fee2e2; color: #991b1b; }

        .btn-toggle {
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-toggle:hover { opacity: 0.8; }
        .btn-deactivate { background: #fee2e2; color: #dc2626; }
        .btn-activate { background: #dcfce7; color: #15803d; }
        @media print {
            /* 1. Ocultamos toda la interfaz del dashboard */
            body * {
                visibility: hidden; /* Invisibilidad total */
            }

            /* 2. Hacemos visible SOLO el modal que queremos imprimir */
            #modalDetalleTrabajo, 
            #modalDetalleTrabajo * {
                visibility: visible; /* Lo traemos a la luz */
            }

            /* 3. Posicionamos el modal al inicio de la hoja */
            #modalDetalleTrabajo {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important; /* Forzamos que se vea */
            }

            /* 4. Ocultamos botones específicos dentro del modal que no deben salir en el PDF */
            .btn-upload, button { 
                display: none !important; 
            }
        }
</style>
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
                <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4): ?>
                    <div class="nav-item <?php echo ($id_rol != 3) ? 'active' : ''; ?>" data-page="inicio"><i class="fas fa-tachometer-alt"></i> <span>Inicio</span></div>
                <?php endif; ?>

                <div class="nav-item <?php echo ($id_rol == 3) ? 'active' : ''; ?>" data-page="trabajos"><i class="fas fa-clipboard-list"></i> <span>Trabajos</span></div>

                <!-- === NUEVA SECCIÓN: CONTRATOS === -->
                <?php if ($id_rol == 1 || $id_rol == 2 || $id_rol == 4): ?>
                    <div class="nav-item" data-page="contratos"><i class="fas fa-file-signature"></i> <span>Contratos</span></div>
                <?php endif; ?>

                <!-- === NUEVA SECCIÓN: CUADRILLAS === -->
                <!-- Asumimos que los técnicos de campo (3) no gestionan cuadrillas -->
                <?php if ($id_rol == 1 || $id_rol == 2): ?>
                    <div class="nav-item" data-page="cuadrillas"><i class="fas fa-users-cog"></i> <span>Cuadrillas</span></div>
                <?php endif; ?>

                <?php if ($id_rol == 1 || $id_rol == 4): ?>
                    <div class="nav-item" data-page="reportes"><i class="fas fa-chart-line"></i> <span>Reportes</span></div>
                <?php endif; ?>

                <?php if ($id_rol == 1): ?>
                    <div class="nav-item" data-page="usuarios"><i class="fas fa-users"></i> <span>Usuarios</span></div>
                <?php endif; ?>
            </nav>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <strong id="sidebarUserName"><?php echo $_SESSION['nombre_usuario'] ?? 'Usuario'; ?></strong><br>
                <span id="sidebarUserRol"><?php 
                        // Traducimos el número de rol a texto para mostrarlo bonito
                        $nombres_roles = [
                            1 => 'Administrador', 
                            2 => 'Supervisor', 
                            3 => 'Técnico de Campo', 
                            4 => 'Secretaria'
                        ];
                        echo $nombres_roles[$id_rol] ?? 'Rol no definido';
                    ?></span>
            </div>
            <button type="button" class="logout-sidebar" onclick="window.location.href='../Controlador/UsuarioController.php?accion=logout';">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </button>
        </div>
    </div>

    <div class="main-content">
        
        <div id="page-inicio" class="page-panel <?php echo ($id_rol != 3) ? 'active-panel' : ''; ?>">
            
            <style>
                .essolin-dashboard { padding: 10px 0; font-family: 'Segoe UI', Arial, sans-serif; }
                .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
                .card { background: #ffffff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
                .col-span-1 { grid-column: span 1; }
                .col-span-2 { grid-column: span 2; }
                .col-span-4 { grid-column: span 4; }
                .kpi-header { display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: 14px; font-weight: 600; margin-bottom: 10px; }
                .kpi-value { font-size: 32px; font-weight: bold; color: #1a2b4c; }
                .kpi-trend { font-size: 13px; margin-top: 5px; }
                .trend-up { color: #10b981; }
                .trend-neutral { color: #8b5cf6; }
                .section-title { color: #1e293b; font-size: 16px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
            </style>

            <div class="page-title"><i class="fas fa-chart-pie"></i> Panel Principal ESSOLIN</div>

            <div class="essolin-dashboard">
                <!-- FILA 1: KPIs Principales -->
                <div class="dashboard-grid">
                    <div class="card col-span-1" style="border-left: 4px solid #3b82f6;">
                        <div class="kpi-header"><span>Trabajos en Proceso</span><i class="fas fa-tools" style="color: #3b82f6;"></i></div>
                        <div class="kpi-value" id="dash-proc">0</div> <!-- ID original mantenido -->
                        <div class="kpi-trend trend-up"><i class="fas fa-clock"></i> Actividad operativa</div>
                    </div>
                    <div class="card col-span-1" style="border-left: 4px solid #10b981;">
                        <div class="kpi-header"><span>Cuadrillas Libres</span><i class="fas fa-truck" style="color: #10b981;"></i></div>
                        <div class="kpi-value" id="kpiCuadrillasLibres">0</div> <!-- Nuevo KPI -->
                        <div class="kpi-trend trend-up"><i class="fas fa-check-circle"></i> Disponibles ahora</div>
                    </div>
                    <div class="card col-span-1" style="border-left: 4px solid #8b5cf6;">
                        <div class="kpi-header"><span>Contratos Activos</span><i class="fas fa-file-signature" style="color: #8b5cf6;"></i></div>
                        <div class="kpi-value" id="kpiContratosActivos">0</div> <!-- Nuevo KPI -->
                        <div class="kpi-trend trend-neutral"><i class="fas fa-minus"></i> Vigentes</div>
                    </div>
                    <div class="card col-span-1" style="border-left: 4px solid #f59e0b;">
                        <div class="kpi-header"><span>Trabajos Finalizados</span><i class="fas fa-flag-checkered" style="color: #f59e0b;"></i></div>
                        <div class="kpi-value" id="dash-fin">0</div> <!-- ID original mantenido -->
                        <div class="kpi-trend trend-up"><i class="fas fa-chart-line"></i> Este mes</div>
                    </div>
                </div>

                <!-- FILA 2: Gráficos -->
                <div class="dashboard-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <!-- Gráfico 1 -->
                    <div class="card col-span-1">
                        <div class="section-title">Distribución de Estados</div>
                        <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                            <canvas id="graficoEstados"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfico 2 -->
                    <div class="card col-span-1">
                        <div class="section-title">Trabajos por Actividad</div>
                        <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                            <canvas id="graficoActividades"></canvas>
                        </div>
                    </div>

                    <!-- Gráfico 3 (NUEVO) -->
                    <div class="card col-span-1">
                        <div class="section-title">Ranking por Cuadrilla</div>
                        <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                            <canvas id="graficoCuadrillas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- FILA 3: Evidencias -->
                <div class="dashboard-grid">
                    <div class="card col-span-4">
                        <div class="section-title"><i class="fas fa-camera-retro"></i> Últimas Evidencias Fotográficas</div>
                        <div id="dashFotosRecientes" style="display: flex; gap: 15px; overflow-x: auto; padding-bottom: 10px;"> <!-- ID original mantenido -->
                            <p style="color: #94a3b8; font-style: italic;">Cargando registro fotográfico en tiempo real...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="page-trabajos" class="page-panel <?php echo ($id_rol == 3) ? 'active-panel' : ''; ?>">
            
            <div class="page-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-briefcase"></i> Gestión de Trabajos</div>
                
                <?php if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 4): ?>
                    <button id="btnNuevoTrabajo" onclick="abrirModalNuevoTrabajo()" style="background-color: #00779e; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                        + Nuevo trabajo
                    </button>
                <?php endif; ?>
            </div>

            <!-- === BARRA DE FILTROS === -->
            <div style="display: flex; gap: 15px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div style="flex: 2;">
                    <!-- onkeyup busca automáticamente mientras escribes -->
                    <input type="text" id="inputBuscar" placeholder="🔍 Buscar por ID, elemento, ubicación..." onkeyup="cargarTablaTrabajos()" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="flex: 1;">
                    <!-- onchange actualiza al seleccionar -->
                    <select id="selectEstado" onchange="cargarTablaTrabajos()" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Todos los Estados</option>
                        <option value="Asignado">Programado</option>
                        <option value="En Proceso">En Proceso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <select id="selectTipo" onchange="cargarTablaTrabajos()" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Todos los Tipos</option>
                        <option value="Poste">Poste</option>
                        <option value="SCP">SCP</option>
                        <option value="SE">SE</option>
                        <option value="SAM">SAM</option>
                    </select>
                </div>
            </div>
            <!-- === FIN DE FILTROS === -->

            <!-- === TABLA PRINCIPAL === -->
            <div class="module-card">
                <div style="overflow-x:auto;">
                    <table id="tablaTrabajosPrincipal">
                        <thead>
                            <tr>
                                <th>ID / Elemento</th>
                                <th>Ubicación y Detalles</th>
                                <th>Tipo / Contrato</th>
                                <th>Supervisor Asignado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTrabajos"></tbody>
                    </table>
                </div>
            </div>
            <!-- === FIN DE TABLA === -->

        </div>

                <!-- === VISTA DE LISTADO DE CONTRATOS === -->
        <div id="page-contratos" class="page-panel contenedor-principal" style="padding: 20px;">
    
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Listado de Contratos</h2>
                <!-- Botón para abrir el modal vacío (Nuevo Contrato) -->
                <?php if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 4): ?>
                    <button id="btnNuevoContrato" onclick="abrirModalContrato()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        + Nuevo Contrato
                    </button>
                <?php endif; ?>
            </div>

            <table style="width: 100%; border-collapse: collapse; background: white;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left;">
                        <th style="padding: 12px; border: 1px solid #e2e8f0;">Código Padre</th>
                        <th style="padding: 12px; border: 1px solid #e2e8f0;">Descripción</th>
                        <th style="padding: 12px; border: 1px solid #e2e8f0;">Estado</th>
                        <th style="padding: 12px; border: 1px solid #e2e8f0;">Trabajos</th> <!-- Nueva columna -->
                        <th style="padding: 12px; border: 1px solid #e2e8f0;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaContratosBody"></tbody>
            </table>
        </div>

        <div id="modalContrato" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
            <div class="modal-moderno">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
                    <h3 style="color:#1a2b4c; margin:0;">
                        <i class="fas fa-file-signature"></i> Gestionar Contrato
                    </h3>
                    <button onclick="cerrarModalContrato()" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="formModalContrato">
                    <input type="hidden" id="modalContratoId">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: bold; margin-bottom: 5px;">Código Padre:</label>
                        <input type="text" id="modalCodigoPadre" class="input-moderno" placeholder="Ej. PLUZ-2026-000" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: bold; margin-bottom: 5px;">Descripción:</label>
                        <input type="text" id="modalDescripcion" class="input-moderno" placeholder="Detalle del contrato" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: bold; margin-bottom: 5px;">Estado Inicial:</label>
                        <select id="modalEstado" class="input-moderno">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div style="text-align: right; margin-top: 25px;">
                        <button type="button" onclick="guardarContrato()" class="btn-primary" style="width: 100%; padding: 12px; background: #2563eb;">
                            <i class="fas fa-save"></i> Guardar Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- === VISTA DE CUADRILLAS === -->
        <div id="page-cuadrillas" class="page-panel">
            <div class="page-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-users-cog"></i> Gestión de Cuadrillas</div>
                <button onclick="abrirModalCuadrilla()" class="btn-primary" style="background-color: #2c7da0;">
                    <i class="fas fa-plus"></i> Nueva Cuadrilla
                </button>
            </div>

            <div class="module-card">
                <div style="overflow-x:auto;">
                    <table id="tablaCuadrillas" class="tabla-moderna">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cuadrilla</th>
                                <th>Supervisor (Jefe)</th>
                                <th>Técnicos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody><!-- Se llena con JS --></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="page-reportes" class="page-panel">
            <div class="page-title"><i class="fas fa-chart-pie"></i> Reporte Operativo Detallado</div>
            
            <div class="module-card" style="background-color: #e9ecef;">
                <form id="formFiltrosReporte">
                    <div class="form-row" style="align-items: flex-end;">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" id="filtroFechaInicio">
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" id="filtroFechaFin">
                        </div>
                        <div class="form-group">
                            <label>Estado del Trabajo</label>
                            <select id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="Pendientes">Todos los Pendientes (Programados / En Proceso)</option>
                                <option value="Asignado">Solo Programados</option>
                                <option value="En Proceso">Solo En Proceso</option>
                                <option value="Finalizado">Solo Finalizados</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: none; display: flex; flex-direction: column; gap: 10px; justify-content: flex-end;">
                            <button type="button" onclick="generarReporte()" class="btn-primary" style="background-color: #2c7da0; margin: 0;">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" onclick="exportarExcel()" class="btn-secondary" style="background-color: #27ae60; margin: 0;">
                                <i class="fas fa-file-excel"></i> Exportar CSV
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="module-card">
                <div style="overflow-x:auto;">
                    <table id="tablaReportes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>F. Registro</th>
                                <th>F. Programada</th>
                                <th>F. Finalización</th>
                                <th>Días Transcurridos</th>
                                <th>Actividad</th>
                                <th>Ubicación</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="9" style="text-align: center; color: #888;">Utilice los filtros superiores para generar un reporte.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="page-usuarios" class="page-panel">
            <div class="page-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div><i class="fas fa-users"></i> Gestión de Usuarios</div>
                <button onclick="abrirModalUsuario()" class="btn-primary" style="background-color: #2c7da0;">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>
            
            <div class="module-card">
                <div style="overflow-x:auto;">
                    <table id="tablaUsuarios" class="tabla-moderna">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Usuario (Login)</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" style="text-align: center; color: #888;">Cargando usuarios...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL NUEVO TRABAJO === -->
<div id="modalTrabajo" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:90%; max-width:700px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #003954; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <i class="fas fa-hard-hat" style="color: #00779e;"></i> Registrar Nuevo Trabajo
        </h3>
        
        <form id="formModalNuevoTrabajo" onsubmit="guardarNuevoTrabajo(event)">
            <!-- Fila 1: Contrato y Tipo -->
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <!-- CÁMBIALO ASÍ: -->
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Contrato Pluz Asociado</label>
                    <select id="selectContratoTrabajo" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Cargando contratos...</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Tipo de Infraestructura</label>
                    <select id="modalTipo" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Poste">Poste</option>
                        <option value="SCP">Subestación Compacta Pedestal (SCP)</option>
                        <option value="SE">Subestación Convencional (SE)</option>
                        <option value="SAM">Subestación Aérea Monoposte (SAM)</option>
                    </select>
                </div>
            </div>

            <!-- Fila 2: Elemento y Prioridad -->
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Código del Elemento (Ej. POSTE-123)</label>
                    <input type="text" id="modalElemento" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; text-transform: uppercase;">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Prioridad</label>
                    <select id="modalPrioridad" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Alta">Alta</option>
                        <option value="Media" selected>Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
            </div>

            <!-- Fila 3: Ubicación -->
            <div style="margin-bottom: 15px;">
                <label style="font-size: 0.85em; color: #555; font-weight: bold;">Ubicación / Dirección exacta</label>
                <input type="text" id="modalUbicacion" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <!-- Fila Extra: Descripción -->
            <div style="margin-bottom: 15px;">
                <label style="font-size: 0.85em; color: #555; font-weight: bold;">Descripción del Trabajo</label>
                <textarea id="modalDescripcion" required rows="3" placeholder="Detalle las tareas exactas a realizar..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
            </div>
            <!-- Fila 4: Supervisor y Fecha -->
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 2;">
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Asignar a Supervisor</label>
                    <select id="modalSupervisor" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Cargando supervisores...</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 0.85em; color: #555; font-weight: bold;">Fecha Programada</label>
                    <input type="date" id="modalFechaProgramada" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <!-- Botones -->
            <div style="text-align: right; margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px;">
                <button type="button" onclick="cerrarModalTrabajo()" style="background: white; color: #555; border: 1px solid #ccc; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-right: 10px;">Cancelar</button>
                <button type="submit" style="background: #00779e; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Asignar Trabajo</button>
            </div>
        </form>
    </div>
</div>


<div id="modalUsuario" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:90%; max-width:500px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
            <h3 style="color:#1a2b4c; margin:0;"><i class="fas fa-user-plus"></i> Registrar Usuario</h3>
            <button onclick="cerrarModalUsuario()" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="formModalNuevoUsuario">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Nombre Completo</label>
                <input type="text" id="modalUsuNombre" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Usuario (Login)</label>
                    <input type="text" id="modalUsuLogin" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" id="modalUsuPass" required>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Rol del Sistema</label>
                <select id="modalUsuRol" required>
                        <option value="1">Administrador</option>
                        <option value="2">Supervisor</option>
                        <option value="3">Técnico</option>
                        <option value="4">Secretaria</option>
                </select>
            </div>
            <div style="text-align: right; margin-top: 25px;">
                <button type="button" onclick="guardarNuevoUsuario()" class="btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditarUsuario" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:8px; width:90%; max-width:500px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
            <h3 style="color:#1a2b4c; margin:0;"><i class="fas fa-user-edit"></i> Modificar Usuario</h3>
            <button onclick="cerrarModalEditarUsuario()" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>
        
        <form id="formModalEditarUsuario">
            <input type="hidden" id="editUsuId"> <div class="form-group" style="margin-bottom: 15px;">
                <label>Nombre Completo</label>
                <input type="text" id="editUsuNombre" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Correo / Usuario (Login)</label>
                <input type="email" id="editUsuLogin" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Rol del Sistema</label>
                    <select id="editUsuRol" required>
                        <option value="1">Administrador</option>
                        <option value="2">Supervisor</option>
                        <option value="3">Técnico</option>
                        <option value="4">Secretaria</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <input type="text" id="editUsuPass" placeholder="Dejar en blanco para no cambiar">
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 25px;">
                <button type="submit" class="btn-primary" style="width: 100%; background-color: #f39c12;"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetalleTrabajo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1050; justify-content: center; align-items: center;">
    <div style="background: white; width: 700px; max-height: 90vh; border-radius: 8px; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <!-- Datos ocultos para que el reporte los pueda leer -->
        <div id="datosReporteOcultos" style="display:none;">
            <span id="repCodigoTrabajo"></span>
            <span id="repIdTrabajo"></span>
            <span id="repIdTipo"></span>
            <span id="repPrioridad"></span>
            <span id="repUsuario"></span>
            <span id="repFechaProg"></span>
            <span id="repFechaFin"></span>
        </div>
        <div style="background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #00779e;">Detalle de Trabajo: <span id="detIdTrabajo"></span></h3>
            
            <!-- Contenedor para alinear los botones -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Botón de cerrar -->
                <button onclick="cerrarModalDetalle()" style="background: none; border: none; font-size: 1.5em; cursor: pointer;">&times;</button>
            </div>
        </div>
        
        <!-- Agrega esto dentro de tu modalDetalleTrabajo -->
        <div class="seccion-asignacion" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px; border: 1px solid #dee2e6;">
            <strong style="display: block; margin-bottom: 8px;"><i class="fas fa-users"></i> Asignar o Cambiar Cuadrilla:</strong>
            <div style="display: flex; gap: 10px;">
                <select id="selectAsignarCuadrilla" class="form-control" style="flex: 1; padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">Sin cuadrilla asignada...</option>
                    <?php
                        // Consultamos las cuadrillas disponibles directamente
                        require_once '../php/conexion.php'; // Asegúrate de que la ruta sea correcta
                        $pdoModal = getConexion();
                        $stmtC = $pdoModal->query("SELECT id_cuadrilla, nombre_cuadrilla FROM cuadrillas");
                        while($c = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$c['id_cuadrilla']}'>{$c['nombre_cuadrilla']}</option>";
                        }
                    ?>
                </select>
                
                <button onclick="guardarAsignacionCuadrilla()" style="background: #28a745; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>

        <!-- Info Principal -->
        <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-bottom: 2px solid #f0f0f0;">
            <div><strong>Elemento:</strong> <span id="detElemento"></span></div>
            <div><strong>Ubicación:</strong> <span id="detUbicacion"></span></div>
            <div><strong>Supervisor:</strong> <span id="detSupervisor"></span></div>
            <div><strong>Estado:</strong> <span id="detEstado"></span></div>
        </div>

        <!-- Cuerpo: Integración de Bitácora y Fotos -->
        <div style="padding: 20px;">
            <div style="display: flex; gap: 20px;">
                <!-- Columna Bitácora -->
                <div style="flex: 1;">
                    <h4 style="margin-top:0; color: #333;">📋 Bitácora (Comentarios)</h4>
                    
                    <!-- Historial de comentarios -->
                    <div id="contenedorComentarios" style="background: #f9f9f9; padding: 10px; border-radius: 5px; height: 180px; overflow-y: auto; font-size: 0.85em; margin-bottom: 10px;">
                        <!-- Aquí se cargarán los comentarios -->
                    </div>

                    <!-- Formulario para agregar nuevo comentario -->
                    <!-- Cambia tu etiqueta form por esta -->
                        <form id="formNuevoComentario" onsubmit="return false;" style="display: flex; gap: 5px;">
                            <input type="hidden" id="inputBitacoraIdTrabajo" value="">
                            <!-- REVISA QUE ESTÉ ESCRITO ASÍ: -->
                            <textarea id="textoNuevoComentario" placeholder="Escribe un comentario..." style="flex: 1; padding: 5px; border: 1px solid #ccc; border-radius: 4px; resize: none; height: 40px;"></textarea>
                            <!-- Asegúrate de que el botón diga type="button" para que no intente enviar el form -->
                            <button type="button" onclick="guardarComentarioBitacora()" style="background: #00779e; color: white; border: none; padding: 0 10px; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
            </div>
                <!-- Columna Evidencias -->
                <div style="flex: 1;">
                    <h4 style="margin-top:0; color: #333;">📸 Evidencias (Fotos)</h4>
                    
                    <!-- ✅ AÑADIMOS EL ID AQUÍ PARA CONTROLARLO POR JS -->
                    <div id="seccionSubidaEvidencias" style="margin-bottom: 10px; padding: 10px; border: 1px dashed #ccc; border-radius: 4px; background: #fafafa;">
                        <input type="file" id="inputSubirFoto" accept="image/*" style="font-size: 0.8em; width: 100%;">
                        <button onclick="subirEvidencia()" style="margin-top: 5px; width: 100%; background: #27ae60; color: white; border: none; padding: 5px; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-upload"></i> Subir Foto
                        </button>
                    </div>

                    <div id="contenedorFotos" style="background: #f9f9f9; padding: 10px; border-radius: 5px; height: 180px; overflow-y: auto; display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px;">
                        <!-- Aquí se cargan las fotos -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalCuadrilla" class="modal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div class="modal-moderno">
        <h3 style="margin-top:0; color: #1e293b;">Gestionar Cuadrilla</h3>
        <form id="formModalCuadrilla">
            <input type="hidden" id="modalCuadrillaId">
            
            <label>Nombre de la Cuadrilla:</label>
            <input type="text" id="modalCuadrillaNombre" class="input-moderno" placeholder="Ej. Cuadrilla Alpha" required>
            
            <label>Supervisor:</label>
            <select id="modalCuadrillaSupervisor" class="input-moderno" required>
                <option value="">-- Seleccione un supervisor --</option>
            </select>
            
            <label>Técnicos (Obligatorio seleccionar al menos 1):</label>
            <div id="listaTecnicos" class="scroll-box"></div>

            <button type="button" onclick="validarYGuardar()" class="btn-save">Guardar Cambios</button>
            <button type="button" onclick="cerrarModalCuadrilla()" style="background: none; border: none; color: #64748b; width: 100%; margin-top: 10px; cursor: pointer;">Cancelar</button>
        </form>
    </div>
</div>
<script>
    const ID_ROL_ACTUAL = <?php echo $id_rol; ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/Dashboard.js"></script>
</body>
</html>