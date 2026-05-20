// === VARIABLES GLOBALES PARA GRÁFICOS ===
let chartEstados = null;
let chartActividades = null;

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

        // Disparadores dinámicos al cambiar de pestaña
        if (targetPage === 'trabajos') { 
            cargarTrabajosAlMuro(); 
        } else if (targetPage === 'evidencias') { 
            cargarSelectTrabajos(); 
            cargarGaleriaEvidencias(); 
        } else if (targetPage === 'inicio') {
            cargarDashboardInicio();
        }
    });
});

// === LÓGICA DEL PANEL DE INICIO (GRÁFICOS) ===
function cargarDashboardInicio() {
    fetch('../php/obtener_datos_dashboard.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // 1. Tarjetas Superiores
                document.getElementById('dash-prog').innerText = data.tarjetas.programados;
                document.getElementById('dash-proc').innerText = data.tarjetas.en_proceso;
                document.getElementById('dash-fin').innerText = data.tarjetas.finalizados;

                // 2. Gráfico Circular (Dona)
                const ctxEstados = document.getElementById('graficoEstados').getContext('2d');
                if(chartEstados) chartEstados.destroy();
                chartEstados = new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: ['Programados', 'En Proceso', 'Finalizados'],
                        datasets: [{
                            data: [data.tarjetas.programados, data.tarjetas.en_proceso, data.tarjetas.finalizados],
                            backgroundColor: ['#d1ecf1', '#fff3cd', '#d4edda'],
                            borderColor: ['#0c5460', '#856404', '#155724'],
                            borderWidth: 1
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });

                // 3. Gráfico de Barras
                const ctxActividades = document.getElementById('graficoActividades').getContext('2d');
                if(chartActividades) chartActividades.destroy();
                const nombresAct = data.grafico_barras.map(item => item.nombre_tipo);
                const cantidadesAct = data.grafico_barras.map(item => item.cantidad);

                chartActividades = new Chart(ctxActividades, {
                    type: 'bar',
                    data: {
                        labels: nombresAct,
                        datasets: [{
                            label: 'Cantidad de Trabajos',
                            data: cantidadesAct,
                            backgroundColor: '#2c7da0',
                            borderRadius: 4
                        }]
                    },
                    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });

                // 4. Últimas fotos (Aquí está el Foreach de JavaScript que preguntaste)
                const contenedorFotos = document.getElementById('dashFotosRecientes');
                contenedorFotos.innerHTML = '';
                if(data.fotos_recientes.length === 0) {
                    contenedorFotos.innerHTML = '<p style="color: #888;">No hay fotos registradas todavía.</p>';
                } else {
                    data.fotos_recientes.forEach(foto => {
                        contenedorFotos.innerHTML += `
                            <div style="min-width: 180px; max-width: 180px; border: 1px solid #ddd; border-radius: 8px; padding: 5px; background: #fff; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <img src="${foto.ruta_archivo}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 6px;">
                                <p style="font-size: 0.75rem; color: #555; margin-top: 5px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">📍 ${foto.ubicacion}</p>
                            </div>
                        `;
                    });
                }
            }
        })
        .catch(error => console.error("Error al cargar dashboard:", error));
}

// === PESTAÑA TRABAJOS (TABLA PRINCIPAL) ===
function cargarTrabajosAlMuro() {
    const tbody = document.querySelector('#tablaTrabajosPrincipal tbody');
    tbody.innerHTML = '<tr><td colspan="6">Cargando datos operativos...</td></tr>';

    fetch('../php/listar_trabajos.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                tbody.innerHTML = ''; 
                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6">No hay trabajos registrados.</td></tr>';
                    return;
                }
                
                result.data.forEach(t => {
                    let estilosBadge = '';
                    if (t.estado === 'Programado') estilosBadge = 'background: #d1ecf1; color: #0c5460;';
                    else if (t.estado === 'En Proceso') estilosBadge = 'background: #fff3cd; color: #856404;';
                    else if (t.estado === 'Finalizado') estilosBadge = 'background: #d4edda; color: #155724;';
                    else estilosBadge = 'background: #e2e3e5; color: #383d41;';

                    const opcionesEstado = ['Programado', 'En Proceso', 'Finalizado'];
                    let selectHTML = `<select onchange="cambiarEstado(${t.id_trabajo}, this)" style="${estilosBadge} border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; cursor: pointer; outline: none;">`;
                    
                    opcionesEstado.forEach(opcion => {
                        let seleccionado = (t.estado === opcion) ? 'selected' : '';
                        selectHTML += `<option value="${opcion}" ${seleccionado} style="background: white; color: black;">${opcion}</option>`;
                    });
                    selectHTML += `</select>`;

                    tbody.innerHTML += `
                        <tr>
                            <td>${t.id_trabajo}</td>
                            <td>${t.actividad}</td>
                            <td>${t.ubicacion}</td>
                            <td style="max-width: 250px; font-size: 0.85rem; color: #555;">${t.descripcion}</td>
                            <td>${selectHTML}</td>
                            <td>
                                <button onclick="verEvidenciasModal(${t.id_trabajo})" style="background-color: #f39c12; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.3s;">
                                    <i class="fas fa-camera"></i> Ver Fotos
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            tbody.innerHTML = '<tr><td colspan="6" style="color:red;">Error al cargar.</td></tr>';
        });
}

// === ACTUALIZAR ESTADO DE UN TRABAJO ===
function cambiarEstado(id_trabajo, selectElement) {
    const nuevoEstado = selectElement.value;
    fetch('../php/actualizar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_trabajo: id_trabajo, estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if (nuevoEstado === 'Programado') { selectElement.style.background = '#d1ecf1'; selectElement.style.color = '#0c5460'; } 
            else if (nuevoEstado === 'En Proceso') { selectElement.style.background = '#fff3cd'; selectElement.style.color = '#856404'; } 
            else if (nuevoEstado === 'Finalizado') { selectElement.style.background = '#d4edda'; selectElement.style.color = '#155724'; }
            // Opcional: Actualizar el dashboard si cambiaste de estado
            cargarDashboardInicio();
        } else {
            alert("Hubo un error al actualizar: " + data.mensaje);
            cargarTrabajosAlMuro(); 
        }
    })
    .catch(error => console.error("Error de conexión:", error));
}

// === VER FOTOS EN MODAL ===
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

function cerrarModalFotos() { document.getElementById('modalVerFotos').style.display = 'none'; }

// === MODAL NUEVO TRABAJO ===
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
        if(data.success) { alert("Registrado!"); cerrarModalTrabajo(); cargarTrabajosAlMuro(); cargarDashboardInicio(); } 
    });
});

// === EVIDENCIAS GENERALES ===
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
        if(data.success) { alert("¡Evidencia subida!"); this.reset(); cargarGaleriaEvidencias(); cargarDashboardInicio(); } 
    });
});

function cargarGaleriaEvidencias() {
    fetch('../php/listar_evidencias.php').then(r => r.json()).then(res => {
        if (res.success) {
            const gal = document.getElementById('galeriaEvidencias');
            gal.innerHTML = res.data.length ? '' : '<p>No hay evidencias.</p>';
            res.data.forEach((e, index) => {
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

// === USUARIO Y LOGOUT ===
document.getElementById('sidebarUserName').innerText = localStorage.getItem('usuarioNombre') || 'Usuario';
document.getElementById('logoutSidebarBtn').addEventListener('click', () => { localStorage.clear(); window.location.href = '../php/logout.php'; });

// === AL ABRIR LA PÁGINA, CARGAMOS LOS DATOS INICIALES ===
cargarDashboardInicio();
cargarTrabajosAlMuro();