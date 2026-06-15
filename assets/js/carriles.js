const modalCarril = document.getElementById('modalCarril');
const modalVer = document.getElementById('modalVerCarril'); 
const formCarril = document.getElementById('formCarril');
const btnGuardar = document.getElementById('btnGuardar');
const detalleContenido = document.getElementById('detalleContenido');
const totalCarriles = document.getElementById('totalCarriles');

const API_URL = 'index.php?p=carriles'; 

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

function cerrarModalCarril() {
    modalCarril.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalCarril.classList.add('hidden');
    }, 200);
}

function cerrarModalVer() {
    modalVer.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalVer.classList.add('hidden');
    }, 200);
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        if (!modalCarril.classList.contains('hidden')) cerrarModalCarril();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

async function abrirModalCarril(id_carril = null) {
    formCarril.reset(); 
    try { Validador.limpiarEstilos(formCarril); } catch(e) {}
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_carril');
    const modalTitulo = document.getElementById('modalTitulo');

    if (id_carril) {
        if (inputAction) inputAction.value = 'actualizar';
        if (inputIdHidden) inputIdHidden.value = id_carril;
        if (modalTitulo) modalTitulo.textContent = 'Actualizar Datos del Carril';
        
        btnGuardar.innerHTML = 'Actualizar datos <i class="fas fa-sync-alt ml-2"></i>';
        
        const carril = await peticionAjax(`obtenerCarriles&id=${id_carril}`);
        
        if (carril) {
            document.getElementById('numero').value = carril.numero;
            document.getElementById('capacidad_maxima').value = carril.capacidad_maxima;
            document.getElementById('activo').value = carril.activo;
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Carril';
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalCarril.classList.remove('hidden');
    setTimeout(() => {
        modalCarril.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function verDetalle(id) {
    const carril = await peticionAjax(`obtenerCarriles&id=${id}`);
    if (!carril) return;

    document.getElementById("verNumero").innerText = carril.numero;
    document.getElementById("verCapacidadMaxima").innerText = carril.capacidad_maxima;
    document.getElementById("verActivo").innerHTML = carril.activo == 1 
        ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs">Activo</span>' 
        : '<span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs">Inactivo</span>';

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaCarriles() {
    const tbody = document.getElementById('listaCarriles');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="4" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></tr>`;

    const carriles = await peticionAjax('listarCarriles');

    if (!carriles || carriles.length === 0) {
        if(totalCarriles) totalCarriles.textContent = '0 Registrados';
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-12 text-gray-500">
                    <i class="fas fa-road text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay carriles registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    if(totalCarriles) totalCarriles.textContent = `${carriles.length} Registrados`;

    tbody.innerHTML = carriles.map(carril => {
        const estadoHtml = carril.activo == 1
            ? '<span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs">Activo</span>'
            : '<span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs">Inactivo</span>';

        return `
        <tr class="carril-row border-b border-gray-800/50 hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${carril.numero} ${carril.capacidad_maxima}">
            <td class="p-4 font-medium text-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold">
                        ${carril.numero}
                    </div>
                    <span>Carril ${carril.numero}</span>
                </div>
            </td>
            <td class="p-4 text-gray-300">${carril.capacidad_maxima} nadadores</td>
            <td class="p-4">${estadoHtml}</td>
            <td class="p-4 text-right">
                ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                <div class="flex justify-end gap-2">
                    <button onclick="verDetalle(${carril.id_carril})" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 p-2 rounded-lg transition" title="Ver Detalle">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button onclick="abrirModalCarril(${carril.id_carril})" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 p-2 rounded-lg transition" title="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="eliminarCarril(${carril.id_carril})" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-2 rounded-lg transition" title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
                ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
            </td>
          </tr>
    `}).join('');
}
    
const inputBusqueda = document.getElementById('busquedaCarril');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.carril-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda') || '';
            fila.style.display = textoFila.toLowerCase().includes(valor) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formCarril); } catch(e){}
    cargarTablaCarriles();

    formCarril.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        const erroresJS = Validador.validarFormulario(formCarril);
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; 
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formCarril);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalCarril();
                cargarTablaCarriles();
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

async function eliminarCarril(id_carril) {
    const confirmacion = confirm("¿Está seguro de eliminar este carril? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_carril', id_carril);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            cargarTablaCarriles();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}