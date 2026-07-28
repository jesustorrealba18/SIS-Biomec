<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRD | Live 3D Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/three@0.152.0/build/three.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; overflow: hidden; margin: 0; padding: 0; }
        .font-reloj { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        
        #canvas-container { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; }
        #ui-layer { position: relative; z-index: 10; pointer-events: none; }
        
        .glow-text { text-shadow: 0 0 20px rgba(255,255,255,0.3); }
        .glow-green { text-shadow: 0 0 30px rgba(16, 185, 129, 0.8); }
        .glow-red { text-shadow: 0 0 30px rgba(239, 68, 68, 0.8); }
    </style>
</head>
<body class="bg-[#060512]">

    <div id="canvas-container"></div>

    <div id="ui-layer" class="w-full h-screen flex flex-col justify-between p-4 sm:p-8 md:p-12">
        
        <div id="pantallaStandby" class="absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-1000 bg-[#060512]/90 backdrop-blur-md z-50">
            <i class="fas fa-swimmer text-6xl md:text-8xl text-indigo-500/50 mb-6"></i>
            <h1 class="text-4xl sm:text-6xl md:text-8xl font-black text-gray-300 tracking-widest uppercase">SGRD LIVE</h1>
            <p class="text-indigo-400 mt-4 animate-pulse uppercase tracking-widest font-bold text-sm md:text-xl">Esperando Atleta...</p>
        </div>

        <div id="pantallaCarrera" class="w-full h-full flex flex-col justify-between opacity-0 hidden transition-opacity duration-1000">
            
            <div class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-end">
                <div class="mb-4 sm:mb-0">
                    <div class="inline-block px-3 py-1 md:px-5 md:py-2 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 font-bold tracking-widest uppercase text-[10px] md:text-sm mb-2" id="uiPrueba">--</div>
                    <h2 class="text-4xl sm:text-6xl lg:text-[80px] font-black text-white tracking-tighter uppercase glow-text leading-none" id="uiAtleta">--</h2>
                </div>
                
                <div class="text-left sm:text-right mt-2 sm:mt-0">
                    <span id="uiEstado" class="block text-sm md:text-xl font-bold uppercase tracking-widest text-gray-400 mb-1">--</span>
                    <span id="uiReloj" class="font-reloj text-[15vw] sm:text-8xl lg:text-[130px] leading-none font-extrabold text-white transition-colors duration-300 block">00:00.00</span>
                </div>
            </div>

            <div class="w-full flex justify-between items-end pb-2 md:pb-6">
                <div class="flex gap-4">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 md:w-5 md:h-5 bg-emerald-500 rounded-sm shadow-[0_0_15px_#10b981]"></div><span class="text-white text-[10px] md:text-sm font-bold tracking-widest">ATLETA REAL</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 md:w-5 md:h-5 bg-gray-400 rounded-sm"></div><span class="text-white text-[10px] md:text-sm font-bold tracking-widest" id="lblGhost">RECORD</span></div>
                </div>
                <div class="text-gray-500 font-bold text-[10px] md:text-sm uppercase tracking-widest">
                    Piscina <span id="uiDistanciaPool">--</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // 1. CONFIGURACIÓN DEL MOTOR 3D (THREE.JS)
        // ==========================================
        const container = document.getElementById('canvas-container');
        const scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x060512, 0.0025); 

        const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.shadowMap.enabled = true;
        container.appendChild(renderer.domElement);

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
        scene.add(ambientLight);
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(0, 100, 50);
        dirLight.castShadow = true;
        scene.add(dirLight);

        // --- LA PISCINA ---
        const poolLength = 100; 
        const poolWidth = 40;

        const waterGeo = new THREE.PlaneGeometry(poolLength, poolWidth, 32, 32);
        const waterMat = new THREE.MeshStandardMaterial({ color: 0x0891b2, transparent: true, opacity: 0.7, roughness: 0.1 });
        const water = new THREE.Mesh(waterGeo, waterMat);
        water.rotation.x = -Math.PI / 2; 
        water.receiveShadow = true;
        scene.add(water);

        const borderGeo = new THREE.BoxGeometry(poolLength + 4, 2, poolWidth + 4);
        const borderMat = new THREE.MeshStandardMaterial({ color: 0x1f2937 });
        const border = new THREE.Mesh(borderGeo, borderMat);
        border.position.y = -1.1; 
        scene.add(border);

        const blockGeo = new THREE.BoxGeometry(4, 3, 4);
        const blockMat = new THREE.MeshStandardMaterial({ color: 0x1e3a8a });
        
        const blockReal = new THREE.Mesh(blockGeo, blockMat);
        blockReal.position.set(-poolLength/2 - 2, 1.5, 10); 
        blockReal.castShadow = true;
        scene.add(blockReal);

        const blockGhost = new THREE.Mesh(blockGeo, blockMat);
        blockGhost.position.set(-poolLength/2 - 2, 1.5, -10); 
        blockGhost.castShadow = true;
        scene.add(blockGhost);

        const swimmerGeo = new THREE.CapsuleGeometry( 1.2, 4, 4, 8 );
        swimmerGeo.rotateZ(Math.PI / 2);

        const realMat = new THREE.MeshStandardMaterial({ color: 0x10b981, emissive: 0x059669 });
        const meshReal = new THREE.Mesh(swimmerGeo, realMat);
        meshReal.castShadow = true;
        scene.add(meshReal);

        const ghostMat = new THREE.MeshStandardMaterial({ color: 0x9ca3af, transparent: true, opacity: 0.5 });
        const meshGhost = new THREE.Mesh(swimmerGeo, ghostMat);
        scene.add(meshGhost);

        function ajustarCamara() {
            const aspect = window.innerWidth / window.innerHeight;
            camera.aspect = aspect;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);

            if (aspect < 0.7) {
                camera.position.set(0, 95, 60); 
                camera.lookAt(0, -15, 0);
            } else if (aspect < 1.2) {
                camera.position.set(0, 65, 65); 
                camera.lookAt(0, -5, 0);
            } else {
                camera.position.set(0, 45, 65); 
                camera.lookAt(0, 0, 0); 
            }
        }
        window.addEventListener('resize', ajustarCamara);
        ajustarCamara();

        function animate3D() {
            requestAnimationFrame(animate3D);
            renderer.render(scene, camera);
        }
        animate3D();

        // ==========================================
        // 2. LÓGICA DE TELEMETRÍA (FÍSICA CORREGIDA)
        // ==========================================
        const pantallaStandby = document.getElementById('pantallaStandby');
        const pantallaCarrera = document.getElementById('pantallaCarrera');
        const uiAtleta = document.getElementById('uiAtleta');
        const uiPrueba = document.getElementById('uiPrueba');
        const uiReloj = document.getElementById('uiReloj');
        const uiEstado = document.getElementById('uiEstado');
        const uiDistanciaPool = document.getElementById('uiDistanciaPool');
        const lblGhost = document.getElementById('lblGhost');

        let animacionReloj;
        let inicioRelojCliente = null;
        let estadoActual = 'standby';
        let datosCarrera = null;
        
        let tiempoObjetivoMs = 0; 
        let velocidadGhost = 0; 
        let velocidadActualProyectada = 0; 
        let reaccionGhostMs = 650; 

        async function escucharTelemetria() {
            try {
                const res = await fetch('index.php?p=live&accion=get_telemetria');
                const json = await res.json();

                if (json.status === 'success') {
                    procesarDatos(json.data, json.server_time);
                } else {
                    mostrarStandby();
                }
            } catch (error) {}
        }
        setInterval(escucharTelemetria, 1000);

        function procesarDatos(data, serverTime) {
            datosCarrera = data;
            pantallaStandby.classList.add('opacity-0', 'hidden');
            pantallaCarrera.classList.remove('hidden');
            setTimeout(() => pantallaCarrera.classList.remove('opacity-0'), 50);

            uiAtleta.textContent = `${data.nombres} ${data.apellidos}`;
            uiPrueba.textContent = `${data.distancia_total}m ${data.estilo}`;
            uiDistanciaPool.textContent = `${data.tipo_piscina}m`;
            
            tiempoObjetivoMs = parseInt(data.tiempo_objetivo_ms) || estimarTiempo(data.distancia_total, data.estilo);
            velocidadGhost = (data.distancia_total) / (tiempoObjetivoMs - reaccionGhostMs); 
            lblGhost.textContent = data.tiempo_objetivo_ms ? "RECORD PERSONAL" : "RITMO ESTIMADO";

            const estadoCarrera = data.estado_carrera;

            if (estadoCarrera === 'iniciando') {
                detenerRelojInterno();
                uiReloj.textContent = "00:00.00";
                uiEstado.textContent = "EN SUS MARCAS";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-amber-500 animate-pulse";
                uiReloj.classList.remove('glow-green', 'text-emerald-400');
                
                resetearSimulador3D();
            } 
            else if (estadoCarrera === 'en_curso' || estadoCarrera === 'en_viraje') {
                uiEstado.textContent = estadoCarrera === 'en_viraje' ? "EN VIRAJE..." : "NADANDO";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-emerald-400";
                
                if (estadoActual !== 'en_curso' && estadoActual !== 'en_viraje') {
                    const offsetMs = serverTime - parseInt(data.inicio_timestamp_ms);
                    inicioRelojCliente = performance.now() - offsetMs;
                    arrancarRelojInterno();
                }
            }
            else if (estadoCarrera === 'finalizado') {
                detenerRelojInterno();
                uiEstado.textContent = "TIEMPO OFICIAL";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-emerald-400";
                uiReloj.classList.add('glow-green', 'text-emerald-400');
                uiReloj.textContent = formatearMilisegundos(parseFloat(data.ultimo_tiempo_parcial_ms));
                
                moverAvatar3D(meshReal, parseInt(data.distancia_total), parseInt(data.distancia_total), parseInt(data.tipo_piscina), 10);
            }

            estadoActual = estadoCarrera;
        }

        function arrancarRelojInterno() {
            const actualizar = () => {
                const transcurrido = performance.now() - inicioRelojCliente;
                uiReloj.textContent = formatearMilisegundos(transcurrido);
                
                if(datosCarrera && transcurrido > 0) {
                    const longPiscina = parseInt(datosCarrera.tipo_piscina); 
                    const distTotal = parseInt(datosCarrera.distancia_total);

                    // ==============================================
                    // 1. FANTASMA
                    // ==============================================
                    let tiempoEfectivoGhost = transcurrido - reaccionGhostMs;
                    let distGhost = tiempoEfectivoGhost > 0 ? (tiempoEfectivoGhost * velocidadGhost) : 0;
                    if (distGhost > distTotal) distGhost = distTotal;
                    moverAvatar3D(meshGhost, distGhost, distTotal, longPiscina, -10);

                    // ==============================================
                    // 2. ATLETA REAL (Con Auto-Deducción de Reacción)
                    // ==============================================
                    let reaccionRealMs = parseFloat(datosCarrera.tiempo_reaccion_ms) || (parseFloat(datosCarrera.tiempo_reaccion_seg) * 1000) || 0;
                    
                    // SOLUCIÓN 1: Si el entrenador aún NO presiona Reacción (0), asumimos 700ms temporales para que el muñeco salte y no se congele.
                    let reaccionEfectiva = reaccionRealMs > 0 ? reaccionRealMs : 700;

                    let distUltimoTramo = parseInt(datosCarrera.ultima_distancia_recorrida_m) || 0;
                    let tiempoUltimoTramo = parseFloat(datosCarrera.ultimo_tiempo_parcial_ms) || 0;
                    let distReal = 0;
                    
                    if (distUltimoTramo === 0) {
                        // Tramo 1 (Salto y Nado inicial)
                        let tiempoEnMovimiento = transcurrido - reaccionEfectiva;
                        if (tiempoEnMovimiento < 0) {
                            distReal = 0; // Espera en el taco esos milisegundos
                        } else {
                            distReal = tiempoEnMovimiento * velocidadGhost; // Salta y nada fluído
                        }
                    } else {
                        // Tramos > 0
                        let tiempoDesdeUltimoTramo = transcurrido - tiempoUltimoTramo;
                        let tiempoEfectivoTramo = tiempoUltimoTramo - reaccionEfectiva;
                        if (tiempoEfectivoTramo <= 0) tiempoEfectivoTramo = 1;
                        
                        velocidadActualProyectada = distUltimoTramo / tiempoEfectivoTramo; 
                        distReal = distUltimoTramo + (tiempoDesdeUltimoTramo * velocidadActualProyectada);
                    }
                    
                    // ==============================================
                    // SOLUCIÓN 2: CLAMP ESTRICTO AL PRÓXIMO SPLIT
                    // ==============================================
                    // Si el entrenador marca botones cada 25m, el límite no es 50m, es 25m.
                    let intervaloSplit = parseInt(datosCarrera.distancia_split) || 25; 
                    let proximoLimite = distUltimoTramo + intervaloSplit;
                    
                    if (proximoLimite > distTotal) proximoLimite = distTotal;
                    
                    // Si el avatar alcanza la distancia del split y el botón no se ha presionado,
                    // se queda nadando en ese punto exacto (ej. 24.999m -> mitad de la piscina)
                    if (distReal >= proximoLimite) {
                        distReal = proximoLimite - 0.001; 
                    }

                    if(datosCarrera.estado_carrera === 'en_viraje') {
                        distReal = distUltimoTramo - 0.001; 
                    }

                    moverAvatar3D(meshReal, distReal, distTotal, longPiscina, 10);
                }

                animacionReloj = requestAnimationFrame(actualizar);
            };
            cancelAnimationFrame(animacionReloj);
            animacionReloj = requestAnimationFrame(actualizar);
        }

        // ==========================================
        // MATEMÁTICA VISUAL (Posición Z dinámica para carriles)
        // ==========================================
        function moverAvatar3D(mesh, distanciaRecorrida, distanciaTotal, longitudPiscinaMts, zOffset) {
            let lapActual = Math.floor(distanciaRecorrida / longitudPiscinaMts);
            let metrosEnLap = distanciaRecorrida % longitudPiscinaMts; 
            
            if (distanciaRecorrida >= distanciaTotal) {
                lapActual = Math.floor(distanciaTotal / longitudPiscinaMts) - 1;
                metrosEnLap = longitudPiscinaMts;
            }

            // Mantiene el muñeco en su carril respectivo (10 = Real, -10 = Ghost)
            mesh.position.z = zOffset;

            // --- POSICIÓN EN EL TACO (0 Metros) ---
            if (distanciaRecorrida === 0) {
                mesh.position.set(-52, 4.2, zOffset);
                mesh.rotation.set(0, 0, 0);
                return;
            }

            // --- FASE DE CLAVADO (0 a 4 metros) ---
            const metrosDeSalto = 4;
            if (lapActual === 0 && distanciaRecorrida < metrosDeSalto) {
                let progresoSalto = distanciaRecorrida / metrosDeSalto; // 0.0 a 1.0
                let unidades3DPorMetro = poolLength / longitudPiscinaMts;
                let finSaltoX = -50 + (metrosDeSalto * unidades3DPorMetro);

                mesh.position.x = -52 + (finSaltoX - (-52)) * progresoSalto;
                mesh.position.y = 4.2 * (1 - Math.pow(progresoSalto, 1.5));
                mesh.rotation.z = -(Math.PI / 4) * Math.sin(progresoSalto * Math.PI);
                mesh.rotation.y = 0;
                return; 
            }

            // --- FASE DE NADO ---
            let porcentajeAvance = metrosEnLap / longitudPiscinaMts;
            mesh.position.y = (Math.sin(performance.now() * 0.005) * 0.2); // Flotabilidad
            mesh.rotation.z = 0; 
            
            if (lapActual % 2 === 0) {
                mesh.position.x = (-poolLength / 2) + (poolLength * porcentajeAvance);
                mesh.rotation.y = 0; 
            } else {
                mesh.position.x = (poolLength / 2) - (poolLength * porcentajeAvance);
                mesh.rotation.y = Math.PI; // Da la vuelta (180 grados)
            }
        }

        function resetearSimulador3D() {
            meshReal.position.set(-52, 4.2, 10);
            meshReal.rotation.set(0, 0, 0);
            meshGhost.position.set(-52, 4.2, -10);
            meshGhost.rotation.set(0, 0, 0);
        }

        function mostrarStandby() {
            if (estadoActual !== 'standby') {
                detenerRelojInterno();
                pantallaCarrera.classList.add('opacity-0');
                setTimeout(() => {
                    pantallaCarrera.classList.add('hidden');
                    pantallaStandby.classList.remove('hidden', 'opacity-0');
                }, 1000);
                estadoActual = 'standby';
            }
        }
        function detenerRelojInterno() { cancelAnimationFrame(animacionReloj); }
        
        function formatearMilisegundos(ms) { 
            if(ms < 0) ms = 0;
            const tC = Math.floor(ms / 10), c = tC % 100, tS = Math.floor(tC / 100), s = tS % 60, m = Math.floor(tS / 60);
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}.${c.toString().padStart(2, '0')}`;
        }
        function estimarTiempo(dist, est) { return (dist / 50) * 35000; }
    </script>
</body>
</html>