// assets/js/validador.js

(function(){
    const s = document.createElement('style');
    s.textContent = `
        .validador-ayuda {
            font-size: 11px;
            margin-top: 4px;
            display: block;
            font-weight: 500;
            letter-spacing: 0.01em;
            transition: color 0.2s ease;
            line-height: 1.4;
        }
        .validador-ayuda.v-info  { color: #6b7280; }
        .validador-ayuda.v-ok    { color: #34d399; }
        .validador-ayuda.v-error { color: #f87171; }
    `;
    document.head.appendChild(s);
})();

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
                // Validación estricta de Segundos y Centésimas (SS.cc)
                if (reglas.includes('decimal_tiempo') && !/^\d{1,2}\.\d{2}$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> debe tener el formato exacto SS.cc (Ejemplo: 05.50).`);
                }

                // Validación estricta de Fechas (Máximo un mes atrás, cero futuro)
                if (reglas.includes('fecha_reciente') && valor !== '') {
                    // Separar YYYY-MM-DD para evitar el desfase de zona horaria de JavaScript
                    const partes = valor.split('-');
                    if (partes.length === 3) {
                        const fechaInput = new Date(partes[0], partes[1] - 1, partes[2]);
                        
                        // Fecha de Hoy a las 00:00:00
                        const hoy = new Date();
                        hoy.setHours(0, 0, 0, 0);

                        // Fecha Límite Lógica (Exactamente 1 mes atrás)
                        const limitePasado = new Date();
                        limitePasado.setMonth(limitePasado.getMonth() - 1);
                        limitePasado.setHours(0, 0, 0, 0);

                        if (fechaInput > hoy) {
                            errores.push(`- <b>${nombreCampo}</b> no puede ser una fecha futura.`);
                        } else if (fechaInput < limitePasado) {
                            errores.push(`- <b>${nombreCampo}</b> está fuera de rango. El sistema solo permite registrar marcas de hasta hace un mes atrás.`);
                        }
                    } else {
                        errores.push(`- <b>${nombreCampo}</b> tiene un formato de fecha corrupto.`);
                    }
                }
            }
        });

        return errores.length > 0 ? errores.join("<br>") : false;
    }

    static MOSTRAR_AYUDA_EN_VALIDACION = true;

    static MENSAJES_REGLAS = {
        requerido:       'Este campo es obligatorio',
        cedula:          'Formato requerido: V-12345678 o E-12345678',
        letras:          'Solo se permiten letras y espacios',
        numeros:         'Solo se permiten números enteros',
        decimal:         'Solo se permiten números con hasta 2 decimales',
        tiempo:          'Formato requerido: MM:SS.cc (Ej: 01:25.50)',
        decimal_tiempo:  'Formato requerido: SS.cc (Ej: 05.50)',
        correo:          'Ingresa un correo válido, ejemplo: usuario@dominio.com',
        telefono:        'Formato: 0412-1234567 (7 a 20 dígitos)',
        texto:           'Contiene caracteres no permitidos',
        fecha_logica:    'Fecha fuera de rango permitido',
        fecha_reciente:  'Fecha fuera del rango permitido (máximo 1 mes atrás)'
    };

    static REQUISITOS_INFO = {
        requerido:       'Campo obligatorio',
        cedula:          'Formato: V-12345678 o E-12345678',
        letras:          'Solo letras y espacios',
        numeros:         'Solo números enteros',
        decimal:         'Número con hasta 2 decimales (ej: 12.34)',
        tiempo:          'Formato MM:SS.cc (ej: 01:25.50)',
        decimal_tiempo:  'Formato SS.cc (ej: 05.50)',
        correo:          'Ejemplo: usuario@dominio.com',
        telefono:        'Ejemplo: 0412-1234567',
        texto:           'Letras, números y signos de puntuación básicos',
        fecha_logica:    'Fecha no futura, máxima 120 años atrás',
        fecha_reciente:  'Fecha reciente, máximo 1 mes atrás'
    };

    static mostrarAyuda(campo, mensaje, tipo) {
        if (!Validador.MOSTRAR_AYUDA_EN_VALIDACION) return;
        let ayuda = campo.nextElementSibling;
        if (!ayuda || !ayuda.classList.contains('validador-ayuda')) {
            ayuda = document.createElement('small');
            ayuda.className = 'validador-ayuda';
            campo.parentNode.insertBefore(ayuda, campo.nextSibling);
        }
        ayuda.textContent = mensaje;
        ayuda.className = 'validador-ayuda v-' + tipo;
    }

    static ocultarAyuda(campo) {
        const ayuda = campo.nextElementSibling;
        if (ayuda && ayuda.classList.contains('validador-ayuda')) {
            ayuda.remove();
        }
    }

    /**
     * Evalúa un campo individual y le da color de éxito (verde) o error (rojo)
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
                Validador.mostrarAyuda(campo, Validador.MENSAJES_REGLAS.requerido, 'error');
                return false;
            }
            Validador.ocultarAyuda(campo);
            return true;
        }

        let errorEspecifico = '';

        if (reglas.includes('cedula')   && !/^[VEve]-\d{7,8}$/.test(valor))                    { valido = false; errorEspecifico = Validador.MENSAJES_REGLAS.cedula; }
        if (reglas.includes('letras')   && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor))           { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.letras; }
        if (reglas.includes('numeros')  && !/^[0-9]+$/.test(valor))                            { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.numeros; }
        if (reglas.includes('decimal')  && !/^\d+(\.\d{1,2})?$/.test(valor))                   { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.decimal; }
        if (reglas.includes('tiempo')   && !/^\d{1,2}:\d{2}\.\d{2}$/.test(valor))              { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.tiempo; }
        if (reglas.includes('decimal_tiempo') && !/^\d{1,2}\.\d{2}$/.test(valor))              { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.decimal_tiempo; }
        if (reglas.includes('correo')   && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor))          { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.correo; }
        if (reglas.includes('telefono') && !/^[\d\-\+\(\)\s]{7,20}$/.test(valor))              { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.telefono; }
        if (reglas.includes('texto')    && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ0-9\s.,;:()\-\#\/]+$/.test(valor)) { valido = false; if (!errorEspecifico) errorEspecifico = Validador.MENSAJES_REGLAS.texto; }

        if (reglas.includes('fecha_logica') && valor !== '') {
            const hoy = new Date();
            const año = hoy.getFullYear();
            const mes = String(hoy.getMonth() + 1).padStart(2, '0');
            const dia = String(hoy.getDate()).padStart(2, '0');
            const strHoy = `${año}-${mes}-${dia}`;
            const strMinimo = `${año - 120}-${mes}-${dia}`;
            if (valor > strHoy) { valido = false; if (!errorEspecifico) errorEspecifico = 'La fecha no puede estar en el futuro'; }
            else if (valor < strMinimo) { valido = false; if (!errorEspecifico) errorEspecifico = 'Fecha demasiado antigua (más de 120 años)'; }
        }

        if (reglas.includes('fecha_reciente') && valor !== '') {
            const partes = valor.split('-');
            if (partes.length === 3) {
                const fechaInput = new Date(partes[0], partes[1] - 1, partes[2]);
                const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
                const limite = new Date(); limite.setMonth(limite.getMonth() - 1); limite.setHours(0, 0, 0, 0);
                if (fechaInput > hoy) { valido = false; if (!errorEspecifico) errorEspecifico = 'La fecha no puede estar en el futuro'; }
                else if (fechaInput < limite) { valido = false; if (!errorEspecifico) errorEspecifico = 'Máximo permitido: hace 1 mes'; }
            }
        }

        if (campo.hasAttribute('data-min')) {
            const min = parseInt(campo.getAttribute('data-min'));
            if (valor.length < min) { valido = false; if (!errorEspecifico) errorEspecifico = `Mínimo ${min} caracteres`; }
        }
        if (campo.hasAttribute('data-max')) {
            const max = parseInt(campo.getAttribute('data-max'));
            if (valor.length > max) { valido = false; if (!errorEspecifico) errorEspecifico = `Has superado el máximo de ${max} caracteres`; }
        }

        campo.style.borderColor = valido ? '#34d399' : '#f87171';

        if (!valido) {
            Validador.mostrarAyuda(campo, errorEspecifico, 'error');
        } else {
            Validador.mostrarAyuda(campo, 'Campo válido', 'ok');
        }

        return valido;
    }

    /**
     * Aplica validación en tiempo real usando Delegación de Eventos
     * Protege inputs estáticos y dinámicos (creados después de cargar la página)
     * @param {HTMLFormElement} formulario
     */
    static vincularTiempoReal(formulario) {

        // ==============================================================
        // 0. REQUISITOS AL ENFOCAR / OCULTAR AL SALIR
        // ==============================================================
        formulario.addEventListener('focus', function(e) {
            const campo = e.target;
            if (!campo.hasAttribute('data-validar')) return;
            const reglas = campo.getAttribute('data-validar').split('|');
            const valor = campo.value.trim();

            if (valor === '' || campo.style.borderColor !== '#34d399') {
                let requisitos = reglas
                    .map(r => Validador.REQUISITOS_INFO[r])
                    .filter(Boolean);
                if (campo.hasAttribute('data-min')) {
                    requisitos.push(`Mínimo ${campo.getAttribute('data-min')} caracteres`);
                }
                if (campo.hasAttribute('data-max')) {
                    requisitos.push(`Máximo ${campo.getAttribute('data-max')} caracteres`);
                }
                if (requisitos.length > 0) {
                    Validador.mostrarAyuda(campo, requisitos.join(' · '), 'info');
                }
            }
        }, true);

        formulario.addEventListener('blur', function(e) {
            const campo = e.target;
            if (!campo.hasAttribute('data-validar')) return;
            const valor = campo.value.trim();
            if (valor === '') {
                Validador.ocultarAyuda(campo);
                if (campo.getAttribute('data-validar').split('|').includes('requerido')) {
                    campo.style.borderColor = '';
                }
            }
        }, true);

        // ==============================================================
        // 1. CANDADO TECLADO: Bloquea la tecla antes de que se escriba
        // ==============================================================
        formulario.addEventListener('keydown', function(e) {
            const campo = e.target;
            
            // Si el elemento donde escriben NO tiene data-validar, lo ignoramos
            if (!campo.hasAttribute('data-validar')) return;

            const reglas = campo.getAttribute('data-validar').split('|');

            // Dejamos pasar teclas de control: Backspace, Tab, Flechas, Ctrl, etc.
            //if (e.key.length !== 1 || e.ctrlKey || e.altKey || e.metaKey) return;
            if (!e.key || e.key.length !== 1 || e.ctrlKey || e.altKey || e.metaKey) return;

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

                        // MÁSCARA INTELIGENTE PARA TIEMPO (MM:SS.cc)
            if (reglas.includes('tiempo')) {
                // 1. Extraemos solo los números que el usuario va tecleando
                let raw = campo.value.replace(/\D/g, ''); 
                let formateado = '';
                
                // 2. Construimos el formato predictivo
                if (raw.length > 0) {
                    formateado = raw.substring(0, 2); // Minutos
                }
                if (raw.length > 2) {
                    formateado += ':' + raw.substring(2, 4); // Segundos
                }
                if (raw.length > 4) {
                    formateado += '.' + raw.substring(4, 6); // Centésimas
                }
                
                campo.value = formateado;
            }

            // MÁSCARA INTELIGENTE PARA SEGUNDOS Y CENTÉSIMAS (SS.cc)
            if (reglas.includes('decimal_tiempo')) {
                // 1. Extraemos solo los números
                let raw = campo.value.replace(/\D/g, ''); 
                let formateado = '';
                
                // 2. Construimos el formato predictivo
                if (raw.length > 0) {
                    formateado = raw.substring(0, 2); // Primeros 2 dígitos son segundos
                }
                if (raw.length > 2) {
                    formateado += '.' + raw.substring(2, 4); // Ponemos el punto y las centésimas
                }
                
                campo.value = formateado;
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
            Validador.ocultarAyuda(c);
        });
    }


    
}
