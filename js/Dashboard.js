// === VARIABLES GLOBALES PARA GRÁFICOS ===
let chartEstados = null;
let chartActividades = null;
let chartCuadrillas = null;
// === LÓGICA DE NAVEGACIÓN ===
const navItems = document.querySelectorAll('.nav-item');
const pagePanels = document.querySelectorAll('.page-panel');

const ROL_ADMIN = 1;
const ROL_SUPERVISOR = 2;
const ROL_TECNICO = 3;
const ROL_SECRETARIA = 4;

// Definimos la variable global basada en el localStorage
const ROL_ACTUAL = parseInt(localStorage.getItem('idRol')) || 0;

// Definimos los booleanos de permiso globalmente
const IS_ADMIN = (ROL_ACTUAL === ROL_ADMIN);
const IS_SUPERVISOR = (ROL_ACTUAL === ROL_SUPERVISOR);
const IS_SECRETARIA = (ROL_ACTUAL === ROL_SECRETARIA);

navItems.forEach(item => {
    item.addEventListener('click', () => {
        navItems.forEach(nav => nav.classList.remove('active'));
        pagePanels.forEach(panel => panel.classList.remove('active-panel'));
        item.classList.add('active');
        const targetPage = item.getAttribute('data-page');
        document.getElementById('page-' + targetPage).classList.add('active-panel');

        // Disparadores dinámicos al cambiar de pestaña
        if (targetPage === 'trabajos') { 
            cargarTablaTrabajos(); 
        } else if (targetPage === 'inicio') {
            cargarDatosDashboard();
        } else if (targetPage === 'reportes') {
            generarReporte(); 
        } else if (targetPage === 'usuarios') { 
            cargarUsuarios();                   
        } else if (targetPage === 'cuadrillas') {
            cargarTablaCuadrillas();
        } else if (targetPage === 'contratos') {
            cargarTablaContratos();
        }    
    });
});

function aplicarPermisosInterfaz() {
    const rol = parseInt(localStorage.getItem('idRol'));
    
    // Seleccionamos todos los ítems de navegación
    const navItems = document.querySelectorAll('.nav-item');
    
    // Si no tenemos rol, lo mejor es redirigir al login (por seguridad)
    if (!rol) {
        window.location.href = '../index2.html';
        return;
    }

    // Mapeo de permisos (true = se muestra, false = se oculta)
    // 1: Admin, 2: Supervisor, 3: Tecnico, 4: Secretaria
    
    const permisos = {
        'inicio': { 1: true, 2: true, 3: true, 4: true },
        'trabajos': { 1: true, 2: true, 3: true, 4: true },
        'contratos': { 1: true, 2: true, 3: false, 4: true }, // Supervisor visualiza, Tecnico no
        'cuadrillas': { 1: true, 2: true, 3: true, 4: true }, // Supervisor ve la suya, Tecnico si
        'reportes': { 1: true, 2: false, 3: false, 4: true }, // Supervisor no, Secretaria sí
        'usuarios': { 1: true, 2: false, 3: false, 4: false }  // Solo Admin
    };

    // Aplicamos el ocultamiento
    Object.keys(permisos).forEach(page => {
        const elemento = document.querySelector(`[data-page="${page}"]`);
        if (elemento) {
            if (permisos[page][rol]) {
                elemento.style.display = 'flex'; // O el display que tenga tu CSS (ej: block o flex)
            } else {
                elemento.style.display = 'none';
            }
        }
    });

    const btnNuevoTrabajo = document.getElementById('btnNuevoTrabajo');
    const btnNuevoContrato = document.getElementById('btnNuevoContrato');

    // Si es Supervisor (Rol 2), ocultamos los botones de creación global
    if (rol === 2) {
        if (btnNuevoTrabajo) btnNuevoTrabajo.style.display = 'none';
        if (btnNuevoContrato) btnNuevoContrato.style.display = 'none';
    }
}

// === INICIALIZACIÓN UNIFICADA ===
document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Ocultamos pestañas prohibidas inmediatamente
    aplicarPermisosInterfaz();

    // 2. Cargamos los datos iniciales
    // Detectamos qué pestaña está activa por defecto
    const pageInicio = document.getElementById('page-inicio');
    const pageTrabajos = document.getElementById('page-trabajos');

    if (pageInicio && pageInicio.classList.contains('active-panel')) {
        cargarDatosDashboard();
    }
    
    if (pageTrabajos && pageTrabajos.classList.contains('active-panel')) {
        cargarTablaTrabajos();
    }

    // 3. Cargas globales necesarias siempre
    cargarTablaTrabajos();
    
    console.log("Sistema ESSOLIN: Interfaz inicializada correctamente.");
});

// === LÓGICA DEL PANEL DE INICIO (GRÁFICOS) ===
// === FUNCIÓN PRINCIPAL PARA CARGAR TODO EL DASHBOARD ===
function cargarDatosDashboard() {
    // Apuntamos al controlador de PHP que me acabas de mostrar
    fetch('../Controlador/DashboardController.php?accion=obtener_datos')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 1. ACTUALIZAR KPIs PRINCIPALES (Los que ya tenías)
                if (document.getElementById('dash-proc')) {
                    document.getElementById('dash-proc').innerText = data.en_proceso;
                }
                if (document.getElementById('dash-fin')) {
                    document.getElementById('dash-fin').innerText = data.finalizados;
                }
                // (Si tienes el de programados y tiempo, agrégalos aquí de la misma forma)

                // 2. ACTUALIZAR NUEVOS KPIs (Contratos y Cuadrillas)
                if (document.getElementById('kpiContratosActivos')) {
                    document.getElementById('kpiContratosActivos').innerText = data.contratos_activos;
                }
                if (document.getElementById('kpiCuadrillasLibres')) {
                    document.getElementById('kpiCuadrillasLibres').innerText = data.cuadrillas_libres;
                }

                // 3. DIBUJAR LAS FOTOGRAFÍAS RECIENTES
                const divFotos = document.getElementById('dashFotosRecientes');
                if (divFotos) {
                    if (data.fotos && data.fotos.length > 0) {
                        divFotos.innerHTML = ''; // Limpiamos el texto de "Cargando..."
                        
                        // Recorremos las fotos y creamos una tarjeta para cada una
                        data.fotos.forEach(foto => {
                            divFotos.innerHTML += `
                                <div style="min-width: 220px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <img src="data:image/jpeg;base64,${foto.base64}" style="width: 100%; height: 150px; object-fit: cover;">
                                    <div style="padding: 12px; font-size: 13px; color: #64748b;">
                                        <strong style="color: #1a2b4c; font-size: 14px;">${foto.elemento}</strong><br>
                                        <i class="far fa-clock"></i> ${foto.fecha}
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        divFotos.innerHTML = '<p style="color: #94a3b8; font-style: italic;">No hay evidencias fotográficas recientes.</p>';
                    }
                }

                // 4. ACTUALIZAR GRÁFICOS
                // Aquí debes llamar a las funciones que ya tenías para dibujar tus gráficos con Chart.js
                dibujarGraficoEstados(data.programados, data.en_proceso, data.finalizados);
                dibujarGraficoActividades(data.actividades);
                dibujarGraficoCuadrillas(data.cuadrillas); // <-- Añadir esta línea
                

            } else {
                console.error("Error del servidor:", data.mensaje);
            }
        })
        .catch(error => {
            console.error("Error de conexión al cargar el Dashboard:", error);
        });
}

// === FUNCIÓN: DIBUJAR GRÁFICO DE ESTADOS (DONA) ===
function dibujarGraficoEstados(programados, enProceso, finalizados) {
    const ctx = document.getElementById('graficoEstados');
    if (!ctx) return;

    // Si ya existe un gráfico, lo destruimos para evitar superposiciones
    if (chartEstados) {
        chartEstados.destroy();
    }

    chartEstados = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Programados', 'En Proceso', 'Finalizados'],
            datasets: [{
                data: [programados, enProceso, finalizados],
                backgroundColor: ['#e2e8f0', '#3b82f6', '#10b981'], // Gris, Azul, Verde
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%' // Hace que la dona sea más delgada y elegante
        }
    });
}

// === FUNCIÓN: DIBUJAR GRÁFICO DE ACTIVIDADES (BARRAS) ===
function dibujarGraficoActividades(actividades) {
    const ctx = document.getElementById('graficoActividades');
    if (!ctx) return;

    if (chartActividades) {
        chartActividades.destroy();
    }

    // Extraemos los nombres de las actividades y sus cantidades del JSON
    const labels = actividades.map(item => item.actividad);
    const data = actividades.map(item => item.cantidad);

    chartActividades = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Trabajos',
                data: data,
                backgroundColor: '#3b82f6', // Azul corporativo
                borderRadius: 6 // Bordes redondeados en las barras
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Ocultamos la leyenda porque es redundante
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 } // Para que no muestre decimales (no hay 1.5 trabajos)
                },
                x: {
                    grid: { display: false } // Quitamos las líneas de fondo verticales para que se vea más limpio
                }
            }
        }
    });
}

// === FUNCIÓN: DIBUJAR GRÁFICO DE CUADRILLAS (BARRAS HORIZONTALES) ===
function dibujarGraficoCuadrillas(cuadrillas) {
    const ctx = document.getElementById('graficoCuadrillas');
    if (!ctx) return;

    if (chartCuadrillas) {
        chartCuadrillas.destroy();
    }

    // Ordenamos de mayor a menor para que se vea como un Top/Ranking
    cuadrillas.sort((a, b) => b.cantidad - a.cantidad);

    const labels = cuadrillas.map(item => item.cuadrilla);
    const data = cuadrillas.map(item => item.cantidad);

    chartCuadrillas = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Trabajos Finalizados',
                data: data,
                backgroundColor: '#10b981', // Verde éxito para los cierres
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', // ESTO HACE QUE LAS BARRAS SEAN HORIZONTALES
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
}

// ===============================================================
// == PESTAÑA TRABAJOS (TABLA PRINCIPAL) ===
// ===============================================================
function cargarTablaTrabajos() {
    // 1. LEER LOS FILTROS
    const busqueda = document.getElementById('inputBuscar')?.value || '';
    const estado = document.getElementById('selectEstado')?.value || '';
    const tipo = document.getElementById('selectTipo')?.value || '';

    const tbody = document.querySelector('#tablaTrabajosPrincipal tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando datos operativos...</td></tr>';

    // 2. ENVIAR FILTROS AL CONTROLADOR
    const url = `../Controlador/TrabajoController.php?accion=listar&busqueda=${encodeURIComponent(busqueda)}&estado=${encodeURIComponent(estado)}&tipo=${encodeURIComponent(tipo)}`;

    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                tbody.innerHTML = '';
                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay trabajos registrados.</td></tr>';
                    return;
                }

                // 3. DIBUJAR LA TABLA CON PERMISOS
                result.data.forEach(t => {
                    let estilosBadge = '';
                    if (t.estado === 'Programado') estilosBadge = 'background: #d1ecf1; color: #0c5460;';
                    else if (t.estado === 'En Proceso') estilosBadge = 'background: #fff3cd; color: #856404;';
                    else if (t.estado === 'Finalizado') estilosBadge = 'background: #d4edda; color: #155724;';
                    else estilosBadge = 'background: #e2e3e5; color: #383d41;';

                    // --- LÓGICA DE PERMISOS (UI) ---
                    const estaFinalizado = t.estado === 'Finalizado';
                    const puedeEditarEstado = (ROL_ACTUAL === ROL_ADMIN || ROL_ACTUAL === ROL_SECRETARIA || ROL_ACTUAL === ROL_SUPERVISOR);
                    const puedeEliminar = (ROL_ACTUAL === ROL_ADMIN || ROL_ACTUAL === ROL_SECRETARIA);

                    const atributoDisabled = (estaFinalizado || !puedeEditarEstado) ? 'disabled' : '';
                    const estiloCursor = (estaFinalizado || !puedeEditarEstado) ? 'cursor: not-allowed; opacity: 0.8;' : 'cursor: pointer;';

                    // Select de Estados
                    let selectHTML = `<select onchange="cambiarEstado(${t.id_trabajo}, this)" ${atributoDisabled} style="${estilosBadge} border: none; padding: 4px 8px; border-radius: 4px; font-weight: bold; ${estiloCursor}">
                        <option value="Programado" ${t.estado === 'Programado' ? 'selected' : ''}>Programado</option>
                        <option value="En Proceso" ${t.estado === 'En Proceso' ? 'selected' : ''}>En Proceso</option>
                        <option value="Finalizado" ${t.estado === 'Finalizado' ? 'selected' : ''}>Finalizado</option>
                    </select>`;

                    // Botones de acción
                    let botonPrincipal = '';
                    if (estaFinalizado) {
                        // Todos pueden descargar PDF si está finalizado
                        botonPrincipal = `
                            <button onclick="descargarPDFTrabajo(${t.id_trabajo})" style="background-color: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85em; margin-right: 5px;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>`;
                    } else {
                        // Ver detalle para todos
                        botonPrincipal = `
                            <button onclick="verDetalle(${t.id_trabajo}, '${t.elemento}', '${t.nombre_tipo}', '${t.estado}', '${t.contrato}', '${t.codigo_trabajo}', '${t.ubicacion}', '${t.supervisor}', '${t.id_cuadrilla}')" style="background-color: #00779e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85em; margin-right: 5px;">  
                                <i class="fas fa-eye"></i> Ver
                            </button>`;
                    }

                    // Botón Eliminar (Solo Admin y Secretaria)
                    let botonEliminar = (puedeEliminar && !estaFinalizado) ? `
                        <button onclick="eliminarTrabajo(${t.id_trabajo})" style="background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85em;" title="Eliminar Trabajo">
                            <i class="fas fa-trash"></i>
                        </button>` : '';

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${t.id_trabajo}</strong><br><small>${t.elemento || ''}</small></td>
                            <td>${t.ubicacion || ''}<br><small style="color: #666;">${t.descripcion || ''}</small></td>
                            <td>${t.nombre_tipo || 'N/A'}<br><small style="color: #666;">${t.contrato || ''}</small></td>
                            <td>${t.supervisor || 'Sin asignar'}</td>
                            <td>${selectHTML}</td>
                            <td>
                                ${botonPrincipal}
                                ${botonEliminar}
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">${result.mensaje}</td></tr>`;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            tbody.innerHTML = '<tr><td colspan="6" style="color:red; text-align:center;">Error de conexión.</td></tr>';
        });
}

// === ACTUALIZAR ESTADO DE UN TRABAJO ===
// === FUNCIÓN PARA CAMBIAR EL ESTADO DESDE EL SELECT ===
function cambiarEstado(idTrabajo, selectElement) {
    const nuevoEstado = selectElement.value;

    // 1. Si eligen finalizado, pedimos confirmación (Opcional pero muy recomendado por tu regla de negocio)
    if (nuevoEstado === 'Finalizado') {
        if (!confirm("¿Estás seguro de marcar este trabajo como FINALIZADO? Una vez cerrado, no se podrá modificar.")) {
            // Si cancelan, recargamos la tabla para que el select vuelva a su estado original
            cargarTablaTrabajos(); 
            return;
        }
    }

    // 2. Enviamos el nuevo estado al servidor
    fetch('../Controlador/TrabajoController.php?accion=actualizar_estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            accion: 'actualizar_estado', // <-- Corregido aquí
            id_trabajo: idTrabajo,
            estado: nuevoEstado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // === LA LÍNEA MÁGICA ===
            // Si la base de datos se actualizó bien, RECARGAMOS LA TABLA al instante.
            // Esto redibujará la fila, bloqueará el select y cambiará los botones.
            cargarTablaTrabajos(); 
        } else {
            alert("Error al actualizar: " + data.mensaje);
            cargarTablaTrabajos(); // Recargamos para revertir el error visual
        }
    })
    .catch(error => {
        console.error("Error de conexión:", error);
        alert("Ocurrió un error al intentar cambiar el estado.");
        cargarTablaTrabajos(); // Recargamos para revertir el error visual
    });
}

// === FUNCIÓN POP PARA NUEVO TRABAJO ===
function abrirModalNuevoTrabajo() {
    // 1. Mostrar el modal y limpiar el formulario
    document.getElementById('modalTrabajo').style.display = 'flex';
    document.getElementById('formModalNuevoTrabajo').reset();
    document.getElementById('modalFechaProgramada').valueAsDate = new Date();

    // 2. Hacer la petición al backend para traer Contratos y Supervisores
    fetch('../Controlador/TrabajoController.php?accion=datos_formulario')
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                // AQUÍ ESTÁ EL CAMBIO: Usamos el nuevo ID 'selectContratoTrabajo'
                const selContrato = document.getElementById('selectContratoTrabajo');
                selContrato.innerHTML = '<option value="">Seleccione un contrato...</option>';
                res.contratos.forEach(c => {
                    selContrato.innerHTML += `<option value="${c.id_contrato}">${c.codigo_padre} - ${c.descripcion}</option>`;
                });

                // Llenar selector de Supervisores (Este se queda igual)
                const selSupervisor = document.getElementById('modalSupervisor');
                selSupervisor.innerHTML = '<option value="">Seleccione un supervisor...</option>';
                res.supervisores.forEach(s => {
                    selSupervisor.innerHTML += `<option value="${s.id_usuario}">${s.nombre_completo}</option>`;
                });
            } else {
                console.error("Error del servidor:", res.mensaje);
                alert("Error al cargar las listas: " + res.mensaje);
            }
        })
        .catch(error => {
            console.error("Error crítico en Fetch:", error);
        });
}

function cerrarModalTrabajo() { document.getElementById('modalTrabajo').style.display = 'none'; document.getElementById('formModalNuevoTrabajo').reset(); }

// === FUNCIÓN DIRECTA PARA GUARDAR EL TRABAJO ===
function guardarNuevoTrabajo(event) {
    // 1. Detener la recarga de la página que hace el formulario por defecto
    event.preventDefault();

    // 2. Recolectar todos los datos ingresados
    const data = {
        // --- AQUÍ ESTÁ EL CAMBIO ---
        id_contrato: document.getElementById('selectContratoTrabajo').value, 
        // ---------------------------
        tipo: document.getElementById('modalTipo').value,
        elemento: document.getElementById('modalElemento').value.toUpperCase(),
        prioridad: document.getElementById('modalPrioridad').value,
        ubicacion: document.getElementById('modalUbicacion').value,
        descripcion: document.getElementById('modalDescripcion').value,
        id_supervisor: document.getElementById('modalSupervisor').value,
        fecha_programada: document.getElementById('modalFechaProgramada').value
    };

    // 3. Enviar los datos al Controlador PHP usando POST
    fetch('../Controlador/TrabajoController.php?accion=guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            // Si todo salió bien en la base de datos:
            cerrarModalTrabajo();        // Ocultamos el modal
            cargarTablaTrabajos();       // Recargamos la tabla de fondo
            alert("¡Trabajo creado y asignado exitosamente!"); // Avisamos a la secretaria
        } else {
            alert("Error al guardar: " + res.mensaje);
        }
    })
    .catch(error => {
        console.error("Error crítico al enviar los datos:", error);
        alert("Hubo un problema de conexión al intentar guardar.");
    });
}

function verDetalle(id, elemento, tipo, estado, contrato, id_trabajo, ubicacion, supervisor, id_cuadrilla) {
    // Mostrar modal
    document.getElementById('modalDetalleTrabajo').style.display = 'flex';
    
    // --- 🔒 BLOQUEO VISUAL: Ocultar subida si es Secretaria (Rol 4) ---
    const seccionSubida = document.getElementById('seccionSubidaEvidencias');
    if (seccionSubida) {
        // Asumiendo que ROL_ACTUAL es una variable global en tu JS
        seccionSubida.style.display = (ROL_ACTUAL === 4) ? 'none' : 'block';
    }

    // Llenar datos básicos
    document.getElementById('detIdTrabajo').innerText = id_trabajo;
    document.getElementById('detElemento').innerText = elemento;
    document.getElementById('detUbicacion').innerText = ubicacion;
    document.getElementById('detSupervisor').innerText = supervisor;
    document.getElementById('detEstado').innerText = estado;

    const selectCuadrilla = document.getElementById('selectAsignarCuadrilla');
    if (selectCuadrilla) {
        selectCuadrilla.value = id_cuadrilla && id_cuadrilla !== 'null' ? id_cuadrilla : '';
    }

    // Conectar el ID del trabajo y cargar historial/fotos
    document.getElementById('inputBitacoraIdTrabajo').value = id;
    cargarHistorialBitacora(id);
    cargarFotosEnModal(id);
}

function guardarComentarioBitacora() {
    const id_trabajo = document.getElementById('inputBitacoraIdTrabajo').value;
    const campoTexto = document.getElementById('textoNuevoComentario');
    
    // 1. Verificación de existencia del campo
    if (!campoTexto) {
        alert("Error: No se encuentra el campo de texto en el HTML. Revisa el ID.");
        return;
    }

    const comentario = campoTexto.value;

    // 2. Debug: Esto saldrá en tu consola (F12)
    console.log("ID Trabajo:", id_trabajo);
    console.log("Comentario detectado:", comentario);

    // 3. Validación
    if (!comentario || comentario.trim() === "") {
        alert("El sistema detecta que el campo está vacío. Revisa que el ID del textarea sea 'textoNuevoComentario'");
        return;
    }

    // Si llegamos aquí, enviamos al servidor
    fetch('../Controlador/BitacoraController.php?accion=guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_trabajo: id_trabajo, comentario: comentario })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            campoTexto.value = ''; // Limpiar
            cargarHistorialBitacora(id_trabajo);
        } else {
            alert("Error del servidor: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error crítico:", error);
    });
}

function cargarHistorialBitacora(id_trabajo) {
    const contenedor = document.getElementById('contenedorComentarios');
    contenedor.innerHTML = '<p style="color: #888; text-align: center;">Cargando historial...</p>';

    fetch(`../Controlador/BitacoraController.php?accion=listar&id=${id_trabajo}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                contenedor.innerHTML = '';
                if (result.data.length === 0) {
                    contenedor.innerHTML = '<p style="color: #aaa; text-align: center; font-size: 0.8em;">No hay comentarios.</p>';
                    return;
                }
                result.data.forEach(nota => {
                    contenedor.innerHTML += `
                        <div style="background: white; border-left: 4px solid #2c7da0; padding: 8px; margin-bottom: 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #555;">
                                <strong>${nota.autor}</strong>
                                <span>${nota.fecha}</span>
                            </div>
                            <p style="margin: 3px 0 0 0; font-size: 0.85rem;">${nota.comentario}</p>
                        </div>
                    `;
                });
                contenedor.scrollTop = contenedor.scrollHeight;
            }
        });
}

function cargarFotosEnModal(id) {
    const contenedor = document.getElementById('contenedorFotos');
    
    fetch(`../Controlador/EvidenciaController.php?accion=listar&id_trabajo=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data && res.data.length > 0) {
                let html = '';
                res.data.forEach(foto => {
                    // ¡AQUÍ ESTÁ EL CAMBIO!
                    // Usamos 'foto.ruta_archivo' porque eso es lo que viene en tu objeto JSON
                    const srcImagen = foto.ruta_archivo;
                    
                    html += `<div style="border: 1px solid #ccc; padding: 2px; border-radius: 4px; overflow: hidden; height: 80px;">
                                <img src="${srcImagen}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                             </div>`;
                });
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = '<p style="color:#777; font-size:0.8em;">Sin evidencias.</p>';
            }
        })
        .catch(err => {
            console.error("Error:", err);
            contenedor.innerHTML = 'Error al cargar.';
        });
}

function subirEvidencia() {
    if (ROL_ACTUAL === 4) {
        alert("Acceso denegado: No tienes permisos para subir evidencias.");
        return;
    }
    const inputArchivo = document.getElementById('inputSubirFoto');
    const id_trabajo = document.getElementById('inputBitacoraIdTrabajo').value;

    if (inputArchivo.files.length === 0) {
        alert("Selecciona una imagen primero.");
        return;
    }

    const formData = new FormData();
    // Clave 'foto' coincide con $_FILES['foto'] en tu PHP
    formData.append('foto', inputArchivo.files[0]); 
    // Clave 'id_trabajo' coincide con $datos['id_trabajo']
    formData.append('id_trabajo', id_trabajo); 

    // Ajusta la ruta a tu controlador real si es necesario
    fetch('../Controlador/EvidenciaController.php?accion=subir', {
        method: 'POST',
        body: formData // Nota: FormData establece el Content-Type automáticamente
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.mensaje); // "Foto guardada en BD correctamente."
            inputArchivo.value = ''; // Limpiar input
            cargarFotosEnModal(id_trabajo); // Recargar la cuadrícula
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Hubo un problema de conexión al subir la foto.");
    });
}

function eliminarTrabajo(idTrabajo) {
    // Alerta nativa del navegador para confirmar
    if (!confirm("¿Estás seguro de que deseas eliminar este trabajo? Esta acción no se puede deshacer.")) {
        return; // Si el usuario cancela, detenemos la función aquí
    }

    // Enviamos la petición al controlador
    fetch('../Controlador/TrabajoController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            accion: 'eliminar',
            id_trabajo: idTrabajo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Trabajo eliminado correctamente.");
            cargarTablaTrabajos(); // Recargamos la tabla para que el trabajo desaparezca
        } else {
            alert("Error al eliminar: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Ocurrió un error en la conexión.");
    });
}

// === FUNCIÓN PARA GENERAR Y DESCARGAR EL REPORTE PDF ===
function descargarPDFTrabajo(idTrabajo) {
    // Alertamos al usuario de que el proceso inició
    console.log("Generando PDF para el trabajo: " + idTrabajo);

    fetch(`../Controlador/TrabajoController.php?accion=obtener_detalle_pdf&id_trabajo=${idTrabajo}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("Error al obtener los datos para el PDF: " + data.mensaje);
                return;
            }

            const t = data.trabajo;
            const fotos = data.evidencias || [];

            // 1. Creamos un "lienzo" HTML temporal y oculto con tamaño A4
            const divImpresion = document.createElement('div');
            divImpresion.style.width = '210mm'; // Ancho de hoja A4
            divImpresion.style.padding = '15mm';
            divImpresion.style.backgroundColor = '#ffffff';
            divImpresion.style.color = '#333';
            divImpresion.style.fontFamily = 'Arial, sans-serif';
            divImpresion.style.position = 'absolute';
            divImpresion.style.top = '-9999px'; // Lo escondemos fuera de la pantalla

            // 2. Procesamos las fotografías para el diseño
            let htmlFotos = '';
            if (fotos.length > 0) {
                htmlFotos = '<div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">';
                fotos.forEach(f => {
                    // Usamos el formato Data URI para inyectar la imagen directamente
                    htmlFotos += `<img src="data:image/jpeg;base64,${f.base64}" style="width: 47%; height: 250px; object-fit: cover; border-radius: 6px; border: 1px solid #ccd1d9; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">`;
                });
                htmlFotos += '</div>';
            } else {
                htmlFotos = '<div style="padding: 20px; background: #f8f9fa; border: 1px dashed #ccc; text-align: center; color: #7f8c8d; border-radius: 6px;">No se registraron evidencias fotográficas para este trabajo.</div>';
            }

            // 3. Inyectamos el diseño visual del PDF
            divImpresion.innerHTML = `
                <!-- Cabecera corporativa -->
                <div style="border-bottom: 4px solid #1a2b4c; padding-bottom: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h1 style="color: #1a2b4c; margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 1px;">Certificado de Cierre</h1>
                        <h2 style="color: #4CAF50; margin: 5px 0 0 0; font-size: 16px; letter-spacing: 2px;">ESSOLIN - GESTIÓN OPERATIVA</h2>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; font-size: 14px; color: #555;">Trabajo ID: <strong style="color: #1a2b4c; font-size: 16px;">#${t.id_trabajo}</strong></p>
                        <p style="margin: 5px 0 0 0; font-size: 14px; color: #555;">Estado: <strong style="color: #155724; background: #d4edda; padding: 3px 8px; border-radius: 4px;">FINALIZADO</strong></p>
                    </div>
                </div>

                <!-- Tabla de Datos -->
                <h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 15px; font-size: 18px;">Información del Elemento</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 35px; font-size: 14px;">
                    <tbody>
                        <tr>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; width: 25%; color: #1e293b;">Código de Elemento:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0; width: 25%; font-weight: bold;">${t.elemento || 'N/A'}</td>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; width: 25%; color: #1e293b;">Prioridad Asignada:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0; width: 25%;">${t.prioridad || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">Contrato Asociado:</td>
                            <td colspan="3" style="padding: 12px; border: 1px solid #e2e8f0;">${t.codigo_contrato ? t.codigo_contrato + ' - ' + t.desc_contrato : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">Cuadrilla Responsable:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0;">${t.nombre_cuadrilla || 'No asignada'}</td>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">Supervisor a Cargo:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0;">${t.nombre_supervisor || 'No asignado'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">Fecha de Registro:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0;">${t.fecha_registro || 'N/A'}</td>
                            <td style="padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">Fecha Finalización:</td>
                            <td style="padding: 12px; border: 1px solid #e2e8f0; color: #155724; font-weight: bold;">${t.fecha_finalizacion || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sección Evidencias -->
                <h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 15px; font-size: 18px;">Registro Fotográfico</h3>
                ${htmlFotos}
                
                <!-- Pie de página -->
                <div style="margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                    Reporte operativo generado electrónicamente. La información contenida es de uso exclusivo para la supervisión y control del sistema ESSOLIN.
                </div>
            `;

            // Agregamos el lienzo oculto a la página
            document.body.appendChild(divImpresion);

            //ESPERAR A QUE CARGUEN LAS FOTOS ===
            const imagenes = divImpresion.getElementsByTagName('img');
            
            // Creamos un arreglo de promesas. Cada promesa representa una foto.
            const promesasDeCarga = Array.from(imagenes).map(img => {
                return new Promise((resolve) => {
                    if (img.complete) {
                        resolve(); // Si ya estaba en caché, resuelve de inmediato
                    } else {
                        img.onload = () => resolve(); // Resuelve cuando termina de cargar
                        img.onerror = () => {
                            console.warn("No se pudo cargar la imagen: " + img.src);
                            resolve(); // Resolvemos igual para que el PDF se genere, aunque falte una foto
                        };
                    }
                });
            });

            // generamos el PDF
            Promise.all(promesasDeCarga).then(() => {
                const { jsPDF } = window.jspdf;
                
                html2canvas(divImpresion, { scale: 2, useCORS: true, logging: false }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                    
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                    
                    // 1. Descargamos el archivo directamente a tu entorno
                    pdf.save(`ESSOLIN_Cierre_${t.elemento || t.id_trabajo}.pdf`);
                    
                    // 2. Limpieza (quitamos el div oculto sin tocar botones)
                    if (document.body.contains(divImpresion)) {
                        document.body.removeChild(divImpresion);
                    }
                    
                }).catch(err => {
                    console.error("Fallo al dibujar el PDF:", err);
                    alert("Ocurrió un error al armar el documento PDF.");
                    if (document.body.contains(divImpresion)) {
                        document.body.removeChild(divImpresion);
                    }
                });
            });

        })
        .catch(err => {
            console.error("Fallo crítico:", err);
            alert("Ocurrió un error en el sistema al intentar procesar el documento.");
            btn.innerHTML = '<i class="fas fa-file-pdf"></i> Descargar PDF';
            btn.disabled = false;
        });
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalleTrabajo').style.display = 'none';
}

// ===============================================================
// === PESTAÑA REPORTES (BÚSQUEDA Y EXPORTACIÓN A EXCEL) ===
// ===============================================================
function generarReporte() {
    const estado = document.getElementById('filtroEstado').value;
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;
    
    const tbody = document.querySelector('#tablaReportes tbody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando información...</td></tr>';

    fetch(`../Controlador/ReporteController.php?accion=generar&estado=${estado}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
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

// ===============================================================
// === MÓDULO DE GESTIÓN DE USUARIOS ===
// ===============================================================
function cargarUsuarios() {
    const tbody = document.querySelector('#tablaUsuarios tbody');
    if(!tbody) return; // Protección si no estás en la pestaña usuarios

    // 1. Nos aseguramos de mostrar el mensaje de carga
    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 15px; color: #888;">Cargando usuarios desde la base de datos...</td></tr>';

    fetch('../Controlador/UsuarioController.php?accion=listar')
        .then(response => response.text()) // 💡 TRUCO: Leemos como texto primero para atrapar errores ocultos de PHP
        .then(texto => {
            try {
                const res = JSON.parse(texto); // Intentamos convertir el texto a JSON
                
                if(res.success) {
                    tbody.innerHTML = ''; // Limpiamos el "Cargando..."
                    
                    if (!res.data || res.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 15px; color: #888;">No hay usuarios registrados.</td></tr>';
                        return;
                    }

                    res.data.forEach(u => {
                        // 🛡️ PROTECCIÓN ANTI-NULL: Si un dato viene vacío desde MySQL, evitamos que la página colapse
                        const nombre = u.nombre_completo || 'Sin Nombre';
                        const inicial = nombre.charAt(0).toUpperCase();
                        const usuario = u.usuario || 'Sin correo';
                        const rol = u.rol || 'Sin Rol';
                        const estado = u.estado || 'Inactivo';
                        
                        let colorFondo = estado === 'Activo' ? '#d1e7dd' : '#f8d7da';
                        let colorTexto = estado === 'Activo' ? '#0f5132' : '#842029';
                        let disableSelect = u.es_yo ? 'disabled title="No puedes cambiar tu propio estado"' : '';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>#${u.id_usuario}</strong></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold;">
                                        ${inicial}
                                    </div>
                                    ${nombre}
                                </div>
                            </td>
                            <td style="color: #64748b;">${usuario}</td>
                            <td>
                                <span style="background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
                                    ${rol}
                                </span>
                            </td>
                            <td>
                                <select class="select-pildora" style="background-color: ${colorFondo}; color: ${colorTexto}; border: none; font-weight: bold;" onchange="cambiarEstadoUsuario(${u.id_usuario}, this)" ${disableSelect}>
                                    <option value="Activo" ${estado === 'Activo' ? 'selected' : ''}>Activo</option>
                                    <option value="Suspendido" ${estado === 'Suspendido' ? 'selected' : ''}>Suspendido</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn-accion-linea" onclick="abrirModalEditarUsuario(${u.id_usuario}, '${nombre}', '${usuario}', '${rol}')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    // Si el controlador devuelve success = false (ej. sin permisos)
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 15px; color: red;">Error: ${res.mensaje}</td></tr>`;
                }
            } catch (e) {
                // 🛑 EL SALVAVIDAS: Si PHP lanza un Warning/Error (ej. columna inexistente), caerá aquí
                console.error("Lo que devolvió PHP no es un JSON válido. Respuesta cruda:", texto);
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 15px; color: red;">Error interno del servidor. Abre la consola (F12) para ver el detalle.</td></tr>`;
            }
        })
        .catch(error => {
            console.error("Error de red o conexión:", error);
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 15px; color: red;">Error de conexión con el servidor.</td></tr>`;
        });
}

function abrirModalUsuario() { document.getElementById('modalUsuario').style.display = 'flex'; }
function cerrarModalUsuario() { document.getElementById('modalUsuario').style.display = 'none'; document.getElementById('formModalNuevoUsuario').reset(); }

// === GUARDAR NUEVO USUARIO ===
function guardarNuevoUsuario() {
    const form = document.getElementById('formModalNuevoUsuario');
    
    // Capturamos los datos
    const datos = {
        accion: 'guardar',
        nombre: document.getElementById('modalUsuNombre').value,
        usuario: document.getElementById('modalUsuLogin').value,
        password: document.getElementById('modalUsuPass').value,
        rol: document.getElementById('modalUsuRol').value
    };

    fetch('../Controlador/UsuarioController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert("Usuario guardado con éxito");
            // CERRAMOS EL MODAL CORRECTO
            document.getElementById('modalUsuario').style.display = 'none';
            form.reset(); 
            cargarUsuarios(); // Recarga la tabla real
        } else {
            alert("Error: " + data.mensaje);
        }
    })
    .catch(err => {
        console.error("Error crítico:", err);
        alert("Error de conexión con el servidor.");
    });
}
// === CAMBIAR ESTADO DEL USUARIO ===
function cambiarEstadoUsuario(id_usuario, selectElement) {
    const nuevoEstado = selectElement.value;
    
    fetch('../Controlador/UsuarioController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'actualizar_estado', id_usuario: id_usuario, estado: nuevoEstado })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if (nuevoEstado === 'Activo') { 
                selectElement.style.background = '#d4edda'; 
                selectElement.style.color = '#155724'; 
            } else { 
                selectElement.style.background = '#f8d7da'; 
                selectElement.style.color = '#721c24'; 
            }
        } else {
            alert("Error al actualizar: " + data.mensaje);
            cargarUsuarios(); // Recargar para deshacer el cambio visual
        }
    })
    .catch(error => console.error("Error de conexión:", error));
}

window.abrirModalEditarUsuario = function(id, nombre, email, rolTexto) {
    document.getElementById('modalEditarUsuario').style.display = 'flex';
    
    // Llenamos las cajas de texto
    document.getElementById('editUsuId').value = id;
    document.getElementById('editUsuNombre').value = nombre;
    document.getElementById('editUsuLogin').value = email;
    document.getElementById('editUsuPass').value = ''; // Se deja en blanco por seguridad

    // Traducimos el texto del rol al número para seleccionar la opción correcta
    let idRol = 3; // Técnico por defecto
    if (rolTexto === 'Administrador') idRol = 1;
    if (rolTexto === 'Supervisor') idRol = 2;
    if (rolTexto === 'Secretaria') idRol = 4;

    document.getElementById('editUsuRol').value = idRol;
}

window.cerrarModalEditarUsuario = function() {
    document.getElementById('modalEditarUsuario').style.display = 'none';
}

// === EDITAR USUARIO EXISTENTE ===
const formEditarUsuario = document.getElementById('formModalEditarUsuario');
if (formEditarUsuario) {
    formEditarUsuario.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // ¡IDs actualizados para que coincidan con tu HTML!
        const inputId = document.getElementById('editUsuId');
        const inputNombre = document.getElementById('editUsuNombre');
        const inputEmail = document.getElementById('editUsuLogin');
        const inputPassword = document.getElementById('editUsuPass');
        const inputRol = document.getElementById('editUsuRol');

        if(!inputId || !inputNombre || !inputEmail || !inputPassword || !inputRol) {
            console.error("¡ALERTA! Faltan inputs en el HTML.");
            return;
        }

        const datos = {
            accion: 'editar',
            id_usuario: inputId.value,
            nombre: inputNombre.value,
            usuario: inputEmail.value,
            password: inputPassword.value,
            rol: inputRol.value // Ahora sí enviará un número (1, 2, 3 o 4)
        };

        fetch('../Controlador/UsuarioController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Usuario actualizado correctamente");
                cerrarModalEditarUsuario();
                cargarUsuarios(); // Refresca la tabla
            } else {
                alert("Error: " + data.mensaje);
            }
        })
        .catch(error => console.error("Error al editar:", error));
    });
}

// ===============================================================
// === MÓDULO DE CUADRILLAS ===
// ===============================================================

function cargarTablaCuadrillas() {
    // 1. Buscamos el cuerpo de la tabla en el HTML
    const tbody = document.querySelector('#tablaCuadrillas tbody');
    
    if (!tbody) {
        console.error("Error: No se encontró la tabla de cuadrillas en el HTML.");
        return;
    }

    // 2. Mostramos un mensaje de carga temporal
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 15px; color: #888;">Cargando cuadrillas...</td></tr>';

    // 3. Hacemos la petición al controlador
    fetch('../Controlador/CuadrillaController.php?accion=listar')
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                tbody.innerHTML = ''; 
                
                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 15px; color: #888;">No hay cuadrillas registradas.</td></tr>';
                    return;
                }

                // 4. Recorremos los datos que llegaron del PHP y creamos las filas
                res.data.forEach(c => {
                    
                    let nombresTecnicos = 'Sin técnicos';
                    if (Array.isArray(c.tecnicos_nombres)) {
                        nombresTecnicos = c.tecnicos_nombres.join(', ');
                    } else if (c.tecnicos_nombres) {
                        nombresTecnicos = c.tecnicos_nombres;
                    }

                    // Preparamos los IDs para el modal (Soporta si PHP manda Array o String)
                    let idsTecnicosJSON = "[]";
                    if (Array.isArray(c.tecnicos_ids)) {
                        idsTecnicosJSON = JSON.stringify(c.tecnicos_ids);
                    } else if (typeof c.tecnicos_ids === 'string' && c.tecnicos_ids.trim() !== '') {
                        idsTecnicosJSON = `[${c.tecnicos_ids}]`;
                    }

                    const nombreSeguro = c.nombre ? c.nombre.replace(/"/g, '&quot;') : '';

                    tbody.innerHTML += `
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">${c.id_cuadrilla}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;"><strong>${nombreSeguro}</strong></td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">${c.supervisor_nombre || 'No asignado'}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee; font-size: 0.9em; color: #555;">${nombresTecnicos}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                <!-- Botón Editar (Ahora utiliza nombreSeguro) -->
                                <button onclick='abrirModalCuadrilla(${c.id_cuadrilla}, "${nombreSeguro}", "${c.id_supervisor}", ${JSON.stringify(c.tecnicos_ids)})' style="background: #f39c12; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px;" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Botón Eliminar -->
                                <button onclick="eliminarCuadrilla(${c.id_cuadrilla})" style="background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 15px; color: red;">Error: ${res.mensaje}</td></tr>`;
            }
        })
        .catch(error => {
            console.error("Error al cargar cuadrillas:", error);
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 15px; color: red;">Error de conexión con el servidor.</td></tr>';
        });
}

/**
 * Abre el modal de cuadrilla y carga los datos (supervisores y técnicos)
 * @param {string|number} id - ID de la cuadrilla (vacío si es nueva)
 * @param {string} nombre - Nombre de la cuadrilla
 * @param {string|number} id_sup - ID del supervisor asignado
 * @param {Array} idsTecnicos - Array con los IDs de los técnicos ya asignados
 */
function abrirModalCuadrilla(id = '', nombre = '', id_sup = '', idsTecnicos = []) {
    // 1. Mostrar el modal
    document.getElementById('modalCuadrilla').style.display = 'flex';
    
    // 2. Llenar campos básicos (si es edición, id tendrá valor; si no, será vacío)
    document.getElementById('modalCuadrillaId').value = id;
    document.getElementById('modalCuadrillaNombre').value = nombre;
    
    // 3. Obtener la lista actualizada de supervisores y técnicos desde el controlador
    fetch(`../Controlador/CuadrillaController.php?accion=datos_form&id_cuadrilla=${id}`)
        .then(response => response.json())
        .then(res => {
            if (!res.success) {
                alert("Error al cargar los datos del formulario: " + res.mensaje);
                return;
            }

            // --- A. Llenar Supervisor ---
            const selSup = document.getElementById('modalCuadrillaSupervisor');
            selSup.innerHTML = '<option value="">-- Seleccione un supervisor --</option>';
            
            res.supervisores.forEach(s => {
                const selected = (s.id_usuario == id_sup) ? 'selected' : '';
                selSup.innerHTML += `<option value="${s.id_usuario}" ${selected}>${s.nombre_completo}</option>`;
            });

            // --- B. Llenar Técnicos (Checkboxes) ---
            const divTec = document.getElementById('listaTecnicos');
            divTec.innerHTML = ''; // Limpiamos la lista antes de volver a pintar
            
            res.tecnicos.forEach(t => {
                // Convertimos a string para comparar de forma segura (evita errores de tipo)
                const idTecnicoStr = t.id_usuario.toString();
                
                // Comprobamos si este técnico está en el array idsTecnicos
                // .some recorre el array comparando el ID actual contra los IDs que ya tiene la cuadrilla
                const isChecked = idsTecnicos.some(item => item.toString() === idTecnicoStr);
                const checkedAttr = isChecked ? 'checked' : '';

                divTec.innerHTML += `
                    <div style="margin-bottom: 5px;">
                        <input type="checkbox" name="tecnicos[]" value="${t.id_usuario}" ${checkedAttr}> 
                        ${t.nombre_completo}
                    </div>`;
            });
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            alert("Hubo un error de conexión con el servidor.");
        });
}

function guardarCuadrilla() {
    // Ejemplo de cómo obtener un array de checkboxes seleccionados
    const checkboxes = document.querySelectorAll('input[name="tecnicos[]"]:checked');
    const tecnicosSeleccionados = Array.from(checkboxes).map(c => c.value);

    const datos = {
        id_cuadrilla: document.getElementById('modalCuadrillaId').value, // ID oculto
        nombre: document.getElementById('modalCuadrillaNombre').value,
        id_supervisor: document.getElementById('modalCuadrillaSupervisor').value,
        tecnicos: [...document.querySelectorAll('input[name="tecnicos[]"]:checked')].map(c => c.value)
    };

    fetch('../Controlador/CuadrillaController.php?accion=guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert("¡Éxito!");
            cerrarModalCuadrilla();
            cargarTablaCuadrillas(); // Refresca la tabla
        } else {
            alert("Error: " + res.mensaje);
        }
    });
}

function guardarAsignacionCuadrilla() {
    // Tomamos el ID del input que ya usas para la bitácora
    const idTrabajo = document.getElementById('inputBitacoraIdTrabajo').value; 
    const idCuadrilla = document.getElementById('selectAsignarCuadrilla').value;

    if (!idCuadrilla) {
        alert("Por favor, seleccione una cuadrilla.");
        return;
    }

    // Enviamos los datos como JSON al controlador
    fetch('../Controlador/TrabajoController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            accion: 'asignar_cuadrilla',
            id_trabajo: idTrabajo,
            id_cuadrilla: idCuadrilla
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("¡Cuadrilla asignada correctamente!");
            document.getElementById('modalDetalleTrabajo').style.display = 'none';
            cargarTablaTrabajos(); // Recargamos la tabla para ver los cambios
        } else {
            alert("Error al asignar: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Ocurrió un error en la conexión.");
    });
}

function validarYGuardar() {
    const nombre = document.getElementById('modalCuadrillaNombre').value;
    const supervisor = document.getElementById('modalCuadrillaSupervisor').value;
    const tecnicos = document.querySelectorAll('input[name="tecnicos[]"]:checked');

    // 1. Validar campos de texto
    if (!nombre || !supervisor) {
        alert("Por favor, completa el nombre y selecciona un supervisor.");
        return;
    }

    // 2. Validar que al menos un técnico esté seleccionado
    if (tecnicos.length === 0) {
        alert("Debes seleccionar al menos un técnico para la cuadrilla.");
        return;
    }

    // Si todo está bien, llamamos a la función de guardar que ya tenías
    guardarCuadrilla(); 
}

function eliminarCuadrilla(id) {
    if (!confirm("⚠️ ¿Estás seguro de eliminar esta cuadrilla? Esta acción no se puede deshacer.")) {
        return;
    }

    fetch('../Controlador/CuadrillaController.php?accion=eliminar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cuadrilla: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert("¡Cuadrilla eliminada!");
            cargarTablaCuadrillas(); // Refresca la tabla automáticamente
        } else {
            alert("Error: " + res.mensaje);
        }
    });
}

function cerrarModalCuadrilla() {
    document.getElementById('modalCuadrilla').style.display = 'none';
}

// ===============================================================
// === MÓDULO DE CONTRATOS===
// ===============================================================
//CARGAR TABLA: Trae los datos de la BD y los dibuja en el HTML
function cargarTablaContratos() {
    fetch('../Controlador/ContratoController.php?accion=listar')
    .then(r => r.json())
    .then(res => {
        const tbody = document.getElementById('tablaContratosBody');
        tbody.innerHTML = '';
        
        // Verificamos si el usuario tiene permiso para editar
        // Admin es 1, Secretaria es 4
        const puedeEditar = (ROL_ACTUAL === ROL_ADMIN || ROL_ACTUAL === ROL_SECRETARIA);
        
        res.data.forEach(c => {
            const esActivo = c.estado === 'Activo';
            
            // Lógica del botón de acciones: 
            // Si puedeEditar es true, mostramos el botón. Si no, dejamos un espacio vacío.
            let columnaAcciones = '';
            if (puedeEditar) {
                columnaAcciones = `
                    <button class="btn-toggle ${esActivo ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="cambiarEstadoContrato(${c.id_contrato}, '${c.estado}')">
                        ${esActivo ? 'Desactivar' : 'Activar'}
                    </button>`;
            }

            tbody.innerHTML += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px; font-weight: 600; color: #1e293b;">${c.codigo_padre}</td>
                    <td style="padding: 15px; color: #64748b;">${c.descripcion}</td>
                    <td style="padding: 15px;">
                        <span class="badge ${esActivo ? 'badge-active' : 'badge-inactive'}">${c.estado}</span>
                    </td>
                    
                    <td style="padding: 15px; font-weight: bold; color: #334155;">${c.total_trabajos || 0}</td>
                        
                    <!-- === COLUMNA DE ACCIONES DINÁMICA === -->
                    <td style="padding: 15px;">
                        ${columnaAcciones}
                    </td>
                </tr>
            `;
        });
    });
}

// Función para Activar o Inactivar un contrato con validaciones
function cambiarEstadoContrato(idContrato, estadoActual) {
    // 1. Determinamos qué quiere hacer el usuario
    const esActivo = estadoActual === 'Activo';
    const nuevoEstado = esActivo ? 'Inactivo' : 'Activo';
    
    // 2. Preparamos la pregunta de confirmación según el caso
    let mensajeConfirmacion = "";
    if (esActivo) {
        mensajeConfirmacion = "¿Estás seguro de que deseas INACTIVAR este contrato?\n\nNota: El sistema bloqueará esta acción si el contrato aún tiene trabajos sin finalizar.";
    } else {
        mensajeConfirmacion = "¿Estás seguro de que deseas REACTIVAR este contrato?\n\nAl hacerlo, volverá a estar disponible para asignar nuevos trabajos.";
    }

    // 3. Mostramos la alerta. Si el usuario da a "Cancelar", detenemos todo
    if (!confirm(mensajeConfirmacion)) {
        return; 
    }

    // 4. Si aceptó, enviamos la orden al servidor
    fetch('../Controlador/ContratoController.php?accion=cambiar_estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_contrato: idContrato,
            nuevo_estado: nuevoEstado 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Si todo salió bien
            cargarTablaContratos(); 
        } else {
            // Si el backend lo bloqueó (ej. tiene trabajos pendientes)
            alert("⚠️ Atención: " + data.mensaje);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Ocurrió un error al intentar cambiar el estado.");
    });
}

function abrirModalContrato(id = '', codigo = '', desc = '', estado = 'Activo') {
    // 1. Mostrar el modal
    document.getElementById('modalContrato').style.display = 'flex';
    
    // 2. Si no hay ID, es un NUEVO contrato, así que limpiamos todo
    if (id === '') {
        document.getElementById('modalContratoId').value = '';
        document.getElementById('modalCodigoPadre').value = '';
        document.getElementById('modalDescripcion').value = '';
        document.getElementById('modalEstado').value = 'Activo';
    } else {
        // Si hay ID, es edición, así que cargamos los datos
        document.getElementById('modalContratoId').value = id;
        document.getElementById('modalCodigoPadre').value = codigo;
        document.getElementById('modalDescripcion').value = desc;
        document.getElementById('modalEstado').value = estado;
    }
}

//GUARDAR: Valida y envía al servidor
function guardarContrato() {
    const id = document.getElementById('modalContratoId').value;
    const codigo = document.getElementById('modalCodigoPadre').value;
    const desc = document.getElementById('modalDescripcion').value;
    const estado = document.getElementById('modalEstado').value;

    if (!codigo || !desc) {
        alert("Por favor, rellena el Código y la Descripción.");
        return;
    }

    fetch('../Controlador/ContratoController.php?accion=guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_contrato: id, codigo_padre: codigo, descripcion: desc, estado: estado })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert("Guardado correctamente");
            cerrarModalContrato();
            cargarTablaContratos();
        } else {
            alert("Error al guardar");
        }
    });
}

function cerrarModalContrato() {
    // 1. Ocultamos el modal
    document.getElementById('modalContrato').style.display = 'none';
    
    // 2. Limpiamos los campos del formulario
    // Asegúrate de que el id del formulario sea 'formModalContrato' (o el que uses)
    document.getElementById('modalCodigoPadre').value = '';
    document.getElementById('modalDescripcion').value = '';
    document.getElementById('modalEstado').value = 'Activo';
    
    // 3. MUY IMPORTANTE: Limpiamos el ID oculto para que no crea que estamos editando después
    document.getElementById('modalContratoId').value = '';
}

// === USUARIO Y LOGOUT ===
document.getElementById('sidebarUserName').innerText = localStorage.getItem('usuarioNombre') || 'Usuario';
document.getElementById('logoutSidebarBtn').addEventListener('click', function(e) {
    console.log("¡El clic se detectó correctamente!");
    
    // Forzamos el cierre de sesión
    const url = '../Controlador/UsuarioController.php?accion=logout';
    console.log("Redirigiendo a: " + url);
    
    // Limpiamos y redirigimos
    localStorage.clear();
    window.location.href = url;
});



