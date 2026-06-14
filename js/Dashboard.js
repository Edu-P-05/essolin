// === VARIABLES GLOBALES PARA GRÁFICOS ===
let chartEstadosInstance = null;
let chartActividadesInstance = null;
let chartCuadrillasInstance = null;

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
        } else if (targetPage === 'reportes') {
            generarReporte(); 
        } else if (targetPage === 'usuarios') { 
            cargarUsuarios();                   
        }
    });
});

// === LÓGICA DEL PANEL DE INICIO (GRÁFICOS) ===
function cargarDashboardInicio() {
    fetch('../php/obtener_datos_dashboard.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Inyectar valores numéricos
                document.getElementById('dash-prog').innerText = result.programados;
                document.getElementById('dash-proc').innerText = result.en_proceso;
                document.getElementById('dash-fin').innerText = result.finalizados;
                document.getElementById('dash-tiempo').innerText = result.tiempo_promedio + " d";

                // GRÁFICO 1: Estados (Dona)
                if (chartEstadosInstance) chartEstadosInstance.destroy();
                const ctxEst = document.getElementById('graficoEstados').getContext('2d');
                chartEstadosInstance = new Chart(ctxEst, {
                    type: 'doughnut',
                    data: {
                        labels: ['Programados', 'En Proceso', 'Finalizados'],
                        datasets: [{
                            data: [result.programados, result.en_proceso, result.finalizados],
                            backgroundColor: ['#d1ecf1', '#fff3cd', '#d4edda'],
                            borderColor: ['#0c5460', '#856404', '#155724'],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });

                // GRÁFICO 2: Actividades (Barras)
                if (chartActividadesInstance) chartActividadesInstance.destroy();
                const labelAct = result.actividades.map(a => a.actividad);
                const dataAct = result.actividades.map(a => a.cantidad);
                const ctxAct = document.getElementById('graficoActividades').getContext('2d');
                chartActividadesInstance = new Chart(ctxAct, {
                    type: 'bar',
                    data: {
                        labels: labelAct,
                        datasets: [{
                            label: 'Cantidad de Trabajos',
                            data: dataAct,
                            backgroundColor: '#2c7da0',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });

                // GRÁFICO 3: Cuadrillas (Barras Horizontales)
                if (chartCuadrillasInstance) chartCuadrillasInstance.destroy();
                const labelCua = result.cuadrillas.map(c => c.cuadrilla);
                const dataCua = result.cuadrillas.map(c => c.cantidad);
                const ctxCua = document.getElementById('graficoCuadrillas').getContext('2d');
                chartCuadrillasInstance = new Chart(ctxCua, {
                    type: 'bar',
                    data: {
                        labels: labelCua,
                        datasets: [{
                            label: 'Órdenes Completadas',
                            data: dataCua,
                            backgroundColor: '#27ae60',
                            borderRadius: 4
                        }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                });

            } else {
                console.error("Error desde PHP:", result.mensaje);
            }
        })
        .catch(error => console.error("Error de conexión:", error));
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

                    // --- INICIO DEL CANDADO LÓGICO ---
                    let atributoBloqueado = (t.estado === 'Finalizado') ? 'disabled' : '';
                    let cursorEstilo = (t.estado === 'Finalizado') ? 'cursor: not-allowed; opacity: 0.8;' : 'cursor: pointer;';

                    const opcionesEstado = ['Programado', 'En Proceso', 'Finalizado'];
                    
                    let selectHTML = `<select onchange="cambiarEstado(${t.id_trabajo}, this)" ${atributoBloqueado} style="${estilosBadge} border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; outline: none; ${cursorEstilo}">`;
                    // --- FIN DEL CANDADO LÓGICO ---
                    
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
                                <div style="display: flex; gap: 5px;">
                                    <button onclick="abrirModalBitacora(${t.id_trabajo})" style="background-color: #3498db; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.3s; flex: 1;">
                                        <i class="fas fa-comment-dots"></i> Bitácora
                                    </button>
                                    <button onclick="verEvidenciasModal(${t.id_trabajo})" style="background-color: #f39c12; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.3s; flex: 1;">
                                        <i class="fas fa-camera"></i> Fotos
                                    </button>
                                </div>
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
                    // CONDICIONAL DE ROL: Solo si soy Admin (1) creo el botón de eliminar
                    let btnEliminar = '';
                    if (typeof ID_ROL_ACTUAL !== 'undefined' && ID_ROL_ACTUAL === 1) {
                        btnEliminar = `<button onclick="eliminarEvidencia(${foto.id_evidencia}, '${foto.ruta_archivo}')" style="margin-top: 5px; background-color: #c0392b; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; width: 100%; transition: 0.3s;"><i class="fas fa-trash"></i> Eliminar</button>`;
                    }

                    const imgCard = `
                        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 8px; width: 200px; text-align: center; background: #f9f9f9;">
                            <img src="${foto.ruta_archivo}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                            <p style="font-size: 0.75rem; color: #666; margin-top: 8px;"><i class="far fa-clock"></i> ${foto.fecha_subida}</p>
                            <a href="${foto.ruta_archivo}" download="ID${id_trabajo}_Evidencia${index + 1}.jpg" class="btn-download-img">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                            ${btnEliminar}
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
        fecha_programada: document.getElementById('modalFechaProgramada').value, // <-- NUEVA LÍNEA
        id_usuario: 1 
    };
    fetch('../php/guardar_trabajo.php', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(datos) 
    })
    .then(response => response.json()).then(data => {
        if(data.success) { 
            alert("¡Trabajo Programado con éxito!"); 
            cerrarModalTrabajo(); 
            cargarTrabajosAlMuro(); 
            cargarDashboardInicio(); 
        } else {
            alert("Error: " + data.mensaje);
        }
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

// === PESTAÑA REPORTES (BÚSQUEDA Y EXPORTACIÓN A EXCEL) ===
function generarReporte() {
    const estado = document.getElementById('filtroEstado').value;
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;
    
    const tbody = document.querySelector('#tablaReportes tbody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando información...</td></tr>';

    fetch(`../php/generar_reporte.php?estado=${estado}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No se encontraron registros con esos filtros.</td></tr>';
                    return;
                }
                
                data.data.forEach(t => {
                    let colorEstado = (t.estado === 'Finalizado') ? 'color: #155724; font-weight:bold;' : 
                                      (t.estado === 'En Proceso') ? 'color: #856404; font-weight:bold;' : 
                                      'color: #0c5460; font-weight:bold;';

                    // Validar si las fechas existen (si son nulas, mostramos un guion)
                    let fProg = t.fecha_programada ? t.fecha_programada : '-';
                    let fFin = t.fecha_finalizacion ? t.fecha_finalizacion : '-';

                    // --- CÁLCULO DE KPI: Tiempo de Resolución (Lead Time) ---
                    let diasTranscurridos = '-';
                    if (t.estado === 'Finalizado' && t.fecha_finalizacion) {
                        let fechaReg = new Date(t.fecha_registro);
                        let fechaCierre = new Date(t.fecha_finalizacion);
                        
                        // Diferencia en milisegundos convertida a días
                        let diferenciaTiempo = Math.abs(fechaCierre - fechaReg);
                        let diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 60 * 60 * 24));
                        
                        // Si se hizo el mismo día, muestra 0 días. Si no, muestra la cantidad.
                        diasTranscurridos = diferenciaDias + " días";
                    }

                    // Limpiar la descripción de posibles saltos de línea para que no rompa la tabla HTML
                    let descLimpia = t.descripcion ? t.descripcion.replace(/\n/g, " ") : "";

                    tbody.innerHTML += `
                        <tr>
                            <td>${t.id_trabajo}</td>
                            <td>${t.fecha_registro}</td>
                            <td>${fProg}</td>
                            <td>${fFin}</td>
                            <td style="font-weight:bold; text-align:center;">${diasTranscurridos}</td>
                            <td>${t.actividad}</td>
                            <td>${t.ubicacion}</td>
                            <td style="font-size: 0.85rem; max-width: 250px;">${descLimpia}</td>
                            <td style="${colorEstado}">${t.estado}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">Error: ${data.mensaje}</td></tr>`;
            }
        })
        .catch(error => {
            console.error(error);
            tbody.innerHTML = '<tr><td colspan="5" style="color:red; text-align:center;">Error de red.</td></tr>';
        });
}

function exportarExcel() {
    const tabla = document.getElementById("tablaReportes");
    let csv = [];
    
    for (let i = 0; i < tabla.rows.length; i++) {
        let fila = [];
        let columnas = tabla.rows[i].querySelectorAll("td, th");
        
        // Evitamos exportar la fila de aviso si no hay datos
        if (columnas.length === 1 && i === 1) return;

        for (let j = 0; j < columnas.length; j++) {
            let dato = columnas[j].innerText.replace(/"/g, '""');
            fila.push('"' + dato + '"');
        }
        csv.push(fila.join(","));
    }
    
    const archivoCSV = new Blob(["\ufeff" + csv.join("\n")], { type: "text/csv;charset=utf-8;" });
    const linkDescarga = document.createElement("a");
    linkDescarga.download = "Reporte_Operativo_ESSOLIN.csv";
    linkDescarga.href = window.URL.createObjectURL(archivoCSV);
    linkDescarga.click();
}

// === LÓGICA DE LA BITÁCORA DE TRABAJOS ===
function abrirModalBitacora(id_trabajo) {
    document.getElementById('modalBitacora').style.display = 'flex';
    document.getElementById('tituloModalBitacoraID').innerText = id_trabajo;
    document.getElementById('inputBitacoraIdTrabajo').value = id_trabajo;
    cargarHistorialBitacora(id_trabajo);
}

function cerrarModalBitacora() {
    document.getElementById('modalBitacora').style.display = 'none';
    document.getElementById('formNuevoComentario').reset();
}

function cargarHistorialBitacora(id_trabajo) {
    const contenedor = document.getElementById('contenedorComentarios');
    contenedor.innerHTML = '<p style="color: #888; text-align: center;">Cargando historial...</p>';

    fetch(`../php/obtener_bitacora.php?id=${id_trabajo}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                contenedor.innerHTML = '';
                if (result.data.length === 0) {
                    contenedor.innerHTML = '<p style="color: #aaa; text-align: center; margin-top: 20px;">No hay comentarios registrados. Sé el primero en dejar una nota.</p>';
                    return;
                }
                
                result.data.forEach(nota => {
                    contenedor.innerHTML += `
                        <div style="background: white; border-left: 4px solid #2c7da0; padding: 10px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.8rem; color: #555;">
                                <strong><i class="fas fa-user-circle"></i> ${nota.autor}</strong>
                                <span><i class="far fa-clock"></i> ${nota.fecha}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.9rem; color: #333; line-height: 1.4;">${nota.comentario}</p>
                        </div>
                    `;
                });
                // Auto-scroll hacia abajo
                contenedor.scrollTop = contenedor.scrollHeight;
            }
        })
        .catch(error => console.error("Error cargando bitácora:", error));
}

document.getElementById('formNuevoComentario').addEventListener('submit', function(e) {
    e.preventDefault();
    const id_trabajo = document.getElementById('inputBitacoraIdTrabajo').value;
    const comentario = document.getElementById('textoNuevoComentario').value;

    fetch('../php/guardar_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_trabajo: id_trabajo, comentario: comentario })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('textoNuevoComentario').value = '';
            cargarHistorialBitacora(id_trabajo); // Recargar la lista al instante
        } else {
            alert("Error: " + data.mensaje);
        }
    });
});

// === MÓDULO DE GESTIÓN DE USUARIOS ===
function cargarUsuarios() {
    const tbody = document.querySelector('#tablaUsuarios tbody');
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando usuarios...</td></tr>';

    fetch('../php/listar_usuarios.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                tbody.innerHTML = '';
                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay usuarios registrados.</td></tr>';
                    return;
                }

                result.data.forEach(u => {
                    // Colores para el Rol
                    let badgeRol = '';
                    if(u.rol === 'Administrador') badgeRol = 'background:#e74c3c; color:white; padding:3px 8px; border-radius:12px; font-size:0.8rem;';
                    else if(u.rol === 'Supervisor') badgeRol = 'background:#f39c12; color:white; padding:3px 8px; border-radius:12px; font-size:0.8rem;';
                    else if(u.rol === 'Secretaria') badgeRol = 'background:#9b59b6; color:white; padding:3px 8px; border-radius:12px; font-size:0.8rem;';
                    else badgeRol = 'background:#3498db; color:white; padding:3px 8px; border-radius:12px; font-size:0.8rem;'; // Tecnico

                    // Colores para el Estado
                    let colorEstado = (u.estado === 'Activo') ? 'color:#27ae60; font-weight:bold;' : 'color:#c0392b; font-weight:bold;';

                    // Lógica del Botón de Acción con Protección Anti-Lockout
                    let btnAccion = '';
                    if (u.es_yo) {
                        // Si soy yo mismo, muestro un candado y desactivo el botón
                        btnAccion = `<button style="background-color: #95a5a6; color: white; padding: 5px 10px; border: none; border-radius: 4px; font-size: 0.8rem; cursor: not-allowed;" title="Por seguridad, no puedes suspender tu propia cuenta" disabled><i class="fas fa-lock"></i> Es tu cuenta</button>`;
                    } else if (u.estado === 'Activo') {
                        btnAccion = `<button onclick="cambiarEstadoUsuario(${u.id_usuario}, 'Suspendido')" style="background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 4px; font-size: 0.8rem; cursor: pointer; transition: 0.3s;" title="Suspender acceso"><i class="fas fa-ban"></i> Suspender</button>`;
                    } else {
                        btnAccion = `<button onclick="cambiarEstadoUsuario(${u.id_usuario}, 'Activo')" style="background-color: #27ae60; color: white; padding: 5px 10px; border: none; border-radius: 4px; font-size: 0.8rem; cursor: pointer; transition: 0.3s;" title="Reactivar acceso"><i class="fas fa-check-circle"></i> Activar</button>`;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${u.id_usuario}</td>
                            <td style="font-weight:bold;">${u.nombre_completo}</td>
                            <td><i class="fas fa-user" style="color:#888;"></i> ${u.usuario}</td>
                            <td><span style="${badgeRol}">${u.rol}</span></td>
                            <td style="${colorEstado}">${u.estado}</td>
                            <td>${btnAccion}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error: ${result.mensaje}</td></tr>`;
            }
        })
        .catch(error => {
            console.error(error);
            tbody.innerHTML = '<tr><td colspan="6" style="color:red; text-align:center;">Error de red.</td></tr>';
        });
}

function abrirModalUsuario() { document.getElementById('modalUsuario').style.display = 'flex'; }
function cerrarModalUsuario() { document.getElementById('modalUsuario').style.display = 'none'; document.getElementById('formModalNuevoUsuario').reset(); }

document.getElementById('formModalNuevoUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    const datos = {
        nombre: document.getElementById('modalUsuNombre').value,
        usuario: document.getElementById('modalUsuLogin').value,
        password: document.getElementById('modalUsuPass').value,
        rol: document.getElementById('modalUsuRol').value
    };

    fetch('../php/guardar_usuario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("¡Usuario registrado correctamente!");
            cerrarModalUsuario();
            cargarUsuarios(); // Recargamos la tabla
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(error => console.error("Error al guardar usuario:", error));
});

function cambiarEstadoUsuario(id_usuario, nuevoEstado) {
    // Pequeña confirmación por seguridad para no suspender a alguien por error
    let accionTxt = (nuevoEstado === 'Suspendido') ? 'suspender el acceso de' : 'reactivar a';
    if (!confirm(`¿Estás seguro de que deseas ${accionTxt} este usuario?`)) return;

    fetch('../php/actualizar_estado_usuario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_usuario: id_usuario, estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            cargarUsuarios(); // Recargamos la tabla automáticamente
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(error => console.error("Error al actualizar estado del usuario:", error));
}

// Función para eliminar evidencias (Exclusivo Admin)
function eliminarEvidencia(id_evidencia, ruta_archivo) {
    if (!confirm("⚠️ ¿Estás seguro de que deseas eliminar esta fotografía de manera permanente? Esta acción no se puede deshacer.")) {
        return;
    }

    fetch('../php/eliminar_evidencia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_evidencia: id_evidencia, ruta: ruta_archivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Obtenemos el ID del trabajo actual para refrescar el modal automáticamente
            const id_trabajo_actual = document.getElementById('tituloModalFotosID').innerText;
            verEvidenciasModal(id_trabajo_actual);
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(error => console.error("Error al eliminar evidencia:", error));
}

// === MÓDULO DE FOTOS RECIENTES EN EL DASHBOARD ===
function cargarFotosRecientesDashboard() {
    const contenedor = document.getElementById('dashFotosRecientes');
    if (!contenedor) return;

    fetch('../php/obtener_evidencias_recientes.php')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                contenedor.innerHTML = '';
                if (result.data.length === 0) {
                    contenedor.innerHTML = '<p style="color: #888; font-style: italic;">Aún no hay evidencias registradas en el sistema.</p>';
                    return;
                }

                result.data.forEach(foto => {
                    // Extraemos solo la fecha (sin la hora) para que se vea más limpio
                    let fechaCorta = foto.fecha_subida.split(' ')[0];
                    
                    const card = `
                        <div style="min-width: 160px; max-width: 160px; border: 1px solid #ddd; padding: 8px; border-radius: 8px; background: white; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <img src="${foto.ruta_archivo}" style="width: 100%; height: 110px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                            <div style="font-size: 0.8rem; font-weight: bold; margin-top: 8px; color: #1a2b4c;" title="${foto.ubicacion}">
                                ID: ${foto.id_trabajo}
                            </div>
                            <div style="font-size: 0.7rem; color: #666; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <i class="fas fa-map-marker-alt" style="color: #e74c3c;"></i> ${foto.ubicacion}
                            </div>
                        </div>
                    `;
                    contenedor.innerHTML += card;
                });
            } else {
                contenedor.innerHTML = '<p style="color: red;">No se pudieron cargar las imágenes.</p>';
            }
        })
        .catch(error => {
            console.error(error);
            contenedor.innerHTML = '<p style="color: red;">Error de red al cargar evidencias.</p>';
        });
}

// === USUARIO Y LOGOUT ===
document.getElementById('sidebarUserName').innerText = localStorage.getItem('usuarioNombre') || 'Usuario';
document.getElementById('logoutSidebarBtn').addEventListener('click', () => { localStorage.clear(); window.location.href = '../php/logout.php'; });

// === AL ABRIR LA PÁGINA, CARGAMOS LOS DATOS INICIALES ===
if (document.getElementById('page-inicio').classList.contains('active-panel')) {
    cargarDashboardInicio();
    cargarFotosRecientesDashboard(); // <-- Aquí llamamos a las fotos
}
cargarTrabajosAlMuro();
cargarTrabajosAlMuro();