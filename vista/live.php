<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRD | Live 3D Simulator (AAA GLTF)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- IMPORT MAPS PARA LIBRERÍAS PROFESIONALES DE THREE.JS -->
    <script type="importmap">
      {
        "imports": {
          "three": "https://cdn.jsdelivr.net/npm/three@0.152.0/build/three.module.js",
          "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.152.0/examples/jsm/"
        }
      }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; overflow: hidden; margin: 0; padding: 0; transition: background-color 0.5s ease; }
        .font-reloj { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        
        #canvas-container { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; }
        #ui-layer { position: relative; z-index: 10; pointer-events: none; }
        
        .glow-text { text-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .dark .glow-text { text-shadow: 0 0 20px rgba(255,255,255,0.3); }
        .glow-green { text-shadow: 0 0 30px rgba(16, 185, 129, 0.8); }
        
        .pointer-auto { pointer-events: auto; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#060512]">

    <div id="canvas-container"></div>

    <div id="ui-layer" class="w-full h-screen flex flex-col justify-between p-4 sm:p-8 md:p-12">
        
        <div class="absolute top-4 right-4 sm:top-8 sm:right-8 pointer-auto z-50">
            <button id="btnTheme" class="w-12 h-12 rounded-full bg-white/20 dark:bg-black/40 backdrop-blur-md border border-slate-300 dark:border-indigo-500/30 text-slate-700 dark:text-indigo-400 flex items-center justify-center hover:scale-110 transition-transform shadow-lg">
                <i class="fas fa-moon dark:hidden text-xl"></i>
                <i class="fas fa-sun hidden dark:block text-xl"></i>
            </button>
        </div>

        <div id="pantallaStandby" class="absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-1000 bg-slate-50/90 dark:bg-[#060512]/90 backdrop-blur-md z-40">
            <i class="fas fa-swimmer text-6xl md:text-8xl text-indigo-500/50 mb-6"></i>
            <h1 class="text-4xl sm:text-6xl md:text-8xl font-black text-slate-800 dark:text-gray-300 tracking-widest uppercase transition-colors duration-500">SGRD LIVE</h1>
            <p class="text-indigo-500 dark:text-indigo-400 mt-4 animate-pulse uppercase tracking-widest font-bold text-sm md:text-xl">Esperando Atleta...</p>
        </div>

        <div id="pantallaCarrera" class="w-full h-full flex flex-col justify-between opacity-0 hidden transition-opacity duration-1000 z-10">
            <div class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-end mt-12 sm:mt-0">
                <div class="mb-4 sm:mb-0">
                    <div class="inline-block px-3 py-1 md:px-5 md:py-2 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 font-bold tracking-widest uppercase text-[10px] md:text-sm mb-2" id="uiPrueba">--</div>
                    <h2 class="text-4xl sm:text-6xl lg:text-[80px] font-black text-slate-900 dark:text-white tracking-tighter uppercase glow-text leading-none transition-colors duration-500" id="uiAtleta">--</h2>
                </div>
                
                <div class="text-left sm:text-right mt-2 sm:mt-0">
                    <span id="uiEstado" class="block text-sm md:text-xl font-bold uppercase tracking-widest text-slate-500 dark:text-gray-400 mb-1">--</span>
                    <span id="uiReloj" class="font-reloj text-[15vw] sm:text-8xl lg:text-[130px] leading-none font-extrabold text-slate-900 dark:text-white transition-colors duration-300 block">00:00.00</span>
                </div>
            </div>

            <div class="w-full flex justify-between items-end pb-2 md:pb-6">
                <div class="flex gap-4">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 md:w-5 md:h-5 bg-emerald-500 rounded-sm shadow-[0_0_15px_#10b981]"></div><span class="text-slate-700 dark:text-white text-[10px] md:text-sm font-bold tracking-widest transition-colors duration-500">ATLETA REAL</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 md:w-5 md:h-5 bg-slate-400 rounded-sm"></div><span class="text-slate-700 dark:text-white text-[10px] md:text-sm font-bold tracking-widest transition-colors duration-500" id="lblGhost">RECORD</span></div>
                </div>
                <div class="text-slate-500 font-bold text-[10px] md:text-sm uppercase tracking-widest">
                    Piscina <span id="uiDistanciaPool">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT TIPO MODULE -->
    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
        import * as SkeletonUtils from 'three/addons/utils/SkeletonUtils.js';

        // ==========================================
        // 1. CONFIGURACIÓN DEL MOTOR 3D Y MODO CLARO/OSCURO
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
        scene.add(blockReal);
        const blockGhost = new THREE.Mesh(blockGeo, blockMat);
        blockGhost.position.set(-poolLength/2 - 2, 1.5, -10); 
        scene.add(blockGhost);

        // ==========================================
        // 2. CARGA DEL ARCHIVO .GLB Y ANIMACIONES
        // ==========================================
        const meshReal = new THREE.Group();
        const meshGhost = new THREE.Group();
        scene.add(meshReal);
        scene.add(meshGhost);

        let mixerReal = null, mixerGhost = null;
        let accionesReal = {}, accionesGhost = {};
        let animActualReal = '', animActualGhost = '';

        const loader = new GLTFLoader();
        
        loader.load('/ProyectoPiscinaBolivariana/SIS-Biomec/assets/avatar3D/nadador.glb', (gltf) => {
            console.log("✅ ARCHIVO nadador.glb CARGADO CON ÉXITO");
            
            const modeloBase = gltf.scene;
            
            modeloBase.scale.set(5.5, 5.5, 5.5); 
            modeloBase.rotation.y = Math.PI / 2; 

            modeloBase.traverse((c) => {
                if (c.isMesh) c.castShadow = true;
            });

            // Filtramos el "Root Motion" (desplazamiento) de Mixamo
            gltf.animations.forEach((clip) => {
                clip.tracks = clip.tracks.filter(track => !track.name.match(/Hips.*\.position/i));
            });

            // 1. Configurar Atleta Real
            meshReal.add(modeloBase);
            mixerReal = new THREE.AnimationMixer(modeloBase);
            gltf.animations.forEach((clip) => {
                let accionReal = mixerReal.clipAction(clip);
                if (clip.name === 'Salto') {
                    accionReal.setLoop(THREE.LoopOnce);
                    accionReal.clampWhenFinished = true; 
                }
                accionesReal[clip.name] = accionReal;
            });

            // 2. Configurar Atleta Fantasma (Clonado)
            const modeloGhost = SkeletonUtils.clone(modeloBase);
            modeloGhost.traverse((c) => {
                if (c.isMesh) {
                    c.material = new THREE.MeshStandardMaterial({
                        color: 0x9ca3af,
                        transparent: true,
                        opacity: 0.45
                    });
                }
            });
            meshGhost.add(modeloGhost);
            mixerGhost = new THREE.AnimationMixer(modeloGhost);
            gltf.animations.forEach((clip) => {
                let accionGhost = mixerGhost.clipAction(clip);
                if (clip.name === 'Salto') {
                    accionGhost.setLoop(THREE.LoopOnce);
                    accionGhost.clampWhenFinished = true;
                }
                accionesGhost[clip.name] = accionGhost;
            });

            reproducirAnimacion('Real', 'Taco');
            reproducirAnimacion('Ghost', 'Taco');
        }, undefined, (error) => {
            console.error('❌ Error cargando nadador.glb:', error);
        });

        function reproducirAnimacion(tipo, nombreNuevaAnim) {
            const esReal = (tipo === 'Real');
            const acciones = esReal ? accionesReal : accionesGhost;
            const animActual = esReal ? animActualReal : animActualGhost;

            if (!acciones[nombreNuevaAnim] || animActual === nombreNuevaAnim) return;

            if (animActual && acciones[animActual]) {
                acciones[animActual].fadeOut(0.3); 
            }
            
            acciones[nombreNuevaAnim].reset().fadeIn(0.3).play(); 

            if (esReal) animActualReal = nombreNuevaAnim;
            else animActualGhost = nombreNuevaAnim;
        }

        // ==========================================
        // 3. SISTEMA DE TEMAS (DARK/LIGHT)
        // ==========================================
        const htmlElement = document.documentElement;
        const btnTheme = document.getElementById('btnTheme');

        function actualizarColores3D() {
            const isDark = htmlElement.classList.contains('dark');
            if (isDark) {
                scene.fog.color.setHex(0x060512); 
                ambientLight.intensity = 0.7;
                dirLight.intensity = 0.8;
                water.material.color.setHex(0x0891b2);
            } else {
                scene.fog.color.setHex(0xf8fafc); 
                ambientLight.intensity = 1.2;
                dirLight.intensity = 1.1;
                water.material.color.setHex(0x38bdf8); 
            }
        }
        
        btnTheme.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            actualizarColores3D();
        });
        actualizarColores3D();

        function ajustarCamara() {
            const aspect = window.innerWidth / window.innerHeight;
            camera.aspect = aspect;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);

            if (aspect < 0.7) { camera.position.set(0, 95, 60); camera.lookAt(0, -15, 0); } 
            else if (aspect < 1.2) { camera.position.set(0, 65, 65); camera.lookAt(0, -5, 0); } 
            else { camera.position.set(0, 45, 65); camera.lookAt(0, 0, 0); }
        }
        window.addEventListener('resize', ajustarCamara);
        ajustarCamara();

        // ==========================================
        // 4. ANIMACIÓN VISUAL Y LERP
        // ==========================================
        let datosCarrera = null;
        let targetDistReal = 0, targetDistGhost = 0;
        let distVisualReal = 0, distVisualGhost = 0;
        const clock = new THREE.Clock(); 

        function animate3D() {
            requestAnimationFrame(animate3D);
            const delta = clock.getDelta();

            if (mixerReal) mixerReal.update(delta);
            if (mixerGhost) mixerGhost.update(delta);

            const suavizado = 0.05; 
            distVisualReal += (targetDistReal - distVisualReal) * suavizado;
            distVisualGhost += (targetDistGhost - distVisualGhost) * suavizado;

            if(Math.abs(targetDistReal - distVisualReal) < 0.01) distVisualReal = targetDistReal;
            if(Math.abs(targetDistGhost - distVisualGhost) < 0.01) distVisualGhost = targetDistGhost;

            if (datosCarrera) {
                const longPiscina = parseInt(datosCarrera.tipo_piscina);
                const distTotal = parseInt(datosCarrera.distancia_total);
                
                moverAvatar3D(meshReal, distVisualReal, distTotal, longPiscina, 10, 'Real');
                moverAvatar3D(meshGhost, distVisualGhost, distTotal, longPiscina, -10, 'Ghost');
            }

            renderer.render(scene, camera);
        }
        animate3D();


        // ==========================================
        // 5. CONTROL DE MOVIMIENTOS Y REPRODUCTOR GLTF
        // ==========================================
// ==========================================
// 5. CONTROL DE MOVIMIENTOS (CON AJUSTES SOLICITADOS)
// ==========================================
function moverAvatar3D(mesh, distanciaRecorrida, distanciaTotal, longitudPiscinaMts, zOffset, tipo) {
    let lapActual = Math.floor(distanciaRecorrida / longitudPiscinaMts);
    let metrosEnLap = distanciaRecorrida % longitudPiscinaMts; 
    
    if (distanciaRecorrida >= distanciaTotal) {
        lapActual = Math.floor(distanciaTotal / longitudPiscinaMts) - 1;
        metrosEnLap = longitudPiscinaMts;
    }

    let lastX = mesh.userData.lastX || mesh.position.x;
    let velocidadVisual = Math.abs(mesh.position.x - lastX);
    mesh.userData.lastX = mesh.position.x;

    mesh.position.z = zOffset;

    // --- CONSTANTES FÍSICAS (AJUSTADAS) ---
    const Y_TACO = 1.8;            // Altura sobre el taco (pies tocando el bloque)
    const Y_NADO = -5.5;           // Profundidad de nado (cabeza fuera)
    const Y_FLECHA = -7.5;         // Reposo en agua (mantenemos tu valor original)

    const radioCuerpo = 7.0; 
    const paredSalida = (-poolLength / 2) + radioCuerpo;
    const paredVuelta = (poolLength / 2) - radioCuerpo;
    const distanciaEfectiva = paredVuelta - paredSalida;

    // --- FASE 0: En el Taco ---
    if (distanciaRecorrida === 0) {
        mesh.position.set(-52, Y_TACO, zOffset);
        mesh.rotation.set(0, 0, 0); 
        mesh.userData.currentY = Y_TACO; 
        reproducirAnimacion(tipo, 'Taco');
        return;
    }

    // --- FASE 1: Clavado (Salto) ---
    const metrosDeSalto = 4;
    if (lapActual === 0 && distanciaRecorrida < metrosDeSalto) {
        let progresoSalto = distanciaRecorrida / metrosDeSalto; 
        
        let avanceSalto3D = distanciaEfectiva * (metrosDeSalto / longitudPiscinaMts);
        let finSaltoX = paredSalida + avanceSalto3D;

        mesh.position.x = -52 + (finSaltoX - (-52)) * progresoSalto;
        
        // Curva de salto: desde Y_TACO hasta Y_NADO
        let alturaSalto = Y_TACO - ( (Y_TACO - Y_NADO) * Math.pow(progresoSalto, 1.3) );
        mesh.position.y = alturaSalto;
        mesh.userData.currentY = alturaSalto;

        mesh.rotation.z = -(Math.PI / 4) * Math.sin(progresoSalto * Math.PI);
        mesh.rotation.y = 0;

        reproducirAnimacion(tipo, 'Salto');
        return; 
    }

    // --- FASE 2: Nado en agua ---
    let porcentajeAvance = metrosEnLap / longitudPiscinaMts;
    
    if (lapActual % 2 === 0) {
        mesh.position.x = paredSalida + (distanciaEfectiva * porcentajeAvance);
        mesh.rotation.y = 0; 
    } else {
        mesh.position.x = paredVuelta - (distanciaEfectiva * porcentajeAvance);
        mesh.rotation.y = Math.PI; 
    }
    mesh.rotation.z = 0; 

    // Determinar profundidad según si está nadando o en reposo (flecha)
    let targetY = (velocidadVisual > 0.01) ? Y_NADO : Y_FLECHA;
    
    if (velocidadVisual > 0.01) { 
        reproducirAnimacion(tipo, 'Nado');
    } else {
        reproducirAnimacion(tipo, 'Flecha'); 
    }

    // Transición suave (lerp) con factor 0.15
    if (mesh.userData.currentY === undefined) mesh.userData.currentY = targetY;
    mesh.userData.currentY += (targetY - mesh.userData.currentY) * 0.15; 
    
    // Aplicamos la altura calculada sumándole el balanceo natural del agua
    mesh.position.y = mesh.userData.currentY + (Math.sin(performance.now() * 0.005) * 0.1); 
}



        // ==========================================
        // 6. LÓGICA DE TELEMETRÍA
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
        let velocidadGhost = 0; 
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
            
            let tiempoObjetivoMs = parseInt(data.tiempo_objetivo_ms) || estimarTiempo(data.distancia_total, data.estilo);
            velocidadGhost = data.distancia_total / (tiempoObjetivoMs - reaccionGhostMs); 
            lblGhost.textContent = data.tiempo_objetivo_ms ? "RECORD PERSONAL" : "RITMO ESTIMADO";

            const estadoCarrera = data.estado_carrera;

            if (estadoCarrera === 'iniciando') {
                detenerRelojInterno();
                uiReloj.textContent = "00:00.00";
                uiEstado.textContent = "EN SUS MARCAS";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-amber-500 animate-pulse";
                uiReloj.classList.remove('glow-green', 'text-emerald-400', 'dark:text-emerald-400');
                uiReloj.classList.add('text-slate-900', 'dark:text-white');
                resetearSimulador3D();
            } 
            else if (estadoCarrera === 'en_curso' || estadoCarrera === 'en_viraje') {
                uiEstado.textContent = estadoCarrera === 'en_viraje' ? "EN VIRAJE..." : "NADANDO";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-emerald-500 dark:text-emerald-400";
                
                if (estadoActual !== 'en_curso' && estadoActual !== 'en_viraje') {
                    const offsetMs = serverTime - parseInt(data.inicio_timestamp_ms);
                    inicioRelojCliente = performance.now() - offsetMs;
                    arrancarRelojInterno();
                }
            }
            else if (estadoCarrera === 'finalizado') {
                detenerRelojInterno();
                uiEstado.textContent = "TIEMPO OFICIAL";
                uiEstado.className = "text-xl sm:text-2xl font-bold uppercase tracking-widest text-emerald-500 dark:text-emerald-400";
                uiReloj.classList.remove('text-slate-900', 'dark:text-white');
                uiReloj.classList.add('glow-green', 'text-emerald-500', 'dark:text-emerald-400');
                uiReloj.textContent = formatearMilisegundos(parseFloat(data.ultimo_tiempo_parcial_ms));
                targetDistReal = parseInt(data.distancia_total);
            }

            estadoActual = estadoCarrera;
        }

        function arrancarRelojInterno() {
            const actualizar = () => {
                const transcurrido = performance.now() - inicioRelojCliente;
                uiReloj.textContent = formatearMilisegundos(transcurrido);
                
                if(datosCarrera && transcurrido > 0) {
                    const distTotal = parseInt(datosCarrera.distancia_total);

                    let tiempoEfectivoGhost = transcurrido - reaccionGhostMs;
                    targetDistGhost = tiempoEfectivoGhost > 0 ? (tiempoEfectivoGhost * velocidadGhost) : 0;
                    if (targetDistGhost > distTotal) targetDistGhost = distTotal;

                    let reaccionRealMs = parseFloat(datosCarrera.tiempo_reaccion_ms) || (parseFloat(datosCarrera.tiempo_reaccion_seg) * 1000) || 0;
                    let reaccionEfectiva = reaccionRealMs > 0 ? reaccionRealMs : 700;

                    let distUltimoTramo = parseInt(datosCarrera.ultima_distancia_recorrida_m) || 0;
                    let tiempoUltimoTramo = parseFloat(datosCarrera.ultimo_tiempo_parcial_ms) || 0;
                    
                    let rawAvance = 0; 

                    if (distUltimoTramo === 0) {
                        let tiempoEnMovimiento = transcurrido - reaccionEfectiva;
                        rawAvance = tiempoEnMovimiento < 0 ? 0 : tiempoEnMovimiento * velocidadGhost; 
                    } else {
                        let tiempoDesdeUltimoTramo = transcurrido - tiempoUltimoTramo;
                        let tiempoEfectivoTramo = tiempoUltimoTramo - reaccionEfectiva;
                        if (tiempoEfectivoTramo <= 0) tiempoEfectivoTramo = 1;
                        let velCalculada = distUltimoTramo / tiempoEfectivoTramo; 
                        rawAvance = tiempoDesdeUltimoTramo * velCalculada;
                    }
                    
                    let intervaloSplit = parseInt(datosCarrera.distancia_split) || 25; 
                    let maxAvanceEnSplit = intervaloSplit; 
                    if (distUltimoTramo + maxAvanceEnSplit > distTotal) {
                        maxAvanceEnSplit = distTotal - distUltimoTramo;
                    }
                    maxAvanceEnSplit -= 0.001; 

                    const zonaFrenado = 3.0; 
                    
                    if (maxAvanceEnSplit > zonaFrenado && rawAvance > (maxAvanceEnSplit - zonaFrenado)) {
                        let limiteFuerte = maxAvanceEnSplit - zonaFrenado;
                        let exceso = rawAvance - limiteFuerte;
                        
                        let avanceSuavizado = limiteFuerte + (zonaFrenado * (1 - Math.exp(-exceso / zonaFrenado)));
                        targetDistReal = distUltimoTramo + avanceSuavizado;
                    } else {
                        targetDistReal = distUltimoTramo + rawAvance;
                        if (targetDistReal >= distUltimoTramo + maxAvanceEnSplit) {
                            targetDistReal = distUltimoTramo + maxAvanceEnSplit;
                        }
                    }

                    if(datosCarrera.estado_carrera === 'en_viraje') {
                        targetDistReal = distUltimoTramo - 0.001; 
                    }
                }

                animacionReloj = requestAnimationFrame(actualizar);
            };
            cancelAnimationFrame(animacionReloj);
            animacionReloj = requestAnimationFrame(actualizar);
        }

function resetearSimulador3D() {
    targetDistReal = 0;
    targetDistGhost = 0;
    distVisualReal = 0;
    distVisualGhost = 0;
    
    const Y_TACO = 1.8; // Mismo valor que en moverAvatar3D
    meshReal.position.set(-52, Y_TACO, 10);
    meshReal.rotation.set(0, 0, 0);
    meshReal.userData.currentY = Y_TACO; 

    meshGhost.position.set(-52, Y_TACO, -10);
    meshGhost.rotation.set(0, 0, 0);
    meshGhost.userData.currentY = Y_TACO;

    reproducirAnimacion('Real', 'Taco');
    reproducirAnimacion('Ghost', 'Taco');
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