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

               if (reglas.includes('tiempo') && !/^\d{1,2}:\d{2}\.\d{2}$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> debe tener el formato exacto MM:SS.cc (Ejemplo: 01:25.50).`);
                }

                // Validación estricta de Tiempo (MM:SS.cc)
                if (reglas.includes('tiempo') && !/^\d{1,2}:\d{2}\.\d{2}$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> requiere el formato cronométrico MM:SS.cc (ej: 01:25.50).`);
                }
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

                if (reglas.includes('fecha_logica') && valor !== '') {
                    // Obtenemos la fecha local actual en formato YYYY-MM-DD
                    const hoy = new Date();
                    const año = hoy.getFullYear();
                    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                    const dia = String(hoy.getDate()).padStart(2, '0');
                    
                    const strHoy = `${año}-${mes}-${dia}`;
                    const strMinimo = `${año - 120}-${mes}-${dia}`;

                    // Al estar en formato ISO (YYYY-MM-DD), JavaScript permite compararlas usando < o >
                    if (valor > strHoy) {
                        errores.push(`- <b>${nombreCampo}</b> no puede ser una fecha en el futuro.`);
                    } else if (valor < strMinimo) {
                        errores.push(`- <b>${nombreCampo}</b> indica una fecha demasiado antigua (más de 120 años).`);
                    }
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

    /**
     * Evalúa un campo individual y le da color de éxito (verde) o error (rojo)
     */
    static validarCampo(campo) {
        const reglas = campo.getAttribute('data-validar').split('|');
        const valor = campo.value.trim();
        let valido = true;

        // Limpiamos los estilos previos
        campo.style.borderColor = '';
        campo.title = '';

        if (valor === '') {
            if (reglas.includes('requerido')) {
                campo.style.borderColor = '#f87171'; // Rojo
                return false;
            }
            return true; // Si está vacío pero no es requerido, está OK
        }

        // ==========================================
        // REGLAS DE EXPRESIONES REGULARES
        // ==========================================
        if (reglas.includes('cedula')   && !/^[VEve]-\d{7,8}$/.test(valor))                    valido = false;
        if (reglas.includes('letras')   && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor))           valido = false;
        if (reglas.includes('numeros')  && !/^[0-9]+$/.test(valor))                            valido = false;
        
        // ---> TUS NUEVAS REGLAS DE CRONOMETRAJE <---
        if (reglas.includes('decimal')  && !/^\d+(\.\d{1,2})?$/.test(valor))                   valido = false;
        if (reglas.includes('tiempo')   && !/^\d{1,2}:\d{2}\.\d{2}$/.test(valor))              valido = false;
        // -------------------------------------------

        if (reglas.includes('correo')   && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor))          valido = false;
        if (reglas.includes('telefono') && !/^[\d\-\+\(\)\s]{7,20}$/.test(valor))              valido = false;
        if (reglas.includes('texto')    && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s.,;:()\-\#\/]+$/.test(valor)) valido = false;
        if (reglas.includes('fecha_logica') && valor !== '') {
            const hoy = new Date();
            const año = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');
            const strHoy = `${año}-${mes}-${dia}`;
            const strMinimo = `${año - 120}-${mes}-${dia}`;
            
            if (valor > strHoy || valor < strMinimo) valido = false;
        }
        // ==========================================
        // REGLAS DE LONGITUD
        // ==========================================
        if (campo.hasAttribute('data-min')) {
            if (valor.length < parseInt(campo.getAttribute('data-min'))) valido = false;
        }
        if (campo.hasAttribute('data-max')) {
            if (valor.length > parseInt(campo.getAttribute('data-max'))) valido = false;
        }

        // Aplicamos el color: Verde (#34d399) si es válido, Rojo (#f87171) si es inválido
        campo.style.borderColor = valido ? '#34d399' : '#f87171';
        
        return valido;
    }


    /* Estaba asi antes
    
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
    } */

    /**
     * Vincula validaciones en tiempo real (keydown filtra, input valida)
     * param {HTMLFormElement} formulario
     */
/*     static vincularTiempoReal(formulario) {
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
 */

/**
     * Aplica validación en tiempo real usando Delegación de Eventos
     * Protege inputs estáticos y dinámicos (creados después de cargar la página)
     * @param {HTMLFormElement} formulario 
     */
    static vincularTiempoReal(formulario) {
        
        // ==============================================================
        // 1. CANDADO TECLADO: Bloquea la tecla antes de que se escriba
        // ==============================================================
        formulario.addEventListener('keydown', function(e) {
            const campo = e.target;
            
            // Si el elemento donde escriben NO tiene data-validar, lo ignoramos
            if (!campo.hasAttribute('data-validar')) return;

            const reglas = campo.getAttribute('data-validar').split('|');

            // Dejamos pasar teclas de control: Backspace, Tab, Flechas, Ctrl, etc.
            if (e.key.length !== 1 || e.ctrlKey || e.altKey || e.metaKey) return;

            // Regla 'letras': Bloquea números
            if (reglas.includes('letras') && /[0-9]/.test(e.key)) e.preventDefault();
            
            // Regla 'numeros': SOLO permite números del 0 al 9
            if (reglas.includes('numeros') && !/^[0-9]$/.test(e.key)) e.preventDefault();

           // Regla 'decimal': Números, un punto, y MÁXIMO 2 decimales
            if (reglas.includes('decimal')) {
                // Autocorrección amigable: Cambia la coma por punto
                if (e.key === ',') {
                    e.preventDefault();
                    if (!campo.value.includes('.')) {
                        // Inserta el punto donde esté el cursor
                        const start = campo.selectionStart;
                        campo.value = campo.value.substring(0, start) + '.' + campo.value.substring(campo.selectionEnd);
                        campo.selectionStart = campo.selectionEnd = start + 1;
                        campo.dispatchEvent(new Event('input', { bubbles: true })); 
                    }
                    return;
                }
                
                if (!/^[0-9.]$/.test(e.key)) e.preventDefault();
                if (e.key === '.' && campo.value.includes('.')) e.preventDefault();

                // NUEVO: Bloqueo inteligente del tercer decimal
                if (/^[0-9]$/.test(e.key) && campo.value.includes('.')) {
                    const partes = campo.value.split('.');
                    // Si ya existen 2 números después del punto
                    if (partes[1].length >= 2) {
                        // Y el cursor del usuario está ubicado DESPUÉS del punto
                        if (campo.selectionStart > campo.value.indexOf('.')) {
                            // Y no ha seleccionado texto para sobreescribirlo
                            if (campo.selectionStart === campo.selectionEnd) {
                                e.preventDefault(); // Bloqueamos la tecla
                            }
                        }
                    }
                }
            }

            // Regla 'tiempo': SOLO permite números, ":" y "." (Máximo uno de cada uno)
            if (reglas.includes('tiempo')) {
                // Bloquea cualquier cosa que no sea número, dos puntos, punto o coma
                if (!/^[0-9:.,]$/.test(e.key)) e.preventDefault();
                
                // Evita que escriban un segundo ':' si ya existe uno
                if (e.key === ':' && campo.value.includes(':')) e.preventDefault();
                
                // Evita que escriban un segundo '.' si ya existe uno
                if ((e.key === '.' || e.key === ',') && campo.value.includes('.')) e.preventDefault();

                // Si ponen una coma, la convertimos en punto instantáneamente
                if (e.key === ',') {
                    e.preventDefault();
                    campo.value += '.';
                    campo.dispatchEvent(new Event('input', { bubbles: true })); 
                }
            }

            // Regla 'cedula': Formato V-12345678
            if (reglas.includes('cedula') && !/[VEve\-0-9]/.test(e.key)) e.preventDefault();
        });

        // ==============================================================
        // 2. CANDADO PORTAPAPELES: Limpia si pegan (Ctrl+V) basura
        // ==============================================================
        formulario.addEventListener('input', function(e) {
            const campo = e.target;
            
            // Si el elemento no tiene reglas, salimos
            if (!campo.hasAttribute('data-validar')) return;

            const reglas = campo.getAttribute('data-validar').split('|');

            if (reglas.includes('numeros')) {
                campo.value = campo.value.replace(/[^0-9]/g, '');
            }

            if (reglas.includes('decimal')) {
                // 1. Cambia comas por puntos y borra basura
                let val = campo.value.replace(/,/g, '.').replace(/[^0-9.]/g, '');
                
                // 2. Si pegaron un número con decimales largos (ej: 12.3456)
                const partes = val.split('.');
                if (partes.length > 1) {
                    // Unimos la parte entera con un máximo de 2 caracteres de la parte decimal
                    val = partes[0] + '.' + partes.slice(1).join('').substring(0, 2);
                }
                
                campo.value = val;
            }

            if (reglas.includes('tiempo')) {
                campo.value = campo.value.replace(/[^0-9:.]/g, '');
            }

            if (reglas.includes('cedula')) {
                let val = campo.value;
                if (/^[VEve]$/.test(val)) {
                    campo.value = val.toUpperCase() + '-';
                } else if (/^[VEve]-/.test(val)) {
                    let prefijo = val.substring(0, 2).toUpperCase();
                    let digitos = val.substring(2).replace(/[^0-9]/g, '').substring(0, 8);
                    campo.value = prefijo + digitos;
                }
            }

            // Disparamos la función visual (borde verde o rojo)
            if(typeof Validador.validarCampo === 'function') {
                Validador.validarCampo(campo);
            }
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
