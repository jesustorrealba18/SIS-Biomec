const modalAsignacion = document.getElementById('modalAsignacion');
const modalVer = document.getElementById('modalVerAsignacion');
const formAsignacion = document.getElementById('formAsignacion');
const btnGuardar = document.getElementById('btnGuardar');
const totalAsignaciones = document.getElementById('totalAsignaciones');

const API_URL = 'index.php?p=asignacion'; 

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        if (typeof UI !== 'undefined') {
            UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        }
        return null;
    }
}

function cerrarModalAsignacion() {
    if (modalAsignacion && modalAsignacion.firstElementChild) {
        modalAsignacion.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modalAsignacion) modalAsignacion.classList.add('hidden');
    }, 200);
}

function cerrarModalVer() {
    if (modalVer && modalVer.firstElementChild) {
        modalVer.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modalVer) modalVer.classList.add('hidden');
    }, 200);
}

function cerrarModalVerCarril() {
    const modal = document.getElementById('modalVerCarril');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function cerrarModalVerBloque() {
    const modal = document.getElementById('modalVerBloque');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

function cerrarModalVerGrupo() {
    const modal = document.getElementById('modalVerGrupo');
    if (modal && modal.firstElementChild) {
        modal.firstElementChild.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (modalAsignacion && !modalAsignacion.classList.contains('hidden')) cerrarModalAsignacion();
        if (modalVer && !modalVer.classList.contains('hidden')) cerrarModalVer();
        
        const modalCarril = document.getElementById('modalVerCarril');
        const modalBloque = document.getElementById('modalVerBloque');
        const modalGrupo = document.getElementById('modalVerGrupo');
        
        if (modalCarril && !modalCarril.classList.contains('hidden')) cerrarModalVerCarril();
        if (modalBloque && !modalBloque.classList.contains('hidden')) cerrarModalVerBloque();
        if (modalGrupo && !modalGrupo.classList.contains('hidden')) cerrarModalVerGrupo();
    }
});

async function verDetalleCarril(id) {
    const carril = await peticionAjax(`obtenerDetalleCarril&id=${id}`);
    if (!carril) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del carril');
        return;
    }

    const verNumero = document.getElementById('verCarrilNumero');
    const verCapacidad = document.getElementById('verCarrilCapacidad');
    const verEstado = document.getElementById('verCarrilEstado');
    
    if (verNumero) verNumero.innerText = carril.numero;
    if (verCapacidad) verCapacidad.innerText = carril.capacidad_maxima;
    if (verEstado) {
        verEstado.innerHTML = carril.activo == 1 
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs">Activo</span>' 
            : '<span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs">Inactivo</span>';
    }

    const modal = document.getElementById('modalVerCarril');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

async function verDetalleBloque(id) {
    const bloque = await peticionAjax(`obtenerDetalleBloque&id=${id}`);
    if (!bloque) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del bloque horario');
        return;
    }

    const verDia = document.getElementById('verBloqueDia');
    const verInicio = document.getElementById('verBloqueInicio');
    const verFin = document.getElementById('verBloqueFin');
    const verRango = document.getElementById('verBloqueRango');
    
    if (verDia) verDia.innerText = bloque.dia_semana;
    if (verInicio) verInicio.innerText = bloque.hora_inicio;
    if (verFin) verFin.innerText = bloque.hora_fin;
    if (verRango) {
        verRango.innerHTML = `<span class="px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-sm">${bloque.hora_inicio} - ${bloque.hora_fin}</span>`;
    }

    const modal = document.getElementById('modalVerBloque');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}

async function verDetalleGrupo(id) {
    const grupo = await peticionAjax(`obtenerDetalleGrupo&id=${id}`);
    if (!grupo) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle del grupo');
        return;
    }

    const verNombre = document.getElementById('verGrupoNombre');
    const verDescripcion = document.getElementById('verGrupoDescripcion');
    const verEntrenador = document.getElementById('verGrupoEntrenador');
    const verEstado = document.getElementById('verGrupoEstado');
    
    if (verNombre) verNombre.innerText = grupo.nombre;
    if (verDescripcion) verDescripcion.innerText = grupo.descripcion || 'Sin descripción';
    if (verEntrenador) {
        verEntrenador.innerHTML = grupo.entrenador_nombre 
            ? `${grupo.entrenador_nombre} <span class="text-xs text-gray-500">(${grupo.entrenador_cedula})</span>` 
            : '<span class="text-gray-500">Sin entrenador asignado</span>';
    }
    if (verEstado) {
        verEstado.innerHTML = grupo.activo == 1 
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs">Activo</span>' 
            : '<span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs">Inactivo</span>';
    }

    const modal = document.getElementById('modalVerGrupo');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            if (modal.firstElementChild) modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
}


async function cargarSelects() {
    try {
        const carriles = await peticionAjax('listarCarriles');
        const selectCarril = document.getElementById('id_carril');
        if (selectCarril && carriles) {
            selectCarril.innerHTML = '<option value="">Seleccione un carril</option>';
            carriles.forEach(carril => {
                selectCarril.innerHTML += `<option value="${carril.id_carril}">Carril ${carril.numero} (Cap: ${carril.capacidad_maxima})</option>`;
            });
        }

        const bloques = await peticionAjax('listarHorarios');
        const selectBloque = document.getElementById('id_bloque_horario');
        if (selectBloque && bloques) {
            selectBloque.innerHTML = '<option value="">Seleccione un horario</option>';
            bloques.forEach(bloque => {
                selectBloque.innerHTML += `<option value="${bloque.id_bloque}">${bloque.dia_semana} - ${bloque.hora_inicio} a ${bloque.hora_fin}</option>`;
            });
        }

        const grupos = await peticionAjax('listarGruposParaSelect');
        const selectGrupo = document.getElementById('id_grupo');
        if (selectGrupo && grupos) {
            selectGrupo.innerHTML = '<option value="">Seleccione un grupo</option>';
            grupos.forEach(grupo => {
                selectGrupo.innerHTML += `<option value="${grupo.id_grupo}">${grupo.nombre}</option>`;
            });
        }
    } catch (error) {
        console.error("Error cargando selects:", error);
    }
}

async function abrirModalAsignacion(id_asignacion = null) {
    if (formAsignacion) formAsignacion.reset(); 
    try { 
        if (typeof Validador !== 'undefined') Validador.limpiarEstilos(formAsignacion); 
    } catch(e) {}
    
    const inputIdHidden = document.getElementById('id_asignacion');
    const modalTitulo = document.getElementById('modalTitulo');

    await cargarSelects();

    if (id_asignacion) {
        if (inputIdHidden) inputIdHidden.value = id_asignacion;
        if (modalTitulo) modalTitulo.textContent = 'Actualizar Asignación de Carril';
        
        if (btnGuardar) btnGuardar.innerHTML = 'ACTUALIZAR ASIGNACIÓN <i class="fas fa-sync-alt ml-2"></i>';
        
        const asignacion = await peticionAjax(`obtenerAsignacion&id=${id_asignacion}`);
        
        if (asignacion) {
            const idCarril = document.getElementById('id_carril');
            const idBloque = document.getElementById('id_bloque_horario');
            const idGrupo = document.getElementById('id_grupo');
            const diaEspecifico = document.getElementById('dia_especifico');
            const fechaInicio = document.getElementById('fecha_vigente_inicio');
            const fechaFin = document.getElementById('fecha_vigente_fin');
            const activa = document.getElementById('activa');
            
            if (idCarril) idCarril.value = asignacion.id_carril;
            if (idBloque) idBloque.value = asignacion.id_bloque_horario;
            if (idGrupo) idGrupo.value = asignacion.id_grupo;
            if (diaEspecifico) diaEspecifico.value = asignacion.dia_especifico;
            if (fechaInicio) fechaInicio.value = asignacion.fecha_vigente_inicio;
            if (fechaFin) fechaFin.value = asignacion.fecha_vigente_fin;
            if (activa) activa.checked = asignacion.activa == 1;
        }
    } else {
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Asignación de Carril';
        if (btnGuardar) btnGuardar.innerHTML = 'GUARDAR ASIGNACIÓN <i class="fas fa-save ml-2"></i>';
        
        const activaCheck = document.getElementById('activa');
        if (activaCheck) activaCheck.checked = true;
    }

    if (modalAsignacion) {
        modalAsignacion.classList.remove('hidden');
        setTimeout(() => {
            if (modalAsignacion.firstElementChild) {
                modalAsignacion.firstElementChild.classList.remove('scale-95', 'opacity-0');
            }
        }, 10);
    }
}

async function verDetalle(id) {
    const asignacion = await peticionAjax(`obtenerAsignacion&id=${id}`);
    if (!asignacion) {
        if (typeof UI !== 'undefined') UI.error('Error', 'No se pudo cargar el detalle de la asignación');
        return;
    }

    const verCarril = document.getElementById('verCarril');
    const verBloque = document.getElementById('verBloqueHorario');
    const verGrupo = document.getElementById('verGrupo');
    const verDia = document.getElementById('verDiaEspecifico');
    const verFechaInicio = document.getElementById('verFechaInicio');
    const verFechaFin = document.getElementById('verFechaFin');
    const verEstado = document.getElementById('verEstado');
    
    if (verCarril) verCarril.innerText = asignacion.carril_numero || asignacion.id_carril;
    if (verBloque) verBloque.innerHTML = `${asignacion.dia_semana || ''} ${asignacion.hora_inicio || ''} - ${asignacion.hora_fin || ''}`;
    if (verGrupo) verGrupo.innerText = asignacion.grupo_nombre || asignacion.id_grupo;
    if (verDia) verDia.innerText = asignacion.dia_especifico || '—';
    if (verFechaInicio) verFechaInicio.innerText = asignacion.fecha_vigente_inicio;
    if (verFechaFin) verFechaFin.innerText = asignacion.fecha_vigente_fin;
    if (verEstado) {
        verEstado.innerHTML = asignacion.activa == 1 
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs">Activa</span>' 
            : '<span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs">Inactiva</span>';
    }

    if (modalVer) {
        modalVer.classList.remove('hidden');
        setTimeout(() => {
            if (modalVer.firstElementChild) {
                modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
            }
        }, 10);
    }
}

async function cargarTablaAsignaciones() {
    const tbody = document.getElementById('listaAsignaciones');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando asignaciones...</span></tr>`;

    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const asignaciones = await peticionAjax(`listarAsignaciones&estado=${filtroEstado}`);

    if (!asignaciones || asignaciones.length === 0) {
        if(totalAsignaciones) totalAsignaciones.textContent = '0 Registrados';
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center p-12 text-gray-500">
                    <i class="fas fa-exchange-alt text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay asignaciones registradas</span>
                </td>
            </tr>
        `;
        return;
    }

    if(totalAsignaciones) totalAsignaciones.textContent = `${asignaciones.length} Asignaciones`;

    let html = '';
    asignaciones.forEach(a => {
        const busqueda = `${a.carril_numero} ${a.dia_semana} ${a.grupo_nombre}`.toLowerCase();
        
        let botonAccion = '';
        if (a.activa == 1) { 
            botonAccion = `
                <button onclick="eliminarAsignacion(${a.id_asignacion})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Desactivar Asignación">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>
            `;
        }

        html += `
            <tr class="asignacion-row hover:bg-white/5 transition-colors duration-200" data-busqueda="${busqueda}">
                <td class="p-4 font-medium text-white">
                    <div class="flex items-center gap-2">
                        Carril ${a.carril_numero}
                        <button onclick="verDetalleCarril(${a.id_carril})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del carril">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                    </div>
                </td>
                <td class="p-4 text-gray-300">
                    <div class="flex items-center gap-2">
                        ${a.dia_semana || '—'} ${a.hora_inicio || ''} - ${a.hora_fin || ''}
                        <button onclick="verDetalleBloque(${a.id_bloque_horario})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del horario">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                    </div>
                </td>
                <td class="p-4 text-gray-300">
                    <div class="flex items-center gap-2">
                        ${a.grupo_nombre || '—'}
                        <button onclick="verDetalleGrupo(${a.id_grupo})" class="text-indigo-400 hover:text-indigo-300 transition" title="Ver detalle del grupo">
                            <i class="fas fa-info-circle text-xs"></i>
                        </button>
                    </div>
                 </td>
                <td class="p-4 text-gray-300">${a.dia_especifico || '—'}</td>
                <td class="p-4 text-gray-300">${a.fecha_vigente_inicio || '—'}</td>
                <td class="p-4">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full ${a.activa == 1 ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'} uppercase tracking-wide">
                        ${a.activa == 1 ? 'Activa' : 'Inactiva'}
                    </span>
                 </td>
                <td class="p-4 text-right space-x-1">
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="verDetalle(${a.id_asignacion})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Ver Detalle">
                        <i class="fas fa-eye text-base"></i>
                    </button>
                    <button onclick="abrirModalAsignacion(${a.id_asignacion})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Asignación">
                        <i class="fas fa-edit text-base"></i>
                    </button>
                    ${botonAccion}
                    ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
                 </td>
             </tr>
        `;
    });

    tbody.innerHTML = html;
}

const inputBusqueda = document.getElementById('busquedaAsignacion');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.asignacion-row');
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda');
            if (textoFila && textoFila.includes(valor)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaAsignaciones();
    try { 
        if (typeof Validador !== 'undefined' && formAsignacion) {
            Validador.vincularTiempoReal(formAsignacion); 
        }
    } catch (e) {}

    if (formAsignacion) {
        formAsignacion.addEventListener('submit', async function (e) {
            e.preventDefault(); 
            
            let erroresJS = false;
            try {
                if (typeof Validador !== 'undefined') {
                    erroresJS = Validador.validarFormulario(formAsignacion);
                }
            } catch(e) {}
            
            if (erroresJS) {
                if (typeof UI !== 'undefined') UI.advertencia('Datos Inválidos', erroresJS);
                return; 
            }

            const textoOriginal = btnGuardar ? btnGuardar.innerHTML : '';
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';
            }

            const datosForm = new FormData(formAsignacion);
            const resultado = await peticionAjax('guardar', datosForm);

            if (resultado) {
                if (resultado.status === 'success') {
                    if (typeof UI !== 'undefined') UI.exito('Transacción Exitosa', resultado.message);
                    cerrarModalAsignacion();
                    cargarTablaAsignaciones();
                } else if (resultado.status === 'warning') {
                    let msjErrores = Object.values(resultado.errores).join("<br>");
                    if (typeof UI !== 'undefined') UI.advertencia('Validación', msjErrores);
                } else {
                    if (typeof UI !== 'undefined') UI.error('Error', resultado.message);
                }
            }
            
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
            }
        });
    }
});

async function eliminarAsignacion(id_asignacion) {
    if (confirm("¿Está seguro de desactivar esta asignación?")) {
        let datosDelete = new FormData();
        datosDelete.append('accion', 'eliminar'); 
        datosDelete.append('id_asignacion', id_asignacion);

        const resultado = await peticionAjax('eliminar', datosDelete);
        if (resultado && resultado.status === 'success') {
            if (typeof UI !== 'undefined') UI.exito('Desactivada', 'La asignación ha sido desactivada.');
            cargarTablaAsignaciones();
        } else {
            if (typeof UI !== 'undefined') UI.error('Error', resultado?.message || 'No se pudo desactivar la asignación.');
        }
    }
}