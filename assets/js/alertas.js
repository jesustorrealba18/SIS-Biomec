// alertas.js - Gestor centralizado de SweetAlert2

const UI = {
    // Configuración base (tus colores oscuros estandarizados)
    config: {
        background: '#161430',
        color: '#fff',
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#374151'
    },

    // Métodos simplificados
    exito: function(titulo, mensaje) {
        return Swal.fire({
            ...this.config,
            icon: 'success',
            title: titulo,
            text: mensaje
        });
    },

    error: function(titulo, mensaje) {
        return Swal.fire({
            ...this.config,
            icon: 'error',
            title: titulo,
            text: mensaje
        });
    },

    advertencia: function(titulo, mensaje) {
        return Swal.fire({
            ...this.config,
            icon: 'warning',
            title: titulo,
            text: mensaje
        });
    },

    // Ideal para el botón de eliminar
    confirmar: function(titulo, mensaje) {
        return Swal.fire({
            ...this.config,
            icon: 'warning',
            title: titulo,
            text: mensaje,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-2"></i>Sí, confirmar',
            cancelButtonText: 'Cancelar'
        });
    },

    // NUEVO MÉTODO CENTRALIZADO: Para Soft Delete con Auditoría
    pedirJustificacion: function(titulo, mensaje, placeholder = 'Escriba la justificación detallada aquí...') {
        return Swal.fire({
            ...this.config,
            title: titulo,
            text: mensaje,
            icon: 'warning',
            input: 'textarea', // El textarea da más espacio visual para redactar
            inputPlaceholder: placeholder,
            inputAttributes: {
                'aria-label': 'Justificación'
            },
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // Rojo para acciones destructivas
            cancelButtonColor: this.config.cancelButtonColor,
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