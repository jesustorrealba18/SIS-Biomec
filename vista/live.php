<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRD | Pizarra en Vivo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; overflow: hidden; }
        .font-reloj { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        
        /* Tema Claro */
        .bg-stars-light { background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 40px 40px; }
        .glow-green-light { text-shadow: 0 0 40px rgba(16, 185, 129, 0.4); }
        
        /* Tema Oscuro */
        .dark .bg-stars-dark { background-image: radial-gradient(#252345 1px, transparent 1px); background-size: 40px 40px; }
        .dark .glow-green-dark { text-shadow: 0 0 40px rgba(16, 185, 129, 0.6); }
    </style>
</head>
<body class="h-screen w-screen flex flex-col justify-center items-center bg-gray-50 dark:bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] dark:from-[#111026] dark:to-[#060512] bg-stars-light dark:bg-stars-dark transition-colors duration-500 p-4 sm:p-8">

    <!-- ESTADO 1: STANDBY -->
    <div id="pantallaStandby" class="text-center absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-1000">
        <i class="fas fa-swimmer text-7xl text-indigo-500/50 dark:text-indigo-500/30 mb-6"></i>
        <h1 class="text-4xl sm:text-5xl font-black text-gray-300 dark:text-white/50 tracking-widest uppercase">SGRD LIVE</h1>
        <p class="text-indigo-600 dark:text-indigo-400/50 mt-4 animate-pulse uppercase tracking-widest font-bold text-sm sm:text-base">Esperando siguiente competidor...</p>
    </div>

    <!-- ESTADO 2: CARRERA ACTIVA -->
    <div id="pantallaCarrera" class="w-full max-w-7xl px-4 sm:px-8 opacity-0 hidden transition-opacity duration-1000 z-10 flex flex-col items-center">
        
        <!-- Info del Atleta y Prueba -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-500/20 border border-indigo-300 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 font-bold tracking-widest uppercase text-xs sm:text-sm mb-4" id="uiPrueba">
                --
            </div>
            <h2 class="text-4xl sm:text-6xl md:text-8xl font-black text-gray-900 dark:text-white tracking-tighter uppercase leading-tight" id="uiAtleta">
                --
            </h2>
        </div>

        <!-- El Reloj Gigante -->
        <div class="relative flex justify-center items-center my-4 sm:my-8 w-full">
            <span id="uiReloj" class="font-reloj text-[18vw] md:text-[180px] lg:text-[220px] leading-none font-extrabold text-gray-800 dark:text-white transition-colors duration-300">
                00:00.00
            </span>
        </div>

        <!-- Estado actual de la carrera -->
        <div class="mt-4 sm:mt-8 text-center">
            <span id="uiEstado" class="text-2xl sm:text-3xl font-bold uppercase tracking-widest text-gray-500">
                --
            </span>
            <p id="uiUltimoTramo" class="text-lg sm:text-xl text-indigo-600 dark:text-indigo-300 mt-2 font-mono h-8">
                <!-- Aquí aparece info del tramo -->
            </p>
        </div>
    </div>

    <script>
        const pantallaStandby = document.getElementById('pantallaStandby');
        const pantallaCarrera = document.getElementById('pantallaCarrera');
        
        const uiAtleta = document.getElementById('uiAtleta');
        const uiPrueba = document.getElementById('uiPrueba');
        const uiReloj = document.getElementById('uiReloj');
        const uiEstado = document.getElementById('uiEstado');
        const uiUltimoTramo = document.getElementById('uiUltimoTramo');

        let animacionReloj;
        let inicioRelojCliente = null;
        let estadoActual = 'standby';

        // 1. EL POLLING (Pregunta al servidor cada 1 segundo)
        async function escucharTelemetria() {
            try {
                const res = await fetch('index.php?p=live&accion=get_telemetria');
                const json = await res.json();

                if (json.status === 'success') {
                    procesarDatos(json.data, json.server_time);
                } else {
                    mostrarStandby();
                }
            } catch (error) {
                console.debug("Buscando señal...", error);
            }
        }
        
        setInterval(escucharTelemetria, 1000);

        // 2. MAQUINA DE ESTADOS VISUAL
        function procesarDatos(data, serverTime) {
            pantallaStandby.classList.add('opacity-0', 'hidden');
            pantallaCarrera.classList.remove('hidden');
            setTimeout(() => pantallaCarrera.classList.remove('opacity-0'), 50);

            uiAtleta.textContent = `${data.nombres} ${data.apellidos}`;
            uiPrueba.textContent = `${data.distancia_total}m ${data.estilo} - PISCINA ${data.tipo_piscina}`;

            // Reset de estilos del reloj
            uiReloj.className = "font-reloj text-[18vw] md:text-[180px] lg:text-[220px] leading-none font-extrabold text-gray-800 dark:text-white transition-colors duration-300";

            const estadoCarrera = data.estado_carrera;

            if (estadoCarrera === 'iniciando') {
                detenerRelojInterno();
                uiReloj.textContent = "00:00.00";
                uiEstado.textContent = "EN SUS MARCAS";
                uiEstado.className = "text-2xl sm:text-3xl font-bold uppercase tracking-widest text-amber-600 dark:text-amber-500 animate-pulse";
                uiUltimoTramo.textContent = "";
            } 
            else if (estadoCarrera === 'en_curso' || estadoCarrera === 'en_viraje') {
                uiEstado.textContent = estadoCarrera === 'en_viraje' ? "EN VIRAJE" : "NADANDO";
                uiEstado.className = "text-2xl sm:text-3xl font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-500";
                
                // Si acaba de cambiar a "Corriendo", calculamos la diferencia exacta con el servidor
                if (estadoActual !== 'en_curso' && estadoActual !== 'en_viraje') {
                    const offsetMs = serverTime - parseInt(data.inicio_timestamp_ms);
                    inicioRelojCliente = performance.now() - offsetMs;
                    arrancarRelojInterno();
                }

                if (parseInt(data.ultima_distancia_recorrida_m) > 0) {
                    uiUltimoTramo.textContent = `Marca ${data.ultima_distancia_recorrida_m}m cruzada`;
                }
            }
            else if (estadoCarrera === 'finalizado') {
                detenerRelojInterno();
                uiEstado.textContent = "TIEMPO OFICIAL";
                uiEstado.className = "text-3xl sm:text-4xl font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400";
                uiReloj.classList.add('text-emerald-600', 'dark:text-emerald-400', 'glow-green-light', 'dark:glow-green-dark');
                
                uiReloj.textContent = formatearMilisegundos(parseFloat(data.ultimo_tiempo_parcial_ms));
                uiUltimoTramo.textContent = `Llegada a los ${data.distancia_total}m`;
            }

            estadoActual = estadoCarrera;
        }

        function mostrarStandby() {
            if (estadoActual !== 'standby') {
                detenerRelojInterno();
                pantallaCarrera.classList.add('opacity-0');
                setTimeout(() => {
                    pantallaCarrera.classList.add('hidden');
                    pantallaStandby.classList.remove('hidden');
                    setTimeout(() => pantallaStandby.classList.remove('opacity-0'), 50);
                }, 1000);
                estadoActual = 'standby';
            }
        }

        // 3. MOTOR DEL RELOJ
        function arrancarRelojInterno() {
            const actualizar = () => {
                const transcurrido = performance.now() - inicioRelojCliente;
                uiReloj.textContent = formatearMilisegundos(transcurrido);
                animacionReloj = requestAnimationFrame(actualizar);
            };
            cancelAnimationFrame(animacionReloj);
            animacionReloj = requestAnimationFrame(actualizar);
        }

        function detenerRelojInterno() {
            cancelAnimationFrame(animacionReloj);
        }

        function formatearMilisegundos(ms) {
            if(ms < 0) ms = 0;
            const totalCentesimas = Math.floor(ms / 10);
            const centesimas = totalCentesimas % 100;
            const totalSegundos = Math.floor(totalCentesimas / 100);
            const segundos = totalSegundos % 60;
            const minutos = Math.floor(totalSegundos / 60);

            const strMin = minutos.toString().padStart(2, '0');
            const strSeg = segundos.toString().padStart(2, '0');
            const strCent = centesimas.toString().padStart(2, '0');

            return minutos > 0 ? `${strMin}:${strSeg}.${strCent}` : `${strSeg}.${strCent}`;
        }
    </script>
</body>
</html>