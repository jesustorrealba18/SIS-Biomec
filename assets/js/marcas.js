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