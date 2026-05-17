// assets/js/validador.js

class Validador {
    /**
     * Revisa todos los inputs de un formulario buscando reglas de validación
     * @param {HTMLFormElement} formulario 
     * @returns {string|boolean} - Retorna un string con errores en HTML o false si todo está OK
     */
    static validarFormulario(formulario) {
        let errores = [];
        
        // Buscamos todos los campos dentro del formulario que tengan el atributo "data-validar"
        const inputs = formulario.querySelectorAll('[data-validar]');

        inputs.forEach(input => {
            // Extraemos las reglas separadas por el símbolo | (Ej: "requerido|letras")
            const reglas = input.getAttribute('data-validar').split('|');
            const valor = input.value.trim();
            
            // Nombre amigable para mostrar en el error
            const nombreCampo = input.getAttribute('data-nombre') || input.name;

            // 1. Regla: Requerido
            if (reglas.includes('requerido') && valor === '') {
                errores.push(`- El campo <b>${nombreCampo}</b> es obligatorio.`);
                return; // Si está vacío, no seguimos validando las demás reglas para ese input
            }

            // Si el campo no está vacío, validamos formatos
            if (valor !== '') {
                // 2. Regla: Solo Letras
                if (reglas.includes('letras') && !/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> solo acepta letras.`);
                }

                // 3. Regla: Solo Números
                if (reglas.includes('numeros') && !/^[0-9]+$/.test(valor)) {
                    errores.push(`- <b>${nombreCampo}</b> solo acepta números enteros.`);
                }

                // 4. Regla: Longitud Mínima (Lee el atributo data-min)
                if (input.hasAttribute('data-min')) {
                    let min = parseInt(input.getAttribute('data-min'));
                    if (valor.length < min) {
                        errores.push(`- <b>${nombreCampo}</b> debe tener al menos ${min} caracteres.`);
                    }
                }
            }
        });

        return errores.length > 0 ? errores.join("<br>") : false;
    }


   /*  
   ----------------Apoyarse del HTML------------------

   <input type="text" id="cedula" name="cedula" 
       data-validar="requerido|numeros" 
       data-nombre="Cédula de Identidad" 
       data-min="6">

<input type="text" id="nombres" name="nombres" 
       data-validar="requerido|letras" 
       data-nombre="Nombres">

<input type="text" id="telefono_emergencia" name="telefono_emergencia" 
       data-validar="numeros" 
       data-nombre="Teléfono de Emergencia" 
       data-min="10"></input>
       
        // 1. DOBLE VALIDACIÓN (Capa Frontend)
        // Usamos nuestro Validador global para revisar todo según los data-attributes del HTML
        const erroresJS = Validador.validarFormulario(formRep);
        
        if (erroresJS) {
            UI.advertencia('Datos Incompletos o Inválidos', erroresJS);
            return; // Cortamos la ejecución aquí. Cero estrés para el servidor.
        }
       
       */
}


