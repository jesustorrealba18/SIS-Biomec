// =====================================================================
// MOTOR DE NORMALIZACIÓN FINA BIDIRECCIONAL
// =====================================================================
/* const NormalizadorPiscina = (function() {
    let modoActual = 'reales'; // Estados: 'reales', 'a_50m', 'a_25m'

    function formatear(segundosTotales) {
        if (!segundosTotales) return '00.00';
        const num = parseFloat(segundosTotales);
        if (num < 60) return num.toFixed(2) + 's';
        const minutos = Math.floor(num / 60);
        const segundos = (num % 60).toFixed(2);
        return `${minutos.toString().padStart(2, '0')}:${segundos.padStart(5, '0')}`;
    }

    return {
        cambiarModo: function(nuevoModo) {
            modoActual = nuevoModo;
            
            if(typeof UI !== 'undefined') {
                if(modoActual === 'a_50m') UI.exito('Modo Olímpica', 'Tiempos de 25m ajustados a 50m.');
                else if(modoActual === 'a_25m') UI.exito('Modo Corta', 'Tiempos de 50m ajustados a 25m.');
            }
            
            // Recargamos la tabla visualmente
            if (typeof cargarTablaMarcas === 'function') cargarTablaMarcas();
        },
        
        procesarTiempo: function(tiempoSeg, factor, piscinaOriginal) {
            // 1. Si no hay que convertir, devolvemos el tiempo real
            if (modoActual === 'reales') return { tiempoStr: formatear(tiempoSeg), esConvertido: false };
            
            // 2. Si la marca YA ESTÁ en la piscina deseada, no se convierte
            if (modoActual === 'a_50m' && piscinaOriginal === '50m') return { tiempoStr: formatear(tiempoSeg), esConvertido: false };
            if (modoActual === 'a_25m' && piscinaOriginal === '25m') return { tiempoStr: formatear(tiempoSeg), esConvertido: false };
            
            // 3. Si por algún error en BD falta el factor, devolvemos el real
            if (!factor) return { tiempoStr: formatear(tiempoSeg), esConvertido: false };
            
            // 4. Aplicamos conversión matemática bidireccional
            const tiempoConvertido = parseFloat(tiempoSeg) * parseFloat(factor);
            const piscinaDestino = modoActual === 'a_50m' ? '50m' : '25m';
            
            return { 
                tiempoStr: formatear(tiempoConvertido), 
                esConvertido: true,
                factorUsado: factor,
                piscinaDestino: piscinaDestino
            };
        }
    };
})(); */


// =====================================================================
// MOTOR DE NORMALIZACIÓN FINA BIDIRECCIONAL
// =====================================================================
const NormalizadorPiscina = (function() {
    let modoActual = 'reales'; 

    function formatear(segundosTotales) {
        if (!segundosTotales) return '00.00';
        const num = parseFloat(segundosTotales);
        if (num < 60) return num.toFixed(2) + 's';
        const minutos = Math.floor(num / 60);
        const segundos = (num % 60).toFixed(2);
        return `${minutos.toString().padStart(2, '0')}:${segundos.padStart(5, '0')}`;
    }

    return {
        cambiarModo: function(nuevoModo) {
            modoActual = nuevoModo;
            if(typeof UI !== 'undefined') {
                if(modoActual === 'a_50m') UI.exito('Modo Olímpica', 'Tiempos visuales ajustados a 50m.');
                else if(modoActual === 'a_25m') UI.exito('Modo Corta', 'Tiempos visuales ajustados a 25m.');
            }
            if (typeof cargarTablaMarcas === 'function') cargarTablaMarcas();
        },
        
        getModo: function() { return modoActual; },
        
        procesarTiempo: function(tiempoSeg, factor, piscinaOriginal) {
            const numOriginal = parseFloat(tiempoSeg);
            
            if (modoActual === 'reales' || 
               (modoActual === 'a_50m' && piscinaOriginal === '50m') || 
               (modoActual === 'a_25m' && piscinaOriginal === '25m') || 
               !factor) {
                return { tiempoStr: formatear(numOriginal), tiempoRaw: numOriginal, esConvertido: false };
            }
            
            const tiempoConvertido = numOriginal * parseFloat(factor);
            const piscinaDestino = modoActual === 'a_50m' ? '50m' : '25m';
            
            return { 
                tiempoStr: formatear(tiempoConvertido), 
                tiempoRaw: tiempoConvertido, 
                esConvertido: true,
                factorUsado: factor,
                piscinaDestino: piscinaDestino
            };
        }
    };
})();