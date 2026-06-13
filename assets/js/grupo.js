const modalGrupo = document.getElementById('modalGrupo');
const formGrupo = document.getElementById('formGrupo');
const btnGuardar = document.getElementById('btnGuardar');

const API_URL = 'index.php?p=grupo'; 

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

function cerrarModalGrupo() {
    modalGrupo.classList.add('hidden');
    modalGrupo.firstElementChild.classList.add('scale-95', 'opacity-0');
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalGrupo.classList.contains('hidden')) {
        cerrarModalGrupo();
    }
});

async function abrirModalGrupo(idGrupo = null) {
    formGrupo.reset(); 
    try { Validador.limpiarEstilos(formGrupo); } catch(e) {}
    
    const inputIdOriginal = document.getElementById('id_grupo_original');
    if (inputIdOriginal) {
        inputIdOriginal.value = idGrupo || '';
    }

    modalGrupo.classList.remove('hidden');
    setTimeout(() => {
        modalGrupo.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    const selectEntrenador = document.getElementById('id_entrenador');
    selectEntrenador.innerHTML = '<option value="">Cargando entrenadores...</option>';

    try {
        const respuesta = await fetch('index.php?p=grupo&accion=listarEntrenadoresGlobales');
        if (!respuesta.ok) throw new Error('Error en respuesta de red');
        const entrenadores = await respuesta.json();

        if (entrenadores && entrenadores.length > 0) {
            selectEntrenador.innerHTML = '<option value="">Seleccione un entrenador...</option>';
            entrenadores.forEach(ent => {
                const option = document.createElement('option');
                option.value = ent.id_entrenador;
                option.textContent = `${ent.nombres} ${ent.apellidos} (${ent.cedula})`;
                selectEntrenador.appendChild(option);
            });
        } else {
            selectEntrenador.innerHTML = '<option value="">No hay entrenadores registrados en el sistema</option>';
        }
    } catch (err) {
        console.error("Error cargando entrenadores:", err);
        selectEntrenador.innerHTML = '<option value="">Error al cargar entrenadores</option>';
    }

    if (idGrupo) {
        btnGuardar.innerHTML = 'ACTUALIZAR GRUPO <i class="fas fa-sync-alt ml-2"></i>';
        const grupo = await peticionAjax(`obtenerGrupo&id=${idGrupo}`);
        
        if (grupo) {
            document.getElementById('nombre').value = grupo.nombre;
            document.getElementById('descripcion').value = grupo.descripcion || '';
            
            setTimeout(() => {
                selectEntrenador.value = grupo.id_entrenador || '';
            }, 100);
        }
    } else {
        btnGuardar.innerHTML = 'GUARDAR GRUPO <i class="fas fa-save ml-2"></i>';
    }
}

async function cargarTablaGrupos() {
    const tbody = document.getElementById('listaGrupos');
    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando grupos...</span></td></tr>`;

    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const grupos = await peticionAjax(`listarGrupos&estado=${filtroEstado}`);

    if (!grupos || grupos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500">
                    <i class="fas fa-layer-group text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay grupos registrados en este estado</span>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    grupos.forEach(g => {
        const entrenadorText = g.entrenador_nombre ? `${g.entrenador_nombre} <span class="text-[10px] text-gray-500">(${g.entrenador_cedula})</span>` : '<span class="text-xs text-gray-600 italic">Sin entrenador asignado</span>';
        const busqueda = `${g.nombre} ${g.descripcion} ${g.entrenador_nombre || ''}`.toLowerCase();
        
        let botonAccion = '';
        if (g.activo == 1) { 
            botonAccion = `
                <button onclick="eliminarGrupo(${g.id_grupo})" class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-500/10 transition duration-200" title="Archivar Grupo">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>
            `;
        } else {
            botonAccion = `
                <button onclick="reactivarGrupo(${g.id_grupo})" class="text-emerald-400 hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-500/10 transition duration-200" title="Reactivar Grupo">
                    <i class="fas fa-check-circle text-base"></i>
                </button>
            `;
        }

        html += `
            <tr class="grupo-row hover:bg-white/5 transition-colors duration-200" data-busqueda="${busqueda}">
                <td class="p-4 font-medium text-white">${g.nombre}</td>
                <td class="p-4 text-gray-300 text-xs max-w-xs truncate">${g.descripcion || '—'}</td>
                <td class="p-4 text-gray-300">${entrenadorText}</td>
                <td class="p-4">
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full ${g.activo == 1 ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'} uppercase tracking-wide">
                        ${g.activo == 1 ? 'Activo' : 'Archivado'}
                    </span>
                </td>
                <td class="p-4 text-right space-x-1">
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                    <button onclick="abrirModalGrupo(${g.id_grupo})" class="text-indigo-400 hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-500/10 transition duration-200" title="Editar Grupo">
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

const inputBusqueda = document.getElementById('busquedaNombre');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.grupo-row');
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda');
            fila.style.display = textoFila.includes(valor) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaGrupos();
    try { Validador.vincularTiempoReal(formGrupo); } catch (e) {}

    formGrupo.addEventListener('submit', async function (e) {
        e.preventDefault(); 
        const erroresJS = Validador.validarFormulario(formGrupo);
        
        if (erroresJS) {
            UI.advertencia('Datos Inválidos', erroresJS);
            return; 
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formGrupo);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalGrupo();
                cargarTablaGrupos();
            } else if (resultado.status === 'warning') {
                let msjErrores = Object.values(resultado.errores).join("<br>");
                UI.advertencia('Validación', msjErrores);
            } else {
                UI.error('Error', resultado.message);
            }
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});

async function eliminarGrupo(id_grupo) {
    if (confirm("¿Está seguro de archivar este grupo de entrenamiento?")) {
        let datosDelete = new FormData();
        datosDelete.append('accion', 'eliminar'); 
        datosDelete.append('id_grupo', id_grupo);

        const resultado = await peticionAjax('eliminar', datosDelete);
        if (resultado && resultado.status === 'success') {
            UI.exito('Archivado', 'El grupo ha sido desactivado.');
            cargarTablaGrupos();
        } else {
            UI.error('Error', 'No se pudo desactivar el registro.');
        }
    }
}

async function reactivarGrupo(id_grupo) {
    if (confirm("¿Desea reactivar este grupo de entrenamiento?")) {
        let datosReactivar = new FormData();
        datosReactivar.append('accion', 'reactivar');
        datosReactivar.append('id_grupo', id_grupo);

        const resultado = await peticionAjax('reactivar', datosReactivar);
        if (resultado && resultado.status === 'success') {
            UI.exito('Reactivado', 'El grupo vuelve a estar activo.');
            cargarTablaGrupos();
        } else {
            UI.error('Error', 'No se pudo reactivar el grupo.');
        }
    }
}