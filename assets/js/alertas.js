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
    }
};