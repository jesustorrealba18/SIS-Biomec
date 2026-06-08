// assets/js/mantenimiento.js

const API_MANTENIMIENTO = 'index.php?p=mantenimiento';


/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_MANTENIMIENTO}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

const Mantenimiento = {

    /**
     * Inicia el proceso de creación del Backup
     */
    generarRespaldo: async () => {
        // Alerta de carga tipo "No cerrar navegador"
        Swal.fire({
            title: 'Generando Respaldo...',
            html: 'Empaquetando <b>sis_natacion</b> y <b>sis_seguridad</b>.<br>Por favor, no recargues la página.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // Se asume que el controlador responderá con un JSON que contiene la URL de descarga
            const respuesta = await fetch(`${API_MANTENIMIENTO}&accion=backup`, { method: 'POST' });
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Respaldo Exitoso',
                    text: 'Las bases de datos han sido respaldadas correctamente.',
                    confirmButtonColor: '#10B981'
                }).then(() => {
                    // Forzamos la descarga del archivo generado
                    window.location.href = resultado.url_descarga;
                    
                    // Opcional: Actualizar la fecha del "último respaldo" en la UI
                    document.getElementById('txtUltimoRespaldo').textContent = 'Hace un momento';
                });
            } else {
                throw new Error(resultado.message || 'Error desconocido al generar el archivo.');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Fallo en el Respaldo',
                text: error.message,
                confirmButtonColor: '#EF4444'
            });
        }
    },

    // =========================================================
    // LÓGICA DE INTERFAZ PARA ARRASTRAR Y SOLTAR (DROPZONE)
    // =========================================================
    
    initDropzone: () => {
        const zonaDrop = document.getElementById('zonaDrop');
        const inputArchivo = document.getElementById('archivoRespaldo');

        // Hacer clic en la zona abre el buscador de archivos
        zonaDrop.addEventListener('click', () => inputArchivo.click());

        // Eventos de arrastrar
        zonaDrop.addEventListener('dragover', (e) => {
            e.preventDefault();
            zonaDrop.classList.add('dragover');
        });

        zonaDrop.addEventListener('dragleave', (e) => {
            e.preventDefault();
            zonaDrop.classList.remove('dragover');
        });

        zonaDrop.addEventListener('drop', (e) => {
            e.preventDefault();
            zonaDrop.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                inputArchivo.files = e.dataTransfer.files;
                Mantenimiento.archivoSeleccionado(inputArchivo);
            }
        });
    },

    archivoSeleccionado: (input) => {
        const btnRestaurar = document.getElementById('btnRestaurar');
        const infoArchivo = document.getElementById('infoArchivo');
        const archivoCargado = document.getElementById('archivoCargado');
        const nombreArchivoTxt = document.getElementById('nombreArchivoTxt');

        if (input.files && input.files[0]) {
            const archivo = input.files[0];
            const extension = archivo.name.split('.').pop().toLowerCase();

            // Validar extensión
            if (extension !== 'sql' && extension !== 'zip') {
                UI.error('Archivo Inválido', 'Solo se permiten archivos .sql o .zip generados por el sistema.');
                Mantenimiento.limpiarArchivo();
                return;
            }

            // Actualizar Interfaz
            nombreArchivoTxt.textContent = archivo.name;
            infoArchivo.classList.add('hidden');
            archivoCargado.classList.remove('hidden');

            // Habilitar botón de restaurar
            btnRestaurar.classList.remove('bg-red-600/50', 'text-white/50', 'cursor-not-allowed');
            btnRestaurar.classList.add('bg-red-600', 'hover:bg-red-500', 'text-white', 'shadow-lg', 'shadow-red-500/20');
        }
    },

    limpiarArchivo: (evento = null) => {
        if(evento) evento.stopPropagation(); // Evita que se abra el buscador al hacer clic en "Cambiar archivo"
        
        const input = document.getElementById('archivoRespaldo');
        const btnRestaurar = document.getElementById('btnRestaurar');
        
        input.value = '';
        document.getElementById('infoArchivo').classList.remove('hidden');
        document.getElementById('archivoCargado').classList.add('hidden');

        // Deshabilitar botón
        btnRestaurar.classList.remove('bg-red-600', 'hover:bg-red-500', 'text-white', 'shadow-lg', 'shadow-red-500/20');
        btnRestaurar.classList.add('bg-red-600/50', 'text-white/50', 'cursor-not-allowed');
    },

    /**
     * Inicia el proceso crítico de Restauración
     */
    iniciarRestauracion: () => {
        const input = document.getElementById('archivoRespaldo');
        
        if (!input.files || input.files.length === 0) return;

        // Doble validación de seguridad (UX Profesional para acciones destructivas)
        Swal.fire({
            title: '¡ADVERTENCIA CRÍTICA!',
            icon: 'warning',
            html: 'Estás a punto de sobrescribir <b>TODA LA BASE DE DATOS</b>.<br>Los registros actuales se perderán si no están en este respaldo.<br><br>Escribe <b>CONFIRMAR</b> para continuar:',
            input: 'text',
            inputAttributes: { autocapitalize: 'off' },
            showCancelButton: true,
            confirmButtonText: 'Restaurar Ahora',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#4B5563',
            preConfirm: (texto) => {
                if (texto !== 'CONFIRMAR') {
                    Swal.showValidationMessage('Debes escribir CONFIRMAR (en mayúsculas) para proceder.');
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Mantenimiento.ejecutarRestauracion(input.files[0]);
            }
        });
    },

    ejecutarRestauracion: async (archivo) => {
        const formData = new FormData();
        formData.append('archivo_respaldo', archivo);

        Swal.fire({
            title: 'Restaurando Sistema...',
            html: 'Este proceso puede tardar varios minutos.<br><b>No apagues el equipo ni cierres esta ventana.</b>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const respuesta = await fetch(`${API_MANTENIMIENTO}&accion=restore`, {
                method: 'POST',
                body: formData
            });
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Restauración Completada',
                    text: 'El sistema ha sido restaurado exitosamente.',
                    confirmButtonColor: '#10B981'
                }).then(() => {
                    // Recargar la página o enviar al login si las sesiones cambiaron
                    window.location.reload();
                });
            } else {
                throw new Error(resultado.message || 'Ocurrió un error procesando el archivo SQL.');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de Restauración',
                text: error.message,
                confirmButtonColor: '#EF4444'
            });
            Mantenimiento.limpiarArchivo();
        }
    }
};

// Inicializar eventos al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    Mantenimiento.initDropzone();
});