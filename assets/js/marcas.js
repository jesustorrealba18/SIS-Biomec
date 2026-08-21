// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalMarca = document.getElementById('modalMarca');
const formMarca = document.getElementById('formMarca');
const btnGuardar = document.getElementById('btnGuardar');

const API_URL = 'index.php?p=marcas'; 

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicación con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

function obtenerFechaLocal() {
    const fecha = new Date();
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================

function cerrarModalMarca() {
    modalMarca.classList.add('hidden');
    modalMarca.firstElementChild.classList.add('scale-95', 'opacity-0');
    
    // 1. Resetear el formulario tradicional
    formMarca.reset();
    resetearContexto();
    
    // 2. Limpiar el contenedor dinámico de Splits (RF-06)
    document.getElementById('rejillaSplits').innerHTML = '';
    document.getElementById('contenedorSplits').classList.add('hidden');
    document.getElementById('alertaCoherencia').innerHTML = '';
    
    // 3. Resetear el Buscador Predictivo de Atletas
    document.getElementById('id_atleta').value = '';
    const inputBuscar = document.getElementById('inputBuscarAtleta');
    if(inputBuscar) {
        inputBuscar.value = '';
        inputBuscar.classList.remove('text-emerald-400', 'font-bold');
        inputBuscar.removeAttribute('readonly');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');
    }

    selectSesion.disabled = false;
    selectSesion.classList.remove('opacity-30', 'cursor-not-allowed');
    selectEvento.disabled = false;
    selectEvento.classList.remove('opacity-30', 'cursor-not-allowed');

    // 🧹 DESBLOQUEAR INPUTS DE TIEMPO para el próximo registro
  // Desbloquear inputs de tiempo (excepto brazadas_por_largo)
document.querySelectorAll('#contenedorTiemposManuales input, #rejillaSplits input').forEach(input => {
    if (input.id !== 'brazadas_por_largo') {
        input.removeAttribute('readonly');
        input.classList.remove('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
    }
});
}


// Cerrar modal con la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalMarca.classList.contains('hidden')) {
        cerrarModalMarca();
    }
});

// =====================================================================
// ABRIR MODAL (INTELIGENTE: ORQUESTA EL CREATE Y EL UPDATE)
// =====================================================================
async function abrirModalMarca(id_marca = null) {
    // 1. Limpieza inicial del modal
    formMarca.reset(); 
    try { Validador.limpiarEstilos(formMarca); } catch(e) {}
    resetearContexto(); 

     // 🧹 DESBLOQUEAR INPUTS DE TIEMPO (por si quedaron bloqueados)
    document.querySelectorAll('#contenedorTiemposManuales input, #rejillaSplits input').forEach(input => {
    if (input.id !== 'brazadas_por_largo') {
        input.removeAttribute('readonly');
        input.classList.remove('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
    }
});
    
    document.getElementById('id_marca').value = '';
    document.getElementById('accion_form').value = 'registrar';
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.innerHTML = 'GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>';
    btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
    btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
    
    const rejillaSplits = document.getElementById('rejillaSplits');
    if(rejillaSplits) rejillaSplits.innerHTML = '';

    modalMarca.classList.remove('hidden');
    setTimeout(() => {
        modalMarca.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    // =====================================================================
    // MODO EDICIÓN: La función "Muta" el formulario y recrea el estado
    // =====================================================================
    if (id_marca) {
        const data = await peticionAjax(`obtenerDetalleMarca&id=${id_marca}`);
        
        if (!data) {
            UI.error('Error', 'No se pudieron cargar los datos para edición.');
            cerrarModalMarca();
            return;
        }

        // --- HELPERS DE FORMATEO ESTRICTO (Para inyectar DB en UI) ---
        // Transforma "1.25" a "01.25"
        const formatoEstrictoParcial = (segundosStr) => {
            if (!segundosStr) return '';
            const num = parseFloat(segundosStr);
            if (isNaN(num)) return '';
            const [entero, decimal] = num.toFixed(2).split('.');
            return `${entero.padStart(2, '0')}.${decimal}`;
        };

        // Transforma "61.50" a "01:01.50" (Para el input humano)
        const formatoEstrictoFinal = (segundosStr) => {
            if (!segundosStr) return '';
            const num = parseFloat(segundosStr);
            if (isNaN(num)) return '';
            const min = Math.floor(num / 60);
            const segRestantes = num % 60;
            const [entero, decimal] = segRestantes.toFixed(2).split('.');
            return `${min.toString().padStart(2, '0')}:${entero.padStart(2, '0')}.${decimal}`;
        };
        // -------------------------------------------------------------

        document.getElementById('id_marca').value = data.id_marca;

       // --- INICIO DE MODO INMUTABLE (BLOQUEO INTELIGENTE) ---
        const selectSesion = document.getElementById('id_sesion');
        const selectEvento = document.getElementById('id_evento');
        const inputAtleta = document.getElementById('inputBuscarAtleta');
        const inputFecha = document.getElementById('fecha');

        // 1. CONGELAR CONTEXTO 
        if (data.id_sesion) {
            selectSesion.value = data.id_sesion;
        } else if (data.id_evento) {
            selectEvento.value = data.id_evento;
        }
        
        selectSesion.disabled = false;
        selectEvento.disabled = false;
        selectSesion.classList.add('opacity-50', 'pointer-events-none', 'bg-[#0f0d23]');
        selectEvento.classList.add('opacity-50', 'pointer-events-none', 'bg-[#0f0d23]');
        selectSesion.tabIndex = -1;
        selectEvento.tabIndex = -1;

        // 2. CONGELAR ATLETA 
        document.getElementById('id_atleta').value = data.id_atleta; 

        const nombreCompleto = `${data.atleta_nombres || data.nombres} ${data.atleta_apellidos || data.apellidos}`;
        inputAtleta.value = `${nombreCompleto} (C.I: ${data.atleta_cedula || data.cedula})`;
        inputAtleta.disabled = true; 
        inputAtleta.classList.add('opacity-50', 'cursor-not-allowed', 'text-emerald-400', 'font-bold');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');

        // 3. CONGELAR / CONFIGURAR FECHA
        inputFecha.value = data.fecha;
        if (data.id_evento) {
            const optionEvento = selectEvento.options[selectEvento.selectedIndex];
            inputFecha.min = optionEvento.getAttribute('data-inicio');
            inputFecha.max = optionEvento.getAttribute('data-fin');
        } else {
            inputFecha.value = data.fecha;
            inputFecha.readOnly = true; 
            inputFecha.classList.add('opacity-50', 'pointer-events-none'); 
        }
        // --- FIN DE MODO INMUTABLE ---

        // Llenar datos básicos y aplicar Formateo
        document.querySelector('[name="estilo"]').value = data.estilo;
        
        // Tiempo Final (Hidden en puro formato float, Humano en formato estricto)
        document.querySelector('[name="tiempo_final_seg"]').value = data.tiempo_final_seg;
        document.getElementById('tiempo_final_humano').value = formatoEstrictoFinal(data.tiempo_final_seg);
        
        // Reacción formateada
        document.querySelector('[name="tiempo_reaccion_seg"]').value = formatoEstrictoParcial(data.tiempo_reaccion_seg);
        
        document.querySelector('[name="observaciones"]').value = data.observaciones || '';

        // Llenamos el SWOLF
        const inputBrazadas = document.querySelector('[name="brazadas_por_largo"]');
        if (inputBrazadas) {
            inputBrazadas.value = data.swolf_data ? data.swolf_data.num_brazadas : '';
        }

        // Llenar y disparar cálculos de distancias y splits
        const selectDistancia = document.querySelector('[name="distancia_m"]');
        const selectPiscina = document.querySelector('[name="tipo_piscina"]');
        
        selectDistancia.value = data.distancia_m;
        selectPiscina.value = data.tipo_piscina;

        // Al disparar el change, se construye la rejilla de splits dinámica
        selectDistancia.dispatchEvent(new Event('change'));

        // Llenar los splits y virajes construidos con el formato estricto
        if (data.splits && data.splits.length > 0) {
            data.splits.forEach(split => {
                const inputSplit = document.querySelector(`[name="splits[${split.distancia_parcial_m}]"]`);
                if (inputSplit && split.tiempo_parcial_seg) {
                    inputSplit.value = formatoEstrictoParcial(split.tiempo_parcial_seg);
                }
                
                const inputViraje = document.querySelector(`[name="virajes[${split.distancia_parcial_m}]"]`);
                if (inputViraje && split.tiempo_viraje_seg) {
                    inputViraje.value = formatoEstrictoParcial(split.tiempo_viraje_seg);
                }
            });
            
            // Disparar validación matemática para los colores (border red/indigo)
            setTimeout(validarCoherenciaMatematica, 100);
        }

        // Mutar visualmente el botón
        document.getElementById('accion_form').value = 'actualizar';
        btnGuardar.innerHTML = 'ACTUALIZAR REGISTRO <i class="fas fa-sync-alt ml-2"></i>';
        btnGuardar.classList.replace('bg-indigo-600', 'bg-emerald-600');
        btnGuardar.classList.replace('hover:bg-indigo-500', 'hover:bg-emerald-500');
    }
}
// =====================================================================
// BUSCADOR PREDICTIVO DE ATLETAS (COMPONENTE CUSTOM)
// =====================================================================
let atletasGlobal = []; // Memoria caché para no saturar al servidor

const inputBuscar = document.getElementById('inputBuscarAtleta');
const dropdown = document.getElementById('dropdownAtletas');
const ulAtletas = document.getElementById('ulAtletas');
const inputIdOculto = document.getElementById('id_atleta');
const btnLimpiar = document.getElementById('btnLimpiarAtleta');

// Función para dibujar los cuadritos de los atletas en la lista
function renderizarDropdown(lista) {
    ulAtletas.innerHTML = '';
    
    if (lista.length === 0) {
        ulAtletas.innerHTML = '<li class="p-4 text-gray-500 dark:text-gray-400 text-center text-xs">No se encontraron coincidencias</li>';
        return;
    }

    lista.forEach(atleta => {
        const li = document.createElement('li');
        li.className = "p-3 hover:bg-indigo-100 dark:hover:bg-indigo-600/20 hover:text-indigo-700 dark:hover:text-indigo-300 cursor-pointer transition-colors flex justify-between items-center";
        li.innerHTML = `
            <div>
                <div class="font-bold text-gray-900 dark:text-white">${atleta.nombres} ${atleta.apellidos}</div>
                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">C.I: ${atleta.cedula}</div>
            </div>
            <i class="fas fa-check-circle text-indigo-500/0 transition-all"></i>
        `;
        
        li.onclick = () => {
            seleccionarAtleta(atleta);
        };
        ulAtletas.appendChild(li);
    });
}

function seleccionarAtleta(atleta) {
    inputIdOculto.value = atleta.id_atleta;
    inputBuscar.value = `${atleta.nombres} ${atleta.apellidos}`;
    inputBuscar.classList.add('text-indigo-600', 'dark:text-emerald-400', 'font-bold');
    inputBuscar.setAttribute('readonly', true);
    
    dropdown.classList.add('hidden');
    btnLimpiar.classList.remove('hidden');
}

// Asegurar que el botón limpiar esté definido (si no existe, se crea)
if (typeof btnLimpiar !== 'undefined' && btnLimpiar) {
    btnLimpiar.onclick = () => {
        inputIdOculto.value = '';
        inputBuscar.value = '';
        inputBuscar.classList.remove('text-indigo-600', 'dark:text-emerald-400', 'font-bold');
        inputBuscar.removeAttribute('readonly');
        btnLimpiar.classList.add('hidden');
        inputBuscar.focus();
    };
}

// El evento input ya usa las clases adaptadas, solo ajustamos el texto de la cédula a minúsculas para búsqueda
inputBuscar.addEventListener('input', (e) => {
    // 1. EXPRESIÓN REGULAR: Solo letras (incluyendo acentos/ñ), números, espacios y guiones.
    e.target.value = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\-\s]/g, '');

    const texto = e.target.value.toLowerCase();
    
    // 2. Limpiar selección previa
    inputIdOculto.value = '';
    inputBuscar.classList.remove('text-indigo-600', 'dark:text-emerald-400', 'font-bold');

    // 3. Filtrado
    const filtrados = atletasGlobal.filter(a => 
        a.nombres.toLowerCase().includes(texto) || 
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.toLowerCase().includes(texto)
    );
    
    // 4. Mostrar resultados
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
});

inputBuscar.addEventListener('focus', () => {
    if (!inputIdOculto.value) { 
        dropdown.classList.remove('hidden');
        renderizarDropdown(atletasGlobal);
    }
});

document.addEventListener('click', (e) => {
    if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// =====================================================================
// LÓGICA DE EXCLUSIVIDAD Y CASCADA: SESIÓN / EVENTO -> ATLETAS
// =====================================================================
const selectSesion = document.getElementById('id_sesion');
const selectEvento = document.getElementById('id_evento');

function configurarExclusividadSelects() {
    const inputFecha = document.getElementById('fecha');

    selectSesion.addEventListener('change', function() {
        if (this.value !== "") {
            // 1. Bloqueamos evento
            selectEvento.value = "";
            selectEvento.disabled = true;
            selectEvento.classList.add('opacity-30', 'cursor-not-allowed');
            
            // 2. LÓGICA DE FECHA (Autollenado estricto)
            const optionSeleccionada = this.options[this.selectedIndex];
            inputFecha.value = optionSeleccionada.getAttribute('data-fecha');
            inputFecha.readOnly = true; // El entrenador no puede cambiar la fecha de una sesión
            inputFecha.classList.add('opacity-70', 'pointer-events-none');
            
            // 3. Cargar atletas
            cargarAtletasPorContexto('sesion', this.value);
        } else {
            resetearContexto();
        }
    });

    selectEvento.addEventListener('change', function() {
        if (this.value !== "") {
            // 1. Bloqueamos sesión
            selectSesion.value = "";
            selectSesion.disabled = true;
            selectSesion.classList.add('opacity-30', 'cursor-not-allowed');
            
            // 2. LÓGICA DE FECHA (Rango permitido)
            const optionSeleccionada = this.options[this.selectedIndex];
            inputFecha.value = ""; // Limpiamos para que elija
            inputFecha.min = optionSeleccionada.getAttribute('data-inicio');
            inputFecha.max = optionSeleccionada.getAttribute('data-fin');
            inputFecha.readOnly = false; // Aquí sí puede elegir (ej. un evento dura 3 días)
            inputFecha.classList.remove('opacity-70', 'pointer-events-none');
            
            // 3. Cargar atletas
            cargarAtletasPorContexto('evento', this.value);
        } else {
            resetearContexto();
        }
    });

    inputFecha.addEventListener('blur', function() {
        const value = this.value;
        if (!value) return; 

        // 1. Extraemos el año para bloquear años absurdos (como 0002) que el navegador deja pasar
        const year = parseInt(value.split('-')[0]);
        if (year < 2000 || year > 2100) {
            UI.error('Año Inválido', 'Por favor, ingrese un año lógico y real.');
            this.value = this.max || this.min; // Lo forzamos a un límite seguro
            return;
        }

        // 2. Validar límite inferior (Min)
        if (this.min && value < this.min) {
            UI.error('Fecha Inválida', 'La fecha ingresada es anterior al inicio del evento.');
            this.value = this.min;
            return;
        }

        // 3. Validar límite superior (Max)
        if (this.max && value > this.max) {
            UI.error('Fecha Inválida', 'La fecha ingresada es en el futuro o posterior al evento.');
            this.value = this.max;
            return;
        }
    });
}

function resetearContexto() {
    const selectEvento = document.getElementById('id_evento');
    const selectSesion = document.getElementById('id_sesion');
   // 1. Liberar Evento (Limpiamos disabled, tabIndex y TODAS las clases de bloqueo)
    selectEvento.disabled = false;
    selectEvento.classList.remove('opacity-30', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none', 'bg-[#0f0d23]');
    selectEvento.tabIndex = 0;
    
    // 2. Liberar Sesión
    selectSesion.disabled = false;
    selectSesion.classList.remove('opacity-30', 'opacity-50', 'cursor-not-allowed', 'pointer-events-none', 'bg-[#0f0d23]');
    selectSesion.tabIndex = 0;
    
    // Liberar Fecha
   const inputFecha = document.getElementById('fecha');
    inputFecha.value = "";
    inputFecha.min = "";
    inputFecha.max = obtenerFechaLocal(); // ¡Adiós al .toISOString()!
    inputFecha.readOnly = false;
    inputFecha.classList.remove('opacity-70', 'pointer-events-none');

    resetearBuscadorAtleta();
}

// =====================================================================
// NUEVAS FUNCIONES PARA EL BUSCADOR PREDICTIVO EN CASCADA
// =====================================================================

async function cargarAtletasPorContexto(tipo, id_contexto, preseleccionarAtleta = null) {
    // 1. Damos feedback visual mientras carga
    inputBuscar.disabled = true;
    inputBuscar.placeholder = "Cargando atletas confirmados...";
    btnLimpiar.click(); // Limpiamos el buscador si había alguien
    atletasGlobal = []; // Vaciamos la caché anterior

    // 2. Pedimos los datos al backend
    const accion = tipo === 'sesion' ? 'listarAtletasPorSesion' : 'listarAtletasPorEvento';
    const datos = new FormData();
    datos.append('id_contexto', id_contexto);

    const respuesta = await peticionAjax(accion, datos);

    // 3. Llenamos el buscador predictivo
    if (respuesta && respuesta.length > 0) {
        atletasGlobal = respuesta; // Llenamos tu caché
        inputBuscar.disabled = false;
        inputBuscar.placeholder = "Escriba nombre o cédula...";
        
        // Si estamos editando un registro viejo, lo autoseleccionamos
        if (preseleccionarAtleta) {
            seleccionarAtleta(preseleccionarAtleta);
        } else {
            inputBuscar.focus();
        }
    } else {
        inputBuscar.disabled = true;
        inputBuscar.placeholder = `No hay atletas confirmados en este ${tipo}.`;
    }
}

function resetearBuscadorAtleta() {
    atletasGlobal = [];
    btnLimpiar.click();
    inputBuscar.disabled = true;
    inputBuscar.placeholder = "Seleccione sesión o evento primero...";
}

// =====================================================================
// CARGA DINÁMICA DE LOS SELECTS (CON ATRIBUTOS DE FECHA)
// =====================================================================
async function cargarSelectsContexto() {
    // 1. Cargar Sesiones
    const resSesiones = await peticionAjax('listarSesionesSelect');
    if (resSesiones && resSesiones.length > 0) {
        selectSesion.innerHTML = '<option value="">Ninguna - No aplica</option>';
        resSesiones.forEach(s => {
            // INYECTAMOS data-fecha
            selectSesion.innerHTML += `<option value="${s.id_sesion}" data-fecha="${s.fecha}">${formatearFecha(s.fecha)} | ${s.grupo_nombre} (${s.tipo_sesion})</option>`;
        });
    }

    // 2. Cargar Eventos
    const resEventos = await peticionAjax('listarEventosSelect');
    if (resEventos && resEventos.length > 0) {
        selectEvento.innerHTML = '<option value="">Ninguno - No aplica</option>';
        resEventos.forEach(e => {
            // INYECTAMOS data-inicio y data-fin
            selectEvento.innerHTML += `<option value="${e.id_evento}" data-inicio="${e.fecha_inicio}" data-fin="${e.fecha_fin}">${e.nombre} | ${e.tipo} (${e.nivel})</option>`;
        });
    }
}


// =====================================================================
// ESTILOS EXCLUSIVOS PARA LOS SPLITS (sin tocar validador.js)
// =====================================================================
// =====================================================================
// ESTILOS EXCLUSIVOS PARA LOS SPLITS (sin tocar validador.js)
// =====================================================================
// =====================================================================
// ESTILOS EXCLUSIVOS PARA LOS SPLITS (sin tocar validador.js)
// =====================================================================
// =====================================================================
// ESTILOS EXCLUSIVOS PARA LOS SPLITS (sin tocar validador.js)
// =====================================================================
// =====================================================================
// ESTILOS EXCLUSIVOS PARA LOS SPLITS (sin tocar validador.js)
// =====================================================================
(function() {
    const style = document.createElement('style');
    style.textContent = `
        /* La caja debe permitir que el mensaje se vea sin recortarse */
        .split-box {
            overflow: visible !important;
        }
        /* El mensaje de validación se muestra debajo del input, en flujo normal */
        .split-box .validador-ayuda {
            margin-top: 4px;
            margin-bottom: 0;
            font-size: 10px;
            line-height: 1.2;
            display: block;
            text-align: center;
        }
        /* Colores específicos (validador.js ya los asigna, pero los reforzamos) */
        .split-box .validador-ayuda.v-ok {
            color: #34d399;
        }
        .split-box .validador-ayuda.v-error {
            color: #f87171;
        }
        .split-box .validador-ayuda.v-info {
            color: #6b7280;
        }
        /* El contenedor de inputs usa space-y-2 para separarlos */
        .split-box .flex-col > .relative {
            margin-bottom: 0; /* space-y-2 ya maneja el espacio */
        }
    `;
    document.head.appendChild(style);
})();

const selectDistancia = document.getElementById('distancia_m');
const contenedorSplits = document.getElementById('contenedorSplits');
const rejillaSplits = document.getElementById('rejillaSplits');
const contadorSplits = document.getElementById('contadorSplits');

const selectPiscina = document.getElementById('tipo_piscina');

function generarCajasSplits() {
    const distanciaTotal = parseInt(selectDistancia.value);
    const tipoPiscinaVal = selectPiscina.value;
    const tamanoPiscina = tipoPiscinaVal === '25m' ? 25 : 50;
    
    if (isNaN(distanciaTotal)) {
        contenedorSplits.classList.add('hidden');
        rejillaSplits.innerHTML = '';
        return;
    }

    const tamanoTramo = 25; 
    const cantidadTramos = distanciaTotal / tamanoTramo;
    
    rejillaSplits.innerHTML = '';
    
    for (let i = 1; i <= cantidadTramos; i++) {
        let distanciaActual = i * tamanoTramo;
        const esPared = (distanciaActual % tamanoPiscina === 0) && (distanciaActual < distanciaTotal);
        
        let cajaHTML = `
            <div class="bg-white dark:bg-[#161430] p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm relative split-box">
                <label class="block text-[10px] text-gray-600 dark:text-gray-400 uppercase font-bold mb-2 text-center border-b border-gray-100 dark:border-gray-800 pb-1">
                    Tramo ${distanciaActual}m
                </label>
                <div class="flex flex-col space-y-2">
                    <!-- Input de Parcial (Siempre visible) -->
                    <div class="relative">
                        <!-- Etiqueta SPLIT fija: top fijo, no inset-y-0 -->
                        <div class="absolute left-0 pl-2 pointer-events-none" style="top: 0.75rem; height: 1.5rem; display: flex; align-items: center;">
                            <span class="text-[9px] text-indigo-400 font-bold">SPLIT</span>
                        </div>
                        <input type="text" 
                               name="splits[${distanciaActual}]" 
                               data-validar="requerido|decimal_tiempo" 
                               required 
                               data-nombre="Parcial de ${distanciaActual}m" 
                               placeholder="00.00" 
                               class="w-full bg-gray-50 dark:bg-[#0f0d23] border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-emerald-400 font-mono text-sm rounded-lg py-1.5 pr-5 pl-10 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-right split-input">
                        <span class="absolute right-2 top-1.5 text-gray-400 dark:text-gray-500 text-[10px]">s</span>
                    </div>
        `;
        
        // Si hay pared en este tramo, inyectamos la cajita de Viraje
        if (esPared) {
            cajaHTML += `
                    <!-- Input de Viraje (Dinámico) -->
                    <div class="relative">
                        <!-- Etiqueta VIRAJE fija: top fijo -->
                        <div class="absolute left-0 pl-2 pointer-events-none" style="top: 0.75rem; height: 1.5rem; display: flex; align-items: center;">
                            <span class="text-[9px] text-amber-500 font-bold">VIRAJE</span>
                        </div>
                        <input type="text" 
                               name="virajes[${distanciaActual}]" 
                               data-validar="decimal_tiempo" 
                               data-nombre="Viraje en ${distanciaActual}m" 
                               placeholder="00.00" 
                               class="w-full bg-gray-50 dark:bg-[#0f0d23] border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-amber-400 font-mono text-sm rounded-lg py-1.5 pr-5 pl-10 focus:ring-2 focus:ring-amber-500 outline-none transition-all text-right">
                        <span class="absolute right-2 top-1.5 text-gray-400 dark:text-gray-500 text-[10px]">s</span>
                    </div>
            `;
        }
        
        cajaHTML += `
                </div>
            </div>
        `;
        
        rejillaSplits.innerHTML += cajaHTML;
    }

    contadorSplits.innerText = `${cantidadTramos} Tramos (Cada 25m)`;
    contenedorSplits.classList.remove('hidden');
    
    rejillaSplits.style.opacity = 0;
    setTimeout(() => {
        rejillaSplits.style.transition = "opacity 0.3s ease-in-out";
        rejillaSplits.style.opacity = 1;
    }, 50);
}
/* function generarCajasSplits() {
    const distanciaTotal = parseInt(selectDistancia.value);
    const tipoPiscinaVal = selectPiscina.value; // '25m' o '50m'
    const tamanoPiscina = tipoPiscinaVal === '25m' ? 25 : 50;
    
    if (isNaN(distanciaTotal)) {
        contenedorSplits.classList.add('hidden');
        rejillaSplits.innerHTML = '';
        return;
    }

    const tamanoTramo = 25; 
    const cantidadTramos = distanciaTotal / tamanoTramo;
    
    rejillaSplits.innerHTML = '';
    
    for (let i = 1; i <= cantidadTramos; i++) {
        let distanciaActual = i * tamanoTramo;
        
        // LÓGICA MATEMÁTICA: ¿Hay pared aquí? 
        // (Es múltiplo del tamaño de la piscina y NO es la meta final)
        const esPared = (distanciaActual % tamanoPiscina === 0) && (distanciaActual < distanciaTotal);
        
        let cajaHTML = `
            <div class="bg-white dark:bg-[#161430] p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm relative split-box">
                <label class="block text-[10px] text-gray-600 dark:text-gray-400 uppercase font-bold mb-2 text-center border-b border-gray-100 dark:border-gray-800 pb-1">
                    Tramo ${distanciaActual}m
                </label>
                <div class="space-y-2">
                    <!-- Input de Parcial (Siempre visible) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                            <span class="text-[9px] text-indigo-400 font-bold">SPLIT</span>
                        </div>
                        <input type="text" 
                               name="splits[${distanciaActual}]" 
                               data-validar="requerido|decimal_tiempo" 
                               required 
                               data-nombre="Parcial de ${distanciaActual}m" 
                               placeholder="00.00" 
                               class="w-full bg-gray-50 dark:bg-[#0f0d23] border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-emerald-400 font-mono text-sm rounded-lg py-1.5 pr-5 pl-10 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-right split-input">
                        <span class="absolute right-2 top-1.5 text-gray-400 dark:text-gray-500 text-[10px]">s</span>
                    </div>
        `;
        
        // Si hay pared en este tramo, inyectamos la cajita de Viraje
        if (esPared) {
            cajaHTML += `
                    <!-- Input de Viraje (Dinámico) -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                            <span class="text-[9px] text-amber-500 font-bold">VIRAJE</span>
                        </div>
                        <input type="text" 
                               name="virajes[${distanciaActual}]" 
                               data-validar="decimal_tiempo" 
                               data-nombre="Viraje en ${distanciaActual}m" 
                               placeholder="00.00" 
                               class="w-full bg-gray-50 dark:bg-[#0f0d23] border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-amber-400 font-mono text-sm rounded-lg py-1.5 pr-5 pl-10 focus:ring-2 focus:ring-amber-500 outline-none transition-all text-right">
                        <span class="absolute right-2 top-1.5 text-gray-400 dark:text-gray-500 text-[10px]">s</span>
                    </div>
            `;
        }
        
        cajaHTML += `
                </div>
            </div>
        `;
        
        rejillaSplits.innerHTML += cajaHTML;
    }

    contadorSplits.innerText = `${cantidadTramos} Tramos (Cada 25m)`;
    contenedorSplits.classList.remove('hidden');
    
    rejillaSplits.style.opacity = 0;
    setTimeout(() => {
        rejillaSplits.style.transition = "opacity 0.3s ease-in-out";
        rejillaSplits.style.opacity = 1;
    }, 50);
} */

selectDistancia.addEventListener('change', generarCajasSplits);
// IMPORTANTE: Agregar este listener para que re-calcule las paredes si cambian el tipo de piscina
selectPiscina.addEventListener('change', generarCajasSplits);



// =====================================================================
// MOTOR MATEMÁTICO DE TIEMPOS
// =====================================================================

// Convierte un texto como "01:15.50" a 75.50 segundos puros
function convertirTiempoASegundos(tiempoTexto) {
    if (!tiempoTexto) return 0;
    
    // Si viene con formato MM:SS.cc
    if (tiempoTexto.includes(':')) {
        const partes = tiempoTexto.split(':');
        const minutos = parseInt(partes[0]) || 0;
        const segundos = parseFloat(partes[1]) || 0;
        return (minutos * 60) + segundos;
    }
    
    // Si escribieron solo segundos, ej: "45.30"
    return parseFloat(tiempoTexto) || 0;
}

// Convierte 75.50 segundos puros a formato "01:15.50"
function formatearTiempoDesdeSegundos(segundosTotales) {
    if (!segundosTotales) return '00.00';
    const num = parseFloat(segundosTotales);
    
    // Si es menos de un minuto, solo devolvemos los segundos
    if (num < 60) return num.toFixed(2);
    
    // Si pasa del minuto, calculamos
    const minutos = Math.floor(num / 60);
    const segundos = (num % 60).toFixed(2);
    
    // padStart asegura que siempre haya 2 dígitos (ej: "01" en vez de "1")
    return `${minutos.toString().padStart(2, '0')}:${segundos.padStart(5, '0')}`;
}

// 1. Obtenemos el tiempo final que escribió el entrenador
const inputTiempoHumano = document.getElementById('tiempo_final_humano');
const inputTiempoSegundos = document.getElementById('tiempo_final_seg');
const alertaCoherencia = document.getElementById('alertaCoherencia');

function validarCoherenciaMatematica() {
    
    const tiempoFinalSegundos = convertirTiempoASegundos(inputTiempoHumano.value);
    
    // Guardamos ese valor en el input oculto para enviarlo limpio a la Base de Datos
    inputTiempoSegundos.value = tiempoFinalSegundos.toFixed(2);

    // 2. Buscamos todas las cajitas de splits que generamos dinámicamente
    const cajasSplits = document.querySelectorAll('.split-input');
    
    // Si no hay cajas (porque no han seleccionado distancia), salimos
    if (cajasSplits.length === 0) return true; 

    // 3. Sumamos el valor de todos los splits
    let sumaParciales = 0;
    cajasSplits.forEach(caja => {
        sumaParciales += convertirTiempoASegundos(caja.value);
    });

    // 4. Calculamos la diferencia matemática absoluta
    const diferencia = Math.abs(tiempoFinalSegundos - sumaParciales);

    // 5. Evaluamos el Criterio CA-06.2 (Tolerancia estricta de 0.01s)
    if (tiempoFinalSegundos > 0 && sumaParciales > 0) {
        if (diferencia > 0.015) {
            // ==========================================
            // ESTADO DE ERROR: Encendemos los bordes rojos
            // ==========================================
            alertaCoherencia.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Error: Los parciales suman <b>${sumaParciales.toFixed(2)}s</b> y el final es <b>${tiempoFinalSegundos.toFixed(2)}s</b>.`;
            alertaCoherencia.classList.replace('text-emerald-400', 'text-red-500');
            
            // Pintamos el input final de rojo
            inputTiempoHumano.classList.remove('border-indigo-500', 'focus:ring-indigo-500');
            inputTiempoHumano.classList.add('border-red-500', 'ring-2', 'ring-red-500');
            
            // Pintamos el contenedor de splits de rojo
            contenedorSplits.classList.remove('border-gray-700');
            contenedorSplits.classList.add('border-red-500', 'bg-red-900/10');
            
            return false; // Bloquea el envío
        } else {
            // ==========================================
            // ESTADO CORRECTO: Restauramos colores originales
            // ==========================================
            alertaCoherencia.innerHTML = `<i class="fas fa-check-circle"></i> Tiempos coherentes. (Suma: ${sumaParciales.toFixed(2)}s)`;
            alertaCoherencia.classList.replace('text-red-500', 'text-emerald-400');
            
            // Restauramos el input final a su azul índigo
            inputTiempoHumano.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
            inputTiempoHumano.classList.add('border-indigo-500', 'focus:ring-indigo-500');
            
            // Restauramos el contenedor de splits
            contenedorSplits.classList.remove('border-red-500', 'bg-red-900/10');
            contenedorSplits.classList.add('border-gray-700');
            
            return true; // Permite el envío
        }
    }
    
    // Estado neutral (aún no terminan de escribir)
    alertaCoherencia.innerHTML = '';
    
    // Por si borran todo, limpiamos posibles estilos de error residuales
    inputTiempoHumano.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
    inputTiempoHumano.classList.add('border-indigo-500');
    contenedorSplits.classList.remove('border-red-500', 'bg-red-900/10');
    contenedorSplits.classList.add('border-gray-700');

    return true; 
} 

// Ponemos a escuchar al input del tiempo final para que valide al instante
inputTiempoHumano.addEventListener('input', validarCoherenciaMatematica);

document.getElementById('rejillaSplits').addEventListener('input', function(e) {
    if(e.target && e.target.classList.contains('split-input')) {
        validarCoherenciaMatematica();
    }
});



// Función auxiliar para convertir milisegundos a Segundos puros (Ej: 75230ms -> "75.23")
// Tu backend espera decimales estrictos para tiempo_final_seg, splits y virajes.
function formatoSegundosPuros(ms) {
    const centesimasTotales = Math.floor(ms / 10);
    const seg = Math.floor(centesimasTotales / 100);
    const cent = centesimasTotales % 100;
    return `${seg}.${cent.toString().padStart(2, '0')}`;
}

// =====================================================================
// ENVÍO DEL FORMULARIO (CREATE / UPDATE DINÁMICO) PROTEGIDO
// =====================================================================
formMarca.addEventListener('submit', async (e) => {
    e.preventDefault(); 

    // 1. Validaciones visuales (DOM)
    const erroresFormulario = Validador.validarFormulario(formMarca);
    if (erroresFormulario && erroresFormulario.length > 0) {
        const listaErrores = Array.isArray(erroresFormulario) ? erroresFormulario.join('<br>') : erroresFormulario;
        UI.error('Datos Incompletos', `<div class="text-left text-sm mt-2 text-gray-300"><p class="mb-2 font-bold text-white">Corrige lo siguiente:</p>${listaErrores}</div>`);
        return; 
    }

    let datosFormulario = new FormData(formMarca);
    const accionActual = document.getElementById('accion_form') ? document.getElementById('accion_form').value : 'registrar';
    datosFormulario.set('accion', accionActual);

    // =================================================================
    // EL GOLPE MAESTRO DE SEGURIDAD (ADAPTADO A TU BACKEND EXACTO)
    // =================================================================
    const modoCrono = localStorage.getItem('sgrd_crono_mode') || 'manual';
    
    if (modoCrono === 'live') {
        const registrosCompletos = CronometroSeguro.obtenerArrayRegistros();
        
        if (!registrosCompletos || registrosCompletos.length === 0) {
            UI.error('Alerta de Seguridad', 'No hay datos del cronómetro en la memoria segura.');
            return;
        }

        // A) LIMPIEZA: Eliminamos del FormData todo lo que el usuario pudo alterar en el HTML
        for (let key of datosFormulario.keys()) {
            if (key.startsWith('splits[') || key.startsWith('virajes[') || key === 'tiempo_final_seg' || key === 'tiempo_reaccion_seg') {
                datosFormulario.delete(key);
            }
        }

        // B) INYECCIÓN BLINDADA: Llenamos el FormData con los datos inmutables de la RAM
        // usando EXACTAMENTE la nomenclatura que espera tu array $this->camposPermitidos en PHP
        let centesimasAcumuladas = 0;

        registrosCompletos.forEach(registro => {
            const centesimasActuales = Math.floor(registro.tiempoMs / 10);
            const valorSegundos = formatoSegundosPuros(registro.tiempoMs);

            if (registro.tipo === 'reaccion') {
                datosFormulario.set('tiempo_reaccion_seg', valorSegundos);
            } 
            else if (registro.tipo === 'viraje') {
                // PHP lo leerá como el array $virajes[$distancia]
                datosFormulario.set(`virajes[${registro.distancia}]`, valorSegundos);
            } 
            else if (registro.tipo === 'split') {
                const lapCentesimas = centesimasActuales - centesimasAcumuladas;
                centesimasAcumuladas = centesimasActuales;
                
                const segLap = Math.floor(lapCentesimas / 100);
                const centLap = lapCentesimas % 100;
                
                // PHP lo leerá como el array $splits[$distancia]
                datosFormulario.set(`splits[${registro.distancia}]`, `${segLap}.${centLap.toString().padStart(2, '0')}`);

                if (registro.distancia === confDistancia) {
                    datosFormulario.set('tiempo_final_seg', valorSegundos);
                }
            }
        });
    } else {
        // Modo Manual: Validamos coherencia porque el usuario sí los escribió
        if (typeof validarCoherenciaMatematica === 'function' && !validarCoherenciaMatematica()) {
            UI.error('Incoherencia Matemática', 'La suma de los parciales no coincide con el tiempo final.');
            return;
        }
    }

    // 2. Envío al Servidor
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...';
    btnGuardar.disabled = true;

    const resultado = await peticionAjax(accionActual, datosFormulario);

    if (resultado) {
        if (resultado.status === 'success') {
            UI.exito('¡Operación Exitosa!', accionActual === 'actualizar' ? 'Registro actualizado.' : 'Rendimiento registrado con éxito.');
            cerrarModalMarca();
            cargarTablaMarcas(); 
            CronometroSeguro.limpiar(); // Limpiar la memoria
        } 
        else if (resultado.status === 'warning') {
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Datos Incoherentes', mensajesError);
        } 
        else {
            UI.error('Error de Sistema', resultado.message || 'Error inesperado.');
        }
    }

    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});

// =====================================================================
// RENDERIZADO DE LA TABLA PRINCIPAL (READ)
// =====================================================================
let dataTableMarcasInstance = null;
let deepLinkPendiente = null; // Guardamos el ID a resaltar
let deepLinkProcesado = false; // Bandera para evitar bucles
async function cargarTablaMarcas() {
    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const distancia = document.getElementById('filtroDistancia')?.value || '';
    const estilo = document.getElementById('filtroEstilo')?.value || '';
    const piscina = document.getElementById('filtroPiscina')?.value || '';

    // Permisos...
    const puedeEditar = PERMISOS_MODULO.editar && filtroEstado === 'Activo';
    const puedeEliminar = PERMISOS_MODULO.eliminar && filtroEstado === 'Activo';
    const puedeRestaurar = PERMISOS_MODULO.restaurar && filtroEstado === 'Inactivo';

    // Capturar deep link desde URL
    const parametrosURL = new URLSearchParams(window.location.search);
    const idResaltar = parametrosURL.get('h');
    if (idResaltar) {
        deepLinkPendiente = idResaltar;
    }

    let params = new URLSearchParams({ estado: filtroEstado });
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (estilo) params.append('estilo', estilo);
    if (distancia) params.append('distancia', distancia);
    if (piscina) params.append('piscina', piscina);
    
    // Destruir DataTable si existe
    if ($.fn.DataTable.isDataTable('#tablaMarcas')) {
        $('#tablaMarcas').DataTable().destroy();
    }

    const tbody = document.getElementById('tbodyMarcas');
    tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-600 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando marcas...</td></tr>`;

    const marcas = await peticionAjax(`listarMarcas&${params.toString()}`);

    if (!marcas || marcas.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-600 dark:text-gray-400 font-mono text-xs">No hay marcas registradas en esta vista.</td></tr>`;
        return;
    }

    let html = '';
    marcas.forEach(marca => {
        const tiempoReloj = formatearTiempoDesdeSegundos(marca.tiempo_final_seg);
        const fechaLatina = formatearFecha(marca.fecha);

        const badgePB = (marca.es_pb == 1) 
            ? `<span class="bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase shadow-[0_0_10px_rgba(245,158,11,0.2)]" title="¡Mejor Marca Personal!"><i class="fas fa-star mr-1"></i>PB</span>` 
            : '';

        const botonAccion = (filtroEstado === 'Activo' && puedeEliminar)
            ? `<button onclick="eliminarMarca(${marca.id_marca})" class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 p-2 rounded-lg transition" title="Archivar Registro"><i class="fas fa-trash-alt"></i></button>`
            : (filtroEstado === 'Inactivo' && puedeRestaurar)
            ? `<button onclick="reactivarMarca(${marca.id_marca})" class="text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 p-2 rounded-lg transition" title="Restaurar Registro"><i class="fas fa-undo"></i></button>`
            : '';

        const botonEditar = (filtroEstado === 'Activo' && puedeEditar)
            ? `<button onclick="abrirModalMarca(${marca.id_marca})" class="text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 p-2 rounded-lg transition" title="Editar Registro de Tiempo"><i class="fas fa-edit text-base"></i></button>`
            : '';

        const accionesHTML = (botonEditar || botonAccion) ? `${botonEditar}${botonAccion}` : '';
        const justificacionHTML = (filtroEstado === 'Inactivo' && marca.motivo_eliminacion)
            ? `<div class="text-[9px] text-red-600 dark:text-red-400 mt-1 flex items-center gap-1 w-48 leading-tight">
                <i class="fas fa-exclamation-circle"></i> Anulado: ${marca.motivo_eliminacion}
               </div>`
            : '';

        let clasesFila = "hover:bg-gray-100 dark:hover:bg-white/5 transition-colors duration-200 border-b border-gray-200 dark:border-[#252345]";
        html += `
            <tr id="fila-marca-${marca.id_marca}" data-id-marca="${marca.id_marca}" class="${clasesFila}">
                <td class="py-4 pr-4 align-middle">
                    <div class="font-bold text-gray-900 dark:text-white text-sm">${marca.nombre_atleta}</div>
                    <div class="text-[10px] text-gray-600 dark:text-gray-400 font-mono mt-0.5">C.I: ${marca.cedula}</div>
                </td>
                <td class="p-4 align-middle">
                    <div class="font-bold text-indigo-600 dark:text-indigo-300 text-sm">${marca.distancia_m}m ${marca.estilo}</div>
                </td>
                <td class="p-4 text-xs text-gray-600 dark:text-gray-400 align-middle">
                    <i class="fas fa-swimming-pool mr-1 text-gray-400 dark:text-gray-600"></i> ${marca.tipo_piscina}
                </td>
                <td class="p-4 align-middle">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold text-lg">${tiempoReloj}</span>
                        ${badgePB}
                    </div>
                </td>
                <td class="p-4 align-middle">
                    <span class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-bold">
                        ${marca.nivel_evento}
                    </span>
                    ${justificacionHTML}
                </td>
                <td class="p-4 text-xs font-mono text-gray-600 dark:text-gray-400 align-middle" data-sort="${marca.fecha}">
                    ${fechaLatina}
                </td>
                <td class="p-4 align-middle">
                    <div class="flex flex-wrap items-center gap-2 md:justify-end">
                        <button onclick="verDetallesMarca(${marca.id_marca})" class="text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 p-2 rounded-lg transition" title="Ver Análisis de Rendimiento">
                            <i class="fas fa-chart-line text-base"></i>
                        </button>
                        ${accionesHTML}
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    // INICIALIZAR DATATABLES
    dataTableMarcasInstance = $('#tablaMarcas').DataTable({
        responsive: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 3 },
            { responsivePriority: 3, targets: 6, orderable: false },
            { responsivePriority: 4, targets: [1, 2, 4, 5] }
        ],
        order: [[5, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todas"]],
        dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-2"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>'
    });

    // Capturar deep link desde URL
    if (idResaltar) {
        deepLinkPendiente = idResaltar;
        // Esperar a que la tabla termine de dibujarse
        setTimeout(() => {
            procesarDeepLink();
        }, 300);
    }
}

function procesarDeepLink() {
    const idMarca = deepLinkPendiente;
    if (!idMarca || !dataTableMarcasInstance) return;

    const dt = dataTableMarcasInstance;
    const rowId = 'fila-marca-' + idMarca;

    // Buscar el índice de la fila por su ID
    let indexBuscado = -1;
    dt.rows().every(function(rowIdx) {
        const nodoTR = this.node();
        if (nodoTR && nodoTR.id === rowId) {
            indexBuscado = rowIdx;
        }
    });

    if (indexBuscado === -1) {
        console.warn('DeepLink: No se encontró la fila con ID', rowId);
        deepLinkPendiente = null;
        return;
    }

    // Obtener índices de visualización (aplicando filtros y orden)
    const displayIndexes = dt.rows({ order: 'applied', search: 'applied' }).indexes();
    const posicionVisual = displayIndexes.indexOf(indexBuscado);

    if (posicionVisual === -1) {
        console.warn('DeepLink: La fila está oculta por el filtro actual.');
        deepLinkPendiente = null;
        return;
    }

    const tamanioPagina = dt.page.len();
    const paginaActual = dt.page();
    const paginaDestino = tamanioPagina > 0 ? Math.floor(posicionVisual / tamanioPagina) : 0;

    // Función para hacer scroll y resaltar
    function resaltarFila() {
        const trNode = document.getElementById(rowId);
        if (!trNode) return;

        trNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
        trNode.classList.add('border-l-4', 'border-emerald-500');
        const tds = trNode.querySelectorAll('td');
        tds.forEach(td => td.classList.add('!bg-emerald-500/20', 'transition-all', 'duration-1000'));

        setTimeout(() => {
            trNode.classList.remove('border-l-4', 'border-emerald-500');
            tds.forEach(td => td.classList.remove('!bg-emerald-500/20'));
            // Limpiar URL
            const urlLimpia = window.location.pathname + '?p=marcas';
            window.history.replaceState(null, null, urlLimpia);
        }, 4000);
    }

    if (paginaActual !== paginaDestino) {
        // Cambiar de página y luego resaltar
        dt.page(paginaDestino).draw(false);
        // Esperar a que el draw termine
        setTimeout(resaltarFila, 400);
    } else {
        // Ya estamos en la página correcta
        resaltarFila();
    }

    deepLinkPendiente = null;
}

async function cargarFiltroAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    const select = document.getElementById('filtroAtleta');
    
    if (atletas && atletas.length > 0) {
        atletas.forEach(atleta => {
            select.insertAdjacentHTML('beforeend', `<option value="${atleta.id_atleta}">${atleta.nombres} ${atleta.apellidos} - CI: ${atleta.cedula}</option>`);
        });
    }
}

// =====================================================================
// VISUALIZADOR CIENTÍFICO Y GRÁFICAS DE RENDIMIENTO (MAESTRO-DETALLE)
// =====================================================================
let instanciaGrafica = null; 
let instanciaGraficaSplits = null; 

async function verDetallesMarca(id_marca) {
    const modalVer = document.getElementById('modalVer');
    const contenedor = document.getElementById('detalleContenido');
    
    contenedor.innerHTML = `
        <div class="text-center p-12 text-gray-500 dark:text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i>
            <p class="text-xs font-mono uppercase tracking-widest">Sincronizando métricas biomecánicas...</p>
        </div>
    `;
    
    modalVer.classList.remove('hidden');
    
    const data = await peticionAjax(`obtenerDetalleMarca&id=${id_marca}`);
    if (!data) {
        UI.error('Error de Consulta', 'No se pudo estructurar el análisis técnico del registro.');
        cerrarModalVer();
        return;
    }

    const tiempoFinalHumano = formatearTiempoDesdeSegundos(data.tiempo_final_seg);
    const swolfScore = data.swolf_data ? data.swolf_data.swolf : '🚫 N/A';
    const numBrazadas = data.swolf_data ? data.swolf_data.num_brazadas : 'Sin conteo';
    const tReaccion = data.tiempo_reaccion_seg ? parseFloat(data.tiempo_reaccion_seg).toFixed(2) + 's' : '—';
    
    // 1. DIBUJAR LOS TRAMOS (AHORA INCLUYEN EL VIRAJE DINÁMICO)
    let tramosHTML = '';
    if (data.splits && data.splits.length > 0) {
        data.splits.forEach(split => {
            const tiempoNado = parseFloat(split.tiempo_parcial_seg).toFixed(2) + 's';
            
            // Si este tramo tiene un viraje asociado, preparamos el HTML
            const badgeViraje = split.tiempo_viraje_seg 
                ? `<span class="text-amber-500 ml-1.5" title="Tiempo en pared: ${split.tiempo_viraje_seg}s">
                    <i class="fas fa-undo text-[9px]"></i> ${parseFloat(split.tiempo_viraje_seg).toFixed(2)}s
                   </span>` 
                : '';

            tramosHTML += `
                <div class="bg-gray-100 dark:bg-[#161430] border border-gray-200 dark:border-gray-800 p-3 rounded-xl text-center shadow-inner flex flex-col justify-center">
                    <p class="text-[9px] text-gray-600 dark:text-gray-500 uppercase font-black tracking-wider mb-0.5">${split.distancia_parcial_m} Metros</p>
                    <div class="font-mono text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center">
                        ${tiempoNado} ${badgeViraje}
                    </div>
                </div>
            `;
        });
    } else {
        tramosHTML = '<div class="col-span-4 p-4 text-center text-xs text-gray-500 dark:text-gray-500 italic">No se recolectaron parciales en este control.</div>';
    }

    // 2. CONSTRUIR EL MODAL COMPLETO
    contenedor.innerHTML = `
        <div class="mb-6">
            <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold rounded-md uppercase tracking-widest">
                <i class="fas fa-microscope mr-1"></i> Telemetría Deportiva
            </span>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-2">${data.atleta_nombres} ${data.atleta_apellidos}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">C.I: ${data.cedula} • Registro: ${formatearFecha(data.fecha)}</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-gray-100 dark:bg-black/30 p-3.5 rounded-xl border border-gray-200 dark:border-white/5 text-center flex flex-col justify-center">
                <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Tiempo de Registro</p>
                <p class="text-base font-mono text-emerald-600 dark:text-emerald-400 font-black">${tiempoFinalHumano}</p>
                ${data.es_pb == 1 ? '<span class="text-[9px] text-amber-600 dark:text-amber-400 font-bold animate-pulse mt-0.5"><i class="fas fa-trophy mr-1"></i>Récord (PB)</span>' : ''}
            </div>
            
            <div class="bg-gray-100 dark:bg-black/30 p-3.5 rounded-xl border border-gray-200 dark:border-white/5 text-center flex flex-col justify-center">
                <p class="text-[9px] text-amber-600 dark:text-amber-400 uppercase font-bold tracking-wider mb-1">Índice SWOLF</p>
                <p class="text-base font-mono text-amber-600 dark:text-amber-400 font-black">${swolfScore}</p>
                <p class="text-[8px] text-gray-500 dark:text-gray-500 uppercase font-medium mt-0.5">Eficiencia Dinámica</p>
            </div>

            <div class="bg-gray-100 dark:bg-black/30 p-3.5 rounded-xl border border-gray-200 dark:border-white/5 text-center flex flex-col justify-center">
                <p class="text-[9px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-1">Ciclos de Brazada</p>
                <p class="text-base font-mono text-gray-800 dark:text-white font-bold">${numBrazadas}</p>
                <p class="text-[8px] text-gray-500 dark:text-gray-500 uppercase mt-0.5">Por Longitud</p>
            </div>

            <div class="bg-gray-100 dark:bg-black/30 p-3.5 rounded-xl border border-gray-200 dark:border-white/5 text-center flex flex-col justify-center">
                <p class="text-[9px] text-purple-600 dark:text-purple-400 uppercase font-bold tracking-wider mb-1">T. Reacción</p>
                <p class="text-base font-mono text-gray-700 dark:text-gray-300 font-bold">${tReaccion}</p>
                <p class="text-[8px] text-gray-500 dark:text-gray-500 uppercase mt-0.5">Salida del Bloque</p>
            </div>
        </div>

        <div class="mb-6 bg-gray-50 dark:bg-black/10 p-4 rounded-xl border border-gray-200 dark:border-white/5">
            <p class="text-[10px] uppercase text-gray-600 dark:text-gray-400 font-bold tracking-widest mb-3">
                <i class="fas fa-chart-bar text-emerald-600 dark:text-emerald-400 mr-2"></i>Pacing: Desglose de Ritmo por Tramo
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                ${tramosHTML}
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/5">
            <p class="text-[10px] uppercase text-gray-600 dark:text-gray-400 font-bold tracking-widest mb-3">
                <i class="fas fa-chart-line text-indigo-600 dark:text-indigo-400 mr-2"></i>Curva Histórica de Progresión (${data.distancia_m}m ${data.estilo} - Piscina ${data.tipo_piscina})
            </p>
            <div class="w-full h-44 relative">
                <canvas id="canvasEvolucion"></canvas>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-black/20 p-4 rounded-xl border border-gray-200 dark:border-white/5 mt-4">
            <p class="text-[10px] uppercase text-gray-600 dark:text-gray-400 font-bold tracking-widest mb-3">
                <i class="fas fa-chart-line text-cyan-600 dark:text-cyan-400 mr-2"></i> Análisis de Ritmo y Caída de Velocidad
            </p>
            <div class="w-full h-44 relative">
                <canvas id="graficaSplits"></canvas> 
            </div>
        </div>
    `;

    // 3. RENDERIZADO DE LAS GRÁFICAS
    const esDark = document.documentElement.classList.contains('dark');
    const colorTexto = esDark ? '#6b7280' : '#4b5563';
    const colorGrid = esDark ? 'rgba(255, 255, 255, 0.03)' : 'rgba(0, 0, 0, 0.05)';

    if (data.historial_evolucion && data.historial_evolucion.length > 0) {
        const ejeFechas = data.historial_evolucion.map(h => formatearFecha(h.fecha));
        const ejeTiempos = data.historial_evolucion.map(h => parseFloat(h.tiempo_final_seg));

        if (instanciaGrafica) instanciaGrafica.destroy();

        const contextoLienzo = document.getElementById('canvasEvolucion').getContext('2d');
        instanciaGrafica = new Chart(contextoLienzo, {
            type: 'line',
            data: {
                labels: ejeFechas,
                datasets: [{
                    data: ejeTiempos,
                    borderColor: '#6366f1', 
                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981', 
                    pointBorderColor: '#fff',
                    pointRadius: 3.5,
                    tension: 0.25 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: colorGrid },
                        ticks: { color: colorTexto, font: { size: 9, family: 'monospace' } }
                    },
                    y: {
                        grid: { color: colorGrid },
                        ticks: { 
                            color: colorTexto, 
                            font: { size: 9, family: 'monospace' },
                            callback: function(val) { return val + 's'; }
                        }
                    }
                }
            }
        });
    }

    if (data.splits && data.splits.length > 0) {
        const ejeDistancias = data.splits.map(s => s.distancia_parcial_m + 'm');
        const ejeTiemposSplits = data.splits.map(s => parseFloat(s.tiempo_parcial_seg));

        if (instanciaGraficaSplits) instanciaGraficaSplits.destroy();

        const ctxSplits = document.getElementById('graficaSplits').getContext('2d');
        instanciaGraficaSplits = new Chart(ctxSplits, {
            type: 'line',
            data: {
                labels: ejeDistancias,
                datasets: [{
                    data: ejeTiemposSplits,
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#06b6d4',
                    pointBorderColor: '#fff',
                    pointRadius: 3.5,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { color: colorTexto, font: { size: 9, family: 'monospace' } } 
                    },
                    y: { 
                        grid: { color: colorGrid }, 
                        ticks: { color: colorTexto, font: { size: 9, family: 'monospace' }, callback: function(val) { return val + 's'; } } 
                    }
                }
            }
        });
    }
}

// =====================================================================
// MOTOR DE CRONOMETRAJE EN VIVO Y CÁLCULO DE TRAMOS
// =====================================================================

let cronoActivo = false;
let estadoCrono = 'ESPERANDO'; // Estados: 'ESPERANDO', 'EN_CURSO', 'EN_VIRAJE', 'FINALIZADO'
let tiempoInicio = 0;
let tiempoToquePared = 0; // Guardará el timestamp exacto del primer clic en la pared
let animacionReloj;
let registrosCrono = []; // Array más inteligente para diferenciar splits y virajes

// Variables de configuración de la prueba
let confPiscina = 50; 
let confDistancia = 100;
let totalTramosEsperados = 0;
let tramoActual = 0;

// =====================================================================
// HELPER: SINCRONIZACIÓN DE TELEMETRÍA EN SEGUNDO PLANO
// =====================================================================
function enviarLatidoTelemetria(estado, distancia, tiempoMs) {
    const id_atleta = document.getElementById('id_atleta').value;
    const estilo = document.getElementById('estilo').value || 'Libre';
    
    if (!id_atleta) return;

    const datos = new FormData();
    datos.append('accion', 'sync_telemetria');
    datos.append('id_atleta', id_atleta);
    datos.append('distancia_total', confDistancia);
    datos.append('tipo_piscina', confPiscina + 'm');
    datos.append('estilo', estilo);
    datos.append('estado_carrera', estado);
    datos.append('ultima_distancia_recorrida_m', distancia);
    datos.append('ultimo_tiempo_parcial_ms', tiempoMs);

    // INYECCIÓN DIRECTA DE LA ACCIÓN EN LA URL (Más seguro para algunos servidores)
    fetch(`${API_URL}&accion=sync_telemetria`, {
        method: 'POST',
        body: datos
    }).catch(error => {
        console.debug("Latido de telemetría perdido por red:", error);
    });
}

function abrirModalCronoLive() {
    // 1. Obtener datos del formulario manual
    const nombreAtleta = document.getElementById('inputBuscarAtleta').value || 'Atleta No Seleccionado';
    const distancia = parseInt(document.getElementById('distancia_m').value) || 0;
    const estilo = document.getElementById('estilo').value || 'Desconocido';
    const piscinaStr = document.getElementById('tipo_piscina').value;
    const idAtleta = document.getElementById('id_atleta').value;
    
    if (distancia === 0) {
        Swal.fire({ icon: 'warning', title: 'Faltan Datos', text: 'Seleccione la distancia y piscina antes de iniciar el cronómetro en vivo.' });
        return;
    }

    if (!idAtleta) {
    Swal.fire({ icon: 'warning', title: 'Faltan Datos', text: 'Seleccione un atleta antes de iniciar el cronómetro.' });
    return;
    }


    // 2. Configurar la lógica matemática
    confPiscina = piscinaStr === '25m' ? 25 : 50;
    confDistancia = distancia;
    totalTramosEsperados = 1 + (confDistancia / 25); 
    tramoActual = 0;
    registrosSplits = [];

    // 3. Hidratar la interfaz del Modal Full Screen
    document.getElementById('cronoAtletaNombre').textContent = nombreAtleta;
    document.getElementById('cronoPruebaInfo').textContent = `${distancia}m ${estilo} - Piscina ${confPiscina}m`;
    document.getElementById('contadorVueltasCrono').textContent = `0 / ${totalTramosEsperados - 1} Tramos`;
    
    reiniciarCrono();

    enviarLatidoTelemetria('iniciando', 0, 0);

    // 4. Mostrar el Modal
    const modal = document.getElementById('modalCronoEnVivo');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.remove('opacity-0'), 10);
}

function cerrarModalCronoLive() {
    if(cronoActivo) {
        if(!confirm("El cronómetro está corriendo. ¿Desea descartar la medición?")) return;
    }
    
    // 1. ÚNICA PETICIÓN A LA BD: Limpieza absoluta del Live
    enviarLatidoTelemetria('cancelado', 0, 0);
    
    // 2. PURGA TOTAL: Limpiamos la pantalla y variables antes de que se oculte
    reiniciarCrono();

      // 3. 🧹 LIMPIAR MEMORIA SEGURA (para que no interfiera en próximos usos)
    CronometroSeguro.limpiar();
    
    // 4. Cerramos el modal visualmente
    const modal = document.getElementById('modalCronoEnVivo');
    modal.classList.add('opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

// ---------------------------------------------------------
// Lógica de Doble Clic (Split Continuo vs Viraje en Pared)
// ---------------------------------------------------------
function accionarCrono() {
    const tiempoActual = performance.now();
    const btnIcon = document.querySelector('#btnAccionCrono i');
    const btnText = document.getElementById('txtBtnAccionCrono');
    const btnCrono = document.getElementById('btnAccionCrono');

    if (estadoCrono === 'ESPERANDO') {
        // ========== ESTADO 1: INICIAR (Suena la bocina) ==========
        cronoActivo = true;
        estadoCrono = 'EN_CURSO';
        distanciaActual = 0;
        tiempoInicio = tiempoActual;
        animacionReloj = requestAnimationFrame(actualizarRelojUI);

        btnCrono.className = "w-full py-3 sm:py-4 bg-purple-500 hover:bg-purple-400 active:bg-purple-600 text-white rounded-2xl shadow-[0_0_40px_rgba(168,85,247,0.3)] transition-all flex flex-col items-center justify-center group cursor-pointer border-2 border-purple-300/50";
        btnIcon.className = "fas fa-bolt text-2xl sm:text-3xl lg:text-4xl mb-0.5 sm:mb-1 group-active:scale-90 transition-transform";
        btnText.textContent = "Tomar Reacción (Salto)";
        document.getElementById('cronoEstadoTexto').textContent = "Prueba en Curso";
        document.getElementById('btnReiniciarCrono').classList.remove('hidden');

        // <-- LATIDO TELEMETRÍA: Se encendió el reloj, arranca la prueba
        enviarLatidoTelemetria('en_curso', 0, 0);

    } else if (estadoCrono === 'EN_CURSO' && distanciaActual === 0) {
        // ========== ESTADO 2: TOMAR REACCIÓN (Sale del bloque) ==========
        const transcurrido = tiempoActual - tiempoInicio;
        registrosCrono.push({ tipo: 'reaccion', distancia: 0, tiempoMs: transcurrido, formato: formatearMilisegundos(transcurrido) });
        agregarItemListaCrono("Reacción (Salida)", "fa-bolt", "bg-purple-500", formatearMilisegundos(transcurrido));

        // <-- LATIDO TELEMETRÍA: Avisamos que el atleta está nadando
        enviarLatidoTelemetria('en_curso', 0, transcurrido);

        distanciaActual += 25;
        prepararBotonSiguienteTramo(btnCrono, btnIcon, btnText);

    } else if (estadoCrono === 'EN_CURSO' && distanciaActual > 0) {
        // ========== ESTADO 3: LLEGA A UNA MARCA DE 25m ==========
        const transcurrido = tiempoActual - tiempoInicio;
        
        registrosCrono.push({ tipo: 'split', distancia: distanciaActual, tiempoMs: transcurrido, formato: formatearMilisegundos(transcurrido) });

        const esPared = (distanciaActual % confPiscina === 0) && (distanciaActual < confDistancia);
        const esLlegada = (distanciaActual === confDistancia);

        if (esLlegada) {
            // LLEGÓ A LA META FINAL
            estadoCrono = 'FINALIZADO';
            cronoActivo = false;
            cancelAnimationFrame(animacionReloj);
            agregarItemListaCrono(`Llegada Final (${confDistancia}m)`, "fa-flag-checkered", "bg-emerald-500", formatearMilisegundos(transcurrido));

            btnCrono.className = "w-full py-3 sm:py-4 bg-gray-700 text-gray-400 rounded-2xl transition-all flex flex-col items-center justify-center cursor-not-allowed border-2 border-gray-600";
            btnIcon.className = "fas fa-flag-checkered text-3xl sm:text-4xl lg:text-5xl mb-1";
            btnText.textContent = "Prueba Finalizada";
            document.getElementById('cronoEstadoTexto').textContent = "Resultados Listos";
            document.getElementById('btnTransferirCrono').classList.remove('hidden');

            // <-- LATIDO TELEMETRÍA: Prueba finalizada, se limpia el público en unos segundos
            enviarLatidoTelemetria('finalizado', distanciaActual, transcurrido);

        } else if (esPared) {
            // ENTRA EN LA PARED
            estadoCrono = 'EN_VIRAJE';
            tiempoToquePared = tiempoActual; 
            agregarItemListaCrono(`Toca Pared (${distanciaActual}m)`, "fa-hand-paper", "bg-amber-500", formatearMilisegundos(transcurrido));

           btnCrono.className = "w-full py-3 sm:py-4 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white rounded-2xl shadow-[0_0_40px_rgba(245,158,11,0.3)] transition-all flex flex-col items-center justify-center group cursor-pointer border-2 border-amber-300/50";
            btnIcon.className = "fas fa-sign-out-alt text-3xl sm:text-4xl lg:text-5xl mb-1 group-active:scale-90 transition-transform";
            btnText.textContent = "Suelta la Pared (Fin Viraje)";

            // <-- LATIDO TELEMETRÍA: Avisamos al público que está en viraje
            enviarLatidoTelemetria('en_viraje', distanciaActual, transcurrido);

        } else {
            // MARCA VIRTUAL 
            agregarItemListaCrono(`Split (${distanciaActual}m)`, "fa-ruler-horizontal", "bg-indigo-500", formatearMilisegundos(transcurrido));
            
            // <-- LATIDO TELEMETRÍA: Split normal
            enviarLatidoTelemetria('en_curso', distanciaActual, transcurrido);
            
            distanciaActual += 25;
            prepararBotonSiguienteTramo(btnCrono, btnIcon, btnText);
        }

    } else if (estadoCrono === 'EN_VIRAJE') {
        // ========== ESTADO 4: SEGUNDO CLIC (Sale de la Pared) ==========
        const tiempoVirajeMs = tiempoActual - tiempoToquePared;
        const transcurridoTotal = tiempoActual - tiempoInicio;
        const formatoSegundos = (tiempoVirajeMs / 1000).toFixed(2);
        
        registrosCrono.push({ tipo: 'viraje', distancia: distanciaActual, tiempoMs: tiempoVirajeMs, formato: formatoSegundos });
        agregarItemListaCrono(`Tiempo de Viraje (${distanciaActual}m)`, "fa-undo", "bg-orange-500", formatoSegundos + 's');

        // <-- LATIDO TELEMETRÍA: Salió de la pared, sigue nadando
        enviarLatidoTelemetria('en_curso', distanciaActual, transcurridoTotal);

        estadoCrono = 'EN_CURSO';
        distanciaActual += 25;
        prepararBotonSiguienteTramo(btnCrono, btnIcon, btnText);
    }
}

// Helper: Configura visualmente el botón para la siguiente acción

function prepararBotonSiguienteTramo(btnCrono, btnIcon, btnText) {
    const esPared = (distanciaActual % confPiscina === 0) && (distanciaActual < confDistancia);
    const esLlegada = (distanciaActual === confDistancia);

    if (esLlegada) {
        btnIcon.className = "fas fa-flag-checkered text-2xl sm:text-3xl lg:text-4xl mb-0.5 sm:mb-1 group-active:scale-90 transition-transform";
        btnText.textContent = `Llegada Final (${distanciaActual}m)`;
    } else if (esPared) {
        btnIcon.className = "fas fa-hand-paper text-2xl sm:text-3xl lg:text-4xl mb-0.5 sm:mb-1 group-active:scale-90 transition-transform";
        btnText.textContent = `Toca Pared (${distanciaActual}m)`;
    } else {
        btnIcon.className = "fas fa-ruler-horizontal text-2xl sm:text-3xl lg:text-4xl mb-0.5 sm:mb-1 group-active:scale-90 transition-transform";
        btnText.textContent = `Marca Split (${distanciaActual}m)`;
    }
}


/* function prepararBotonSiguienteTramo(btnCrono, btnIcon, btnText) {
    const esPared = (distanciaActual % confPiscina === 0) && (distanciaActual < confDistancia);
    const esLlegada = (distanciaActual === confDistancia);

    btnCrono.className = "flex-1 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white rounded-2xl shadow-[0_0_40px_rgba(79,70,229,0.3)] transition-all flex flex-col items-center justify-center group cursor-pointer border-2 border-indigo-400/50";

    if (esLlegada) {
        btnIcon.className = "fas fa-flag-checkered text-4xl sm:text-5xl mb-2 group-active:scale-90 transition-transform";
        btnText.textContent = `Llegada Final (${distanciaActual}m)`;
    } else if (esPared) {
        btnIcon.className = "fas fa-hand-paper text-4xl sm:text-5xl mb-2 group-active:scale-90 transition-transform";
        btnText.textContent = `Toca Pared (${distanciaActual}m)`;
    } else {
        btnIcon.className = "fas fa-ruler-horizontal text-4xl sm:text-5xl mb-2 group-active:scale-90 transition-transform";
        btnText.textContent = `Marca Split (${distanciaActual}m)`;
    }
} */

// Helper: Pinta el elemento en la lista del cronómetro
/* function agregarItemListaCrono(etiqueta, icono, colorBadge, formatoFinal) {
    const listaHtml = document.getElementById('listaTiemposCrono');
    if (distanciaActual === 0 && estadoCrono !== 'EN_VIRAJE') listaHtml.innerHTML = ''; 

    const itemHtml = `
        <li class="flex justify-between items-center bg-white/10 hover:bg-white/15 p-3 rounded-lg border border-white/5 transition animate-fade-in">
            <div class="flex items-center gap-3">
                <span class="${colorBadge} text-white text-[10px] font-bold px-2 py-1 rounded w-8 text-center"><i class="fas ${icono}"></i></span>
                <span class="text-white text-sm font-medium">${etiqueta}</span>
            </div>
            <span class="text-white font-mono font-bold text-sm tracking-wider">${formatoFinal}</span>
        </li>
    `;
    listaHtml.insertAdjacentHTML('afterbegin', itemHtml);
} */

    function agregarItemListaCrono(etiqueta, icono, colorBadge, formatoFinal) {
    const listaHtml = document.getElementById('listaTiemposCrono');
    if (distanciaActual === 0 && estadoCrono !== 'EN_VIRAJE') listaHtml.innerHTML = ''; 

    const itemHtml = `
        <li class="flex justify-between items-center bg-white dark:bg-white/10 hover:bg-gray-100 dark:hover:bg-white/15 p-3 rounded-lg border border-gray-200 dark:border-white/5 shadow-sm dark:shadow-none transition-colors duration-300 animate-fade-in">
            <div class="flex items-center gap-3">
                <span class="${colorBadge} text-white text-[10px] font-bold px-2 py-1 rounded w-8 text-center"><i class="fas ${icono}"></i></span>
                <span class="text-gray-800 dark:text-white text-sm font-medium">${etiqueta}</span>
            </div>
            <span class="text-gray-900 dark:text-white font-mono font-bold text-sm tracking-wider">${formatoFinal}</span>
        </li>
    `;
    listaHtml.insertAdjacentHTML('afterbegin', itemHtml);
}



// =====================================================================
// MÓDULO DE SEGURIDAD (ANTI-HACKING Y ANTI-CONSOLA F12)
// Adaptado para el Sistema Multi-Splits SGRD
// =====================================================================
const CronometroSeguro = (function() {
    // CAJA FUERTE: Guarda todos los registros del cronómetro
    let registrosOficialesInmutables = null; 

    return {
        // Almacena toda la carrera en RAM al finalizar
        sellarCarrera: function(arrayRegistrosCrono) {
            // Clonamos el array para evitar que se modifique por referencia
            registrosOficialesInmutables = JSON.parse(JSON.stringify(arrayRegistrosCrono));
        },

        // Devuelve el tiempo final absoluto de la carrera
        obtenerTiempoFinalMs: function() {
            if (!registrosOficialesInmutables || registrosOficialesInmutables.length === 0) return null;
            
            // Buscamos el último registro que sea 'split' y que coincida con la meta
            const llegada = registrosOficialesInmutables.find(r => r.tipo === 'split' && r.distancia === confDistancia);
            
            return llegada ? llegada.tiempoMs : null;
        },

        // Devuelve el array completo para que el backend lo procese
        obtenerArrayRegistros: function() {
            return registrosOficialesInmutables;
        },

        limpiar: function() {
            registrosOficialesInmutables = null;
        }
    };
})();

// =====================================================================
// TRANSFERENCIA AL FORMULARIO (MODIFICADA CON SEGURIDAD)
// =====================================================================
function transferirCronoAlFormulario() {
    // 1. 🔒 COPIA SEGURA: Guardamos los registros ANTES de cerrar el modal
    const copiaRegistros = [...registrosCrono];
    
    // 2. Verificar que hay datos
    if (!copiaRegistros || copiaRegistros.length === 0) {
        UI.error('Sin datos', 'No se registraron tiempos en el cronómetro.');
        return;
    }

    // 3. Cerrar el modal (esto reinicia y limpia registrosCrono, pero nosotros usamos la copia)
    cerrarModalCronoLive();

    // 4. 🔒 SELLAR EN MEMORIA: Guardamos la copia en el módulo de seguridad (para el envío)
    CronometroSeguro.sellarCarrera(copiaRegistros);

    // 5. Restaurar UI del formulario manual
    document.getElementById('contenedorTiemposManuales').classList.remove('hidden');
    document.getElementById('btnIrCrono').classList.add('hidden');
    document.getElementById('btnGuardar').classList.remove('hidden');
    document.getElementById('tiempo_final_humano').required = true;

    // 6. Regenerar las cajas de splits según distancia y piscina
    generarCajasSplits();

    // 7. Formateadores
    const formatoFinalEstricto = (ms) => {
        const totalCent = Math.floor(ms / 10);
        const cent = totalCent % 100;
        const segTotales = Math.floor(totalCent / 100);
        const seg = segTotales % 60;
        const min = Math.floor(segTotales / 60);
        return `${min.toString().padStart(2, '0')}:${seg.toString().padStart(2, '0')}.${cent.toString().padStart(2, '0')}`;
    };

    const formatoParcialEstricto = (centesimas) => {
        const seg = Math.floor(centesimas / 100);
        const cent = centesimas % 100;
        return `${seg.toString().padStart(2, '0')}.${cent.toString().padStart(2, '0')}`;
    };

    // 8. Llenar los inputs con los datos de la copia
    let centesimasAcumuladas = 0;

    copiaRegistros.forEach(registro => {
        const centesimasActuales = Math.floor(registro.tiempoMs / 10);
        const valorFormateado = formatoParcialEstricto(centesimasActuales);

        if (registro.tipo === 'reaccion') {
            const inputReaccion = document.getElementById('tiempo_reaccion_seg');
            if (inputReaccion) {
                inputReaccion.value = valorFormateado;
                inputReaccion.setAttribute('readonly', 'true');
                inputReaccion.classList.add('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
            }
        } else if (registro.tipo === 'viraje') {
            const inputViraje = document.querySelector(`[name="virajes[${registro.distancia}]"]`);
            if (inputViraje) {
                inputViraje.value = valorFormateado;
                inputViraje.setAttribute('readonly', 'true');
                inputViraje.classList.add('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
            }
        } else if (registro.tipo === 'split') {
            // Calcular el tiempo de este tramo (diferencia con el anterior)
            const lapCentesimas = centesimasActuales - centesimasAcumuladas;
            centesimasAcumuladas = centesimasActuales;

            const inputSplit = document.querySelector(`[name="splits[${registro.distancia}]"]`);
            if (inputSplit) {
                inputSplit.value = formatoParcialEstricto(lapCentesimas);
                inputSplit.setAttribute('readonly', 'true');
                inputSplit.classList.add('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
            }

            // Si es el split final (distancia == confDistancia), llenar el tiempo final
            if (registro.distancia === confDistancia) {
                const inputFinalH = document.getElementById('tiempo_final_humano');
                const inputFinalS = document.getElementById('tiempo_final_seg');
                if (inputFinalH) {
                    inputFinalH.value = formatoFinalEstricto(registro.tiempoMs);
                    inputFinalH.setAttribute('readonly', 'true');
                    inputFinalH.classList.add('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80', 'font-bold', 'text-indigo-700');
                }
                if (inputFinalS) inputFinalS.value = valorFormateado;
            }
        }
    });


    // 9. Bloquear inputs de tiempo (excepto brazadas_por_largo)
document.querySelectorAll('#contenedorTiemposManuales input, #rejillaSplits input').forEach(input => {
    if (input.id !== 'brazadas_por_largo') {
        input.setAttribute('readonly', 'true');
        input.classList.add('bg-slate-200', 'dark:bg-slate-700', 'cursor-not-allowed', 'opacity-80');
    }
});

    // 10. Ejecutar validación de coherencia para actualizar colores
    validarCoherenciaMatematica();

    // 11. Forzar validación de cada campo de tiempo
const camposTiempo = document.querySelectorAll(
    '#contenedorTiemposManuales input, #rejillaSplits input'
);
camposTiempo.forEach(input => {
    if (input.hasAttribute('data-validar')) {
        // Llamamos directamente al validador para que evalúe el campo y actualice su estado
        Validador.validarCampo(input);
    }
});


}


function reiniciarCrono() {
    // 1. Apagar motores y animación
    cronoActivo = false;
    estadoCrono = 'ESPERANDO';
    cancelAnimationFrame(animacionReloj);
    
    // 2. Resetear TODAS las variables matemáticas a cero absoluto
    tiempoInicio = 0;
    tiempoToquePared = 0;
    distanciaActual = 0;
    tramoActual = 0;
    registrosCrono = [];
    if (typeof registrosSplits !== 'undefined') registrosSplits = []; // Por seguridad
    
    // 3. Limpiar la pantalla del cronómetro (Forzamos los ceros)
    document.getElementById('displayReloj').textContent = "00:00.00";
    document.getElementById('cronoEstadoTexto').textContent = "Esperando Inicio";
    
    // 4. Restaurar el botón principal a su estado "Play" original
    const btnCrono = document.getElementById('btnAccionCrono');
    btnCrono.className = "flex-1 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl shadow-[0_0_40px_rgba(16,185,129,0.3)] transition-all flex flex-col items-center justify-center group cursor-pointer border-2 border-emerald-400/50";
    document.querySelector('#btnAccionCrono i').className = "fas fa-play text-4xl sm:text-5xl mb-2 group-active:scale-90 transition-transform";
    document.getElementById('txtBtnAccionCrono').textContent = "Iniciar Prueba";
    
    document.getElementById('btnReiniciarCrono').classList.add('hidden');
    document.getElementById('btnTransferirCrono').classList.add('hidden');
    
    // Plantilla inicial dinámica
    document.getElementById('listaTiemposCrono').innerHTML = `
        <li class="flex justify-between items-center bg-gray-200 dark:bg-white/5 p-3 rounded-lg border border-gray-300 dark:border-white/5 opacity-60 transition-colors duration-300">
            <div class="flex items-center gap-3">
                <span class="bg-gray-500 dark:bg-gray-700 text-white text-[10px] font-bold px-2 py-1 rounded"><i class="fas fa-hourglass-start"></i></span>
                <span class="text-gray-600 dark:text-gray-400 text-sm font-medium">Presione INICIAR cuando suene la bocina.</span>
            </div>
            <span class="text-gray-500 dark:text-gray-400 font-mono font-bold text-sm">--:--.--</span>
        </li>`;
}

// =====================================================================
// FUNCIÓN PARA EL BOTÓN "REINICIAR" (Sincroniza UI y Base de Datos)
// =====================================================================
window.reiniciarPruebaCompleta = function() {
    const confirmacion = confirm("¿Estás seguro de reiniciar la prueba desde cero? El atleta volverá al taco de salida.");
    
    if (confirmacion) {
        // 1. Limpiamos y detenemos el reloj localmente
        reiniciarCrono();
        
        // 2. ¡LA CLAVE! Le decimos a la base de datos que volvemos al inicio.
        // El estado 'iniciando' es el que el live.php detecta para poner al avatar en posición de 'Taco'
        enviarLatidoTelemetria('iniciando', 0, 0);
        
        // 3. Limpiamos la memoria segura por si había guardado algún split anterior
        CronometroSeguro.limpiar();
    }
}


// =====================================================================
// DETECCIÓN DE CIERRE DE PESTAÑA / NAVEGADOR (Cancelar carrera en curso o en sus marcas)
// =====================================================================
window.addEventListener('beforeunload', function(e) {
    // Verificamos si el modal visualmente está abierto en la pantalla
    const modalLive = document.getElementById('modalCronoEnVivo');
    const cronoAbierto = modalLive && !modalLive.classList.contains('hidden');

    // Ahora actuamos si el cronómetro está corriendo, O si el modal simplemente está abierto (ESPERANDO)
    if (cronoAbierto || cronoActivo || estadoCrono === 'EN_CURSO' || estadoCrono === 'EN_VIRAJE') {
        const id_atleta = document.getElementById('id_atleta')?.value;
        
        if (id_atleta) {
            const datos = new FormData();
            datos.append('accion', 'sync_telemetria');
            datos.append('id_atleta', id_atleta);
            datos.append('distancia_total', confDistancia);
            datos.append('tipo_piscina', confPiscina + 'm');
            datos.append('estilo', document.getElementById('estilo')?.value || 'Libre');
            // Forzamos el estado cancelado para limpiar la BD
            datos.append('estado_carrera', 'cancelado');
            datos.append('ultima_distancia_recorrida_m', distanciaActual || 0);
            datos.append('ultimo_tiempo_parcial_ms', (tiempoInicio > 0 ? performance.now() - tiempoInicio : 0));
            
            const url = `${API_URL}&accion=sync_telemetria`;
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, datos);
            } else {
                fetch(url, { method: 'POST', body: datos, keepalive: true }).catch(() => {});
            }
            
            CronometroSeguro.limpiar();
        }
    }
});

window.addEventListener('pagehide', function(e) {
    const modalLive = document.getElementById('modalCronoEnVivo');
    const cronoAbierto = modalLive && !modalLive.classList.contains('hidden');

    if (cronoAbierto || cronoActivo || estadoCrono === 'EN_CURSO' || estadoCrono === 'EN_VIRAJE') {
        const id_atleta = document.getElementById('id_atleta')?.value;
        if (id_atleta) {
            const datos = new FormData();
            datos.append('accion', 'sync_telemetria');
            datos.append('id_atleta', id_atleta);
            datos.append('distancia_total', confDistancia);
            datos.append('tipo_piscina', confPiscina + 'm');
            datos.append('estilo', document.getElementById('estilo')?.value || 'Libre');
            datos.append('estado_carrera', 'cancelado');
            datos.append('ultima_distancia_recorrida_m', distanciaActual || 0);
            datos.append('ultimo_tiempo_parcial_ms', (tiempoInicio > 0 ? performance.now() - tiempoInicio : 0));
            
            const url = `${API_URL}&accion=sync_telemetria`;
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, datos);
            } else {
                fetch(url, { method: 'POST', body: datos, keepalive: true }).catch(() => {});
            }
            CronometroSeguro.limpiar();
        }
    }
});

// ---------------------------------------------------------
// Helpers de Pintado y Formato
// ---------------------------------------------------------
function actualizarRelojUI() {
    if (!cronoActivo) return;
    
    const transcurrido = performance.now() - tiempoInicio;
    document.getElementById('displayReloj').textContent = formatearMilisegundos(transcurrido);
    
    animacionReloj = requestAnimationFrame(actualizarRelojUI);
}

function formatearMilisegundos(ms) {
    const totalCentesimas = Math.floor(ms / 10);
    const centesimas = totalCentesimas % 100;
    const totalSegundos = Math.floor(totalCentesimas / 100);
    const segundos = totalSegundos % 60;
    const minutos = Math.floor(totalSegundos / 60);

    const strMinutos = minutos.toString().padStart(2, '0');
    const strSegundos = segundos.toString().padStart(2, '0');
    const strCentesimas = centesimas.toString().padStart(2, '0');

    if (minutos > 0) {
        return `${strMinutos}:${strSegundos}.${strCentesimas}`;
    } else {
        return `${strSegundos}.${strCentesimas}`;
    }
}



// =====================================================================
// ENRUTADOR DE INTERFAZ: MANUAL vs EN VIVO
// =====================================================================

window.iniciarRegistroMarca = function() {
    // 1. Abrir el modal base y limpiar los campos
    abrirModalMarca(); 
    
    // 2. Leer la preferencia del usuario
    const modoCrono = localStorage.getItem('sgrd_crono_mode') || 'manual';
    
    // 3. Capturar los elementos de la interfaz
    const contenedorManual = document.getElementById('contenedorTiemposManuales');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnIrCrono = document.getElementById('btnIrCrono');
    const modalTitulo = document.getElementById('modalTitulo');

    // 4. Adaptar la interfaz dinámicamente
    if (modoCrono === 'live') {
        contenedorManual.classList.add('hidden');
        btnGuardar.classList.add('hidden');
        btnIrCrono.classList.remove('hidden');
        
        modalTitulo.innerHTML = '<i class="fas fa-bolt text-amber-500"></i> Configurar Prueba en Vivo';
        document.getElementById('tiempo_final_humano').required = false; 
    } else {
        contenedorManual.classList.remove('hidden');
        btnGuardar.classList.remove('hidden');
        btnIrCrono.classList.add('hidden');
        
        modalTitulo.innerHTML = '<i class="fas fa-stopwatch text-emerald-400"></i> Registrar Control de Tiempo';
        document.getElementById('tiempo_final_humano').required = true;
    }
};

function cerrarModalVer() {
    document.getElementById('modalVer').classList.add('hidden');
    if (instanciaGrafica) {
        instanciaGrafica.destroy();
        instanciaGrafica = null; 
    }
}

// =====================================================================
// AUDITORÍA Y ESTADOS: ELIMINAR Y REACTIVAR 
// =====================================================================

async function eliminarMarca(id_marca) {
    const alerta = await UI.pedirJustificacion(
        'Archivar Registro de Tiempo',
        'Indique el motivo exacto de la anulación (Ej: Descalificación, Fallo de cronómetro):',
        'Escriba la justificación detallada aquí...'
    );

    if (alerta.isConfirmed && alerta.value) {
        let datosDelete = new FormData();
        datosDelete.append('accion', 'eliminar');
        datosDelete.append('id_marca', id_marca);
        datosDelete.append('motivo_eliminacion', alerta.value); 
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Archivado', 'El registro y su justificación han sido guardados en el historial.');
            cargarTablaMarcas();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo Archivar el registro.');
        }
    }
}

async function reactivarMarca(id_marca) {
    const confirmacion = await UI.confirmar(
        '¿Restaurar Marca?',
        'Este registro volverá a ser oficial y visible en los perfiles y estadísticas del atleta.'
    );
    
    if (confirmacion.isConfirmed) {
        let datosReactivar = new FormData();
        datosReactivar.append('accion', 'reactivar');
        datosReactivar.append('id_marca', id_marca);
        
        const resultado = await peticionAjax('reactivar', datosReactivar);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Restaurado', 'El registro vuelve a estar activo.');
            cargarTablaMarcas();
        } else {
            UI.error('Error', 'No se pudo procesar la reactivación.');
        }
    }
}

// =====================================================================
// INICIALIZADOR
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {

   // 1. LEER LA URL AL ENTRAR A LA PÁGINA
    const urlParamsInicio = new URLSearchParams(window.location.search);
    const estadoDesdeUrl = urlParamsInicio.get('estado');

    // 2. Si la URL exige 'Inactivo', forzamos el DOM antes de cargar nada
    if (estadoDesdeUrl) {
        const selectEstado = document.getElementById('filtroEstado');
        if (selectEstado) {
            selectEstado.value = estadoDesdeUrl;
        }
    }

    Validador.vincularTiempoReal(document.getElementById('formMarca'));
    cargarFiltroAtletas();
    configurarExclusividadSelects();
    cargarSelectsContexto();
    cargarTablaMarcas();
});