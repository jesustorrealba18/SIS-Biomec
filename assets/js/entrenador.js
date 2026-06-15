const modalEntrenador = document.getElementById('modalEntrenador');
const modalVer = document.getElementById('modalVerEntrenador'); 
const formEntrenador = document.getElementById('formEntrenador');
const btnGuardar = document.getElementById('btnGuardar');
const detalleContenido = document.getElementById('detalleContenido');
const inputFoto = document.getElementById('foto');
const fotoPreview = document.getElementById('previsualizarFoto'); //
const iconoFotoDefecto = document.getElementById('iconoFotoPorDefecto'); 
const totalEntrenador = document.getElementById('totalEntrenador');

const API_URL = 'index.php?p=entrenador'; 

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

function cerrarModalEntrenador() {
    modalEntrenador.firstElementChild.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalEntrenador.classList.add('hidden');
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
        if (!modalEntrenador.classList.contains('hidden')) cerrarModalEntrenador();
        if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

if (inputFoto) {
    inputFoto.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (fotoPreview) {
                    fotoPreview.src = ev.target.result;
                    fotoPreview.classList.remove('hidden');
                }
                if (iconoFotoDefecto) iconoFotoDefecto.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

async function abrirModalEntrenador(id_entrenador = null) {
    formEntrenador.reset(); 
    try { Validador.limpiarEstilos(formEntrenador); } catch(e) {}
    
    const inputAction = document.getElementById('action_type');
    const inputIdHidden = document.getElementById('id_entrenador');
    const modalTitulo = document.getElementById('modalTitulo');
    
    if (fotoPreview) {
        fotoPreview.src = '';
        fotoPreview.classList.add('hidden');
    }
    if (iconoFotoDefecto) iconoFotoDefecto.classList.remove('hidden');

    if (id_entrenador) {
        if (inputAction) inputAction.value = 'actualizar';
        if (inputIdHidden) inputIdHidden.value = id_entrenador;
        if (modalTitulo) modalTitulo.textContent = 'Actualizar Datos del Entrenador';
        
        btnGuardar.innerHTML = 'Actualizar los datos <i class="fas fa-sync-alt ml-2"></i>';
        
        const entrenador = await peticionAjax(`obtenerEntrenador&id=${id_entrenador}`);
        
        if (entrenador) {
            document.getElementById('cedula').value = entrenador.cedula;
            document.getElementById('nombres').value = entrenador.nombres;
            document.getElementById('apellidos').value = entrenador.apellidos;
            document.getElementById('fecha_nacimiento').value = entrenador.fecha_nacimiento;
            document.getElementById('genero').value = entrenador.genero;
            document.getElementById('correo').value = entrenador.correo;
            document.getElementById('telefono').value = entrenador.telefono;
            document.getElementById('direccion').value = entrenador.direccion;
            
            if (entrenador.foto && fotoPreview) {
                fotoPreview.src = entrenador.foto;
                fotoPreview.classList.remove('hidden');
                if (iconoFotoDefecto) iconoFotoDefecto.classList.add('hidden');
            }
        }
    } else {
        if (inputAction) inputAction.value = 'registrar';
        if (inputIdHidden) inputIdHidden.value = '';
        if (modalTitulo) modalTitulo.textContent = 'Registrar Entrenador';
        btnGuardar.innerHTML = 'GUARDAR <i class="fas fa-save ml-2"></i>';
    }

    modalEntrenador.classList.remove('hidden');
    setTimeout(() => {
        modalEntrenador.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function verDetalle(id) {
    const entrenador = await peticionAjax(`obtenerEntrenador&id=${id}`);
    if (!entrenador) return;

    document.getElementById("verNombreCompleto").innerText = `${entrenador.nombres} ${entrenador.apellidos}`;
    document.getElementById("verCedula").innerText = entrenador.cedula;
    document.getElementById("verGenero").innerText = entrenador.genero === 'M' ? 'Masculino' : 'Femenino';
    document.getElementById("verTelefono").innerText = entrenador.telefono;
    document.getElementById("verFechaNac").innerText = entrenador.fecha_nacimiento || '—';
    document.getElementById("verCorreo").innerText = entrenador.correo || '—';
    document.getElementById("verDireccion").innerText = entrenador.direccion || '—';

    const imgVer = document.getElementById("verFoto");
    const iconoVerDefecto = document.getElementById("verIconoPorDefecto");

    if (entrenador.foto && imgVer) {
        imgVer.src = entrenador.foto;
        imgVer.classList.remove("hidden");
        if (iconoVerDefecto) iconoVerDefecto.classList.add("hidden");
    } else if (imgVer) {
        imgVer.src = "";
        imgVer.classList.add("hidden");
        if (iconoVerDefecto) iconoVerDefecto.classList.remove("hidden");
    }

    modalVer.classList.remove('hidden');
    setTimeout(() => {
        modalVer.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

async function cargarTablaEntrenador() {
    const tbody = document.getElementById('listaEntrenador');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-12 text-gray-500"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span></td></tr>`;

    const entrenadores = await peticionAjax('listarEntrenador');

    if (!entrenadores || entrenadores.length === 0) {
        if(totalEntrenador) totalEntrenador.textContent = '0 Registrados';
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-12 text-gray-500">
                    <i class="fas fa-users-slash text-4xl mb-3 block text-gray-600 animate-pulse"></i>
                    <span class="text-xs uppercase tracking-wider block">No hay entrenadores registrados en el sistema</span>
                </td>
            </tr>
        `;
        return;
    }

    if(totalEntrenador) totalEntrenador.textContent = `${entrenadores.length} Registrados`;

    tbody.innerHTML = entrenadores.map(ent => {
        const InicialesHtml = ent.foto 
            ? `<img src="${ent.foto}" class="w-8 h-8 rounded-full object-cover">`
            : `<div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold uppercase">${ent.nombres[0]}${ent.apellidos[0]}</div>`;

        return `
        <tr class="entrenador-row border-b border-gray-800/50 hover:bg-[#1c1a3a]/40 transition-colors duration-200" data-busqueda="${ent.cedula} ${ent.nombres} ${ent.apellidos}">
            <td class="p-4 font-medium text-white flex items-center gap-3">
                ${InicialesHtml}
                <div>
                    <span class="block">${ent.nombres} ${ent.apellidos}</span>
                    <span class="text-xs text-gray-500">${ent.correo || ''}</span>
                </div>
            </td>
            <td class="p-4 text-gray-300 font-mono text-xs">${ent.cedula}</td>
            <td class="p-4 text-gray-400">${ent.telefono}</td>
            <td class="p-4 text-gray-400 max-w-xs truncate">${ent.direccion}</td>
            <td class="p-4 text-right">
                ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.gestionar ? `
                <div class="flex justify-end gap-2">
                    <button onclick="verDetalle(${ent.id_entrenador})" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 p-2 rounded-lg transition" title="Ver Perfil">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button onclick="abrirModalEntrenador(${ent.id_entrenador})" class="bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 p-2 rounded-lg transition" title="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="eliminarEntrenador(${ent.id_entrenador})" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 p-2 rounded-lg transition" title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
                ` : '<span class="text-gray-600 text-xs">Solo lectura</span>'}
            </td>
        </tr>
    `}).join('');
}
    
const inputBusqueda = document.getElementById('busquedaCedula');
if (inputBusqueda) {
    inputBusqueda.addEventListener('input', function(e) {
        const valor = e.target.value.toLowerCase().trim();
        const filas = document.querySelectorAll('.entrenador-row');
        
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-busqueda') || '';
            fila.style.display = textoFila.toLowerCase().includes(valor) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    try { Validador.vincularTiempoReal(formEntrenador); } catch(e){}
    cargarTablaEntrenador();

    formEntrenador.addEventListener('submit', async function (e) {
        e.preventDefault(); 

        const erroresJS = Validador.validarFormulario(formEntrenador);
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; 
        }

        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';

        const datosForm = new FormData(formEntrenador);
        const resultado = await peticionAjax('guardar', datosForm);

        if (resultado) {
            if (resultado.status === 'success') {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalEntrenador();
                cargarTablaEntrenador();
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

async function eliminarEntrenador(id_entrenador) {
    const confirmacion = confirm("¿Está seguro de eliminar este entrenador? Esta acción no se puede deshacer.");
    
    if (confirmacion) {
        let datosDelete = new FormData();
        datosDelete.append('id_entrenador', id_entrenador);
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Eliminado', 'El registro ha sido removido exitosamente.');
            cargarTablaEntrenador();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo eliminar el registro.');
        }
    }
}