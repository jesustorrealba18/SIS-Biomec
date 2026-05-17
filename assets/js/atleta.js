// --- 1. LÓGICA DE BÚSQUEDA EN TIEMPO REAL ---
document.getElementById('busquedaCedula').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    const filas = document.querySelectorAll('.atleta-row');

    filas.forEach(fila => {
        const cedula = fila.getAttribute('data-cedula').toLowerCase();
        // Muestra la fila si la cédula coincide con la búsqueda
        fila.style.display = cedula.includes(valor) ? '' : 'none';
    });
});

// --- 2. GESTIÓN DE ALERTAS (SWEETALERT2) Y LIMPIEZA DE URL ---
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('m');

    if (msg) {
        const configDefault = {
            background: '#161430',
            color: '#fff',
            confirmButtonColor: '#6366f1',
            timer: 3000,
            timerProgressBar: true
        };

        if (msg === "registrado") {
            Swal.fire({ ...configDefault, title: '¡Excelente!', text: 'El atleta ha sido registrado con éxito.', icon: 'success' });
        } else if (msg === "editado") {
            Swal.fire({ ...configDefault, title: '¡Actualizado!', text: 'Los datos se modificaron correctamente.', icon: 'success' });
        } else if (msg === "eliminado") {
            Swal.fire({ ...configDefault, title: '¡Eliminado!', text: 'El registro ha sido removido del sistema.', icon: 'info' });
        }

        const urlLimpia = window.location.origin + window.location.pathname + "?p=atleta";
        window.history.replaceState({}, document.title, urlLimpia);
    }

    // --- ERRORES DE VALIDACIÓN DEL SERVIDOR ---
    if (typeof ERRORES_VALIDACION !== 'undefined' && Object.keys(ERRORES_VALIDACION).length > 0) {
        const lista = Object.values(ERRORES_VALIDACION).map(e => `<li class="mb-1">${e}</li>`).join('');
        Swal.fire({
            title: 'Errores de validación',
            html: `<ul class="text-left text-sm list-disc list-inside">${lista}</ul>`,
            icon: 'error',
            background: '#161430',
            color: '#fff',
            confirmButtonColor: '#ef4444'
        });

        if (typeof DATOS_FORM !== 'undefined') {
            const d = DATOS_FORM;
            const esEdicion = d.id_atleta && d.id_atleta !== '';
            document.getElementById('modalTitulo').innerText = esEdicion ? 'Editar Atleta' : 'Nuevo Atleta';

            if (esEdicion) document.getElementById('id_atleta').value = d.id_atleta;
            if (d.cedula)              document.getElementById('cedula').value = d.cedula;
            if (d.nombres)             document.getElementById('nombres').value = d.nombres;
            if (d.apellidos)           document.getElementById('apellidos').value = d.apellidos;
            if (d.fecha_nacimiento)    document.getElementById('fecha_nacimiento').value = d.fecha_nacimiento;
            if (d.genero)              document.getElementById('genero').value = d.genero;
            if (d.fichaje_federativo)  document.getElementById('fichaje_federativo').value = d.fichaje_federativo;

            if (d.lateralidad) {
                const radio = document.querySelector(`input[name="lateralidad"][value="${d.lateralidad}"]`);
                if (radio) radio.checked = true;
            }

            modalForm.classList.remove('hidden');
        }
    }
});

// --- 3. LÓGICA DE MODALES (FORMULARIO Y DETALLES) ---
const modalForm = document.getElementById('modalAtleta');
const modalVer = document.getElementById('modalVer');

function abrirModalCrear() {
    const form = document.getElementById('formAtleta');
    form.reset();
    document.getElementById('id_atleta').value = "";
    document.getElementById('modalTitulo').innerText = "Nuevo Atleta";
    
    // Limpiar radios de lateralidad
    document.querySelectorAll('input[name="lateralidad"]').forEach(r => r.checked = false);
    
    modalForm.classList.remove('hidden');
}

function editarAtleta(datos) {
    document.getElementById('id_atleta').value = datos.id_atleta;
    document.getElementById('cedula').value = datos.cedula;
    document.getElementById('nombres').value = datos.nombres;
    document.getElementById('apellidos').value = datos.apellidos;
    document.getElementById('fecha_nacimiento').value = datos.fecha_nacimiento;
    document.getElementById('genero').value = datos.genero;
    document.getElementById('fichaje_federativo').value = datos.fichaje_federativo;
    
    // Seleccionar el radio de lateralidad correspondiente
    const radio = document.querySelector(`input[name="lateralidad"][value="${datos.lateralidad}"]`);
    if(radio) radio.checked = true;

    document.getElementById('modalTitulo').innerText = "Editar Atleta";
    modalForm.classList.remove('hidden');
}

function verDetalles(datos) {
    const html = `
        <div>
            <img src="https://ui-avatars.com/api/?name=${datos.nombres}+${datos.apellidos}&size=128&background=4f46e5&color=fff" 
                 class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-indigo-500/20 shadow-xl">
            <h2 class="text-2xl font-bold text-white">${datos.nombres} ${datos.apellidos}</h2>
            <p class="text-indigo-400 mb-6 font-mono tracking-widest">${datos.cedula}</p>
            
            <div class="grid grid-cols-2 gap-4 text-left bg-black/20 p-5 rounded-2xl border border-white/5">
                <div>
                    <p class="text-[10px] uppercase text-gray-500 font-bold">Edad</p>
                    <p class="text-white">${datos.edad} años</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase text-gray-500 font-bold">Género</p>
                    <p class="text-white">${datos.genero === 'M' ? 'Masculino' : 'Femenino'}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase text-gray-500 font-bold">Lateralidad</p>
                    <p class="text-white">${datos.lateralidad || 'No definido'}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase text-gray-500 font-bold">Fichaje</p>
                    <p class="text-indigo-300 font-mono">${datos.fichaje_federativo || 'S/F'}</p>
                </div>
            </div>
        </div>
    `;
    document.getElementById('detalleContenido').innerHTML = html;
    modalVer.classList.remove('hidden');
}

// --- 4. CONFIRMACIÓN DE ELIMINACIÓN ---
function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará permanentemente al atleta del sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // Rojo
        cancelButtonColor: '#374151',  // Gris oscuro
        confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#161430',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirige al controlador con el ID y el mensaje de eliminación
            window.location.href = `?p=atleta&eliminar=${id}&m=eliminado`;
        }
    });
}

// --- 5. FUNCIONES DE CIERRE ---
function cerrarModal() { 
    modalForm.classList.add('hidden'); 
}

function cerrarModalVer() { 
    modalVer.classList.add('hidden'); 
}

// Cerrar modales al presionar la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        cerrarModal();
        cerrarModalVer();
    }
});