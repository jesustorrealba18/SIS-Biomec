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
// FUNCIONES DE CARGA DINÁMICA (RENDERIZADO DEL CLIENTE)
// =====================================================================

/**
 * Consulta los representantes al servidor y dibuja la tabla dinámicamente
 */
async function cargarTablaRepresentantes() {
    const tbody = document.getElementById('listaRepresentantes');
    
    // Cambiamos el colspan a 6 porque agregamos una columna
    tbody.innerHTML = `<tr><td colspan="6" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const representantes = await peticionAjax('listarRepresentantes');

    if (!representantes || representantes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center p-12 text-gray-500">
                    <i class="fas fa-users-slash text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay representantes registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    representantes.forEach(rep => {
        
        // 1. Lógica para procesar y dibujar a los atletas como etiquetas bonitas
        let htmlAtletas = '<span class="text-[10px] text-gray-600 italic">Sin vinculaciones</span>';
        let textoBusquedaAtletas = ''; // Para alimentar el buscador

        if (rep.atletas_vinculados) {
            textoBusquedaAtletas = rep.atletas_vinculados.toLowerCase();
            const listaAtletas = rep.atletas_vinculados.split('|');
            
            // Map transforma cada nombre en una etiqueta visual de Tailwind
            htmlAtletas = listaAtletas.map(nombre => 
                `<span class="inline-block px-2 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md text-[10px] font-bold uppercase tracking-wider mb-1 mr-1">
                    <i class="fas fa-swimmer mr-1"></i> ${nombre}
                </span>`
            ).join('');
        }

        // 2. String de búsqueda repotenciado (Incluye los nombres de los atletas)
        const busqueda = `${rep.cedula} ${rep.nombres} ${rep.apellidos} ${textoBusquedaAtletas}`.toLowerCase();
        
        html += `
            <tr class="representante-row hover:bg-white/5 transition-colors duration-200" data-busqueda="${busqueda}">
                <td class="p-4 font-medium text-white">${rep.nombres} ${rep.apellidos}</td>
                <td class="p-4 font-mono text-xs tracking-wider text-indigo-300">${rep.cedula}</td>
                <td class="p-4 text-gray-300">${rep.telefono_principal}</td>
                <td class="p-4">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase tracking-wide">
                        ${rep.parentesco}
                    </span>
                </td>
                
                <td class="p-4">
                    <div class="flex flex-wrap max-w-xs">
                        ${htmlAtletas}
                    </div>
                </td>
                
                <td class="p-4 text-right space-x-1">
                    <button onclick="abrirModalRepresentante(${rep.id_representante})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Ficha">
                        <i class="fas fa-edit text-base"></i>
                    </button>
                    <button onclick="eliminarRepresentante(${rep.id_representante})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Eliminar Registro">
                        <i class="fas fa-trash-alt text-base"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    const inputBusqueda = document.getElementById('busquedaCedula');
    if (inputBusqueda && inputBusqueda.value.trim() !== '') {
        inputBusqueda.dispatchEvent(new Event('input'));
    }
}

// =====================================================================
// BARRA DE BÚSQUEDA EN TIEMPO REAL
// =====================================================================
const inputBusqueda = document.getElementById('busquedaCedula');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.representante-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda');
            fila.style.display = textoFila.includes(valor) ? '' : 'none';
        });
    });
}

// =====================================================================
// EVENTO PRINCIPAL: INICIALIZACIÓN Y GUARDADO
// =====================================================================

document.addEventListener('DOMContentLoaded', () => {
    
    // ¡NUEVO!: motor de validación en tiempo real para este formulario
    Validador.vincularTiempoReal(formRep);

    // 1. Cargar la tabla dinámicamente al abrir la pantalla
    cargarTablaRepresentantes();

    // 2. Encender validador...
   try {
        Validador.vincularTiempoReal(formRep); 
    } catch (error) {
        console.warn("Advertencia: No se pudo iniciar el validador en tiempo real.", error);
    }

    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        // DOBLE VALIDACIÓN (Capa Frontend)
        // Validador.limpiarEstilos(formRep); // Limpia rastros viejos
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

                cargarTablaRepresentantes();
                // setTimeout(() => window.location.reload(), 2000); 
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
            cargarTablaRepresentantes();
            // setTimeout(() => window.location.reload(), 2000);
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}