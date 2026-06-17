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
    formMarca.reset(); 
    try { Validador.limpiarEstilos(formMarca); } catch(e) {}
    
    document.getElementById('id_marca').value = '';
    document.getElementById('accion_form').value = 'registrar';
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.innerHTML = 'GUARDAR REGISTRO <i class="fas fa-save ml-2"></i>';
    btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
    btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
    
    const rejillaSplits = document.getElementById('rejillaSplits');
    if(rejillaSplits) rejillaSplits.innerHTML = '';

    const inputAtleta = document.getElementById('inputBuscarAtleta');
    inputAtleta.readOnly = false; 
    inputAtleta.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-gray-800'); 
    document.getElementById('btnLimpiarAtleta').classList.add('hidden'); 
    modalMarca.classList.remove('hidden');
    setTimeout(() => {
        modalMarca.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    // cargarAtletasBuscador();

    // =====================================================================
    // MODO EDICIÓN: Si recibimos un ID, la función "Muta" el formulario
    // =====================================================================
    if (id_marca) {
        const data = await peticionAjax(`obtenerDetalleMarca&id=${id_marca}`);
        
        if (!data) {
            UI.error('Error', 'No se pudieron cargar los datos para edición.');
            cerrarModalMarca();
            return;
        }

        document.getElementById('id_marca').value = data.id_marca;
        
        document.querySelector('[name="fecha"]').value = data.fecha;
        document.querySelector('[name="estilo"]').value = data.estilo;
        document.querySelector('[name="tiempo_final_seg"]').value = data.tiempo_final_seg;
        
        if (typeof formatearTiempoDesdeSegundos === 'function') {
            document.getElementById('tiempo_final_humano').value = formatearTiempoDesdeSegundos(data.tiempo_final_seg);
        } else {
            document.getElementById('tiempo_final_humano').value = data.tiempo_final_seg;
        }

        document.querySelector('[name="tiempo_reaccion_seg"]').value = data.tiempo_reaccion_seg || '';
        document.querySelector('[name="tiempo_viraje_seg"]').value = data.tiempo_viraje_seg || '';
        document.querySelector('[name="nivel_evento"]').value = data.nivel_evento;
        document.querySelector('[name="observaciones"]').value = data.observaciones || '';

       
        document.getElementById('id_atleta').value = data.id_atleta;
        inputAtleta.value = `${data.nombre_atleta} (CI: ${data.cedula})`;
        
        inputAtleta.readOnly = true; 
        inputAtleta.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-800'); 
        document.getElementById('btnLimpiarAtleta').classList.add('hidden'); 


        // Llenamos el SWOLF
        const inputBrazadas = document.querySelector('[name="brazadas_por_largo"]');
        if (inputBrazadas) {
            inputBrazadas.value = data.swolf_data ? data.swolf_data.num_brazadas : '';
        }

        const selectDistancia = document.querySelector('[name="distancia_m"]');
        const selectPiscina = document.querySelector('[name="tipo_piscina"]');
        
        selectDistancia.value = data.distancia_m;
        selectPiscina.value = data.tipo_piscina;

        selectDistancia.dispatchEvent(new Event('change'));

        if (data.splits && data.splits.length > 0) {
            data.splits.forEach(split => {
                const inputSplit = document.querySelector(`[name="splits[${split.distancia_parcial_m}]"]`);
                if (inputSplit) inputSplit.value = split.tiempo_parcial_seg;
            });
        }

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

/* async function cargarAtletasBuscador() {
    const respuesta = await peticionAjax('listarAtletasSelect');
    if (respuesta) {
        atletasGlobal = respuesta;
        
    }
} */

// Función para dibujar los cuadritos de los atletas en la lista
function renderizarDropdown(lista) {
    ulAtletas.innerHTML = '';
    
    if (lista.length === 0) {
        ulAtletas.innerHTML = '<li class="p-4 text-gray-500 text-center text-xs">No se encontraron coincidencias</li>';
        return;
    }

    lista.forEach(atleta => {
        const li = document.createElement('li');
        li.className = "p-3 hover:bg-indigo-600/20 hover:text-indigo-300 cursor-pointer transition-colors flex justify-between items-center";
        li.innerHTML = `
            <div>
                <div class="font-bold text-white">${atleta.nombres} ${atleta.apellidos}</div>
                <div class="text-[10px] text-gray-500 font-mono mt-0.5">C.I: ${atleta.cedula}</div>
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
    inputBuscar.classList.add('text-emerald-400', 'font-bold'); // Feedback visual
    inputBuscar.setAttribute('readonly', true); // Bloqueamos escritura
    
    dropdown.classList.add('hidden');
    btnLimpiar.classList.remove('hidden');
}

btnLimpiar.onclick = () => {
    inputIdOculto.value = '';
    inputBuscar.value = '';
    inputBuscar.classList.remove('text-emerald-400', 'font-bold');
    inputBuscar.removeAttribute('readonly');
    btnLimpiar.classList.add('hidden');
    inputBuscar.focus();
};

inputBuscar.addEventListener('input', (e) => {
    // 1. EXPRESIÓN REGULAR: Solo letras (incluyendo acentos/ñ), números, espacios y guiones.
    // Lo que no coincida con esto, se reemplaza por vacío ('') instantáneamente.
    e.target.value = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\-\s]/g, '');

    const texto = e.target.value.toLowerCase();
    
    // 2. Si el usuario borra todo o edita, limpiamos el ID oculto para obligarlo a seleccionar de nuevo
    inputIdOculto.value = '';
    inputBuscar.classList.remove('text-emerald-400', 'font-bold');

    // 3. Filtrado lógico
    const filtrados = atletasGlobal.filter(a => 
        a.nombres.toLowerCase().includes(texto) || 
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.toLowerCase().includes(texto) // Puesto en toLowerCase() por si escriben "v-"
    );
    
    // 4. Mostrar resultados
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
});

/* inputBuscar.addEventListener('input', (e) => {
    const texto = e.target.value.toLowerCase();
    
    const filtrados = atletasGlobal.filter(a => 
        a.nombres.toLowerCase().includes(texto) || 
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.includes(texto)
    );
    
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
}); */

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

inputFecha.addEventListener('change', function() {
    const value = this.value;
    if (!value) return;

    // 1. Asegurar formato YYYY-MM-DD (año de 4 dígitos)
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        // Si no cumple el formato, no hacemos nada (podría estar escribiendo)
        return;
    }

    // 2. Validar que la fecha sea real (ej. 31 de febrero)
    const date = new Date(value);
    if (isNaN(date.getTime())) {
        UI.error('Fecha Inválida', 'La fecha ingresada no es válida.');
        // No forzamos nada, dejamos que corrija
        return;
    }

    // 3. Validar límite inferior
    if (this.min && value < this.min) {
        UI.error('Fecha Inválida', 'La fecha ingresada es anterior al inicio del evento.');
        this.value = this.min;
        return;
    }

    // 4. Validar límite superior
    if (this.max && value > this.max) {
        UI.error('Fecha Inválida', 'La fecha ingresada es en el futuro o posterior al evento.');
        this.value = this.max;
        return;
    }
});
}

function resetearContexto() {
    // Liberar evento
    selectEvento.disabled = false;
    selectEvento.classList.remove('opacity-30', 'cursor-not-allowed');
    // Liberar sesión
    selectSesion.disabled = false;
    selectSesion.classList.remove('opacity-30', 'cursor-not-allowed');
    
    // Liberar Fecha
   const inputFecha = document.getElementById('fecha');
    inputFecha.value = "";
    inputFecha.min = "";
    inputFecha.max = obtenerFechaLocal(); // ¡Adiós al .toISOString()!
    inputFecha.readOnly = false;
    inputFecha.classList.remove('opacity-70', 'pointer-events-none');

    resetearBuscadorAtleta();
}

/* function configurarExclusividadSelects() {
    selectSesion.addEventListener('change', function() {
        if (this.value !== "") {
            // Bloqueamos evento
            selectEvento.value = "";
            selectEvento.disabled = true;
            selectEvento.classList.add('opacity-30', 'cursor-not-allowed');
            
            // Cargar atletas de esta sesión
            cargarAtletasPorContexto('sesion', this.value);
        } else {
            // Liberamos evento
            selectEvento.disabled = false;
            selectEvento.classList.remove('opacity-30', 'cursor-not-allowed');
            resetearBuscadorAtleta();
        }
    });

    selectEvento.addEventListener('change', function() {
        if (this.value !== "") {
            // Bloqueamos sesión
            selectSesion.value = "";
            selectSesion.disabled = true;
            selectSesion.classList.add('opacity-30', 'cursor-not-allowed');
            
            // Cargar atletas de este evento
            cargarAtletasPorContexto('evento', this.value);
        } else {
            // Liberamos sesión
            selectSesion.disabled = false;
            selectSesion.classList.remove('opacity-30', 'cursor-not-allowed');
            resetearBuscadorAtleta();
        }
    });
} */

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

// IMPORTANTE: Ya NO llames a cargarAtletasBuscador() en tu DOMContentLoaded. 
// Bórrala, porque ahora se cargan dinámicamente con cargarAtletasPorContexto().


// =====================================================================
// LÓGICA DE EXCLUSIVIDAD: SESIÓN VS EVENTO
// =====================================================================
/* const selectSesion = document.getElementById('id_sesion');
const selectEvento = document.getElementById('id_evento');

function configurarExclusividadSelects() {
    selectSesion.addEventListener('change', function() {
        if (this.value !== "") {
            // Si elige sesión, bloqueamos evento
            selectEvento.value = "";
            selectEvento.disabled = true;
            selectEvento.classList.add('opacity-30', 'cursor-not-allowed');
        } else {
            // Si vuelve a "Ninguna", liberamos evento
            selectEvento.disabled = false;
            selectEvento.classList.remove('opacity-30', 'cursor-not-allowed');
        }
    });

    selectEvento.addEventListener('change', function() {
        if (this.value !== "") {
            // Si elige evento, bloqueamos sesión
            selectSesion.value = "";
            selectSesion.disabled = true;
            selectSesion.classList.add('opacity-30', 'cursor-not-allowed');
        } else {
            // Si vuelve a "Ninguno", liberamos sesión
            selectSesion.disabled = false;
            selectSesion.classList.remove('opacity-30', 'cursor-not-allowed');
        }
    });
} */

// =====================================================================
// CARGA DINÁMICA DE LOS SELECTS Eventos y Sesiones
// =====================================================================
/* async function cargarSelectsContexto() {
    // 1. Cargar Sesiones (Solo mostramos las completadas o relevantes para toma de marcas)
    const resSesiones = await peticionAjax('listarSesionesSelect');
    if (resSesiones && resSesiones.length > 0) {
        selectSesion.innerHTML = '<option value="">Ninguna - No aplica</option>';
        resSesiones.forEach(s => {
            selectSesion.innerHTML += `<option value="${s.id_sesion}">${formatearFecha(s.fecha)} | ${s.grupo_nombre} (${s.tipo_sesion})</option>`;
        });
    }

    // 2. Cargar Eventos
    const resEventos = await peticionAjax('listarEventosSelect');
    if (resEventos && resEventos.length > 0) {
        selectEvento.innerHTML = '<option value="">Ninguno - No aplica</option>';
        resEventos.forEach(e => {
            selectEvento.innerHTML += `<option value="${e.id_evento}">${e.nombre}| ${e.tipo} (${e.nivel})| ${formatearFecha(e.fecha_inicio)} - ${formatearFecha(e.fecha_fin)} </option>`;
        });
    }
} */


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



const selectDistancia = document.getElementById('distancia_m');
const contenedorSplits = document.getElementById('contenedorSplits');
const rejillaSplits = document.getElementById('rejillaSplits');
const contadorSplits = document.getElementById('contadorSplits');

function generarCajasSplits() {
    const distanciaTotal = parseInt(selectDistancia.value);
    
    if (isNaN(distanciaTotal)) {
        contenedorSplits.classList.add('hidden');
        rejillaSplits.innerHTML = '';
        return;
    }

    const tamanoTramo = 25; 
    
    // Calculamos la cantidad de cajas (Ej: 100m / 25m = 4 cajas)
    const cantidadTramos = distanciaTotal / tamanoTramo;
    
    rejillaSplits.innerHTML = '';
    
    for (let i = 1; i <= cantidadTramos; i++) {
        let distanciaActual = i * tamanoTramo; // 25, 50, 75, 100...
        
        const cajaHTML = `
            <div class="relative">
                <label class="block text-[10px] text-gray-500 uppercase font-bold mb-1">
                    Parcial ${distanciaActual}m
                </label>
                <div class="relative">
                    <input type="text" 
                           name="splits[${distanciaActual}]" 
                           data-validar="requerido|decimal_tiempo" 
                           required 
                            data-nombre="Parcial de ${distanciaActual}m" 
                           placeholder="00.00" 
                           class="w-full bg-[#161430] border border-gray-700 text-emerald-400 font-mono text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-center split-input">
                    <span class="absolute right-3 top-2.5 text-gray-600 text-xs">s</span>
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

selectDistancia.addEventListener('change', generarCajasSplits);


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
    //const contenedorSplits = document.getElementById('contenedorSplits');
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


// =====================================================================
// ENVÍO DEL FORMULARIO (CREATE / UPDATE DINÁMICO)
// =====================================================================
formMarca.addEventListener('submit', async (e) => {
    e.preventDefault(); 


    const erroresFormulario = Validador.validarFormulario(formMarca);
    
    if (erroresFormulario && erroresFormulario.length > 0) {
        
        const listaErrores = Array.isArray(erroresFormulario) 
                             ? erroresFormulario.join('<br>') 
                             : erroresFormulario;

        UI.error(
            'Datos Incompletos', 
            `<div class="text-left text-sm mt-2 text-gray-300">
                <p class="mb-2 font-bold text-white">Por favor, corrige lo siguiente:</p>
                ${listaErrores}
             </div>`
        );
        
        return; 
    }

    // 1. Filtro de Seguridad: Validamos la coherencia matemática de los Splits
    if (typeof validarCoherenciaMatematica === 'function' && !validarCoherenciaMatematica()) {
        UI.error('Incoherencia Matemática', 'La suma de los parciales no coincide con el tiempo final (Tolerancia: 0.01s).');
        return;
    }

    let datosFormulario = new FormData(formMarca);
    
   
    const inputAccion = document.getElementById('accion_form');
    const accionActual = inputAccion ? inputAccion.value : 'registrar';

    datosFormulario.set('accion', accionActual);

    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...';
    btnGuardar.disabled = true;

    const resultado = await peticionAjax(accionActual, datosFormulario);

    if (resultado) {
        if (resultado.status === 'success') {
            const msjExito = (accionActual === 'actualizar') 
                             ? 'El registro ha sido actualizado correctamente.' 
                             : 'El rendimiento ha sido registrado con éxito.';
                             
            UI.exito('¡Operación Exitosa!', msjExito);
            
            cerrarModalMarca();
            cargarTablaMarcas(); 
        } 
        else if (resultado.status === 'warning') {
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Datos Incompletos', mensajesError);
        } 
        else {
           
            UI.error('Error de Sistema', resultado.message || 'Ocurrió un error inesperado al procesar los datos.');
        }
    }

   
    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});



// =====================================================================
// RENDERIZADO DE LA TABLA PRINCIPAL (READ)
// =====================================================================
async function cargarTablaMarcas() {
    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const distancia = document.getElementById('filtroDistancia')?.value || '';
    const estilo = document.getElementById('filtroEstilo')?.value || '';
    const piscina = document.getElementById('filtroPiscina')?.value || '';

    let params = new URLSearchParams({ estado: filtroEstado });
    
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (estilo) params.append('estilo', estilo);
    if (distancia) params.append('distancia', distancia);
    if (piscina) params.append('piscina', piscina);
    
    const tbody = document.getElementById('tbodyMarcas');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando marcas...</td></tr>';

    const marcas = await peticionAjax(`listarMarcas&${params.toString()}`);

    if (!marcas || marcas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-gray-500 font-mono text-xs">No hay marcas registradas en esta vista.</td></tr>';
        return;
    }

    let html = '';
    marcas.forEach(marca => {
        
        const tiempoReloj = formatearTiempoDesdeSegundos(marca.tiempo_final_seg);
        const fechaLatina = formatearFecha(marca.fecha); 

        const badgePB = (marca.es_pb == 1) 
            ? `<span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold ml-2 uppercase shadow-[0_0_10px_rgba(245,158,11,0.2)]" title="¡Mejor Marca Personal!"><i class="fas fa-star mr-1"></i>PB</span>` 
            : '';

        const botonAccion = (filtroEstado === 'Activo')
            ? `<button onclick="eliminarMarca(${marca.id_marca})" class="text-red-400 hover:bg-red-500/10 p-2 rounded-lg transition" title="Archivar Registro"><i class="fas fa-trash-alt"></i></button>`
            : `<button onclick="reactivarMarca(${marca.id_marca})" class="text-emerald-400 hover:bg-emerald-500/10 p-2 rounded-lg transition" title="Restaurar Registro"><i class="fas fa-undo"></i></button>`;
        
       
        const botonEditar = (filtroEstado === 'Activo')
            ? `<button onclick="abrirModalMarca(${marca.id_marca})" class="text-amber-400 hover:bg-amber-500/10 p-2 rounded-lg transition" title="Editar Registro de Tiempo"><i class="fas fa-edit text-base"></i></button>`
            : '';

        
        const justificacionHTML = (filtroEstado === 'Inactivo' && marca.motivo_eliminacion)
            ? `<div class="text-[9px] text-red-400 mt-1 flex items-center gap-1 w-48 leading-tight">
                <i class="fas fa-exclamation-circle"></i> Anulado: ${marca.motivo_eliminacion}
               </div>`
            : '';

        html += `
            <tr class="hover:bg-white/5 transition-colors duration-200 border-b border-[#252345]">
                
                <td class="p-4">
                    <div class="font-bold text-white text-sm">${marca.nombre_atleta}</div>
                    <div class="text-[10px] text-gray-500 font-mono mt-0.5">C.I: ${marca.cedula}</div>
                </td>
                
                <td class="p-4">
                    <div class="font-bold text-indigo-300 text-sm">${marca.distancia_m}m ${marca.estilo}</div>
                </td>
                
                <td class="p-4 text-xs text-gray-400">
                    <i class="fas fa-swimming-pool mr-1 text-gray-600"></i> ${marca.tipo_piscina}
                </td>
                
                <td class="p-4 flex items-center">
                    <span class="font-mono text-emerald-400 font-bold text-lg">${tiempoReloj}</span>
                    ${badgePB}
                </td>
                
                <td class="p-4">
                    <span class="bg-gray-800 text-gray-300 text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider font-bold">
                        ${marca.nivel_evento}
                    </span>
                    ${justificacionHTML}
                </td>
                
                <td class="p-4 text-xs font-mono text-gray-400">
                    ${fechaLatina}
                </td>
                
                <td class="p-4 text-right space-x-1">
                    <button onclick="verDetallesMarca(${marca.id_marca})" class="text-indigo-400 hover:bg-indigo-500/10 p-2 rounded-lg transition" title="Ver Análisis de Rendimiento (SWOLF y Splits)">
                        <i class="fas fa-chart-line text-base"></i>
                    </button>
                    
                    ${typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar ? `${botonEditar}${botonAccion}` : ''}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
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
        <div class="text-center p-12 text-gray-500">
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
    const tReaccion = data.tiempo_reaccion_seg ? data.tiempo_reaccion_seg + 's' : '—';
    const tViraje = data.tiempo_viraje_seg ? data.tiempo_viraje_seg + 's' : '—';
    
    let tramosHTML = '';
    if (data.splits && data.splits.length > 0) {
        data.splits.forEach(split => {
            tramosHTML += `
                <div class="bg-[#161430] border border-gray-800 p-3 rounded-xl text-center shadow-inner">
                    <p class="text-[9px] text-gray-500 uppercase font-black tracking-wider mb-0.5">${split.distancia_parcial_m} Metros</p>
                    <p class="font-mono text-xs text-emerald-400 font-bold">${parseFloat(split.tiempo_parcial_seg).toFixed(2)}s</p>
                </div>
            `;
        });
    } else {
        tramosHTML = '<div class="col-span-4 p-4 text-center text-xs text-gray-600 italic">No se recolectaron parciales en este control.</div>';
    }

    contenedor.innerHTML = `
        <div class="mb-6">
            <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold rounded-md uppercase tracking-widest">
                <i class="fas fa-microscope mr-1"></i> Telemetría Deportiva
            </span>
            <h2 class="text-xl font-bold text-white mt-2">${data.nombre_atleta}</h2>
            <p class="text-xs text-gray-400 font-mono mt-0.5">C.I: ${data.cedula} • Registro: ${formatearFecha(data.fecha)}</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Tiempo de Registro</p>
                <p class="text-base font-mono text-emerald-400 font-black">${tiempoFinalHumano}</p>
                ${data.es_pb == 1 ? '<span class="text-[9px] text-amber-400 font-bold animate-pulse"><i class="fas fa-trophy mr-1"></i>Récord (PB)</span>' : ''}
            </div>
            
            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-amber-400 uppercase font-bold tracking-wider mb-1">Índice SWOLF</p>
                <p class="text-base font-mono text-amber-400 font-black">${swolfScore}</p>
                <p class="text-[8px] text-gray-500 uppercase font-medium">Eficiencia Dinámica</p>
            </div>

            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Ciclos de Brazada</p>
                <p class="text-base font-mono text-white font-bold">${numBrazadas}</p>
                <p class="text-[8px] text-gray-500 uppercase">Por Longitud</p>
            </div>

            <div class="bg-black/30 p-3.5 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-wider mb-1">Reacción / Viraje</p>
                <p class="text-xs font-mono text-gray-300 font-bold mt-1.5">${tReaccion} | ${tViraje}</p>
                <p class="text-[8px] text-gray-500 uppercase">Bloque / Pared</p>
            </div>
        </div>

        <div class="mb-6 bg-black/10 p-4 rounded-xl border border-white/5">
            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-widest mb-3">
                <i class="fas fa-chart-bar text-emerald-400 mr-2"></i>Pacing: Desglose de Ritmo por Tramo
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                ${tramosHTML}
            </div>
        </div>

        <div class="bg-black/20 p-4 rounded-xl border border-white/5">
            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-widest mb-3">
                <i class="fas fa-chart-line text-indigo-400 mr-2"></i>Curva Histórica de Progresión (${data.distancia_m}m ${data.estilo} - Piscina ${data.tipo_piscina})
            </p>
            <div class="w-full h-44 relative">
                <canvas id="canvasEvolucion"></canvas>
            </div>
        </div>
        <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                <p class="text-[10px] uppercase text-gray-400 font-bold tracking-widest mb-3">
                    <i class="fas fa-chart-line text-indigo-500 mr-2"></i> Análisis de Ritmo y Caída de Velocidad
                </p>
                <div class="w-full h-44 relative">
                    <canvas id="graficaSplits"></canvas> 
                </div>
        </div>
    `;

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
                        grid: { color: 'rgba(255, 255, 255, 0.03)' },
                        ticks: { color: '#6b7280', font: { size: 9, family: 'monospace' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.03)' },
                        ticks: { 
                            color: '#6b7280', 
                            font: { size: 9, family: 'monospace' },
                            callback: function(val) { return val + 's'; }
                        }
                    }
                }
            }
        });
    }

    // =========================================================
    // Inicialización del Motor Gráfico: CAÍDA DE VELOCIDAD
    // =========================================================
    if (data.splits && data.splits.length > 0) {
        const ejeDistancias = data.splits.map(s => s.distancia_parcial_m + 'm');
        const ejeTiemposSplits = data.splits.map(s => parseFloat(s.tiempo_parcial_seg));

        // Destruimos la gráfica anterior para evitar el "parpadeo" (Ghosting)
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
                        ticks: { color: '#6b7280', font: { size: 9, family: 'monospace' } } 
                    },
                    y: { 
                        grid: { color: 'rgba(255, 255, 255, 0.03)' }, 
                        ticks: { color: '#6b7280', font: { size: 9, family: 'monospace' }, callback: function(val) { return val + 's'; } } 
                    }
                }
            }
        });
    }

}

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

    Validador.vincularTiempoReal(document.getElementById('formMarca'));
    cargarFiltroAtletas();
    configurarExclusividadSelects();
    cargarSelectsContexto();
    cargarTablaMarcas();

   // Bloqueo Inteligente del Calendario de Marcas (Corregido Zona Horaria)
  /*   const inputFecha = document.getElementById('fecha');
    if (inputFecha) {
        const hoy = new Date();
        const haceUnMes = new Date();
        haceUnMes.setMonth(hoy.getMonth() - 1);

        // NUEVO: Formateador estricto a YYYY-MM-DD extrayendo la hora LOCAL del sistema
        const formatoLocalISO = (fecha) => {
            const año = fecha.getFullYear();
            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
            const dia = String(fecha.getDate()).padStart(2, '0');
            return `${año}-${mes}-${dia}`;
        };

        inputFecha.max = formatoLocalISO(hoy);
        inputFecha.min = formatoLocalISO(haceUnMes);
    } */

});