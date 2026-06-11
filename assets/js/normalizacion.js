// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalFormulario = document.getElementById('modalFormulario');
const formularioNormalizacion = document.getElementById('formularioNormalizacion');
// La ruta sigue tu patrón de enrutamiento al index.php
const API_URL = 'index.php?p=normalizacion'; 

/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

// =====================================================================
// MANEJO DE LA INTERFAZ
// =====================================================================

function abrirModalFormulario() {
    modalFormulario.classList.remove('hidden');
    setTimeout(() => {
        modalFormulario.classList.add('opacity-100');
        document.getElementById('modalFormularioContent').classList.remove('scale-95');
    }, 10);
}

function cerrarModalFormulario() {
    modalFormulario.classList.remove('opacity-100');
    document.getElementById('modalFormularioContent').classList.add('scale-95');
    setTimeout(() => modalFormulario.classList.add('hidden'), 300);
    formularioNormalizacion.reset();
}

// =====================================================================
// FLUJO TRANSACCIONAL (CRUD)
// =====================================================================

/**
 * Carga inicial de datos al abrir la vista
 */
async function cargarTablaNormalizacion() {
    const datos = await peticionAjax('listarNormalizaciones');
    const cuerpo = document.getElementById('cuerpoTablaNormalizacion');
    cuerpo.innerHTML = '';

    if (datos && datos.length > 0) {
        datos.forEach(item => {
            cuerpo.innerHTML += `
                <tr class="border-b border-[#252345] hover:bg-[#1a1838] transition-colors">
                    <td class="p-4">${item.nombre_atleta}</td>
                    <td class="p-4">${item.estilo} (${item.distancia_m}m)</td>
                    <td class="p-4">${item.tipo_piscina_origen}</td>
                    <td class="p-4">${item.tiempo_original_seg}s</td>
                    <td class="p-4 text-indigo-400 font-bold">${item.tiempo_convertido_seg}s</td>
                    <td class="p-4 text-center">
                        <button onclick="archivarNormalizacion(${item.id_normalizacion})" class="text-red-400 hover:text-red-300">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
}

/**
 * Guardar nueva normalización
 */
formularioNormalizacion.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(formularioNormalizacion);
    
    const resultado = await peticionAjax('registrar', formData);
    
    if (resultado && resultado.status === 'success') {
        UI.exito('Éxito', resultado.message);
        cerrarModalFormulario();
        cargarTablaNormalizacion();
    } else {
        UI.error('Error', resultado?.message || 'Error al procesar.');
    }
});

/**
 * Archivar registro (Borrado lógico con justificación)
 */
async function archivarNormalizacion(id) {
    const { value: motivo } = await Swal.fire({
        title: '¿Archivar registro?',
        text: "Esta acción requiere una justificación:",
        input: 'text',
        inputPlaceholder: 'Motivo de la anulación...',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#d33',
        inputValidator: (value) => { if (!value) return 'Necesitas escribir una razón' }
    });

    if (motivo) {
        let datos = new FormData();
        datos.append('accion', 'eliminar');
        datos.append('id_normalizacion', id);
        datos.append('motivo', motivo);
        
        const resultado = await peticionAjax('eliminar', datos);
        if (resultado && resultado.status === 'success') {
            UI.exito('Archivado', 'El registro ha sido anulado.');
            cargarTablaNormalizacion();
        }
    }
}

// =====================================================================
// INICIALIZACIÓN
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    cargarTablaNormalizacion();
});