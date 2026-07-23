const API_URL = 'index.php?p=temporadas';

const modalTemporada = document.getElementById('modalTemporada');
const formTemporada = document.getElementById('formTemporada');

// =====================================================================
// PETICION AJAX
// =====================================================================

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        const texto = await respuesta.text();

        if (!texto.trim()) throw new Error('Respuesta vacia del servidor');

        if (texto.trim().charAt(0) !== '{' && texto.trim().charAt(0) !== '[') {
            console.error('Respuesta no-JSON:', texto.substring(0, 300));
            throw new Error('El servidor no devolvio JSON valido');
        }

        return JSON.parse(texto);
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

// =====================================================================
// TABLA PRINCIPAL
// =====================================================================

async function cargarTabla() {
    const datos = await peticionAjax('listarTemporadas');
    const tbody = document.getElementById('tbodyTemporadas');

    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-calendar-alt text-4xl mb-3 block text-gray-400 dark:text-gray-600"></i>
            <span class="text-xs uppercase tracking-wider block">No se encontraron temporadas.</span>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(t => {
        const badgeActiva = parseInt(t.activa) === 1
            ? '<span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-green-50 dark:bg-green-500/20 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/30">ACTIVA</span>'
            : '<span class="px-2 py-1 rounded-lg text-[10px] font-bold bg-gray-100 dark:bg-gray-500/20 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-500/30">INACTIVA</span>';

        return `
        <tr class="hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200 border-b border-gray-200 dark:border-[#252345] fila-temporada" data-nombre="${(t.nombre || '').toLowerCase()}">
            <td class="p-4">
                <p class="text-gray-900 dark:text-white font-medium">${t.nombre || 'Sin nombre'}</p>
            </td>
            <td class="p-4 font-mono text-xs text-gray-700 dark:text-gray-300">${formatoFecha(t.fecha_inicio)}</td>
            <td class="p-4 font-mono text-xs text-gray-700 dark:text-gray-300">${formatoFecha(t.fecha_fin)}</td>
            <td class="p-4 text-center">
                <span class="bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 px-2 py-1 rounded-lg text-xs font-bold border border-cyan-200 dark:border-cyan-500/30">${t.total_macrociclos || 0}</span>
            </td>
            <td class="p-4 text-center">${badgeActiva}</td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="abrirModalTemporada(${t.id_temporada})" class="p-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-lg transition cursor-pointer" title="Editar">
                        <i class="fas fa-pen text-sm"></i>
                    </button>
                    ${parseInt(t.activa) !== 1 ? `
                    <button onclick="activarTemporada(${t.id_temporada})" class="p-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition cursor-pointer" title="Activar temporada">
                        <i class="fas fa-toggle-on text-sm"></i>
                    </button>` : ''}
                    <button onclick="eliminarTemporada(${t.id_temporada}, '${t.nombre.replace(/'/g, "\\'")}')" class="p-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition cursor-pointer" title="Eliminar">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </td>
        </tr>
    `}).join('');
}

function filtrarTabla() {
    const texto = document.getElementById('busquedaTemporada').value.toLowerCase();
    const filas = document.querySelectorAll('.fila-temporada');
    filas.forEach(fila => {
        const nombre = fila.getAttribute('data-nombre') || '';
        fila.style.display = nombre.includes(texto) ? '' : 'none';
    });
}

// =====================================================================
// MODAL CREAR/EDITAR
// =====================================================================

function abrirModalTemporada(id_temporada = null) {
    formTemporada.reset();
    document.getElementById('id_temporada').value = '';
    document.getElementById('activa').checked = false;

    if (id_temporada) {
        document.getElementById('modalTemporadaTitulo').innerHTML = '<i class="fas fa-edit text-indigo-400"></i> Editar Temporada';
        peticionAjax(`obtenerTemporada&id=${id_temporada}`).then(temp => {
            if (!temp) return;
            document.getElementById('id_temporada').value = temp.id_temporada;
            document.getElementById('nombre').value = temp.nombre || '';
            document.getElementById('fecha_inicio').value = temp.fecha_inicio || '';
            document.getElementById('fecha_fin').value = temp.fecha_fin || '';
            document.getElementById('activa').checked = parseInt(temp.activa) === 1;
        });
    } else {
        document.getElementById('modalTemporadaTitulo').innerHTML = '<i class="fas fa-calendar-check text-emerald-400"></i> Nueva Temporada';
        document.getElementById('activa').checked = true;
    }

    modalTemporada.style.display = 'flex';
    modalTemporada.classList.remove('hidden');
    setTimeout(() => modalTemporada.firstElementChild.classList.remove('scale-95', 'opacity-0'), 10);
}

function cerrarModalTemporada() {
    modalTemporada.style.display = 'none';
    modalTemporada.classList.add('hidden');
    modalTemporada.firstElementChild.classList.add('scale-95', 'opacity-0');
    formTemporada.reset();
    document.getElementById('id_temporada').value = '';
}

formTemporada.addEventListener('submit', async function(e) {
    e.preventDefault();

    const id_temporada = document.getElementById('id_temporada').value;
    const formData = new FormData(formTemporada);
    formData.append('accion', 'guardar');
    if (id_temporada) {
        formData.append('id_temporada', id_temporada);
    }

    const resultado = await peticionAjax('guardar', formData);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Temporada Guardada', resultado.message);
            cerrarModalTemporada();
            cargarTabla();
        } else if (resultado.status === 'warning') {
            mostrarErroresFormulario(resultado.errores);
        } else {
            UI.error('Error', resultado.message);
        }
    }
});

// =====================================================================
// ACTIVAR TEMPORADA
// =====================================================================

async function activarTemporada(id_temporada) {
    const confirmado = await UI.confirmar(
        'Activar Temporada',
        'Al activar esta temporada, las demas se desactivaran automaticamente. Continuar?'
    );
    if (!confirmado) return;

    const datos = new URLSearchParams({
        accion: 'activar',
        id_temporada: id_temporada
    });

    const resultado = await peticionAjax('activar', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Temporada Activada', resultado.message);
            cargarTabla();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// =====================================================================
// ELIMINAR TEMPORADA
// =====================================================================

async function eliminarTemporada(id_temporada, nombre) {
    const justificacion = await UI.pedirJustificacion(
        'Eliminar Temporada',
        `Se eliminara la temporada <strong>${nombre}</strong>. Esta accion no se puede deshacer.`
    );
    if (!justificacion) return;

    const datos = new URLSearchParams({
        accion: 'eliminar',
        id_temporada: id_temporada
    });

    const resultado = await peticionAjax('eliminar', datos);
    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('Temporada Eliminada', resultado.message);
            cargarTabla();
        } else {
            UI.error('Error', resultado.message);
        }
    }
}

// =====================================================================
// UTILIDADES
// =====================================================================

function mostrarErroresFormulario(errores) {
    Object.entries(errores).forEach(([campo, mensaje]) => {
        const input = document.getElementById(campo);
        if (input) {
            input.classList.add('border-red-500');
            const errorSpan = document.createElement('p');
            errorSpan.className = 'text-red-600 dark:text-red-400 text-[10px] mt-1';
            errorSpan.textContent = mensaje;
            input.parentElement.appendChild(errorSpan);
            setTimeout(() => {
                input.classList.remove('border-red-500');
                if (errorSpan.parentElement) errorSpan.remove();
            }, 4000);
        }
    });
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalTemporada.classList.contains('hidden')) cerrarModalTemporada();
    }
});

cargarTabla();