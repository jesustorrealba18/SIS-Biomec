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

    <!-- AVISO DE GIRO DE PANTALLA -->
    <div id="overlayGiro" class="fixed inset-0 z-[200] bg-slate-900/95 backdrop-blur-xl flex-col items-center justify-center text-white hidden portrait:flex md:portrait:hidden">
        <i class="fas fa-mobile-alt text-7xl animate-[spin_2s_ease-in-out_infinite] mb-6 text-indigo-400"></i>
        <h3 class="text-2xl font-black tracking-widest uppercase mb-2 text-center">Gira tu dispositivo</h3>
        <p class="text-slate-400 text-center px-8 text-sm">El simulador 3D ofrece una experiencia inmersiva que debe visualizarse en formato horizontal.</p>
    </div>

    <!-- UI FLOTANTE SOBRE EL 3D -->
<div id="ui-layer" class="absolute inset-0 w-full h-[100dvh] flex flex-col justify-between p-2 sm:p-4 md:p-6 pointer-events-none z-10 overflow-hidden">        
        <!-- Botón de Tema -->
        <!--  Modo Claro/oscuro
        <div class="absolute top-2 right-2 sm:top-6 sm:right-6 pointer-events-auto z-50">
            <button id="btnTheme" class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-full bg-white/20 dark:bg-black/40 backdrop-blur-md border border-slate-300 dark:border-indigo-500/30 text-slate-700 dark:text-indigo-400 flex items-center justify-center hover:scale-110 transition-transform shadow-lg cursor-pointer">
                <i class="fas fa-moon dark:hidden text-sm sm:text-lg md:text-xl"></i>
                <i class="fas fa-sun hidden dark:block text-sm sm:text-lg md:text-xl"></i>
            </button>
        </div> -->

        <div id="pantallaStandby" class="absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-1000 bg-slate-50/90 dark:bg-[#060512]/90 backdrop-blur-md z-40">
            <i class="fas fa-swimmer text-6xl md:text-8xl text-indigo-500/50 mb-6 drop-shadow-lg"></i>
            <h1 class="text-5xl sm:text-7xl md:text-8xl font-black text-slate-800 dark:text-gray-300 tracking-widest uppercase transition-colors duration-500 drop-shadow-md">SGRD LIVE</h1>
            <p class="text-indigo-600 dark:text-indigo-400 mt-4 animate-pulse uppercase tracking-widest font-bold text-sm md:text-xl">Esperando Atleta...</p>
        </div>

        <!-- PANTALLA DE CARRERA -->
        <div id="pantallaCarrera" class="w-full h-full flex flex-col justify-between opacity-0 hidden transition-opacity duration-1000 z-10 pt-1 sm:pt-0">
            
            <!-- HEADER: nombre a la izquierda, reloj a la derecha (SIEMPRE HORIZONTAL) -->
            <div class="w-full flex flex-wrap items-start justify-between mt-0.5 gap-x-2">
                <!-- Info Atleta (izquierda, ocupa el espacio disponible) -->
                <div class="flex-1 min-w-0 mr-1">
                    <div class="inline-block px-1.5 py-0.5 md:px-3 md:py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 font-bold tracking-widest uppercase text-[6px] sm:text-[8px] md:text-xs mb-1 backdrop-blur-sm" id="uiPrueba">--</div>
                    <h2 id="uiAtleta" class="text-xs sm:text-lg md:text-2xl lg:text-3xl xl:text-4xl font-black text-slate-900 dark:text-white tracking-tighter uppercase glow-text leading-tight truncate transition-colors duration-500 drop-shadow-xl">--</h2>
                </div>
                <!-- Reloj y Estado (derecha, no se encoge) -->
                <div class="text-right flex-shrink-0">
                    <span id="uiEstado" class="block text-[7px] sm:text-[10px] md:text-sm lg:text-base font-bold uppercase tracking-widest text-slate-600 dark:text-gray-300 mb-0.5 drop-shadow-md">--</span>
                    <span id="uiReloj" class="font-reloj text-xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl leading-none font-extrabold text-slate-900 dark:text-white transition-colors duration-300 block drop-shadow-xl">00:00.00</span>
                </div>
            </div>

            <!-- FOOTER: leyenda (siempre visible) -->
            <div class="w-full flex justify-between items-end pb-1 sm:pb-2 flex-shrink-0">
                <div class="flex gap-1.5 md:gap-4 bg-white/30 dark:bg-black/30 p-1 md:p-3 rounded-xl backdrop-blur-sm border border-slate-200 dark:border-white/10 shadow-lg">
                    <div class="flex items-center gap-1 md:gap-2"><div class="w-2 h-2 md:w-4 md:h-4 bg-slate-800 rounded-sm shadow-sm"></div><span class="text-slate-800 dark:text-white text-[7px] md:text-xs font-bold tracking-widest">REAL</span></div>
                    <div class="flex items-center gap-1 md:gap-2"><div class="w-2 h-2 md:w-4 md:h-4 bg-amber-500 rounded-sm"></div><span class="text-slate-800 dark:text-white text-[7px] md:text-xs font-bold tracking-widest" id="lblGhost">RECORD</span></div>
                </div>
                <div class="bg-indigo-500/10 dark:bg-indigo-900/40 p-1 md:p-3 rounded-xl backdrop-blur-sm border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 font-bold text-[7px] md:text-xs uppercase tracking-widest shadow-lg">
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
        scene.fog = new THREE.FogExp2(0xf8fafc, 0.0025);  // Cambia 0x060512 por 0xf8fafc

        const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.shadowMap.enabled = true;
        container.appendChild(renderer.domElement);

        const ambientLight = new THREE.AmbientLight(0xffffff, 1.2); // Cambia el 0.7 por 1.2 
        scene.add(ambientLight);
        const dirLight = new THREE.DirectionalLight(0xffffff, 1.1);// Cambia el 0.8 por 1.1
        dirLight.position.set(0, 100, 50);
        dirLight.castShadow = true;
        scene.add(dirLight);

        // --- LA PISCINA ---
        const poolLength = 100; 
        const poolWidth = 40;

        const waterGeo = new THREE.PlaneGeometry(poolLength, poolWidth, 32, 32);
        const waterMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.7, roughness: 0.1 });// Cambia el 0x0891b2 por 0x38bdf8
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
                        color: 0xd4a373,          // Dorado arena (cálido, elegante)
                        emissive: 0xb8860b,       // Dorado oscuro con emisión
                        emissiveIntensity: 0.2,
                        transparent: true,
                        opacity: 0.85,
                        roughness: 0.3,
                        metalness: 0.7
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
        htmlElement.classList.remove('dark'); /* Forzar modo claro */
/*      Descomentar si quieres modo claro y oscuro y quitar solo la linea de arriba
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
        actualizarColores3D(); */


   function ajustarCamara() {
    const aspect = window.innerWidth / window.innerHeight;
    camera.aspect = aspect;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);

    // Diferencia Y (position.y - lookAt.y) = 37 para todos los casos
    if (aspect < 0.7) {
        // Móvil vertical: más altura y más distancia para abarcar la piscina
        camera.position.set(0, 110, 85);
        camera.lookAt(0, 73, 0); // 110 - 73 = 37
    } 
    else if (aspect < 1.2) {
        // Tablets: altura media, distancia media
        camera.position.set(0, 75, 80);
        camera.lookAt(0, 38, 0); // 75 - 38 = 37
    } 
    else {
        // Escritorio (tu configuración actual)
        camera.position.set(0, 45, 75);
        camera.lookAt(0, 8, 0); // 45 - 8 = 37
    }
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

        /* function animate3D() {
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
        animate3D(); */

        function animate3D() {
            requestAnimationFrame(animate3D);
            const delta = clock.getDelta();

            if (mixerReal) mixerReal.update(delta);
            if (mixerGhost) mixerGhost.update(delta);

            const suavizado = 0.05; 
            
            // ANTI-GLITCH (RUBBER-BANDING): 
            // Si la distancia que llega del servidor es MENOR a la visual actual, y ya saltó del taco (> 4m),
            // no lo retrocedemos, mantenemos su posición hasta que el tiempo real lo alcance.
            if (targetDistReal < distVisualReal && distVisualReal > 4) {
                targetDistReal = distVisualReal;
            }

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

        // 1. Capturamos el ID del atleta directamente de la URL del navegador
        const urlParams = new URLSearchParams(window.location.search);
        const idAtletaLive = urlParams.get('id_atleta');

        async function escucharTelemetria() {
            try {
                // Si alguien entra a la página sin un ID, lo dejamos en Standby
                if (!idAtletaLive) {
                    mostrarStandby();
                    return;
                }

                // 2. Inyectamos el ID en la petición AJAX
                const res = await fetch(`index.php?p=live&accion=get_telemetria&id_atleta=${idAtletaLive}`);
                const json = await res.json();

                if (json.status === 'success') {
                    procesarDatos(json.data, json.server_time);
                } else {
                    mostrarStandby();
                }
            } catch (error) {
                console.error("Error de conexión:", error);
            }
        }
        
        // El setInterval se mantiene igual
        setInterval(escucharTelemetria, 1000);

        function procesarDatos(data, serverTime) {
            datosCarrera = data;
            pantallaStandby.classList.add('opacity-0', 'hidden');
            pantallaCarrera.classList.remove('hidden');
            setTimeout(() => pantallaCarrera.classList.remove('opacity-0'), 50);

            uiAtleta.textContent = `${data.nombres} ${data.apellidos}`;
            uiPrueba.textContent = `${data.distancia_total}m ${data.estilo}`;
            uiDistanciaPool.textContent = `${data.tipo_piscina}m`;
            
            reaccionGhostMs = data.reaccion_ghost_ms ? parseFloat(data.reaccion_ghost_ms) : 650;
            let tiempoObjetivoMs = parseInt(data.tiempo_objetivo_ms) || estimarTiempo(data.distancia_total, data.estilo);
            velocidadGhost = data.distancia_total / (tiempoObjetivoMs - reaccionGhostMs);
          /*   let tiempoObjetivoMs = parseInt(data.tiempo_objetivo_ms) || estimarTiempo(data.distancia_total, data.estilo);
            velocidadGhost = data.distancia_total / (tiempoObjetivoMs - reaccionGhostMs);  */
            //lblGhost.textContent = data.tiempo_objetivo_ms ? "RECORD PERSONAL" : "RITMO ESTIMADO";
            if (data.tiempo_objetivo_ms) {
                lblGhost.textContent = "RECORD PERSONAL";
                lblGhost.classList.remove('text-slate-800', 'dark:text-white');
                lblGhost.classList.add('text-amber-500', 'dark:text-amber-300'); // un poco más intenso
            } else {
                lblGhost.textContent = "RITMO ESTIMADO";
                lblGhost.classList.remove('text-amber-400', 'dark:text-amber-300');
                lblGhost.classList.add('text-slate-800', 'dark:text-white');
            }

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

                   /*  let tiempoEfectivoGhost = transcurrido - reaccionGhostMs;
                    targetDistGhost = tiempoEfectivoGhost > 0 ? (tiempoEfectivoGhost * velocidadGhost) : 0;
                    if (targetDistGhost > distTotal) targetDistGhost = distTotal; */

            // =============================================================
            // NUEVO: Cálculo de targetDistGhost con FLUIDEZ BASADA EN SPLITS
            // =============================================================
            const splitsPB = datosCarrera.splits_pb;
            if (splitsPB && Array.isArray(splitsPB) && splitsPB.length > 0) {
                // Creamos arreglos acumulativos partiendo desde 0
                let tiemposAcum = [0]; 
                let distanciasAcum = [0];
                
                let acumTiempoMs = 0;
                for (let s of splitsPB) {
                    // Acumulamos el tiempo de cada segmento (ej: 28s + 32s = 60s)
                    acumTiempoMs += parseFloat(s.tiempo_parcial_seg) * 1000;
                    tiemposAcum.push(acumTiempoMs);
                    distanciasAcum.push(parseFloat(s.distancia_parcial_m));
                }
                
                // Descontamos el tiempo que tarda en el bloque de salida
                const tiempoFantasma = transcurrido - reaccionGhostMs;
                
                if (tiempoFantasma <= 0) {
                    targetDistGhost = 0; // Aún en el taco, esperando reaccionar
                } else if (tiempoFantasma >= tiemposAcum[tiemposAcum.length - 1]) {
                    targetDistGhost = distTotal; // Terminó la carrera
                } else {
                    // Encontrar en qué tramo de la carrera está nadando actualmente
                    let i = 0;
                    while (i < tiemposAcum.length - 1 && tiemposAcum[i + 1] < tiempoFantasma) i++;
                    
                    const t0 = tiemposAcum[i];           // Tiempo inicio del tramo
                    const t1 = tiemposAcum[i + 1];       // Tiempo fin del tramo
                    const d0 = distanciasAcum[i];        // Distancia inicio del tramo
                    const d1 = distanciasAcum[i + 1];    // Distancia fin del tramo
                    
                    // Interpolar fluidamente: qué porcentaje del tramo ha nadado
                    const progreso = (tiempoFantasma - t0) / (t1 - t0);
                    targetDistGhost = d0 + progreso * (d1 - d0);
                    
                    if (targetDistGhost > distTotal) targetDistGhost = distTotal;
                }
            } else {
                // --- MODO VELOCIDAD CONSTANTE (fallback si no hay splits) ---
                let tiempoEfectivoGhost = transcurrido - reaccionGhostMs;
                targetDistGhost = tiempoEfectivoGhost > 0 ? (tiempoEfectivoGhost * velocidadGhost) : 0;
                if (targetDistGhost > distTotal) targetDistGhost = distTotal;
            }
            // =============================================================

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