// =====================================================================
// CONFIGURACIÓN PRINCIPAL
// =====================================================================

// NUEVA RUTA DIRECTA AL CONTROLADOR PIVOTE A TRAVÉS DEL INDEX:
const API_URL = 'index.php?p=bitacora'; 
const modalRep = document.getElementById('modalBitacora');
let registrosBitacora = []; // Variable global para guardar los datos cargados

document.addEventListener('DOMContentLoaded', () => {
    cargarTablaBitacora();
});
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


async function cargarTablaBitacora() {
    const tbody = document.getElementById('tbodyBitacora');
    tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando registros...</td></tr>';
    
    const respuesta = await peticionAjax('listar');
    
    if (respuesta && respuesta.status === 'success') {
        registrosBitacora = respuesta.data;
        dibujarTabla(registrosBitacora);
    } else {
        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500">Error al cargar la bitácora.</td></tr>';
    }
}

function dibujarTabla(datos) {
    const tbody = document.getElementById('tbodyBitacora');
    tbody.innerHTML = '';

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-500">No hay registros en la bitácora.</td></tr>';
        return;
    }

    datos.forEach((fila, index) => {
        // Colores según la operación (ENUM de tu BD)
        let colorBadge = 'bg-gray-500/20 text-gray-400 border-gray-500/30';
        if (fila.tipo_operacion === 'CREATE') colorBadge = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
        if (fila.tipo_operacion === 'UPDATE') colorBadge = 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        if (fila.tipo_operacion === 'DELETE') colorBadge = 'bg-red-500/20 text-red-400 border-red-500/30';
        if (fila.tipo_operacion === 'LOGIN' || fila.tipo_operacion === 'LOGOUT') colorBadge = 'bg-purple-500/20 text-purple-400 border-purple-500/30';

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/5 transition-colors';
        tr.innerHTML = `
            <td class="p-4 font-mono text-xs text-gray-400">${fila.fecha_operacion}</td>
            <td class="p-4">
                <div class="font-bold text-white text-xs">${fila.nombres} ${fila.apellidos}</div>
                <div class="text-[10px] text-gray-500">${fila.rol_nombre || 'Sin Rol'}</div>
            </td>
            <td class="p-4"><span class="text-indigo-400 font-semibold text-xs">${fila.modulo_afectado}</span></td>
            <td class="p-4">
                <span class="${colorBadge} px-2 py-1 rounded text-[10px] font-bold tracking-wider uppercase">
                    ${fila.tipo_operacion}
                </span>
            </td>
            <td class="p-4 text-right">
                <button onclick="verDetalleBitacora(${index})" class="text-blue-400 hover:text-blue-300 transition" title="Ver detalle completo">
                    <i class="fas fa-eye fa-lg"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL Y FORMATO DE DETALLES)
// =====================================================================

function verDetalleBitacora(indiceArreglo) {
    // Obtenemos el registro exacto del arreglo global usando su índice
    const registro = registrosBitacora[indiceArreglo];
    
    // 1. Llenamos los datos fijos de la parte inferior del modal
    document.getElementById('detalleIP').textContent = registro.ip_origen || 'No registrada';
    // Si tuvieras el User-Agent del navegador en BD podrías ponerlo aquí, por ahora un texto fijo o guión
    document.getElementById('detalleNavegador').textContent = registro.navegador ||'Registrado por Sistema';

    // 2. Contenedor principal donde inyectaremos el contenido
    const divDetalle = document.getElementById('textoDetalleAccion');
    let htmlContenido = '';

    // Mostramos la descripción de qué campo se tocó (Si existe)
    if (registro.campo_modificado) {
         htmlContenido += `<div class="mb-3 text-sm"><span class="text-indigo-400 font-bold uppercase tracking-wider text-[10px]">Contexto:</span> ${registro.campo_modificado}</div>`;
    }

    // 3. Función auxiliar mágica: Detecta si es JSON o Texto y le da diseño
    const procesarValor = (valor, titulo, colorClase, borderClase) => {
        if (!valor) return ''; // Si está vacío (ej: no hay valor anterior al crear), no dibuja nada
        
        let contenido = valor;
        try {
            // Intentamos convertirlo a objeto. Si falla, salta al catch.
            const obj = JSON.parse(valor);
            
            // Si funciona, lo convertimos de nuevo a texto pero con formato bonito (indentado a 4 espacios)
            const jsonBonito = JSON.stringify(obj, null, 4);
            contenido = `<pre class="mt-1 ${colorClase} bg-[#0a0914] p-3 rounded-lg border ${borderClase} overflow-x-auto text-[11px] font-mono leading-relaxed">${jsonBonito}</pre>`;
            
        } catch (e) {
            // Si falló el parseo, significa que es texto normal
            contenido = `<div class="mt-1 text-gray-300 bg-[#0a0914] p-3 rounded-lg border border-gray-700/50 text-xs italic">${valor}</div>`;
        }
        
        return `<div class="mt-4">
                    <span class="text-gray-500 font-bold text-[10px] uppercase tracking-widest">${titulo}</span>
                    ${contenido}
                </div>`;
    };

    // 4. Inyectamos los valores (si existen) con colores semánticos
    // Rojo para lo que se borró/cambió, Verde esmeralda para lo nuevo
    htmlContenido += procesarValor(registro.valor_anterior, 'Dato Anterior / Borrado', 'text-red-400', 'border-red-500/20');
    htmlContenido += procesarValor(registro.valor_nuevo, 'Dato Nuevo / Registrado', 'text-emerald-400', 'border-emerald-500/20');

    // 5. Metemos todo el HTML procesado al div
    divDetalle.innerHTML = htmlContenido;

    // 6. Abrimos el Modal con la animación
    const modal = document.getElementById('modalBitacora');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

// =====================================================================
// MANEJO DE LA INTERFAZ (MODAL)
// =====================================================================


        function cerrarModalBitacora() {
            const modal = document.getElementById('modalBitacora');
            modal.firstElementChild.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape" && !modalRep.classList.contains('hidden')) {
                cerrarModalBitacora();
            }
        });