// assets/js/asistencia.js

const API_ASISTENCIA = 'index.php?p=asistencia';
let html5QrCode; // Variable global para la cámara

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 
    try {
        const res = await fetch(`${API_ASISTENCIA}&accion=${accion}`, opciones);
        return await res.json();
    } catch (error) {
        UI.error('Error de Conexión', 'No se pudo comunicar con el servidor.');
        return null;
    }
}

const Asistencia = {
    idSesionActual: null,
    procesandoLectura: false,

    init: () => {
        // Evento al cambiar de sesión en el dropdown
        document.getElementById('selectSesion').addEventListener('change', (e) => {
            Asistencia.idSesionActual = e.target.value;
            if (Asistencia.idSesionActual) {
                Asistencia.cargarListaAtletas();
            } else {
                document.getElementById('tablaAtletas').innerHTML = '<tr><td colspan="4" class="py-8 text-center text-gray-600 italic">Seleccione una sesión.</td></tr>';
            }
        });

        // Evento para arrancar la cámara
        document.getElementById('btnActivarCamara').addEventListener('click', Asistencia.iniciarCamara);
    },

    iniciarCamara: () => {
        if (!Asistencia.idSesionActual) {
            UI.advertencia('Falta Información', 'Debe seleccionar una sesión de entrenamiento antes de activar el escáner.');
            return;
        }

        const btn = document.getElementById('btnActivarCamara');
        btn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Conectando...';
        btn.disabled = true;

        html5QrCode = new Html5Qrcode("visorCamara");

        html5QrCode.start(
            { facingMode: "environment" }, // Prioriza la cámara trasera en celulares
            { fps: 10, qrbox: { width: 250, height: 250 } },
            Asistencia.alDetectarQR,
            (error) => { /* Ignoramos los frames donde no hay QR */ }
        ).then(() => {
            btn.innerHTML = '<i class="fas fa-camera text-emerald-400"></i> Cámara Activa';
            document.getElementById('txtScanEstado').textContent = 'Apunte el QR del atleta a la cámara...';
        }).catch(err => {
            UI.error('Error de Hardware', 'No se pudo acceder a la cámara. Verifique los permisos del navegador.');
            btn.innerHTML = 'Reintentar Cámara';
            btn.disabled = false;
        });
    },

    /**
     * Se dispara automáticamente cuando la cámara lee un código válido
     */
    alDetectarQR: async (tokenCapturado) => {
        if (Asistencia.procesandoLectura) return; // Evita lecturas dobles muy rápidas
        Asistencia.procesandoLectura = true;

        // Feedback sonoro (Opcional si agregas los mp3, o visual)
        const txtEstado = document.getElementById('txtScanEstado');
        txtEstado.innerHTML = '<span class="text-indigo-400">Procesando token...</span>';

        const formData = new FormData();
        formData.append('id_sesion', Asistencia.idSesionActual);
        formData.append('token_qr', tokenCapturado);

        const respuesta = await peticionAjax('registrar_por_qr', formData);

        if (respuesta && respuesta.status === 'success') {
            document.getElementById('beepSuccess')?.play();
            // Actualizamos la tabla visualmente
            Asistencia.cargarListaAtletas();
            
            // Usamos Toast (alerta pequeña superior) en lugar del alert gigante para no interrumpir el flujo de escanear a 20 niños
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: `Asistencia: ${respuesta.nombre_atleta}`,
                showConfirmButton: false, timer: 2000, background: '#10b981', color: '#fff'
            });
        } else {
            document.getElementById('beepError')?.play();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: respuesta?.message || 'Token inválido',
                showConfirmButton: false, timer: 3000, background: '#ef4444', color: '#fff'
            });
        }

        // Liberamos el candado para la siguiente lectura después de 2 segundos
        setTimeout(() => { 
            Asistencia.procesandoLectura = false; 
            txtEstado.textContent = 'Apunte el QR del atleta a la cámara...';
        }, 2000);
    },

    // =================================================================
    // ACCIONES MANUALES (Ley de Murphy)
    // =================================================================

    accionManual: async (id_atleta, estado) => {
        if (!Asistencia.idSesionActual) return;

        let justificacion = 'Asistencia validada manualmente';

        // Si es Inasistencia o Permiso, OBLIGAMOS a que escriba algo
        if (estado === 'Falto' || estado === 'Permiso') {
            const result = await UI.pedirJustificacion(
                `Registrar: ${estado.toUpperCase()}`, 
                `Debe explicar el motivo de la ausencia/permiso para el expediente.`
            );
            
            if (!result.isConfirmed) return; // Canceló la acción
            justificacion = result.value;
        }

        const formData = new FormData();
        formData.append('id_sesion', Asistencia.idSesionActual);
        formData.append('id_atleta', id_atleta);
        formData.append('estado_asistencia', estado);
        formData.append('justificacion', justificacion);

        const respuesta = await peticionAjax('registrar_manual', formData);

        if (respuesta && respuesta.status === 'success') {
            UI.exito('Guardado', 'El estado fue actualizado correctamente.');
            Asistencia.cargarListaAtletas();
        }
    },

    cargarListaAtletas: async () => {
        // En un futuro cercano, esta función pedirá al controlador PHP que devuelva
        // los atletas de la sesión y pintará los <tr> correspondientes.
        console.log("Cargando atletas para la sesión: ", Asistencia.idSesionActual);
        // Aquí iría la inyección del HTML en el tbody 'tablaAtletas' y la actualización de los stats.
    }
};

document.addEventListener('DOMContentLoaded', Asistencia.init);