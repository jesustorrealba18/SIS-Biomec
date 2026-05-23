/**
 * Convierte una fecha de formato SQL (YYYY-MM-DD) a formato Latino (DD/MM/YYYY)
 */
function formatearFecha(fechaSQL) {
    // Si viene vacía o nula, devolvemos un guion o espacio
    if (!fechaSQL) return '—';

    // Separamos el string por los guiones
    const partes = fechaSQL.split('-');
    
    // Verificamos que realmente tenga las 3 partes (Año, Mes, Día)
    if (partes.length === 3) {
        // Retornamos Día/Mes/Año
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    
    // Si por alguna razón el formato no era YYYY-MM-DD, devolvemos lo que llegó
    return fechaSQL; 
}