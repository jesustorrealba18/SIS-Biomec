<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRD | Pizarra en Vivo y Simulador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .font-reloj { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        
        /* Temas y Fondos */
        .bg-stars-light { background-image: radial-gradient(#e5e7eb 1px, transparent 1px); background-size: 40px 40px; }
        .glow-green-light { text-shadow: 0 0 40px rgba(16, 185, 129, 0.4); }
        .dark .bg-stars-dark { background-image: radial-gradient(#252345 1px, transparent 1px); background-size: 40px 40px; }
        .dark .glow-green-dark { text-shadow: 0 0 40px rgba(16, 185, 129, 0.6); }

        /* Clases para el efecto 3D de la Piscina */
        .perspectiva-piscina { perspective: 800px; perspective-origin: 50% 100%; }
        .piscina-3d { 
            transform: rotateX(60deg); 
            transform-style: preserve-3d;
            box-shadow: 0 30px 50px -10px rgba(6, 182, 212, 0.3), inset 0 0 40px rgba(6, 182, 212, 0.2);
        }
        
        /* Animación del agua */
        @keyframes moverAgua {
            0% { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }
        .textura-agua {
            background-image: 
                linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: moverAgua 4s linear infinite;
        }

        /* Avatar levantado (para contrarrestar el rotateX de la piscina) */
        .avatar-stand { transform: rotateX(-60deg) translateY(-50%); }
    </style>
</head>
<body class="min-h-screen w-full flex flex-col justify-start items-center bg-gray-50 dark:bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] dark:from-[#111026] dark:to-[#060512] bg-stars-light dark:bg-stars-dark transition-colors duration-500 p-4 sm:p-8">

    <div id="pantallaStandby" class="text-center absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-1000">
        <i class="fas fa-swimmer text-7xl text-indigo-500/50 dark:text-indigo-500/30 mb-6"></i>
        <h1 class="text-4xl sm:text-5xl font-black text-gray-300 dark:text-white/50 tracking-widest uppercase">SGRD LIVE</h1>
        <p class="text-indigo-600 dark:text-indigo-400/50 mt-4 animate-pulse uppercase tracking-widest font-bold text-sm sm:text-base">Esperando siguiente competidor...</p>
    </div>

    <div id="pantallaCarrera" class="w-full max-w-7xl opacity-0 hidden transition-opacity duration-1000 z-10 flex flex-col items-center">
        
        <div class="text-center mb-2 sm:mb-4">
            <div class="inline-block px-4 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-500/20 border border-indigo-300 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 font-bold tracking-widest uppercase text-xs sm:text-sm mb-2" id="uiPrueba">
                --
            </div>
            <h2 class="text-3xl sm:text-5xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tighter uppercase leading-tight" id="uiAtleta">
                --
            </h2>
        </div>

        <div class="relative flex justify-center items-center my-2 w-full">
            <span id="uiReloj" class="font-reloj text-[14vw] md:text-[120px] lg:text-[150px] leading-none font-extrabold text-gray-800 dark:text-white transition-colors duration-300">
                00:00.00
            </span>
        </div>

        <div class="text-center z-20">
            <span id="uiEstado" class="text-xl sm:text-2xl font-bold uppercase tracking-widest text-gray-500">
                --
            </span>
        </div>

        <div class="w-full max-w-4xl mx-auto mt-4 sm:mt-12 perspectiva-piscina relative">
            
            <div class="absolute top-[-40px] left-0 w-full flex justify-between text-gray-400 font-bold text-xs uppercase px-4 z-20">
                <span>Pared Lejana</span>
                <span id="uiDistanciaPool">50m</span>
            </div>

            <div class="piscina-3d relative w-full h-[300px] sm:h-[400px] bg-cyan-600/30 dark:bg-cyan-900/40 border-4 border-b-8 border-cyan-400/50 dark:border-cyan-500/30 rounded-xl overflow-hidden mx-auto backdrop-blur-sm">
                
                <div class="absolute inset-0 textura-agua mix-blend-overlay opacity-50"></div>
                
                <div class="absolute left-1/4 w-px h-full bg-cyan-300/30 dark:bg-cyan-500/20"></div>
                <div class="absolute left-2/4 w-px h-full bg-white/40 dark:bg-white/20 border-l border-dashed border-white/50"></div>
                <div class="absolute left-3/4 w-px h-full bg-cyan-300/30 dark:bg-cyan-500/20"></div>
                
                <div class="absolute bottom-10 left-[20%] w-[10%] h-2 bg-cyan-900/50"></div>
                <div class="absolute bottom-10 left-[70%] w-[10%] h-2 bg-cyan-900/50"></div>
                <div class="absolute top-10 left-[20%] w-[10%] h-2 bg-cyan-900/50"></div>
                <div class="absolute top-10 left-[70%] w-[10%] h-2 bg-cyan-900/50"></div>

                <div id="simActual" class="absolute left-[25%] bottom-0 w-12 h-20 -ml-6 transition-all duration-100 ease-linear flex flex-col items-center justify-center z-30 transform translate-z-10">
                    <div class="nadador-body relative w-full h-full transition-transform duration-500 ease-in-out">
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-6 h-8 bg-white/40 blur-md rounded-full animate-pulse"></div>
                        
                        <svg viewBox="0 0 100 120" class="w-full h-full drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)]">
                            <path d="M45,40 Q10,10 25,5" stroke="#fca5a5" stroke-width="8" fill="none" stroke-linecap="round" class="animate-[pulse_1s_ease-in-out_infinite]"/>
                            <path d="M55,40 Q90,10 75,5" stroke="#fca5a5" stroke-width="8" fill="none" stroke-linecap="round" class="animate-[pulse_1s_ease-in-out_infinite_0.5s]"/>
                            <path d="M42,80 L35,110" stroke="#fca5a5" stroke-width="7" fill="none" stroke-linecap="round" class="animate-[pulse_0.5s_ease-in-out_infinite]"/>
                            <path d="M58,80 L65,110" stroke="#fca5a5" stroke-width="7" fill="none" stroke-linecap="round" class="animate-[pulse_0.5s_ease-in-out_infinite_0.25s]"/>
                            <rect x="40" y="30" width="20" height="55" rx="8" fill="#10b981" />
                            <circle cx="50" cy="25" r="14" fill="#059669" stroke="#fff" stroke-width="2"/>
                            <rect x="42" y="20" width="16" height="6" rx="3" fill="#111827"/>
                        </svg>
                    </div>
                    <span class="absolute -bottom-6 bg-emerald-600/90 text-white text-[9px] px-1.5 py-0.5 rounded font-black tracking-widest shadow-lg">REAL</span>
                </div>

                <div id="simGhost" class="absolute left-[75%] bottom-0 w-12 h-20 -ml-6 transition-all duration-100 ease-linear flex flex-col items-center justify-center z-20 opacity-40 transform translate-z-10">
                    <div class="nadador-body relative w-full h-full transition-transform duration-500 ease-in-out">
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-6 h-8 bg-white/20 blur-md rounded-full"></div>
                        
                        <svg viewBox="0 0 100 120" class="w-full h-full">
                            <path d="M45,40 Q10,10 25,5" stroke="#9ca3af" stroke-width="8" fill="none" stroke-linecap="round"/>
                            <path d="M55,40 Q90,10 75,5" stroke="#9ca3af" stroke-width="8" fill="none" stroke-linecap="round"/>
                            <path d="M42,80 L35,110" stroke="#9ca3af" stroke-width="7" fill="none" stroke-linecap="round"/>
                            <path d="M58,80 L65,110" stroke="#9ca3af" stroke-width="7" fill="none" stroke-linecap="round"/>
                            <rect x="40" y="30" width="20" height="55" rx="8" fill="#6b7280" />
                            <circle cx="50" cy="25" r="14" fill="#4b5563" stroke="#e5e7eb" stroke-width="2"/>
                        </svg>
                    </div>
                    <span id="lblGhost" class="absolute -bottom-6 bg-gray-800/80 text-white text-[9px] px-1.5 py-0.5 rounded font-black tracking-widest">RECORD</span>
                </div>

            </div>
            
            <div class="relative w-[105%] -left-[2.5%] h-8 bg-gray-300 dark:bg-gray-800 border-t-4 border-gray-400 dark:border-gray-600 rounded-b-xl shadow-xl z-30 flex justify-center items-center">
                <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest">Bloques de Salida</span>
            </div>
        </div>

    </div>

    <script>
        const pantallaStandby = document.getElementById('pantallaStandby');
        const pantallaCarrera = document.getElementById('pantallaCarrera');
        
        const uiAtleta = document.getElementById('uiAtleta');
        const uiPrueba = document.getElementById('uiPrueba');
        const uiReloj = document.getElementById('uiReloj');
        const uiEstado = document.getElementById('uiEstado');
        
        // Elementos del Simulador
        const simActual = document.getElementById('simActual');
        const simGhost = document.getElementById('simGhost');
        const uiDistanciaPool = document.getElementById('uiDistanciaPool');
        const lblGhost = document.getElementById('lblGhost');

        let animacionReloj;
        let inicioRelojCliente = null;
        let estadoActual = 'standby';
        let datosCarrera = null;

        // Variables Matemáticas del Simulador
        let tiempoObjetivoMs = 0; 
        let velocidadGhost = 0; // m por milisegundo
        let velocidadActualProyectada = 0; 

        // 1. POLLING
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

        // 2. MAQUINA DE ESTADOS
        function procesarDatos(data, serverTime) {
            datosCarrera = data;
            pantallaStandby.classList.add('opacity-0', 'hidden');
            pantallaCarrera.classList.remove('hidden');
            setTimeout(() => pantallaCarrera.classList.remove('opacity-0'), 50);

            uiAtleta.textContent = `${data.nombres} ${data.apellidos}`;
            uiPrueba.textContent = `${data.distancia_total}m ${data.estilo}`;
            uiDistanciaPool.textContent = `Pared de ${data.tipo_piscina}`;
            
            uiReloj.className = "font-reloj text-[14vw] md:text-[120px] lg:text-[150px] leading-none font-extrabold text-gray-800 dark:text-white transition-colors duration-300";

            // CONFIGURAR FANTASMA (Si el backend trae 'tiempo_objetivo_ms', úsalo. Si no, calcúlalo).
            tiempoObjetivoMs = parseInt(data.tiempo_objetivo_ms) || estimarTiempo(data.distancia_total, data.estilo);
            velocidadGhost = data.distancia_total / tiempoObjetivoMs; 
            lblGhost.textContent = data.tiempo_objetivo_ms ? "PB" : "ESTIMADO";

            const estadoCarrera = data.estado_carrera;

            if (estadoCarrera === 'iniciando') {
                detenerRelojInterno();
                uiReloj.textContent = "00:00.00";
                uiEstado.textContent = "EN SUS MARCAS";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-amber-600 dark:text-amber-500 animate-pulse";
                resetearSimulador();
            } 
            else if (estadoCarrera === 'en_curso' || estadoCarrera === 'en_viraje') {
                uiEstado.textContent = estadoCarrera === 'en_viraje' ? "EN VIRAJE..." : "NADANDO";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-500";
                
                if (estadoActual !== 'en_curso' && estadoActual !== 'en_viraje') {
                    const offsetMs = serverTime - parseInt(data.inicio_timestamp_ms);
                    inicioRelojCliente = performance.now() - offsetMs;
                    arrancarRelojInterno();
                }
            }
            else if (estadoCarrera === 'finalizado') {
                detenerRelojInterno();
                uiEstado.textContent = "TIEMPO OFICIAL";
                uiEstado.className = "text-2xl sm:text-3xl font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400";
                uiReloj.classList.add('text-emerald-600', 'dark:text-emerald-400', 'glow-green-light', 'dark:glow-green-dark');
                uiReloj.textContent = formatearMilisegundos(parseFloat(data.ultimo_tiempo_parcial_ms));
                
                // Forzar llegada a la meta
                moverAvatar(simActual, parseInt(data.distancia_total), parseInt(data.distancia_total), parseInt(data.tipo_piscina));
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

        // ==========================================
        // LÓGICA DEL SIMULADOR 3D (Mario Kart Ghost)
        // ==========================================
        
       function arrancarRelojInterno() {
            const actualizar = () => {
                const transcurrido = performance.now() - inicioRelojCliente;
                uiReloj.textContent = formatearMilisegundos(transcurrido);
                
                if(datosCarrera && transcurrido > 0) {
                    const longPiscina = parseInt(datosCarrera.tipo_piscina); 
                    const distTotal = parseInt(datosCarrera.distancia_total);

                    // 1. Mover al Fantasma
                    let distGhost = transcurrido * velocidadGhost;
                    if(distGhost > distTotal) distGhost = distTotal;
                    moverAvatar(simGhost, distGhost, distTotal, longPiscina);

                    // 2. Mover al Atleta Real
                    let distUltimoTramo = parseInt(datosCarrera.ultima_distancia_recorrida_m);
                    let tiempoUltimoTramo = parseFloat(datosCarrera.ultimo_tiempo_parcial_ms);
                    let tiempoEnTramoActual = transcurrido - tiempoUltimoTramo;
                    
                    if (distUltimoTramo === 0) {
                        velocidadActualProyectada = velocidadGhost;
                    } else {
                        velocidadActualProyectada = distUltimoTramo / tiempoUltimoTramo; 
                    }

                    // APLICACIÓN DEL LÍMITE FÍSICO (Clamp)
                    let distReal = distUltimoTramo + (tiempoEnTramoActual * velocidadActualProyectada);
                    
                    // ¿Cuál es la próxima pared?
                    let maximaDistanciaPermitida = distUltimoTramo + longPiscina;
                    if (maximaDistanciaPermitida > distTotal) maximaDistanciaPermitida = distTotal;

                    // Si la proyección supera la pared, lo pegamos a la pared
                    if (distReal >= maximaDistanciaPermitida) {
                        distReal = maximaDistanciaPermitida;
                    }

                    // Si el entrenador le dio al botón de Viraje, lo mantenemos congelado en la pared
                    if(datosCarrera.estado_carrera === 'en_viraje') {
                        distReal = distUltimoTramo;
                    }

                    moverAvatar(simActual, distReal, distTotal, longPiscina);
                }

                animacionReloj = requestAnimationFrame(actualizar);
            };
            cancelAnimationFrame(animacionReloj);
            animacionReloj = requestAnimationFrame(actualizar);
        }
        // Traduce los "metros recorridos" a porcentaje de pantalla según la dirección
      function moverAvatar(elemento, distanciaRecorrida, distanciaTotal, longitudPiscina) {
            let lapActual = Math.floor(distanciaRecorrida / longitudPiscina);
            let metrosEnLap = distanciaRecorrida % longitudPiscina; 
            
            if (distanciaRecorrida >= distanciaTotal) {
                lapActual = Math.floor(distanciaTotal / longitudPiscina) - 1;
                metrosEnLap = longitudPiscina;
            }

            let porcentajeAvance = (metrosEnLap / longitudPiscina) * 100;
            const cuerpoNadador = elemento.querySelector('.nadador-body');
            
            if (lapActual % 2 === 0) {
                // IDA (Sube por la piscina)
                elemento.style.bottom = `${porcentajeAvance}%`;
                cuerpoNadador.style.transform = "rotate(0deg)";
            } else {
                // REGRESO (Baja por la piscina)
                elemento.style.bottom = `${100 - porcentajeAvance}%`;
                cuerpoNadador.style.transform = "rotate(180deg)";
            }
        }
        
        function resetearSimulador() {
            simActual.style.bottom = '0%';
            simActual.querySelector('.nadador-body').style.transform = "rotate(0deg)";
            simGhost.style.bottom = '0%';
            simGhost.querySelector('.nadador-body').style.transform = "rotate(0deg)";
        }

       

        // ==========================================
        // UTILIDADES Y ESTIMACIONES
        // ==========================================

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

        // Creador de un tiempo "Fantasma" si la BD no envía un Record Personal
        function estimarTiempo(distancia, estilo) {
            let tiempoBase50m;
            // Tiempos promedio amateur-competitivo para 50m
            switch(estilo.toLowerCase()) {
                case 'libre': tiempoBase50m = 32000; break;
                case 'mariposa': tiempoBase50m = 35000; break;
                case 'espalda': tiempoBase50m = 38000; break;
                case 'pecho': tiempoBase50m = 42000; break;
                default: tiempoBase50m = 35000; 
            }
            // Multiplicador de fatiga (Mientras más largo el trayecto, más lento es el promedio)
            const factorFatiga = 1 + ((distancia / 50) * 0.05); 
            return (distancia / 50) * tiempoBase50m * factorFatiga;
        }
    </script>
</body>
</html>