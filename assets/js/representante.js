// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalRep = document.getElementById('modalRepresentante');
const formRep = document.getElementById('formRepresentante');
const btnGuardar = document.getElementById('btnGuardar');

// Ajusta esta URL según cómo esté configurado tu enrutador principal
const API_URL = 'api.php?c=representante'; 

/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 * @param {string} accion - Acción a ejecutar en el controlador (listarAtletas, guardar, eliminar, etc.)
 * @param {FormData|null} datos - Datos del formulario si es POST
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
    
    // Limpiamos el input oculto por seguridad
    document.getElementById('cedula_original').value = '';

    // Efecto de aparición suave (Tailwind)
    setTimeout(() => {
        modalRep.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const contenedor = document.getElementById('contenedorCheckboxes');
    contenedor.innerHTML = '<p class="text-xs text-gray-500 animate-pulse p-2">Consultando atletas...</p>';

    // 1. Cargamos la lista de atletas disponibles
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

    // 2. Si es EDITAR, buscamos los datos del representante y llenamos el formulario
    if (idRepresentante) {
        document.getElementById('cedula_original').value = idRepresentante;
        // Aquí podrías hacer un fetch para traer los datos del representante:
        // const datosRep = await peticionAjax('consultarPorId&id=' + idRepresentante);
        // document.getElementById('nombres').value = datosRep.nombres;
        // document.getElementById('apellidos').value = datosRep.apellidos;
        // etc...
    }

    modalRep.classList.remove('hidden');
}

// =====================================================================
// EVENTO PRINCIPAL: GUARDAR / ACTUALIZAR
// =====================================================================

document.addEventListener('DOMContentLoaded', () => {
    
    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); // Evita la recarga blanca de la página

        // 1. DOBLE VALIDACIÓN (Capa Frontend)
        // Usamos nuestro Validador global para revisar todo según los data-attributes del HTML
        const erroresJS = Validador.validarFormulario(formRep);
        
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; // Cortamos la ejecución aquí. Cero estrés para el servidor.
        }

        // 2. PREPARACIÓN DE DATOS (Si la validación JS pasó)
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        // FormData empaca inputs y checkboxes automáticamente
        const datosForm = new FormData(formRep);

        // 3. ENVÍO AL SERVIDOR Y VALIDACIÓN BACKEND
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            // Evaluamos la respuesta estandarizada de nuestro Pivote PHP
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalRepresentante();
                
                // Refrescamos la tabla a los 2 segundos para que el usuario vea el cambio
                setTimeout(() => window.location.reload(), 2000); 
            } 
            else if (resultado.status === 'warning') {
                // Errores detectados por el Modelo (Ej: Cédula ya registrada)
                let msjErrores = Object.values(resultado.errores).join("<br>");
                UI.advertencia('Validación del Servidor', msjErrores);
            } 
            else {
                // Errores graves (Ej: Caída de Base de Datos)
                UI.error('Error de Sistema', resultado.message);
            }
        }

        // Restauramos el botón
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

// =====================================================================
// EVENTO SECUNDARIO: ELIMINAR
// =====================================================================

async function eliminarRepresentante(id_representante) {
    // Usamos el confirm de tu clase UI o SweetAlert directamente
    // Simulando la confirmación de SweetAlert:
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






/* 
// Lógica para abrir/cerrar modal (Mismo estilo que tus compañeros)[cite: 19]
const modalRep = document.getElementById('modalRepresentante');
const formRep = document.getElementById('formRepresentante');

function abrirModalRepresentante() {
    formRep.reset(); // Limpia los campos
    modalRep.classList.remove('hidden');
    // Animación suave
    setTimeout(() => {
        modalRep.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function cerrarModalRepresentante() {
    modalRep.classList.add('hidden');
    modalRep.firstElementChild.classList.add('scale-95', 'opacity-0');
}

// Cerramos modal con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") cerrarModalRepresentante();
});

// Evento Principal de Guardado
document.addEventListener('DOMContentLoaded', () => {
    
    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); // Evitamos recarga de página (¡Chao pantallas blancas!)

        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando Transacción... <i class="fas fa-spinner fa-spin ml-2"></i>';

        // 1. Recolectamos los Atletas seleccionados (Los checkboxes)
        const checkboxes = document.querySelectorAll('input[name="atletas[]"]:checked');
        const atletasAsignados = Array.from(checkboxes).map(cb => parseInt(cb.value));

        // 2. Empaquetamos todo en el JSON
        const datos = {
            cedula: document.getElementById('cedula').value.trim(),
            nombres: document.getElementById('nombres').value.trim(),
            apellidos: document.getElementById('apellidos').value.trim(),
            telefonoP: document.getElementById('telefono_principal').value.trim(),
            telefonoE: document.getElementById('telefono_emergencia').value.trim(),
            email: document.getElementById('correo').value.trim(),
            direccion: document.getElementById('direccion_residencia').value.trim(),
            parentesco: document.getElementById('parentesco').value.trim(),
            atletas_ids: atletasAsignados // <-- Aquí va el array de hijos [1, 2]
        };

        try {
            // 3. Enviamos al API REST
            const respuesta = await fetch('api.php?c=Representante&accion=registrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            // 4. Evaluamos y usamos nuestra clase UI de alertas
            if (respuesta.status === 201) {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalRepresentante();
                // Aquí podrías agregar una función cargarTabla() para refrescar
            } else {
                UI.advertencia('Validación', resultado.message);
            }

        } catch (error) {
            UI.error('Error del Servidor', 'No se pudo completar la transacción.');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'GUARDAR Y VINCULAR';
        }
    });
}); */