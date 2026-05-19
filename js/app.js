/* ============================================================
   ESSOLIN - Gestión Industrial Eléctrica
   Archivo: app.js
   Descripción: Toda la lógica del frontend
   ============================================================ */

// ============================================================
// 1. DATOS INICIALES (MODO DEMO - luego vienen de la BD)
// ============================================================

let trabajos = [
    { id: "T-01", actividad: "Mantenimiento correctivo de sistemas eléctricos", ubicacion: "Planta Sur",    estado: "programado" },
    { id: "T-02", actividad: "Reparación de cableado de alta tensión",           ubicacion: "Taller Central", estado: "programado" },
    { id: "T-03", actividad: "Instalación de tableros de control",               ubicacion: "Oficinas",       estado: "proceso"    },
    { id: "T-04", actividad: "Diagnóstico de fallas en subestación",             ubicacion: "Planta Norte",   estado: "programado" },
    { id: "T-05", actividad: "Mantenimiento de transformadores",                 ubicacion: "Subestación",    estado: "programado" },
    { id: "T-06", actividad: "Reemplazo de interruptores eléctricos",            ubicacion: "Planta Sur",     estado: "programado" },
    { id: "T-07", actividad: "Auditoría técnica de seguridad eléctrica",         ubicacion: "Oficinas",       estado: "finalizado" },
    { id: "T-08", actividad: "Soporte técnico emergencias eléctricas",           ubicacion: "Subestación",    estado: "finalizado" }
];

let usersList = [
    { nombre: "Luis Pérez",    correo: "luis.perez@essolin.com",    rol: "Supervisor Eléctrico",    estado: "Activo" },
    { nombre: "Ana Gómez",     correo: "ana.gomez@essolin.com",     rol: "Operador",                estado: "Activo" },
    { nombre: "Carlos Torres", correo: "carlos.torres@essolin.com", rol: "Supervisor",              estado: "Activo" },
    { nombre: "Maria Ruiz",    correo: "maria.ruiz@essolin.com",    rol: "Administrativo",          estado: "Activo" },
    { nombre: "Pedro Sánchez", correo: "pedro.sanchez@essolin.com", rol: "Operador",                estado: "Activo" },
    { nombre: "Elena Vargas",  correo: "elena.vargas@essolin.com",  rol: "Electricista Senior",     estado: "Activo" },
    { nombre: "Jorge Ramírez", correo: "jorge.ramirez@essolin.com", rol: "Técnico Instrumentista",  estado: "Activo" }
];

const evidenciasImagenes = [
    { titulo: "Técnico revisando tablero eléctrico",   url: "https://picsum.photos/id/26/210/140",  desc: "Inspección de conexiones y fusibles",      fecha: "01/06/2025" },
    { titulo: "Instalación de cableado industrial",     url: "https://picsum.photos/id/20/210/140",  desc: "Tendido de líneas de fuerza en planta",    fecha: "02/06/2025" },
    { titulo: "Reparación de toma de alta tensión",     url: "https://picsum.photos/id/104/210/140", desc: "Sustitución de conectores trifásicos",     fecha: "03/06/2025" },
    { titulo: "Cuadro eléctrico en mantenimiento",      url: "https://picsum.photos/id/123/210/140", desc: "Verificación de protecciones térmicas",    fecha: "04/06/2025" },
    { titulo: "Soldadura de componentes eléctricos",    url: "https://picsum.photos/id/64/210/140",  desc: "Mantenimiento de motores industriales",    fecha: "05/06/2025" },
    { titulo: "Equipo electricista en subestación",     url: "https://picsum.photos/id/15/210/140",  desc: "Medición de parámetros eléctricos",        fecha: "06/06/2025" }
];

// ============================================================
// 2. ESTADÍSTICAS
// ============================================================

function calcularStats() {
    return {
        programados:  trabajos.filter(t => t.estado === "programado").length,
        enProceso:    trabajos.filter(t => t.estado === "proceso").length,
        finalizados:  trabajos.filter(t => t.estado === "finalizado").length,
        observados:   15  // valor fijo de demostración
    };
}

// ============================================================
// 3. ACTUALIZAR INTERFAZ COMPLETA
// ============================================================

function actualizarInterfazCompleta() {
    const stats = calcularStats();

    // Tarjetas de estadísticas (Trabajos y Reportes)
    setTexto('statsProg', stats.programados);
    setTexto('statsProc', stats.enProceso);
    setTexto('statsFin',  stats.finalizados);
    setTexto('statsObs',  stats.observados);

    // Dashboard Inicio
    setTexto('totalProgramadosInicio', stats.programados);
    setTexto('totalEnProcesoInicio',   stats.enProceso);
    setTexto('totalFinalizadosInicio', stats.finalizados);
    setTexto('totalObservadosInicio',  stats.observados);

    // Tarjetas Reportes
    setTexto('repProg', stats.programados);
    setTexto('repProc', stats.enProceso);
    setTexto('repFin',  stats.finalizados);

    renderTablaTrabajos();
    actualizarGraficos(stats);
}

// Utilidad para actualizar texto de un elemento
function setTexto(id, valor) {
    const el = document.getElementById(id);
    if (el) el.innerText = valor;
}

// ============================================================
// 4. TABLA DE TRABAJOS
// ============================================================

function renderTablaTrabajos() {
    const tbody = document.querySelector('#tablaTrabajosPrincipal tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    trabajos.forEach(t => {
        const badgeClass  = t.estado === 'programado' ? 'bg-prog' : (t.estado === 'proceso' ? 'bg-proc' : 'bg-fin');
        const estadoTexto = t.estado === 'programado' ? 'Programado' : (t.estado === 'proceso' ? 'En Proceso' : 'Finalizado');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${t.id}</td>
            <td>${t.actividad}</td>
            <td>${t.ubicacion}</td>
            <td><span class="badge ${badgeClass} estado-selector" data-id="${t.id}">${estadoTexto}</span></td>
        `;
        tbody.appendChild(tr);
    });

    // Evento: clic en badge para cambiar estado
    document.querySelectorAll('.estado-selector').forEach(el => {
        el.addEventListener('click', (e) => {
            e.stopPropagation();
            cambiarEstado(el.getAttribute('data-id'));
        });
    });
}

function cambiarEstado(trabajoId) {
    const trabajo = trabajos.find(t => t.id === trabajoId);
    if (!trabajo) return;
    const ciclo = ["programado", "proceso", "finalizado"];
    trabajo.estado = ciclo[(ciclo.indexOf(trabajo.estado) + 1) % ciclo.length];
    actualizarInterfazCompleta();

    // ---- CONEXIÓN BD: enviar cambio de estado al servidor ----
    // fetch('php/actualizar_estado.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ id: trabajoId, estado: trabajo.estado })
    // });
}

// ============================================================
// 5. FORMULARIO NUEVO TRABAJO
// ============================================================

function agregarTrabajo(event) {
    event.preventDefault();
    const tipo        = document.getElementById('tipoActividad').value;
    const ubic        = document.getElementById('ubicacion').value;
    const fecha       = document.getElementById('fechaTrabajo').value || new Date().toISOString().slice(0, 10);
    const supervisor  = document.getElementById('supervisor').value;
    const observacion = document.getElementById('observaciones').value;

    const newId = "T-" + String(trabajos.length + 11).padStart(2, '0');
    const nuevoTrabajo = {
        id:        newId,
        actividad: `${tipo}${observacion ? ' - ' + observacion.substring(0, 35) : ''}`,
        ubicacion: ubic,
        estado:    "programado"
    };
    trabajos.push(nuevoTrabajo);
    actualizarInterfazCompleta();
    document.getElementById('formNuevoTrabajo').reset();
    alert(`✅ Trabajo ${newId} creado exitosamente`);
    setActivePanel('trabajos');

    // ---- CONEXIÓN BD: guardar nuevo trabajo en el servidor ----
    // fetch('php/guardar_trabajo.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ tipo, ubicacion: ubic, fecha, supervisor, observacion })
    // }).then(r => r.json()).then(data => console.log(data));
}

// ============================================================
// 6. EVIDENCIAS
// ============================================================

function renderEvidenciasGrid() {
    const grid = document.getElementById('evidenciasImagenesGrid');
    if (!grid) return;

    grid.innerHTML = '';
    evidenciasImagenes.forEach(img => {
        grid.innerHTML += `
            <div class="evidence-card-img">
                <img src="${img.url}" alt="Electricista trabajando" loading="lazy">
                <p>${img.titulo}</p>
                <small>${img.desc}</small><br>
                <span class="badge bg-prog" style="margin-top:6px;">${img.fecha}</span>
            </div>
        `;
    });
}

// ============================================================
// 7. TABLA DE USUARIOS
// ============================================================

function renderUsuariosTable() {
    const tbody = document.getElementById('tablaUsuariosBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    usersList.forEach((u, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${u.nombre}</td>
            <td>${u.correo}</td>
            <td>${u.rol}</td>
            <td><i class="fas fa-check-circle" style="color:#2b7e3a;"></i> ${u.estado}</td>
            <td>
                <i class="fas fa-edit action-icon edit-user" data-idx="${idx}" title="Editar rol"></i>
                <i class="fas fa-toggle-on action-icon toggle-user" data-idx="${idx}" title="Cambiar estado"></i>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.querySelectorAll('.edit-user').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = btn.dataset.idx;
            const nr = prompt("Nuevo rol:", usersList[idx].rol);
            if (nr) { usersList[idx].rol = nr; renderUsuariosTable(); }
        });
    });
    document.querySelectorAll('.toggle-user').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = btn.dataset.idx;
            usersList[idx].estado = usersList[idx].estado === "Activo" ? "Inactivo" : "Activo";
            renderUsuariosTable();
        });
    });
}

// ============================================================
// 8. GRÁFICOS (Chart.js)
// ============================================================

let homeChart, doughnutChart, barChart;

function initDynamicCharts() {
    const stats = calcularStats();

    const ctxHome = document.getElementById('homeChart')?.getContext('2d');
    if (ctxHome) {
        if (homeChart) homeChart.destroy();
        homeChart = new Chart(ctxHome, {
            type: 'doughnut',
            data: {
                labels: ['Programados', 'En Proceso', 'Finalizados'],
                datasets: [{ data: [stats.programados, stats.enProceso, stats.finalizados], backgroundColor: ['#3b9fd8', '#f4a261', '#2eac8d'] }]
            },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const ctxDoughnut = document.getElementById('doughnutChart')?.getContext('2d');
    if (ctxDoughnut) {
        if (doughnutChart) doughnutChart.destroy();
        doughnutChart = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['Programados', 'En Proceso', 'Finalizados'],
                datasets: [{ data: [stats.programados, stats.enProceso, stats.finalizados], backgroundColor: ['#3b9fd8', '#f4a261', '#2eac8d'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const ctxBar = document.getElementById('barChartTrabajos')?.getContext('2d');
    if (ctxBar) {
        const total = stats.programados + stats.enProceso + stats.finalizados;
        const eficiencia = total > 0 ? Math.round((stats.finalizados / total) * 100) : 85;
        if (barChart) barChart.destroy();
        barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Eficiencia %', 'Cumplimiento'],
                datasets: [{ label: 'Indicadores', data: [eficiencia, 78], backgroundColor: '#489fb5' }]
            },
            options: { scales: { y: { beginAtZero: true, max: 100 } }, responsive: true }
        });
    }
}

function actualizarGraficos(stats) {
    if (window.doughnutChart?.data) {
        doughnutChart.data.datasets[0].data = [stats.programados, stats.enProceso, stats.finalizados];
        doughnutChart.update();
    }
    const total = stats.programados + stats.enProceso + stats.finalizados;
    if (window.barChart?.data) {
        const eficiencia = total > 0 ? Math.round((stats.finalizados / total) * 100) : 85;
        barChart.data.datasets[0].data = [eficiencia, 78];
        barChart.update();
    }
    if (window.homeChart?.data) {
        homeChart.data.datasets[0].data = [stats.programados, stats.enProceso, stats.finalizados];
        homeChart.update();
    }
}

// ============================================================
// 9. NAVEGACIÓN ENTRE PANELES
// ============================================================

const panels = {
    inicio:    document.getElementById('page-inicio'),
    trabajos:  document.getElementById('page-trabajos'),
    registro:  document.getElementById('page-registro'),
    evidencias:document.getElementById('page-evidencias'),
    reportes:  document.getElementById('page-reportes'),
    usuarios:  document.getElementById('page-usuarios')
};

function setActivePanel(pageId) {
    Object.values(panels).forEach(p => p?.classList.remove('active-panel'));
    panels[pageId]?.classList.add('active-panel');

    document.querySelectorAll('.nav-item').forEach(nav => {
        nav.classList.toggle('active', nav.dataset.page === pageId);
    });

    // Inicializar gráficos sólo al entrar a reportes
    if (pageId === 'reportes') initDynamicCharts();
}

// ============================================================
// 10. LOGIN
// ============================================================

function doLogin() {
    const email = document.getElementById('loginEmail').value.trim();
    const pass  = document.getElementById('loginPassword').value.trim();
    const errEl = document.getElementById('loginError');
    errEl.innerText = '';

    // ---- VALIDACIÓN DEMO (sin base de datos) ----
    // Para conectar con BD, reemplaza este bloque por la llamada a PHP de abajo.
    if (email === "admin@essolin.com" && pass === "Admin123!") {
        abrirApp("Admin");
    } else {
        errEl.innerText = "Credenciales incorrectas. Usa admin@essolin.com / Admin123!";
    }

    // ---- CONEXIÓN BD: login con PHP/MySQL ----
    // fetch('php/login.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ email, password: pass })
    // })
    // .then(r => r.json())
    // .then(data => {
    //     if (data.success) {
    //         abrirApp(data.nombre);
    //     } else {
    //         errEl.innerText = data.mensaje || "Credenciales incorrectas.";
    //     }
    // })
    // .catch(() => { errEl.innerText = "Error de conexión con el servidor."; });
}

function abrirApp(nombreUsuario) {
    const overlay = document.getElementById('loginOverlay');
    overlay.style.opacity = '0';
    setTimeout(() => {
        overlay.style.display = 'none';
        document.getElementById('appWrapper').style.display = 'flex';
        document.getElementById('sidebarUserName').innerText = nombreUsuario;
        renderEvidenciasGrid();
        renderUsuariosTable();
        actualizarInterfazCompleta();
        initDynamicCharts();
    }, 300);
}

// ============================================================
// 11. EXPORTAR CSV
// ============================================================

function downloadCSV(data, filename, headers) {
    const rows = [headers.join(',')];
    for (const row of data) {
        rows.push(headers.map(h => `"${String(row[h] || '').replace(/"/g, '""')}"`).join(','));
    }
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    a.click();
    URL.revokeObjectURL(a.href);
}

// ============================================================
// 12. EVENTOS AL CARGAR EL DOM
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // Login / Logout
    document.getElementById('doLoginBtn').addEventListener('click', doLogin);
    document.getElementById('loginPassword').addEventListener('keypress', e => { if (e.key === 'Enter') doLogin(); });

    document.getElementById('logoutSidebarBtn').addEventListener('click', () => {
        document.getElementById('appWrapper').style.display = 'none';
        const overlay = document.getElementById('loginOverlay');
        overlay.style.display = 'flex';
        overlay.style.opacity = '1';
        setActivePanel('inicio');
    });

    // Navegación
    document.querySelectorAll('.nav-item').forEach(nav => {
        nav.addEventListener('click', () => setActivePanel(nav.dataset.page));
    });

    // Formulario nuevo trabajo
    document.getElementById('formNuevoTrabajo')?.addEventListener('submit', agregarTrabajo);
    document.getElementById('cancelarRegistro')?.addEventListener('click', () => setActivePanel('trabajos'));

    // Exportar CSV
    document.getElementById('downloadEvidenciasBtn')?.addEventListener('click', () => {
        const data = evidenciasImagenes.map(img => ({ nombre: img.titulo, fecha: img.fecha, tipo: "imagen_electrica" }));
        downloadCSV(data, "evidencias_electricas.csv", ["nombre", "fecha", "tipo"]);
    });
    document.getElementById('downloadReportBtn')?.addEventListener('click', () => {
        const stats = calcularStats();
        downloadCSV(
            [{ metrico: "Programados", valor: stats.programados }, { metrico: "Proceso", valor: stats.enProceso }, { metrico: "Finalizados", valor: stats.finalizados }],
            "reporte_essolin.csv", ["metrico", "valor"]
        );
    });
    document.getElementById('exportUsersBtn')?.addEventListener('click', () => {
        downloadCSV(usersList, "usuarios_essolin.csv", ["nombre", "correo", "rol", "estado"]);
    });

    // Inicializar datos de pantalla
    actualizarInterfazCompleta();
});
