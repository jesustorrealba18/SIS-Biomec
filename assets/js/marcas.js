// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================
const modalMarca = document.getElementById('modalMarca');
const formMarca = document.getElementById('formMarca');
const btnGuardar = document.getElementById('btnGuardar');

// NUEVA RUTA DIRECTA AL CONTROLADOR PIVOTE A TRAVÉS DEL INDEX:
const API_URL = 'index.php?p=marcas'; 

/**
 * Función centralizada para peticiones al servidor (Principio DRY)
 */
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


// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================

function cerrarModalMarca() {
    modalMarca.classList.add('hidden');
    modalMarca.firstElementChild.classList.add('scale-95', 'opacity-0');
    
    // 1. Resetear el formulario tradicional
    formMarca.reset();
    
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
}

// Cerrar modal con la tecla Escape
document.addEventListener('keydown', (e) => {
    if (e.key === "Escape" && !modalMarca.classList.contains('hidden')) {
        cerrarModalMarca();
    }
});

// =====================================================================
// ABRIR MODAL (INTELIGENTE: SIRVE PARA REGISTRAR Y EDITAR)
// =====================================================================
async function abrirModalMarca(idAtleta = null) {

     // 1. Reiniciamos el formulario a su estado original
    formMarca.reset(); 
    try { Validador.limpiarEstilos(formMarca); } catch(e) {}


    // 1. Mostramos el modal en pantalla
    modalMarca.classList.remove('hidden');
    setTimeout(() => {
        modalMarca.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    cargarAtletasBuscador();

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

// 1. Función para cargar los atletas desde la BD (Llamar al abrir el modal)
async function cargarAtletasBuscador() {
    // Asumiendo que tu controlador tiene una ruta para listar todos los atletas
    const respuesta = await peticionAjax('listarAtletasSelect');
    if (respuesta) {
        atletasGlobal = respuesta;
        
    }
}

// 2. Función para dibujar los cuadritos de los atletas en la lista
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
        
        // Al hacer clic en un atleta de la lista
        li.onclick = () => {
            seleccionarAtleta(atleta);
        };
        ulAtletas.appendChild(li);
    });
}

// 3. Cuando el usuario selecciona a alguien
function seleccionarAtleta(atleta) {
    inputIdOculto.value = atleta.id_atleta;
    inputBuscar.value = `${atleta.nombres} ${atleta.apellidos}`;
    inputBuscar.classList.add('text-emerald-400', 'font-bold'); // Feedback visual
    inputBuscar.setAttribute('readonly', true); // Bloqueamos escritura
    
    dropdown.classList.add('hidden');
    btnLimpiar.classList.remove('hidden');
}

// 4. Botón para limpiar y buscar a otro
btnLimpiar.onclick = () => {
    inputIdOculto.value = '';
    inputBuscar.value = '';
    inputBuscar.classList.remove('text-emerald-400', 'font-bold');
    inputBuscar.removeAttribute('readonly');
    btnLimpiar.classList.add('hidden');
    inputBuscar.focus();
};

// 5. Evento: Al escribir en el buscador
inputBuscar.addEventListener('input', (e) => {
    const texto = e.target.value.toLowerCase();
    
    // Filtramos el array buscando coincidencias en nombre, apellido o cédula
    const filtrados = atletasGlobal.filter(a => 
        a.nombres.toLowerCase().includes(texto) || 
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.includes(texto)
    );
    
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
});

// 6. Evento: Al hacer clic en el input (mostrar todos)
inputBuscar.addEventListener('focus', () => {
    if (!inputIdOculto.value) { // Solo si no ha seleccionado a nadie aún
        dropdown.classList.remove('hidden');
        renderizarDropdown(atletasGlobal);
    }
});

// 7. Cerrar la lista flotante si hace clic en cualquier otro lado de la pantalla
document.addEventListener('click', (e) => {
    if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});



// Atrapamos el select de distancia y los contenedores
const selectDistancia = document.getElementById('distancia_m');
// (Ya no necesitamos atrapar el select de tipo_piscina para esto)
const contenedorSplits = document.getElementById('contenedorSplits');
const rejillaSplits = document.getElementById('rejillaSplits');
const contadorSplits = document.getElementById('contadorSplits');

// Función centralizada para generar los splits
function generarCajasSplits() {
    const distanciaTotal = parseInt(selectDistancia.value);
    
    // Si falta la distancia, ocultamos todo
    if (isNaN(distanciaTotal)) {
        contenedorSplits.classList.add('hidden');
        rejillaSplits.innerHTML = '';
        return;
    }

    // NUEVA REGLA DEL LÍDER: Los parciales siempre son cada 25m
    const tamanoTramo = 25; 
    
    // Calculamos la cantidad de cajas (Ej: 100m / 25m = 4 cajas)
    const cantidadTramos = distanciaTotal / tamanoTramo;
    
    rejillaSplits.innerHTML = '';
    
    // Bucle mágico
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
                           placeholder="00.00" 
                           class="w-full bg-[#161430] border border-gray-700 text-emerald-400 font-mono text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-center split-input">
                    <span class="absolute right-3 top-2.5 text-gray-600 text-xs">s</span>
                </div>
            </div>
        `;
        
        rejillaSplits.innerHTML += cajaHTML;
    }

    // Actualizamos textos e interfaz
    contadorSplits.innerText = `${cantidadTramos} Tramos (Cada 25m)`;
    contenedorSplits.classList.remove('hidden');
    
    // Animación suave
    rejillaSplits.style.opacity = 0;
    setTimeout(() => {
        rejillaSplits.style.transition = "opacity 0.3s ease-in-out";
        rejillaSplits.style.opacity = 1;
    }, 50);
}

// Ahora SOLO escuchamos a la distancia para disparar la función
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

const inputTiempoHumano = document.getElementById('tiempo_final_humano');
const alertaCoherencia = document.getElementById('alertaCoherencia');
const inputTiempoSegundos = document.getElementById('tiempo_final_seg'); // El oculto para la BD

function validarCoherenciaMatematica() {
    // 1. Obtenemos el tiempo final que escribió el entrenador
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
    // Usamos 0.015 por los decimales de punto flotante en JS
    if (tiempoFinalSegundos > 0 && sumaParciales > 0) {
        if (diferencia > 0.015) {
            alertaCoherencia.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Error: Los parciales suman <b>${sumaParciales.toFixed(2)}s</b> y el final es <b>${tiempoFinalSegundos.toFixed(2)}s</b>.`;
            alertaCoherencia.classList.remove('text-emerald-400');
            alertaCoherencia.classList.add('text-red-500');
            return false; // Bloquea el envío
        } else {
            alertaCoherencia.innerHTML = `<i class="fas fa-check-circle"></i> Tiempos coherentes. (Suma: ${sumaParciales.toFixed(2)}s)`;
            alertaCoherencia.classList.remove('text-red-500');
            alertaCoherencia.classList.add('text-emerald-400');
            return true; // Permite el envío
        }
    }
    
    alertaCoherencia.innerHTML = '';
    return true; // Pasa si aún no han escrito nada
}

// 6. Ponemos a escuchar al input del tiempo final para que valide al instante
inputTiempoHumano.addEventListener('input', validarCoherenciaMatematica);

// También debemos escuchar a las cajitas dinámicas. 
// Como las cajas se crean después, usamos la técnica de Delegación de Eventos:
document.getElementById('rejillaSplits').addEventListener('input', function(e) {
    if(e.target && e.target.classList.contains('split-input')) {
        validarCoherenciaMatematica();
    }
});


// =====================================================================
// ENVÍO DEL FORMULARIO (CREATE / UPDATE)
// =====================================================================
formMarca.addEventListener('submit', async (e) => {
    e.preventDefault(); // Evitamos que la página se recargue

    // 1. Filtro de Seguridad: Validamos la coherencia matemática de los Splits
    // (Esta es la función CA-06.2 que creamos antes)
    if (typeof validarCoherenciaMatematica === 'function' && !validarCoherenciaMatematica()) {
        UI.error('Incoherencia Matemática', 'La suma de los parciales no coincide con el tiempo final (Tolerancia: 0.01s).');
        return;
    }

    // 2. Recolectamos absolutamente todo el formulario
    // ¡Magia!: FormData recoge automáticamente el array de splits[25], splits[50], etc.
    let datosFormulario = new FormData(formMarca);
    
    // Le indicamos al controlador qué ruta POST debe tomar
    datosFormulario.append('accion', 'guardar');

    // Cambiamos el botón a estado de "Cargando"
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESANDO...';
    btnGuardar.disabled = true;

    // 3. Enviamos la petición AJAX al controlador
    const resultado = await peticionAjax('guardar', datosFormulario);

    // 4. Procesamos la respuesta del servidor
    if (resultado) {
        if (resultado.status === 'success') {
            // Guardado exitoso
            UI.exito('¡Rendimiento Registrado!', resultado.message);
            cerrarModalMarca();
            cargarTablaMarcas(); // Descomenta esto cuando tengas la función de pintar la tabla
        } 
        else if (resultado.status === 'warning') {
            // Errores de validación (Ej: Faltó un campo)
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Faltan Datos', mensajesError);
        } 
        else {
            // Error de base de datos
            UI.error('Error de Sistema', resultado.message);
        }
    }

    // Devolvemos el botón a la normalidad
    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});


// =====================================================================
// RENDERIZADO DE LA TABLA PRINCIPAL (READ)
// =====================================================================
async function cargarTablaMarcas() {
    // Leemos si el usuario quiere ver las marcas Activas o Inactivas
    const filtroEstado = document.getElementById('filtroEstado')?.value || 'Activo';
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const distancia = document.getElementById('filtroDistancia')?.value || '';
    const estilo = document.getElementById('filtroEstilo')?.value || '';
    const piscina = document.getElementById('filtroPiscina')?.value || '';

    // 2. Construimos la cadena de parámetros URL dinámicamente
    let params = new URLSearchParams({ estado: filtroEstado });
    
    // Solo anexamos los filtros si el usuario seleccionó algo distinto a "Todos"
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (estilo) params.append('estilo', estilo);
    if (distancia) params.append('distancia', distancia);
    if (piscina) params.append('piscina', piscina);
    
    // Mostramos un esqueleto o texto de carga mientras esperamos a PHP
    const tbody = document.getElementById('tbodyMarcas');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando marcas...</td></tr>';

    // Pedimos los datos al controlador
    const marcas = await peticionAjax(`listarMarcas&${params.toString()}`);

    if (!marcas || marcas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-gray-500 font-mono text-xs">No hay marcas registradas en esta vista.</td></tr>';
        return;
    }

    let html = '';
    marcas.forEach(marca => {
        
        // 1. Convertimos el tiempo al formato humano
        const tiempoReloj = formatearTiempoDesdeSegundos(marca.tiempo_final_seg);
        const fechaLatina = formatearFecha(marca.fecha); // <--- AQUÍ LA USAS

        // 2. Lógica visual: Si la BD dijo que es PB, armamos una medallita dorada
        const badgePB = (marca.es_pb == 1) 
            ? `<span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold ml-2 uppercase shadow-[0_0_10px_rgba(245,158,11,0.2)]" title="¡Mejor Marca Personal!"><i class="fas fa-star mr-1"></i>PB</span>` 
            : '';

        // 3. Evaluamos si mostramos el botón de archivar(rojo) o reactivar(verde)
        const botonAccion = (filtroEstado === 'Activo')
            ? `<button onclick="eliminarMarca(${marca.id_marca})" class="text-red-400 hover:bg-red-500/10 p-2 rounded-lg transition" title="Archivar Registro"><i class="fas fa-trash-alt"></i></button>`
            : `<button onclick="reactivarMarca(${marca.id_marca})" class="text-emerald-400 hover:bg-emerald-500/10 p-2 rounded-lg transition" title="Restaurar Registro"><i class="fas fa-undo"></i></button>`;
        // === EL TOQUE FINAL DE UX ===
        // Si estamos viendo la papelera, armamos el texto rojo con el motivo
        const justificacionHTML = (filtroEstado === 'Inactivo' && marca.motivo_eliminacion)
            ? `<div class="text-[9px] text-red-400 mt-1 flex items-center gap-1 w-48 leading-tight">
                <i class="fas fa-exclamation-circle"></i> Anulado: ${marca.motivo_eliminacion}
               </div>`
            : '';

        // 4. Armamos la fila HTML
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
                    
                    ${botonAccion}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

// 1. Función para llenar el select de atletas (Se llama una sola vez al cargar la página)
async function cargarFiltroAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    const select = document.getElementById('filtroAtleta');
    
    if (atletas && atletas.length > 0) {
        atletas.forEach(atleta => {
            // value="${atleta.id_atleta}" es la clave para la eficiencia de base de datos
            select.insertAdjacentHTML('beforeend', `<option value="${atleta.id_atleta}">${atleta.nombres} ${atleta.apellidos} - CI: ${atleta.cedula}</option>`);
        });
    }
}


// =====================================================================
// VISUALIZADOR CIENTÍFICO Y GRÁFICAS DE RENDIMIENTO (MAESTRO-DETALLE)
// =====================================================================
let instanciaGrafica = null; // Control para evitar bugs de renderizado en Chart.js
let instanciaGraficaSplits = null; 

async function verDetallesMarca(id_marca) {
    const modalVer = document.getElementById('modalVer');
    const contenedor = document.getElementById('detalleContenido');
    
    // Feedback visual de carga asíncrona
    contenedor.innerHTML = `
        <div class="text-center p-12 text-gray-500">
            <i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i>
            <p class="text-xs font-mono uppercase tracking-widest">Sincronizando métricas biomecánicas...</p>
        </div>
    `;
    
    // Desplegamos el contenedor flotante
    modalVer.classList.remove('hidden');
    
    // Solicitamos el paquete de datos unificado al Controlador
    const data = await peticionAjax(`obtenerDetalleMarca&id=${id_marca}`);
    if (!data) {
        UI.error('Error de Consulta', 'No se pudo estructurar el análisis técnico del registro.');
        cerrarModalVer();
        return;
    }

    // Traducción de formatos numéricos a métricas legibles
    const tiempoFinalHumano = formatearTiempoDesdeSegundos(data.tiempo_final_seg);
    const swolfScore = data.swolf_data ? data.swolf_data.swolf : '🚫 N/A';
    const numBrazadas = data.swolf_data ? data.swolf_data.num_brazadas : 'Sin conteo';
    const tReaccion = data.tiempo_reaccion_seg ? data.tiempo_reaccion_seg + 's' : '—';
    const tViraje = data.tiempo_viraje_seg ? data.tiempo_viraje_seg + 's' : '—';
    
    // Procesamiento dinámico de la rejilla de splits (RF-06)
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

    // Inyección estructural en el nodo del DOM
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

    // Inicialización del Motor Gráfico (Chart.js) si posee historial acumulado
    if (data.historial_evolucion && data.historial_evolucion.length > 0) {
        const ejeFechas = data.historial_evolucion.map(h => formatearFecha(h.fecha));
        const ejeTiempos = data.historial_evolucion.map(h => parseFloat(h.tiempo_final_seg));

        // Liberamos memoria destruyendo la instancia previa para evitar parpadeos de caché
        if (instanciaGrafica) instanciaGrafica.destroy();

        const contextoLienzo = document.getElementById('canvasEvolucion').getContext('2d');
        instanciaGrafica = new Chart(contextoLienzo, {
            type: 'line',
            data: {
                labels: ejeFechas,
                datasets: [{
                    data: ejeTiempos,
                    borderColor: '#6366f1', // Línea de tendencia Indigo
                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981', // Puntos de quiebre en Esmeralda
                    pointBorderColor: '#fff',
                    pointRadius: 3.5,
                    tension: 0.25 // Curvatura estética suavizada
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
    // Inicialización del Motor Gráfico 2: CAÍDA DE VELOCIDAD
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
                    borderColor: '#06b6d4', // Un color Cyan para diferenciarla de la otra
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
        instanciaGrafica = null; // Limpieza física del colector de basura
    }
}


// =====================================================================
// AUDITORÍA Y ESTADOS: ELIMINAR Y REACTIVAR 
// =====================================================================

async function eliminarMarca(id_marca) {
    // 1. Invocamos nuestro método centralizado súper limpio
    const alerta = await UI.pedirJustificacion(
        'Archivar Registro de Tiempo',
        'Indique el motivo exacto de la anulación (Ej: Descalificación, Fallo de cronómetro):',
        'Escriba la justificación detallada aquí...'
    );

    // 2. Evaluamos la respuesta de la alerta
    if (alerta.isConfirmed && alerta.value) {
        let datosDelete = new FormData();
        datosDelete.append('accion', 'eliminar');
        datosDelete.append('id_marca', id_marca);
        datosDelete.append('motivo', alerta.value); // alerta.value contiene lo que escribió el usuario
        
        const resultado = await peticionAjax('eliminar', datosDelete);
        
        if (resultado && resultado.status === 'success') {
            UI.exito('Archivado', 'El registro y su justificación han sido guardados en el historial.');
            cargarTablaMarcas();
        } else {
            UI.error('Error', resultado?.message || 'No se pudo desactivar el registro.');
        }
    }
}

async function reactivarMarca(id_marca) {
    // Para reactivar, simplemente usamos el UI.confirmar que ya tenías creado
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
// Cuando el documento cargue, mandamos a pintar la tabla automáticamente
document.addEventListener('DOMContentLoaded', () => {
    cargarFiltroAtletas();
    cargarTablaMarcas();

});