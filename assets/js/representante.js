// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalRep = document.getElementById('modalRepresentante');
const formRep = document.getElementById('formRepresentante');
const btnGuardar = document.getElementById('btnGuardar');

const inputFotoRep = document.getElementById('foto_rep');
const fotoPreviewRep = document.getElementById('fotoPreviewRep');


const API_URL = 'index.php?p=representante'; 

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

// =====================================================================
// ABRIR MODAL (INTELIGENTE: SIRVE PARA REGISTRAR Y EDITAR)
// =====================================================================
async function abrirModalRepresentante(idRepresentante = null) {
  
    formRep.reset(); 
    try { Validador.limpiarEstilos(formRep); } catch(e) {}

     const fechaNacInput = document.getElementById('fecha_nacimiento');
    if (fechaNacInput) {
        fechaNacInput.setCustomValidity('');
        fechaNacInput.classList.remove('border-red-500');
    }


    if (fotoPreviewRep) {
        fotoPreviewRep.innerHTML = '<i class="fas fa-camera text-gray-600 text-lg"></i>';
    }
    
   
    const inputCedulaOriginal = document.getElementById('cedula_original');
    if (inputCedulaOriginal) {
        inputCedulaOriginal.value = idRepresentante || '';
    }

   
    modalRep.classList.remove('hidden');
    setTimeout(() => {
        modalRep.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

   
    const contenedor = document.getElementById('contenedorCheckboxes');
    contenedor.innerHTML = '<p class="text-xs text-gray-500 animate-pulse p-2">Sincronizando atletas...</p>';

   
    const urlAtletas = idRepresentante ? `listarAtletas&id_representante=${idRepresentante}` : 'listarAtletas';
    const atletas = await peticionAjax(urlAtletas);

    if (atletas && atletas.length > 0) {
        contenedor.innerHTML = ''; 
        atletas.forEach(atleta => {
            const marcado = (atleta.seleccionado == 1) ? 'checked' : '';
            const medMarcado = (atleta.aut_medica == 1) ? 'checked' : '';
            const imgMarcado = (atleta.aut_imagen == 1) ? 'checked' : '';
            
            const div = document.createElement('div');
           
            div.className = "flex flex-col p-2 hover:bg-white/5 rounded-lg transition border border-transparent hover:border-gray-700";
            
            div.innerHTML = `
                <div class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="atletas_ids[]" value="${atleta.id_atleta}" 
                           id="atleta_${atleta.id_atleta}" ${marcado}
                           onchange="document.getElementById('permisos_${atleta.id_atleta}').classList.toggle('hidden', !this.checked)"
                           class="w-4 h-4 rounded border-gray-700 bg-gray-900 text-indigo-600 focus:ring-indigo-500">
                    <label for="atleta_${atleta.id_atleta}" class="text-xs text-gray-300 cursor-pointer flex-1 font-bold">
                        ${atleta.nombres} ${atleta.apellidos} 
                        <span class="text-[10px] text-gray-500 ml-1">(${atleta.cedula})</span>
                    </label>
                </div>
                
                <div id="permisos_${atleta.id_atleta}" class="pl-7 pt-2 flex gap-4 ${marcado ? '' : 'hidden'}">
                    <label class="text-[10px] text-gray-400 flex items-center gap-1 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" name="aut_medica[${atleta.id_atleta}]" value="1" ${medMarcado}
                               class="w-3 h-3 rounded bg-gray-800 border-gray-600 text-emerald-500"> 
                        Aut. Médica/Antropométrica
                    </label>
                    <label class="text-[10px] text-gray-400 flex items-center gap-1 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" name="aut_imagen[${atleta.id_atleta}]" value="1" ${imgMarcado}
                               class="w-3 h-3 rounded bg-gray-800 border-gray-600 text-emerald-500"> 
                        Uso de Imagen/Fotos
                    </label>
                </div>
            `;
            contenedor.appendChild(div);
        });
   
    } else {
        contenedor.innerHTML = '<p class="text-[11px] text-yellow-500 p-2">No hay atletas menores disponibles.</p>';
    }

    // ==========================================================
    // 5. MODO EDICIÓN: Llenar los campos personales
    // ==========================================================
    if (idRepresentante) {
       
        btnGuardar.innerHTML = 'ACTUALIZAR DATOS <i class="fas fa-sync-alt ml-2"></i>';
        
     
        const rep = await peticionAjax(`obtenerRepresentante&id=${idRepresentante}`);
        
        if (rep) {
          
            document.getElementById('cedula').value = rep.cedula;
            document.getElementById('nombres').value = rep.nombres;
            document.getElementById('apellidos').value = rep.apellidos;
            document.getElementById('telefono_principal').value = rep.telefono_principal;
            document.getElementById('parentesco').value = rep.parentesco;

           
            if (document.getElementById('fecha_nacimiento')) {
                const inputFecha = document.getElementById('fecha_nacimiento');
                inputFecha.value = rep.fecha_nacimiento || '';

                
                validarEdadEstricta(inputFecha);
                try { Validador.validarCampo(inputFecha); } catch(e) {}
            }
            if (rep.foto && fotoPreviewRep) {
                fotoPreviewRep.innerHTML = `<img src="${rep.foto}" class="w-full h-full object-cover">`;
            }
            
          
            if (document.getElementById('telefono_emergencia')) 
                document.getElementById('telefono_emergencia').value = rep.telefono_secundario || ''; 
            
            if (document.getElementById('correo')) 
                document.getElementById('correo').value = rep.correo || '';
                
            if (document.getElementById('direccion_residencia'))
                document.getElementById('direccion_residencia').value = rep.direccion || ''; 
        }
    } else {
        btnGuardar.innerHTML = 'GUARDAR Y ASOCIAR <i class="fas fa-save ml-2"></i>';
    }
}

// =====================================================================
// FUNCIONES DE CARGA DINÁMICA (RENDERIZADO DEL CLIENTE)
// =====================================================================

async function cargarTablaRepresentantes() {
    
    const tbody = document.getElementById('listaRepresentantes');
    
    tbody.innerHTML = `<tr><td colspan="6" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';

const representantes = await peticionAjax(`listarRepresentantes&estado=${filtroEstado}`);

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
        
        let htmlAtletas = '<span class="text-[10px] text-gray-600 italic">Sin vinculaciones</span>';
        let textoBusquedaAtletas = ''; 

        if (rep.atletas_vinculados) {
            textoBusquedaAtletas = rep.atletas_vinculados.toLowerCase();
            const listaAtletas = rep.atletas_vinculados.split('|');
            
            htmlAtletas = listaAtletas.map(item => {
                const partes = item.split(':');
                const idAtleta = partes[0];
                const nombreAtleta = partes[1];

                return `
                <button onclick="verMiniPerfilAtleta(${idAtleta})" type="button" 
                        class="inline-block px-2 py-1 bg-emerald-500/10 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/20 rounded-md text-[10px] font-bold uppercase tracking-wider mb-1 mr-1 transition-colors cursor-pointer shadow-sm active:scale-95" 
                        title="Ver perfil de ${nombreAtleta}">
                    <i class="fas fa-swimmer mr-1"></i> ${nombreAtleta}
                </button>`;
            }).join('');
        }

        const busqueda = `${rep.cedula} ${rep.nombres} ${rep.apellidos} ${textoBusquedaAtletas}`.toLowerCase();
        
      
        let botonAccion = '';
        
        if (rep.estado === 'Activo' || !rep.estado) { 
            botonAccion = `
                <button onclick="eliminarRepresentante(${rep.id_representante})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Archivar/Desactivar Registro">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>
            `;
        } else {
            botonAccion = `
                <button onclick="reactivarRepresentante(${rep.id_representante})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Reactivar Cuenta">
                    <i class="fas fa-user-check text-base"></i>
                </button>
            `;
        }

       
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
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="abrirModalRepresentante(${rep.id_representante})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Ficha">
                        <i class="fas fa-edit text-base"></i>
                    </button>
                    
                    ${botonAccion}
                    ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
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


    
    Validador.vincularTiempoReal(formRep);

   const fechaNacInput = document.getElementById('fecha_nacimiento');
    
    if (fechaNacInput) {
        const hoy = new Date();
        const mes = String(hoy.getMonth() + 1).padStart(2, '0'); 
        const dia = String(hoy.getDate()).padStart(2, '0');
        
        // Atributo max: Hace 18 años
        const maxDate = `${hoy.getFullYear() - 18}-${mes}-${dia}`;
        fechaNacInput.setAttribute('max', maxDate);

        // Atributo min: Hace 120 años
        const minDate = `${hoy.getFullYear() - 120}-${mes}-${dia}`;
        fechaNacInput.setAttribute('min', minDate);

        // --- NUEVO: Enganchar validación estricta al evento de tipeo ---
        fechaNacInput.addEventListener('input', function() {
            validarEdadEstricta(this);
        });
    }

   try {
        Validador.vincularTiempoReal(formRep); 
    } catch (error) {
        console.warn("Advertencia: No se pudo iniciar el validador en tiempo real.", error);
    }

    

    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); 

       
        const erroresJS = Validador.validarFormulario(formRep);
        
         if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; 
        } 

     /*   const fechaInput = document.getElementById('fecha_nacimiento'); */
   /*  const edadValida = validarEdadEstricta(fechaInput); */

   /*  if (erroresJS || !edadValida) {
        const mensajeFinal = !edadValida ? fechaInput.validationMessage : erroresJS;
        UI.advertencia('Datos Incompletos o Inválidos', mensajeFinal);
        return; 
    } */

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formRep);
        datosForm.append('accion', 'guardar');

        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalRepresentante();

                cargarTablaRepresentantes();
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

function validarEdadEstricta(input) {
    if (!input) return true;

    const fechaVal = input.value;
    if (!fechaVal) {
        input.setCustomValidity('');
        return true;
    }

    const fechaNac = new Date(fechaVal);
    const hoy = new Date();
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const m = hoy.getMonth() - fechaNac.getMonth();
    
    if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }

    if (edad < 18) {
        input.setCustomValidity('El representante debe ser mayor de 18 años.');
        input.classList.add('border-red-500');
        return false;
    } else if (edad > 120) {
        input.setCustomValidity('La edad no puede superar los 120 años.');
        input.classList.add('border-red-500');
        return false;
    }

    input.setCustomValidity('');
    input.classList.remove('border-red-500');
    return true;
}

// =====================================================================
// EVENTO SECUNDARIO: ELIMINAR (BORRADO LÓGICO)
// =====================================================================
async function eliminarRepresentante(id_representante) {
    const confirmacion =  await  UI.confirmar('Archivar Representante', 'Los atletas a su cargo quedarán libres de vinculación. Podrá reactivarlo después.');
     if (!confirmacion.isConfirmed) return;


        let datosDelete = new FormData();

        datosDelete.append('accion', 'eliminar'); 
        datosDelete.append('id_representante', id_representante);

        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Archivado', 'El representante ha sido inactivado exitosamente.');
            cargarTablaRepresentantes();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo desactivar el registro.');
        }
   
}

// =====================================================================
// EVENTO SECUNDARIO: Reactivar Representante
// =====================================================================
async function reactivarRepresentante(id_representante) {
    const confirmacion =  await  UI.confirmar('Reactivar Representante', '¿Desea reactivar a este representante y habilitarlo nuevamente en el sistema?')
         if (!confirmacion.isConfirmed) return;
       
   
        let datosReactivar = new FormData();
        datosReactivar.append('accion', 'reactivar');
        datosReactivar.append('id_representante', id_representante);

        const resultado = await peticionAjax('reactivar', datosReactivar);

            if (resultado && resultado.status === 'success') {
                UI.exito('Reactivado', 'El representante vuelve a estar activo en el directorio.');
                cargarTablaRepresentantes(); 
            } else {
                UI.error('Error', 'No se pudo procesar la reactivación.');
            }
       
      
}


// =====================================================================
// FUNCIONALIDAD: PERFIL DEL REPRESENTANTE (Nativo Tailwind / Dark Mode)
// =====================================================================
/* async function verPerfilRepresentante(idRepresentante) {
    Swal.fire({
        title: 'Cargando perfil...',
        background: document.documentElement.classList.contains('dark') ? '#161430' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1f2937',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    const [rep, todosLosAtletas] = await Promise.all([
        peticionAjax(`obtenerRepresentante&id=${idRepresentante}`),
        peticionAjax(`listarAtletas&id_representante=${idRepresentante}`)
    ]);
    
    Swal.close();

    if (rep) {
        const estadoClases = rep.estado === 'Inactivo' 
            ? 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-500/10'
            : 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/10';

        const vinculados = (todosLosAtletas || []).filter(a => a.seleccionado == 1);
        
        let htmlAtletas = '';
        if (vinculados.length > 0) {
            htmlAtletas = `<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">`;
            vinculados.forEach(atleta => {
                const badgeMed = atleta.aut_medica == 1 
                    ? '<span class="text-emerald-500" title="Autorización Médica Concedida"><i class="fas fa-check-circle"></i> Médica</span>' 
                    : '<span class="text-gray-400" title="Sin Autorización Médica"><i class="fas fa-times-circle"></i> Médica</span>';
                    
                const badgeImg = atleta.aut_imagen == 1 
                    ? '<span class="text-emerald-500" title="Uso de Imagen Concedido"><i class="fas fa-check-circle"></i> Imagen</span>' 
                    : '<span class="text-gray-400" title="Sin Autorización de Imagen"><i class="fas fa-times-circle"></i> Imagen</span>';

                htmlAtletas += `
                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-500/20 flex flex-shrink-0 items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                            ${atleta.nombres.charAt(0)}${atleta.apellidos.charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${atleta.nombres} ${atleta.apellidos}</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">${atleta.cedula}</p>
                            <div class="flex gap-2 mt-1 text-[9px] uppercase font-bold tracking-wider">
                                ${badgeMed}
                                ${badgeImg}
                            </div>
                        </div>
                    </div>
                `;
            });
            htmlAtletas += `</div>`;
        } else {
            htmlAtletas = `<p class="text-sm text-gray-500 dark:text-gray-400 italic bg-gray-100 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/5 text-center">No hay atletas vinculados actualmente.</p>`;
        }

        const html = `
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full mx-auto mb-4 bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20 shadow-xl">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">${rep.nombres} ${rep.apellidos}</h2>
                <p class="text-indigo-600 dark:text-indigo-400 mb-2 font-mono tracking-widest text-xs sm:text-sm">${rep.cedula}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${estadoClases}">${rep.estado || 'Activo'}</span>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-address-card mr-2"></i>Datos de Contacto</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Teléfono Principal</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${rep.telefono_principal || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Tel. Emergencia</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${rep.telefono_secundario || '—'}</p></div>
                    <div class="sm:col-span-2"><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Correo Electrónico</p><p class="text-gray-900 dark:text-white text-xs sm:text-sm break-all">${rep.correo || '—'}</p></div>
                </div>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación y Parentesco</p>
                <div class="grid grid-cols-1 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Vínculo Familiar</p><p class="text-gray-900 dark:text-white font-bold text-sm sm:text-base">${rep.parentesco || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Dirección de Residencia</p><p class="text-gray-900 dark:text-white text-sm sm:text-base leading-relaxed">${rep.direccion || '—'}</p></div>
                </div>
            </div>

            <!-- NUEVA SECCIÓN DE ATLETAS VINCULADOS -->
            <div>
                <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-2 sm:mb-3 flex items-center justify-between">
                    <span><i class="fas fa-swimmer mr-2"></i>Atletas a Cargo</span>
                    <span class="bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 px-2 py-0.5 rounded-full">${vinculados.length}</span>
                </p>
                <div class="bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    ${htmlAtletas}
                </div>
            </div>
        `;
        
        document.getElementById('detalleContenido').innerHTML = html;
        document.getElementById('modalVer').classList.remove('hidden');
    } else {
        UI.error('Error', 'No se pudieron cargar los datos del representante.');
    }
} */

    async function verPerfilRepresentante(idRepresentante) {
    Swal.fire({
        title: 'Cargando perfil...',
        background: document.documentElement.classList.contains('dark') ? '#161430' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1f2937',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    // 1. Ejecutamos ambas peticiones en paralelo
    const [rep, todosLosAtletas] = await Promise.all([
        peticionAjax(`obtenerRepresentante&id=${idRepresentante}`),
        peticionAjax(`listarAtletas&id_representante=${idRepresentante}`)
    ]);
    
    Swal.close();

    if (rep) {
        // Colores según el estado
        const estadoClases = rep.estado === 'Inactivo' 
            ? 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-500/10'
            : 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/10';

        // --- LÓGICA FOTO REPRESENTANTE ---
        // Si hay foto, renderizamos la imagen; si no, dejamos el ícono
        const htmlFotoRep = rep.foto 
            ? `<img src="${rep.foto}" alt="Foto de ${rep.nombres}" class="w-full h-full object-cover rounded-full">`
            : `<i class="fas fa-user-tie"></i>`;

        // 2. Filtramos los atletas que realmente le pertenecen
        const vinculados = (todosLosAtletas || []).filter(a => a.seleccionado == 1);
        
        // 3. Armamos el HTML de los atletas
        let htmlAtletas = '';
        if (vinculados.length > 0) {
            htmlAtletas = `<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">`;
            vinculados.forEach(atleta => {
                // Verificamos permisos para mostrar insignias
                const badgeMed = atleta.aut_medica == 1 
                    ? '<span class="text-emerald-500" title="Autorización Médica Concedida"><i class="fas fa-check-circle"></i> Médica</span>' 
                    : '<span class="text-gray-400" title="Sin Autorización Médica"><i class="fas fa-times-circle"></i> Médica</span>';
                    
                const badgeImg = atleta.aut_imagen == 1 
                    ? '<span class="text-emerald-500" title="Uso de Imagen Concedido"><i class="fas fa-check-circle"></i> Imagen</span>' 
                    : '<span class="text-gray-400" title="Sin Autorización de Imagen"><i class="fas fa-times-circle"></i> Imagen</span>';

                // --- LÓGICA FOTO ATLETA ---
                // Si hay foto, renderizamos la imagen; si no, dejamos las iniciales
                const htmlFotoAtleta = atleta.foto
                    ? `<img src="${atleta.foto}" alt="Foto de ${atleta.nombres}" class="w-full h-full object-cover rounded-full">`
                    : `${atleta.nombres.charAt(0)}${atleta.apellidos.charAt(0)}`;

                htmlAtletas += `
                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-500/20 flex flex-shrink-0 items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                            ${htmlFotoAtleta}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${atleta.nombres} ${atleta.apellidos}</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">${atleta.cedula}</p>
                            <div class="flex gap-2 mt-1 text-[9px] uppercase font-bold tracking-wider">
                                ${badgeMed}
                                ${badgeImg}
                            </div>
                        </div>
                    </div>
                `;
            });
            htmlAtletas += `</div>`;
        } else {
            htmlAtletas = `<p class="text-sm text-gray-500 dark:text-gray-400 italic bg-gray-100 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/5 text-center">No hay atletas vinculados actualmente.</p>`;
        }

        // 4. Inyectamos todo en el HTML principal del modal
        const html = `
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full mx-auto mb-4 bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20 shadow-xl overflow-hidden">
                    ${htmlFotoRep}
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">${rep.nombres} ${rep.apellidos}</h2>
                <p class="text-indigo-600 dark:text-indigo-400 mb-2 font-mono tracking-widest text-xs sm:text-sm">${rep.cedula}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${estadoClases}">${rep.estado || 'Activo'}</span>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-address-card mr-2"></i>Datos Personales y Contacto</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <!-- Se agregó fecha de nacimiento -->
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Fecha de Nacimiento</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${rep.fecha_nacimiento ? rep.fecha_nacimiento.split('-').reverse().join('-') : '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Correo Electrónico</p><p class="text-gray-900 dark:text-white text-xs sm:text-sm break-all">${rep.correo || '—'}</p></div>
                    
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Teléfono Principal</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${rep.telefono_principal || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Tel. Emergencia</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${rep.telefono_secundario || '—'}</p></div>
                </div>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación y Parentesco</p>
                <div class="grid grid-cols-1 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Vínculo Familiar</p><p class="text-gray-900 dark:text-white font-bold text-sm sm:text-base">${rep.parentesco || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Dirección de Residencia</p><p class="text-gray-900 dark:text-white text-sm sm:text-base leading-relaxed">${rep.direccion || '—'}</p></div>
                </div>
            </div>

            <!-- SECCIÓN DE ATLETAS VINCULADOS -->
            <div>
                <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-2 sm:mb-3 flex items-center justify-between">
                    <span><i class="fas fa-swimmer mr-2"></i>Atletas a Cargo</span>
                    <span class="bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 px-2 py-0.5 rounded-full">${vinculados.length}</span>
                </p>
                <div class="bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    ${htmlAtletas}
                </div>
            </div>
        `;
        
        document.getElementById('detalleContenido').innerHTML = html;
        document.getElementById('modalVer').classList.remove('hidden');
    } else {
        UI.error('Error', 'No se pudieron cargar los datos del representante.');
    }
}


// =====================================================================
// FUNCIONALIDAD EXTRA: MINI-PERFIL DEL ATLETA 
// =====================================================================

function cerrarModalVer() {
    const modal = document.getElementById('modalVer');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('detalleContenido').innerHTML = ''; 
    }
}

document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('modalVer');
    if (e.key === "Escape" && modal && !modal.classList.contains('hidden')) {
        cerrarModalVer();
    }
});
async function verMiniPerfilAtleta(idAtleta) {
    Swal.fire({
        title: 'Cargando perfil...',
        background: document.documentElement.classList.contains('dark') ? '#161430' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1f2937',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    const datos = await peticionAjax(`verPerfilAtleta&id=${idAtleta}`);
    Swal.close();

    if (datos) {
        const fotoHtml = datos.foto
            ? `<img src="${datos.foto}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full mx-auto mb-4 border-4 border-indigo-500/20 shadow-xl object-cover">`
            : `<div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full mx-auto mb-4 bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-3xl sm:text-4xl text-indigo-600 dark:text-indigo-400 border-4 border-indigo-200 dark:border-indigo-500/20"><i class="fas fa-user"></i></div>`;

        const estadoColor = {
            Activo: 'text-emerald-600 dark:text-emerald-400',
            Inactivo: 'text-red-600 dark:text-red-400',
            Retirado: 'text-amber-600 dark:text-amber-400',
            Transferido: 'text-blue-600 dark:text-blue-400'
        };

        const html = `
            <div class="text-center mb-6 sm:mb-8">
                ${fotoHtml}
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">${datos.nombres} ${datos.apellidos}</h2>
                <p class="text-indigo-600 dark:text-indigo-400 mb-2 font-mono tracking-widest text-xs sm:text-sm">${datos.cedula}</p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase ${estadoColor[datos.estado] || 'text-gray-600 dark:text-gray-400'} bg-gray-100 dark:bg-white/5">${datos.estado}</span>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-indigo-600 dark:text-indigo-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-user mr-2"></i>Datos Personales</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Edad</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.edad || '--'} años</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Sexo</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.sexo === 'M' ? 'Masculino' : 'Femenino'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Categoría</p><p class="text-indigo-600 dark:text-indigo-300 text-sm sm:text-base">${datos.categoria_nombre || 'S/C'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Teléfono</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.telefono || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Correo</p><p class="text-gray-900 dark:text-white text-xs sm:text-sm break-all">${datos.correo || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Fichaje Club</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.fecha_registro_club ? datos.fecha_registro_club.split('-').reverse().join('-') : '—'}</p></div>
                </div>
            </div>

            <div class="mb-5 sm:mb-6">
                <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-heartbeat mr-2"></i>Datos Médicos</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Grupo Sanguíneo</p><p class="text-gray-900 dark:text-white font-bold text-sm sm:text-base">${datos.grupo_sanguineo || '—'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Seguro Médico</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.seguro_medico || '—'}</p></div>
                    <div class="sm:col-span-2"><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Alergias</p><p class="text-gray-900 dark:text-white text-xs sm:text-sm">${datos.alergias || 'Ninguna registrada'}</p></div>
                    <div class="sm:col-span-2"><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Condiciones Previas</p><p class="text-gray-900 dark:text-white text-xs sm:text-sm">${datos.condiciones_previas || 'Ninguna registrada'}</p></div>
                </div>
                ${datos.contacto_emergencia_nombre ? `
                <div class="mt-3 p-3 rounded-xl bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                    <p class="text-[10px] uppercase text-amber-600 dark:text-amber-400 font-bold mb-2"><i class="fas fa-phone-alt mr-2"></i>Contacto Emergencia</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">
                        <div><p class="text-gray-900 dark:text-white text-sm">${datos.contacto_emergencia_nombre}</p><p class="text-[10px] text-gray-500 dark:text-gray-400">${datos.contacto_emergencia_parentesco || ''}</p></div>
                        <div><p class="text-gray-900 dark:text-white text-sm">${datos.contacto_emergencia_telefono || '—'}</p></div>
                    </div>
                </div>` : ''}
            </div>

            <div>
                <p class="text-[10px] uppercase text-purple-600 dark:text-purple-400 font-bold tracking-widest mb-2 sm:mb-3"><i class="fas fa-trophy mr-2"></i>Datos Federativos</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-left bg-gray-100 dark:bg-black/20 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5">
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">FEVEDA</p><p class="text-indigo-600 dark:text-indigo-300 font-mono text-sm sm:text-base">${datos.numero_feveda || 'S/F'}</p></div>
                    <div><p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Club Procedencia</p><p class="text-gray-900 dark:text-white text-sm sm:text-base">${datos.club_procedencia || '—'}</p></div>
                </div>
            </div>
        `;
        document.getElementById('detalleContenido').innerHTML = html;
        document.getElementById('modalVer').classList.remove('hidden');
    } else {
        UI.error('Error', 'No se pudieron cargar los datos del atleta.');
    }
}

if (inputFotoRep) {
    inputFotoRep.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                fotoPreviewRep.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
        }
    });
}