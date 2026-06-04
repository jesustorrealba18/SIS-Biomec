const modalDrills = document.getElementById('modalDrills');
const formDrills = document.getElementById('formDrills');
const btnGuardar = document.getElementById('btnGuardar');

const API_URL = 'index.php?p=drills'; 

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
        } else {
            alert('Error del Servidor: No se pudo procesar la solicitud.');
        }
        return null;
    }
}

function cerrarModalDrills() {
    modalDrills.classList.add('hidden');
    modalDrills.firstElementChild.classList.add('scale-95', 'opacity-0');
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalDrills.classList.contains('hidden')) {
        cerrarModalDrills();
    }
});

async function abrirModalDrills(id_drill = null) {
    formDrills.reset(); 
    try { Validador.limpiarEstilos(formDrills); } catch(e) {}
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_drill');
    const modalTitulo = document.getElementById('modalTitulo');

    if (id_drill) {
        if (inputAction) inputAction.value = 'actualizar';
        if (inputIdHidden) inputIdHidden.value = id_drill;
        if (modalTitulo) modalTitulo.textContent = 'Actualizar Datos del Entrenamiento';
        
        btnGuardar.innerHTML = 'Actualizar los datos <i class="fas fa-sync-alt ml-2"></i>';
        
        const drill = await peticionAjax(`obtenerDrills&id=${id_drill}`);
        
        if (drill) {
            document.getElementById('nombre').value = drill.nombre || '';
            document.getElementById('estilo').value = drill.estilo || 'Libre';
            document.getElementById('categoria').value = drill.categoria || 'Tecnico';
            document.getElementById('enfoque_tecnico').value = drill.enfoque_tecnico || '';
            document.getElementById('descripcion').value = drill.descripcion || '';
            document.getElementById('instrucciones').value = drill.instrucciones || '';
            document.getElementById('metraje_sugerido').value = drill.metraje_sugerido || '';
            document.getElementById('dificultad').value = drill.dificultad || 'Basico';
            document.getElementById('material_requerido').value = drill.material_requerido || 'Ninguno';
            
            // CORREGIDO: Los flags booleanos se controlan con la propiedad .checked
            document.getElementById('personalizado').checked = parseInt(drill.personalizado) === 1;
            document.getElementById('activo').checked = parseInt(drill.activo) === 1;
            
            if (document.getElementById('id_usuario_creador')) {
                document.getElementById('id_usuario_creador').value = drill.id_usuario_creador || '1';
            }
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Entrenamiento';
        document.getElementById('personalizado').checked = false;
        document.getElementById('activo').checked = true;
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalDrills.classList.remove('hidden');
    setTimeout(() => {
        modalDrills.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaDrills() {
    const tbody = document.getElementById('listaDrills');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="12" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const drills = await peticionAjax('listarDrills');

    if (!drills || drills.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center p-12 text-gray-500">
                    <i class="fas fa-dumbbell text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay entrenamientos registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = drills.map(ent => {
        const badgePersonalizado = parseInt(ent.personalizado) === 1 
            ? `<span class="px-2 py-1 text-[10px] font-bold bg-purple-500/10 text-purple-400 rounded-md border border-purple-500/20">SÍ</span>`
            : `<span class="px-2 py-1 text-[10px] font-bold bg-gray-500/10 text-gray-400 rounded-md">NO</span>`;

        const badgeActivo = parseInt(ent.activo) === 1 
            ? `<span class="px-2 py-1 text-[10px] font-bold bg-green-500/10 text-green-400 rounded-md border border-green-500/20">ACTIVO</span>`
            : `<span class="px-2 py-1 text-[10px] font-bold bg-red-500/10 text-red-400 rounded-md border border-red-500/20">INACTIVO</span>`;

        return `
            <tr class="drills-row border-b border-gray-800/50 hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${ent.nombre} ${ent.estilo} ${ent.categoria}">
                <td class="p-4 font-medium text-white max-w-[180px] truncate" title="${ent.nombre}">${ent.nombre}</td>
                <td class="p-4 text-gray-300 text-xs">${ent.estilo}</td>
                <td class="p-4 text-gray-400 text-xs">${ent.categoria}</td>
                <td class="p-4 text-gray-400 text-xs max-w-[120px] truncate" title="${ent.enfoque_tecnico}">${ent.enfoque_tecnico}</td>
                <td class="p-4 text-gray-500 text-xs max-w-[150px] truncate" title="${ent.descripcion}">${ent.descripcion}</td>
                <td class="p-4 text-gray-500 text-xs max-w-[150px] truncate" title="${ent.instrucciones}">${ent.instrucciones}</td>
                <td class="p-4 text-indigo-400 font-mono text-xs">${ent.metraje_sugerido}</td>
                <td class="p-4 text-gray-300 text-xs">${ent.dificultad}</td>
                <td class="p-4 text-gray-400 text-xs">${ent.material_requerido}</td>
                <td class="p-4 text-center">${badgePersonalizado}</td>
                <td class="p-4 text-center">${badgeActivo}</td>
                <td class="p-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="abrirModalDrills(${ent.id_drill})" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 p-2 rounded-lg transition" title="Editar">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="eliminarDrills(${ent.id_drill})" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-2 rounded-lg transition" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}
    
const inputBusqueda = document.getElementById('busquedaID');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.drills-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda') || '';
            fila.style.display = textoFila.toLowerCase().includes(valor) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formDrills); } catch(e){}
    cargarTablaDrills();

    formDrills.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        if (typeof Validador !== 'undefined' && typeof Validador.validarFormulario === 'function') {
            const erroresJS = Validador.validarFormulario(formDrills);
            if (erroresJS) {
                UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
                return; 
            }
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formDrills);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalDrills();
                cargarTablaDrills();
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

async function eliminarDrills(id_drill) {
    const confirmacion = confirm("¿Está seguro de eliminar este entrenamiento? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_drill', id_drill);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            cargarTablaDrills();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}