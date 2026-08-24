// alertas.js - Gestor centralizado de SweetAlert2
const UI = {
    // ============================================================
    //  CONFIGURACIÓN DINÁMICA SEGÚN TEMA
    // ============================================================
    obtenerConfig: function() {
        const esOscuro = document.documentElement.classList.contains('dark');
        return {
            background: esOscuro ? '#161430' : '#ffffff',
            color: esOscuro ? '#ffffff' : '#1f2937',
            confirmButtonColor: esOscuro ? '#6366f1' : '#4f46e5',
            cancelButtonColor: esOscuro ? '#374151' : '#9ca3af'
        };
    },

    // ============================================================
    //  MÉTODOS DE ALERTA
    // ============================================================
    exito: function(titulo, mensaje) {
        return Swal.fire({
            ...this.obtenerConfig(),
            icon: 'success',
            title: titulo,
            text: mensaje
        });
    },

    error: function(titulo, mensaje) {
        return Swal.fire({
            ...this.obtenerConfig(),
            icon: 'error',
            title: titulo,
            html: mensaje
        });
    },

    advertencia: function(titulo, mensaje) {
        return Swal.fire({
            ...this.obtenerConfig(),
            icon: 'warning',
            title: titulo,
            html: mensaje
        });
    },

    confirmar: function(titulo, mensaje) {
        return Swal.fire({
            ...this.obtenerConfig(),
            icon: 'warning',
            title: titulo,
            html: mensaje,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Sí, confirmar',
            cancelButtonText: 'Cancelar'
        });
    },

    pedirJustificacion: function(titulo, mensaje, placeholder = 'Escriba la justificación detallada aquí...') {
        return Swal.fire({
            ...this.obtenerConfig(),
            title: titulo,
            text: mensaje,
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: placeholder,
            inputAttributes: {
                'aria-label': 'Justificación'
            },
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // Rojo fijo para acciones destructivas
            cancelButtonColor: this.obtenerConfig().cancelButtonColor,
            confirmButtonText: '<i class="fas fa-archive mr-2"></i> Archivar Registro',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value || value.trim().length < 5) {
                    return 'Debe ingresar un motivo válido (mínimo 5 caracteres)';
                }
            }
        });
    }
};