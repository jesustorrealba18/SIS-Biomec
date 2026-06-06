function formatoFecha(fechaSQL) {
    if (!fechaSQL) return '—';
    const partes = fechaSQL.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return fechaSQL;
}

function formatearFecha(fechaSQL) {
    return formatoFecha(fechaSQL);
}

function formatoFechaRango(fechaInicio, fechaFin) {
    if (!fechaInicio) return '—';
    const ini = formatoFecha(fechaInicio);
    if (!fechaFin) return ini;
    return `${ini} - ${formatoFecha(fechaFin)}`;
}

function formatoFechaHora(fechaHoraSQL) {
    if (!fechaHoraSQL) return '—';
    
    // 1. Separamos la fecha de la hora usando el espacio
    const partes = fechaHoraSQL.split(' '); 
    
    if (partes.length === 2) {
        const fecha = partes[0].split('-'); // Dividimos 2026-06-05
        const hora = partes[1];             // Guardamos 21:37:05
        
        if (fecha.length === 3) {
            // Retornamos DD-MM-YYYY HH:MM:SS (Usando guiones como pediste)
            return `${fecha[2]}/${fecha[1]}/${fecha[0]} ${hora}`;
        }
    }
    
    // Si algo falla, devuelve la fecha original para no romper la tabla
    return fechaHoraSQL;
}

function badgeTipo(tipo) {
    const colores = {
        'Regional':      'bg-blue-500/20 text-blue-400 border border-blue-500/30',
        'Nacional':      'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
        'Internacional': 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
        'Selectivo':     'bg-orange-500/20 text-orange-400 border border-orange-500/30',
        'Control':       'bg-gray-500/20 text-gray-400 border border-gray-500/30'
    };
    const cls = colores[tipo] || 'bg-gray-500/20 text-gray-400 border border-gray-500/30';
    return `<span class="px-2 py-1 rounded-lg text-[10px] font-bold ${cls}">${tipo}</span>`;
}

function badgeEstado(estado) {
    const colores = {
        'Planificado':   'bg-indigo-500/20 text-indigo-400',
        'Inscrito':      'bg-cyan-500/20 text-cyan-400',
        'En Progreso':   'bg-yellow-500/20 text-yellow-400',
        'Finalizado':    'bg-green-500/20 text-green-400',
        'Cancelado':     'bg-red-500/20 text-red-400'
    };
    const cls = colores[estado] || 'bg-gray-500/20 text-gray-400';
    return `<span class="px-2 py-1 rounded-lg text-[10px] font-bold ${cls}">${estado}</span>`;
}