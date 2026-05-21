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
            
            // Separamos el ID del Nombre que vienen de la Base de Datos
            htmlAtletas = listaAtletas.map(item => {
                const partes = item.split(':');
                const idAtleta = partes[0];
                const nombreAtleta = partes[1];

                // Convertimos el <span> en un <button> con cursor-pointer y un efecto hover
                return `
                <button onclick="verMiniPerfilAtleta(${idAtleta})" type="button" 
                        class="inline-block px-2 py-1 bg-emerald-500/10 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/20 rounded-md text-[10px] font-bold uppercase tracking-wider mb-1 mr-1 transition-colors cursor-pointer shadow-sm active:scale-95" 
                        title="Ver perfil de ${nombreAtleta}">
                    <i class="fas fa-swimmer mr-1"></i> ${nombreAtleta}
                </button>`;
            }).join('');
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


// =====================================================================
// FUNCIONALIDAD EXTRA: MINI-PERFIL DEL ATLETA (Nativo Tailwind)
// =====================================================================

function cerrarModalVer() {
    const modal = document.getElementById('modalVer');
    if (modal) {
        modal.classList.add('hidden');
        // Limpiamos el contenido para no dejar "fantasmas" del atleta anterior
        document.getElementById('detalleContenido').innerHTML = ''; 
    }
}

// Cerramos este modal también si el usuario presiona la tecla Escape
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('modalVer');
    if (e.key === "Escape" && modal && !modal.classList.contains('hidden')) {
        cerrarModalVer();
    }
});

async function verMiniPerfilAtleta(idAtleta) {
    // 1. Mostramos un loading rápido de SweetAlert mientras viaja al servidor
    Swal.fire({
        title: 'Cargando perfil...',
        background: '#161430',
        color: '#fff',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    // 2. Buscamos los datos del atleta en el servidor
    const datos = await peticionAjax(`verPerfilAtleta&id=${idAtleta}`);
    
    // 3. Cerramos el circulito de carga
    Swal.close();

    // 4. Si trajo los datos, ejecutamos el renderizado idéntico al de tu líder
    if (datos) {
        const fotoHtml = datos.foto
            ? `<img src="${datos.foto}" class="w-28 h-28 rounded-full mx-auto mb-4 border-4 border-indigo-500/20 shadow-xl object-cover">`
            : `<div class="w-28 h-28 rounded-full mx-auto mb-4 bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-400 border-4 border-indigo-500/20"><i class="fas fa-user"></i></div>`;

        const estadoColor = {
            Activo: 'text-emerald-400',
            Inactivo: 'text-red-400',
            Retirado: 'text-amber-400',
            Transferido: 'text-blue-400'
        };

        const html = `
            <div class="text-center mb-8">
                ${fotoHtml}
                <h2 class="text-2xl font-bold text-white">${datos.nombres} ${datos.apellidos}</h2>
                <p class="text-indigo-400 mb-2 font-mono tracking-widest text-sm">${datos.cedula}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${estadoColor[datos.estado] || 'text-gray-400'} bg-white/5">${datos.estado}</span>
            </div>

            <div class="mb-6">
                <p class="text-[10px] uppercase text-indigo-400 font-bold tracking-widest mb-3"><i class="fas fa-user mr-2"></i>Datos Personales</p>
                <div class="grid grid-cols-3 gap-3 text-left bg-black/20 p-4 rounded-2xl border border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500">Edad</p><p class="text-white">${datos.edad || '--'} años</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Sexo</p><p class="text-white">${datos.sexo === 'M' ? 'Masculino' : 'Femenino'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Categoría</p><p class="text-indigo-300">${datos.categoria_nombre || 'S/C'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Teléfono</p><p class="text-white">${datos.telefono || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Correo</p><p class="text-white text-xs">${datos.correo || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Fichaje Club</p><p class="text-white">${datos.fecha_registro_club || '—'}</p></div>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-[10px] uppercase text-emerald-400 font-bold tracking-widest mb-3"><i class="fas fa-heartbeat mr-2"></i>Datos Médicos</p>
                <div class="grid grid-cols-2 gap-3 text-left bg-black/20 p-4 rounded-2xl border border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500">Grupo Sanguíneo</p><p class="text-white font-bold">${datos.grupo_sanguineo || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Seguro Médico</p><p class="text-white">${datos.seguro_medico || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Alergias</p><p class="text-white text-xs">${datos.alergias || 'Ninguna registrada'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Condiciones</p><p class="text-white text-xs">${datos.condiciones_previas || 'Ninguna registrada'}</p></div>
                </div>
                ${datos.contacto_emergencia_nombre ? `
                <div class="mt-3 p-3 rounded-xl bg-black/20 border border-white/5">
                    <p class="text-[10px] uppercase text-amber-400 font-bold mb-2"><i class="fas fa-phone-alt mr-2"></i>Contacto Emergencia</p>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div><p class="text-white text-sm">${datos.contacto_emergencia_nombre}</p><p class="text-[10px] text-gray-500">${datos.contacto_emergencia_parentesco || ''}</p></div>
                        <div><p class="text-white text-sm">${datos.contacto_emergencia_telefono || '—'}</p></div>
                    </div>
                </div>` : ''}
            </div>

            <div>
                <p class="text-[10px] uppercase text-purple-400 font-bold tracking-widest mb-3"><i class="fas fa-trophy mr-2"></i>Datos Federativos</p>
                <div class="grid grid-cols-2 gap-3 text-left bg-black/20 p-4 rounded-2xl border border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500">FEVEDA</p><p class="text-indigo-300 font-mono">${datos.numero_feveda || 'S/F'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500">Club Procedencia</p><p class="text-white">${datos.club_procedencia || '—'}</p></div>
                </div>
            </div>
        `;
        document.getElementById('detalleContenido').innerHTML = html;
        document.getElementById('modalVer').classList.remove('hidden');
    } else {
        UI.error('Error', 'No se pudieron cargar los datos del atleta.');
    }
}