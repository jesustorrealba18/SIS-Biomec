// assets/js/validador.js

class Validador {
    /**
     * Revisa todos los inputs de un formulario buscando reglas de validación
     * @param {HTMLFormElement} formulario 
     * @returns {string|boolean} - Retorna un string con errores en HTML o false si todo está OK
     */
    static validarFormulario(formulario) {
        let errores = [];
        const inputs = formulario.querySelectorAll('[data-validar]');

        inputs.forEach(input => {
            const reglas = input.getAttribute('data-validar').split('|');
            const valor = input.value.trim();
            const nombreCampo = input.getAttribute('data-nombre') || input.name;

            // 1. Regla: Requerido
            if (reglas.includes('requerido') && valor === '') {
                errores.push(`- El campo <b>${nombreCampo}</b> es obligatorio.`);
                return;
            }

            if (valor !== '') {
                // 2. Regla: Solo Letras
                if (reglas.includes('letras') && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> solo acepta letras.`);
                }

                // 3. Regla: Solo Números
                if (reglas.includes('numeros') && !/^[0-9]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> solo acepta números enteros.`);
                }

                // 4. Regla: Longitud Mínima (data-min)
                if (input.hasAttribute('data-min')) {
                    let min = parseInt(input.getAttribute('data-min'));
                    if (valor.length < min) {
                        errores.push(`- <b>${nombreCampo}</b> debe tener al menos ${min} caracteres.`);
                    }
                }

                // 5. Regla: Longitud Máxima (data-max)
                if (input.hasAttribute('data-max')) {
                    let max = parseInt(input.getAttribute('data-max'));
                    if (valor.length > max) {
                        errores.push(`- <b>${nombreCampo}</b> no debe superar ${max} caracteres.`);
                    }
                }

                // 6. Regla: Cédula venezolana (V-12345678 / E-12345678)
                if (reglas.includes('cedula') && !/^[VEve]-\d{7,8}$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> debe tener formato V-12345678 o E-12345678.`);
                }

                // 7. Regla: Correo electrónico
                if (reglas.includes('correo') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> no es un correo válido.`);
                }

                // 8. Regla: Teléfono
                if (reglas.includes('telefono') && !/^[\d\-\+\(\)\s]{7,20}$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> no es un teléfono válido.`);
                }

                // 9. Regla: Texto general (letras, números, puntuación básica)
                if (reglas.includes('texto') && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s.,;:()\-\#\/]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> contiene caracteres no permitidos.`);
                }
            }
        });

        return errores.length > 0 ? errores.join("<br>") : false;
    }

    /**
     * Valida un solo campo y aplica feedback visual (borde verde/rojo)
     * @param {HTMLElement} campo
     * @returns {boolean}
     */
    static validarCampo(campo) {
        const reglas = campo.getAttribute('data-validar').split('|');
        const valor = campo.value.trim();
        let valido = true;

        campo.style.borderColor = '';
        campo.title = '';

        if (valor === '') {
            if (reglas.includes('requerido')) {
                campo.style.borderColor = '#f87171';
                return false;
            }
            return true;
        }

        if (reglas.includes('cedula')   && !/^[VEve]-\d{7,8}$/.test(valor))                    valido = false;
        if (reglas.includes('letras')    && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor))           valido = false;
        if (reglas.includes('numeros')   && !/^[0-9]+$/.test(valor))                              valido = false;
        if (reglas.includes('correo')    && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor))            valido = false;
        if (reglas.includes('telefono')  && !/^[\d\-\+\(\)\s]{7,20}$/.test(valor))                 valido = false;
        if (reglas.includes('texto')     && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s.,;:()\-\#\/]+$/.test(valor)) valido = false;

        if (campo.hasAttribute('data-min')) {
            if (valor.length < parseInt(campo.getAttribute('data-min'))) valido = false;
        }
        if (campo.hasAttribute('data-max')) {
            if (valor.length > parseInt(campo.getAttribute('data-max'))) valido = false;
        }

        campo.style.borderColor = valido ? '#34d399' : '#f87171';
        return valido;
    }

    /**
     * Vincula validaciones en tiempo real (keydown filtra, input valida)
     * @param {HTMLFormElement} formulario
     */
    static vincularTiempoReal(formulario) {
        const campos = formulario.querySelectorAll('[data-validar]');

        campos.forEach(campo => {
            const reglas = campo.getAttribute('data-validar').split('|');
            if (campo.tagName === 'SELECT' || campo.type === 'date' || campo.type === 'file') return;

            // keydown: bloquear caracteres no permitidos antes de que se inserten
            campo.addEventListener('keydown', function(e) {
                if (e.key.length !== 1) return;
                if (reglas.includes('letras') && /[0-9]/.test(e.key))              e.preventDefault();
                if (reglas.includes('numeros') && /[A-Za-zÁÉÍÓÚáéíóúñÑ]/.test(e.key)) e.preventDefault();
                if (reglas.includes('cedula') && !/[VEve\-0-9]/.test(e.key))     e.preventDefault();
            });

            // input: auto-formato cédula + feedback visual
            campo.addEventListener('input', function() {
                if (reglas.includes('cedula')) {
                    let val = this.value;
                    if (/^[VEve]$/.test(val)) {
                        this.value = val.toUpperCase() + '-';
                    } else if (/^[VEve]-/.test(val)) {
                        let prefijo = val.substring(0, 2).toUpperCase();
                        let digitos = val.substring(2).replace(/[^0-9]/g, '').substring(0, 8);
                        this.value = prefijo + digitos;
                    }
                }
                Validador.validarCampo(campo);
            });
        });
    }

    /**
     * Limpia los estilos de validación visual de todos los campos
     * @param {HTMLFormElement} formulario
     */
    static limpiarEstilos(formulario) {
        formulario.querySelectorAll('[data-validar]').forEach(c => {
            c.style.borderColor = '';
            c.title = '';
        });
    }


    
}
