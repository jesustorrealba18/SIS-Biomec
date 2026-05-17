
// Lógica para abrir/cerrar modal (Mismo estilo que tus compañeros)[cite: 19]
const modalRep = document.getElementById('modalRepresentante');
const formRep = document.getElementById('formRepresentante');

function abrirModalRepresentante() {
    formRep.reset(); // Limpia los campos
    modalRep.classList.remove('hidden');
    // Animación suave
    setTimeout(() => {
        modalRep.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function cerrarModalRepresentante() {
    modalRep.classList.add('hidden');
    modalRep.firstElementChild.classList.add('scale-95', 'opacity-0');
}

// Cerramos modal con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") cerrarModalRepresentante();
});

// Evento Principal de Guardado
document.addEventListener('DOMContentLoaded', () => {
    
    formRep.addEventListener('submit', async function (e) {
        e.preventDefault(); // Evitamos recarga de página (¡Chao pantallas blancas!)

        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Procesando Transacción... <i class="fas fa-spinner fa-spin ml-2"></i>';

        // 1. Recolectamos los Atletas seleccionados (Los checkboxes)
        const checkboxes = document.querySelectorAll('input[name="atletas[]"]:checked');
        const atletasAsignados = Array.from(checkboxes).map(cb => parseInt(cb.value));

        // 2. Empaquetamos todo en el JSON
        const datos = {
            cedula: document.getElementById('cedula').value.trim(),
            nombres: document.getElementById('nombres').value.trim(),
            apellidos: document.getElementById('apellidos').value.trim(),
            telefonoP: document.getElementById('telefono_principal').value.trim(),
            telefonoE: document.getElementById('telefono_emergencia').value.trim(),
            email: document.getElementById('correo').value.trim(),
            direccion: document.getElementById('direccion_residencia').value.trim(),
            parentesco: document.getElementById('parentesco').value.trim(),
            atletas_ids: atletasAsignados // <-- Aquí va el array de hijos [1, 2]
        };

        try {
            // 3. Enviamos al API REST
            const respuesta = await fetch('api.php?c=Representante&accion=registrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            // 4. Evaluamos y usamos nuestra clase UI de alertas
            if (respuesta.status === 201) {
                UI.exito('Transacción Exitosa', resultado.message);
                cerrarModalRepresentante();
                // Aquí podrías agregar una función cargarTabla() para refrescar
            } else {
                UI.advertencia('Validación', resultado.message);
            }

        } catch (error) {
            UI.error('Error del Servidor', 'No se pudo completar la transacción.');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'GUARDAR Y VINCULAR';
        }
    });
});