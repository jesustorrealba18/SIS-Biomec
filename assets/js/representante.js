// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalRep = document.getElementById('modalRepresentante');
const formRep = document.getElementById('formRepresentante');
const btnGuardar = document.getElementById('btnGuardar');

// NUEVA RUTA DIRECTA AL CONTROLADOR PIVOTE A TRAVÉS DEL INDEX:
const API_URL = 'index.php?p=representante'; 

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
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================

function cerrarModalRepresentante() {
    modalRep.classList.add('hidden');
    modalRep.firstElementChild.classList.add('scale-95', 'opacity-0');
}

// Cerrar modal con la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalRep.classList.contains('hidden')) {
        cerrarModalRepresentante();
    }
});

/**
 * Abre el modal. Si recibe un ID, precarga los datos para EDITAR. Si no, es REGISTRAR.
 */
async function abrirModalRepresentante(idRepresentante = null) {
    formRep.reset(); 
    
    // ¡NUEVO!: Limpiamos los bordes rojos/verdes que hayan quedado de una interacción previa
    Validador.limpiarEstilos(formRep);

    // Limpiamos el input oculto por seguridad
    document.getElementById('cedula_original').value = '';

    // Efecto de aparición suave
    setTimeout(() => {
        modalRep.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const contenedor = document.getElementById('contenedorCheckboxes');
    contenedor.innerHTML = '<p class="text-xs text-gray-500 animate-pulse p-2">Consultando atletas...</p>';

    // Cargamos la lista de atletas disponibles
    const atletas = await peticionAjax('listarAtletas');

    if (atletas && atletas.length > 0) {
        contenedor.innerHTML = ''; 
        atletas.forEach(atleta => {
            const div = document.createElement('div');
            div.className = "flex items-center gap-3 p-2 hover:bg-white/5 rounded-lg cursor-pointer transition";
            div.innerHTML = `
                <input type="checkbox" name="atletas_ids[]" value="${atleta.id_atleta}" 
                       id="atleta_${atleta.id_atleta}"
                       class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-indigo-600 focus:ring-indigo-500">
                <label for="atleta_${atleta.id_atleta}" class="text-xs text-gray-300 cursor-pointer flex-1">
                    ${atleta.nombres} ${atleta.apellidos} 
                    <span class="text-[10px] text-gray-500 ml-1">(${atleta.cedula})</span>
                </label>
            `;
            contenedor.appendChild(div);
        });
    } else {
        contenedor.innerHTML = '<p class="text-xs text-yellow-500 p-2">No hay atletas disponibles.</p>';
    }

    // Si es EDITAR, lógica para precargar...
    if (idRepresentante) {
        document.getElementById('cedula_original').value = idRepresentante;
        // Lógica de fetch para traer datos...
    }

    modalRep.classList.remove('hidden');
}

// =====================================================================
// EVENTO PRINCIPAL: INICIALIZACIÓN Y GUARDADO
// =====================================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // ¡NUEVO!: Encendemos el motor de validación en tiempo real para este formulario
    Validador.vincularTiempoReal(formRep);

    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        // DOBLE VALIDACIÓN (Capa Frontend)
        const erroresJS = Validador.validarFormulario(formRep);
        
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; 
        }

        // PREPARACIÓN DE DATOS
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formRep);

        // ENVÍO AL SERVIDOR
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalRepresentante();
                setTimeout(() => window.location.reload(), 2000); 
            } 
            else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                UI.advertencia('Validación del Servidor', msjErrores);
            } 
            else {
                UI.error('Error de Sistema', resultado.message);
            }
        }

        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

// =====================================================================
// EVENTO SECUNDARIO: ELIMINAR
// =====================================================================
async function eliminarRepresentante(id_representante) {
    const confirmacion = confirm("¿Está seguro de eliminar este representante? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_representante', id_representante);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}