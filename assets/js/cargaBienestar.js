// =====================================================================
// CONTROLADOR FRONTEND: CARGA Y BIENESTAR (RF-11)
// =====================================================================
const API_URL = 'index.php?p=cargaBienestar';

// Referencias a elementos DOM
const modalEdit = document.getElementById('modalEdit');
const formRegistro = document.getElementById('formRegistro');
const tablaHistorial = document.getElementById('tablaHistorial');
const formEdit = document.getElementById('formEdit');

/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

// =====================================================================
// FUNCIONES DE CARGA DE DATOS
// =====================================================================

/**
 * Carga los atletas en los selects (formulario de registro y filtro historial)
 */
async function cargarAtletas() {
    const data = await peticionAjax('listarAtletasSelect');
    if (!data) return;

    const selectRegistro = document.getElementById('id_atleta');
    const selectHistorial = document.getElementById('filtroAtletaHistorial');

    if (selectRegistro) {
        selectRegistro.innerHTML = '<option value="">Seleccionar Atleta...</option>';
        data.forEach(a => {
            selectRegistro.innerHTML += `<option value="${a.id_atleta}">${a.nombres} ${a.apellidos} (${a.cedula})</option>`;
        });
    }

    if (selectHistorial) {
        selectHistorial.innerHTML = '<option value="">-- Elija un atleta para ver su historial --</option>';
        data.forEach(a => {
            selectHistorial.innerHTML += `<option value="${a.id_atleta}">${a.nombres} ${a.apellidos} (${a.cedula})</option>`;
        });
    }
}

/**
 * Carga la tabla de historial para el atleta seleccionado
 */
async function cargarTablaHistorial(idAtleta) {
    if (!idAtleta) {
        tablaHistorial.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">Seleccione un atleta para ver su historial</td></tr>';
        return;
    }

    const data = await peticionAjax(`listarHistorial&id_atleta=${idAtleta}`);
    if (!data) return;

    if (data.length === 0) {
        tablaHistorial.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">No hay registros para este atleta</td></tr>';
        return;
    }

    let html = '';
    data.forEach(item => {
        const estadoClass = item.estado === 'Activo' ? 'text-green-400' : 'text-red-400';
        html += `
            <tr class="border-b border-white/5">
                <td class="p-3">${item.fecha}</td>
                <td class="p-3">${item.tipo_evento}</td>
                <td class="p-3">${item.rpe}</td>
                <td class="p-3"><span class="${estadoClass}">${item.estado}</span></td>
                <td class="p-3">
                    ${item.estado === 'Activo' ? `
                        <button onclick="abrirEdicion(${item.id_evento})" class="text-indigo-400 hover:text-white mr-2"><i class="fas fa-edit"></i></button>
                        <button onclick="anularEvento(${item.id_evento})" class="text-red-400 hover:text-white"><i class="fas fa-trash"></i></button>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    tablaHistorial.innerHTML = html;
}

// =====================================================================
// REGISTRAR EVENTO
// =====================================================================

async function registrarEvento(e) {
    e.preventDefault();

    // Validación frontend (si se dispone de Validador)
    if (typeof Validador !== 'undefined') {
        const errores = Validador.validarFormulario(formRegistro);
        if (errores) {
            UI.error('Validación', errores);
            return;
        }
    }

    const formData = new FormData(formRegistro);
    formData.append('accion', 'registrar');

    const res = await peticionAjax('registrar', formData);
    if (res?.status === 'success') {
        UI.exito('Éxito', res.message);
        formRegistro.reset();
        cerrarFormularioRegistro();
        // Recargar historial si hay un atleta seleccionado
        const selectHistorial = document.getElementById('filtroAtletaHistorial');
        if (selectHistorial && selectHistorial.value) {
            cargarTablaHistorial(selectHistorial.value);
        }
    } else if (res?.status === 'warning') {
        UI.error('Validación', Object.values(res.errores).join('<br>'));
    } else {
        UI.error('Error', res?.message || 'Error al guardar');
    }
}

// =====================================================================
// EDICIÓN DE EVENTO
// =====================================================================

async function abrirEdicion(idEvento) {
    const data = await peticionAjax(`obtenerEvento&id_evento=${idEvento}`);
    if (!data || data.status === 'error') {
        UI.error('Error', data?.message || 'No se pudo cargar el evento');
        return;
    }

    // Llenar el modal de edición
    document.getElementById('edit_id_evento').value = data.id_evento;
    document.getElementById('edit_rpe').value = data.rpe;
    document.getElementById('edit_calidad_sueno').value = data.calidad_sueno;
    document.getElementById('edit_nivel_fatiga').value = data.nivel_fatiga;
    document.getElementById('edit_descripcion').value = data.descripcion || '';
    document.getElementById('edit_justificacion').value = '';

    // Mostrar modal con animación
    modalEdit.classList.remove('hidden');
    setTimeout(() => {
        modalEdit.firstElementChild?.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function cerrarModalEdit() {
    modalEdit.classList.add('hidden');
    // Resetear animación
    modalEdit.firstElementChild?.classList.add('scale-95', 'opacity-0');
}

async function enviarEdicion(e) {
    e.preventDefault();

    const justificacion = document.getElementById('edit_justificacion')?.value.trim();
    if (!justificacion) {
        UI.error('Validación', 'La justificación del cambio es obligatoria');
        return;
    }

    const formData = new FormData(formEdit);
    formData.append('accion', 'editar');

    const res = await peticionAjax('editar', formData);
    if (res?.status === 'success') {
        UI.exito('Éxito', res.message);
        cerrarModalEdit();
        // Recargar tabla
        const selectHistorial = document.getElementById('filtroAtletaHistorial');
        if (selectHistorial && selectHistorial.value) {
            cargarTablaHistorial(selectHistorial.value);
        }
    } else {
        UI.error('Error', res?.message || 'Error al actualizar');
    }
}

// =====================================================================
// ANULAR EVENTO (SOFT DELETE)
// =====================================================================

async function anularEvento(idEvento) {
    const result = await UI.pedirJustificacion(
        'Anular Registro',
        '¿Está seguro de anular este evento? Indique el motivo:'
    );

    if (!result.isConfirmed || !result.value) return;

    const formData = new FormData();
    formData.append('accion', 'anular');
    formData.append('id_evento', idEvento);
    formData.append('justificacion_cambio', result.value);

    const res = await peticionAjax('anular', formData);
    if (res?.status === 'success') {
        UI.exito('Anulado', res.message);
        const selectHistorial = document.getElementById('filtroAtletaHistorial');
        if (selectHistorial && selectHistorial.value) {
            cargarTablaHistorial(selectHistorial.value);
        }
    } else {
        UI.error('Error', res?.message || 'No se pudo anular el registro');
    }
}

// =====================================================================
// MANEJO DEL FORMULARIO DE REGISTRO (TOGGLE)
// =====================================================================

function mostrarFormularioRegistro() {
    const container = document.getElementById('formRegistroContainer');
    if (container) container.classList.remove('hidden');
}

function cerrarFormularioRegistro() {
    const container = document.getElementById('formRegistroContainer');
    if (container) container.classList.add('hidden');
}

// =====================================================================
// GRÁFICA DE EVOLUCIÓN (RF-11.2) - OPCIONAL CON CHART.JS
// =====================================================================

async function mostrarGraficaEvolucion(idAtleta) {
    if (!idAtleta) return;
    const data = await peticionAjax(`obtenerHistorialConMetricas&id_atleta=${idAtleta}`);
    if (!data || data.length === 0) return;

    // Esta función sería llamada desde un botón o pestaña en la vista
    // Usar Chart.js para dibujar líneas de RPE, sueño, fatiga vs fecha
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no cargado');
        return;
    }

    const canvas = document.getElementById('graficaEvolucion');
    if (!canvas) return;

    // Destruir gráfica anterior si existe
    if (window.chartInstancia) window.chartInstancia.destroy();

    const fechas = data.map(item => item.fecha);
    const rpeData = data.map(item => item.rpe);
    const suenoData = data.map(item => item.calidad_sueno);
    const fatigaData = data.map(item => item.nivel_fatiga);

    const ctx = canvas.getContext('2d');
    window.chartInstancia = new Chart(ctx, {
        type: 'line',
        data: {
            labels: fechas,
            datasets: [
                { label: 'RPE', data: rpeData, borderColor: '#ef4444', tension: 0.2 },
                { label: 'Calidad Sueño', data: suenoData, borderColor: '#10b981', tension: 0.2 },
                { label: 'Nivel Fatiga', data: fatigaData, borderColor: '#f59e0b', tension: 0.2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 10 } }
        }
    });
}

// =====================================================================
// INICIALIZACIÓN
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Cargar atletas en los selects
    cargarAtletas();

    // Evento para cargar historial al cambiar atleta
    const selectHistorial = document.getElementById('filtroAtletaHistorial');
    if (selectHistorial) {
        selectHistorial.addEventListener('change', (e) => {
            cargarTablaHistorial(e.target.value);
        });
    }

    // Evento de envío del formulario de registro
    if (formRegistro) {
        formRegistro.addEventListener('submit', registrarEvento);
    }

    // Evento de envío del formulario de edición
    if (formEdit) {
        formEdit.addEventListener('submit', enviarEdicion);
    }

    // Botones para mostrar/ocultar formulario de registro
    const btnAbrir = document.getElementById('btnAbrirRegistro');
    const btnCerrar = document.getElementById('btnCerrarRegistro');
    if (btnAbrir) btnAbrir.addEventListener('click', mostrarFormularioRegistro);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarFormularioRegistro);

    // Cerrar modal de edición con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalEdit && !modalEdit.classList.contains('hidden')) {
            cerrarModalEdit();
        }
    });
});