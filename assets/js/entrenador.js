document.getElementById('busquedaEntrenador').addEventListener('input', function(e) {
    const valor = e.target.value.toLowerCase();
    const filas = document.querySelectorAll('.entrenador-row');
    filas.forEach(fila => {
        const busqueda = fila.getAttribute('data-busqueda').toLowerCase();
        fila.style.display = busqueda.includes(valor) ? '' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formEntrenador');
    if (form) {
        Validador.vincularTiempoReal(form);

        form.addEventListener('submit', function(e) {
            Validador.limpiarEstilos(form);
            const errores = Validador.validarFormulario(form);
            if (errores) {
                e.preventDefault();
                Swal.fire({
                    title: 'Datos inválidos',
                    html: errores,
                    icon: 'error',
                    background: '#161430',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('m');

    if (msg) {
        const cfg = {
            background: '#161430',
            color: '#fff',
            confirmButtonColor: '#6366f1',
            timer: 3000,
            timerProgressBar: true
        };

        const mensajes = {
            registrado: { title: '¡Excelente!', text: 'El entrenador ha sido registrado con éxito.', icon: 'success' },
            editado:    { title: '¡Actualizado!', text: 'Los datos se modificaron correctamente.', icon: 'success' },
            eliminado:  { title: '¡Desactivado!', text: 'El entrenador ha sido eliminado.', icon: 'info' }
        };

        if (mensajes[msg]) {
            Swal.fire({ ...cfg, ...mensajes[msg] });
        }

        window.history.replaceState({}, document.title, window.location.origin + window.location.pathname + "?p=atleta");
    }

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
            const esEdicion = d.id_entrenador && d.id_entrenador !== '';
            document.getElementById('modalTitulo').innerText = esEdicion ? 'Editar Entrenador' : 'Nuevo Entrenador';
            poblarFormulario(d, esEdicion);
            if (d.foto) {
                document.getElementById('fotoPreview').innerHTML = `<img src="${d.foto}" class="w-full h-full object-cover">`;
                document.getElementById('foto_actual').value = d.foto;
            }
            document.getElementById('modalEntrenador').classList.remove('hidden');
        }
    }

    const fotoInput = document.getElementById('foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('fotoPreview').innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

const modalForm = document.getElementById('modalEntrenador');
const modalVer = document.getElementById('modalVer');

function cambiarTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');
}

function abrirModalCrear() {
    document.getElementById('formEntrenador').reset();
    document.getElementById('id_entrenador').value = '';
    document.getElementById('foto_actual').value = '';
    document.getElementById('fotoPreview').innerHTML = '<i class="fas fa-camera text-gray-600 text-lg"></i>';
    document.getElementById('modalTitulo').innerText = 'Nuevo Entrenador';
    Validador.limpiarEstilos(document.getElementById('formEntrenador'));
    cambiarTab('personal');
    modalForm.classList.remove('hidden');
}

function poblarFormulario(d, esEdicion) {
    if (esEdicion) document.getElementById('id_entrenador').value = d.id_entrenador;
    if (d.cedula)                           document.getElementById('cedula').value = d.cedula;
    if (d.nombres)                          document.getElementById('nombres').value = d.nombres;
    if (d.apellidos)                        document.getElementById('apellidos').value = d.apellidos;
    if (d.fecha_nacimiento)                 document.getElementById('fecha_nacimiento').value = d.fecha_nacimiento;
    if (d.genero)                           document.getElementById('genero').value = d.genero;
    if (d.estado)                           document.getElementById('estado').value = d.estado;
    if (d.direccion)                        document.getElementById('direccion').value = d.direccion;
    if (d.telefono)                         document.getElementById('telefono').value = d.telefono;
    if (d.correo)                           document.getElementById('correo').value = d.correo;
}

function editarEntrenador(datos) {
    poblarFormulario(datos, true);

    if (datos.foto) {
        document.getElementById('fotoPreview').innerHTML = `<img src="${datos.foto}" class="w-full h-full object-cover">`;
        document.getElementById('foto_actual').value = datos.foto;
    } else {
        document.getElementById('fotoPreview').innerHTML = '<i class="fas fa-camera text-gray-600 text-lg"></i>';
    }

    document.getElementById('modalTitulo').innerText = 'Editar Entrenador';
    cambiarTab('personal');
    modalForm.classList.remove('hidden');
}

function verDetalles(datos) {
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
                <div><p class="text-[10px] uppercase text-gray-500">Edad</p><p class="text-white">${datos.edad} años</p></div>
                <div><p class="text-[10px] uppercase text-gray-500">Genero</p><p class="text-white">${datos.genero === 'M' ? 'Masculino' : 'Femenino'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500">Teléfono</p><p class="text-white">${datos.telefono || '—'}</p></div>
                <div><p class="text-[10px] uppercase text-gray-500">Correo</p><p class="text-white text-xs">${datos.correo || '—'}</p></div>
            </div>
        </div>
    `;
    document.getElementById('detalleContenido').innerHTML = html;
    modalVer.classList.remove('hidden');
}

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Desea eliminar a el entrenador?',
        text: "El entrenador ha sido eliminado correctamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: '<i class="fas fa-user-slash mr-2"></i>Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#161430',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?p=entrenador&eliminar=${id}&m=eliminado`;
        }
    });
}

function cerrarModal() { 
    modalForm.classList.add('hidden'); 
    document.getElementById('formEntrenador').reset();
    Validador.limpiarEstilos(document.getElementById('formEntrenador'));
}

function cerrarModalVer() { 
    modalVer.classList.add('hidden'); 
}

document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        cerrarModal();
        cerrarModalVer();
    }
});
