<?php
// Archivo: vista/complementos/header.php
// Barra superior común con menú desplegable para móvil
?>
<header class="sticky top-0 z-20 bg-white/80 dark:bg-[#0f0d23]/80 backdrop-blur-md border-b border-gray-200 dark:border-[#252345] py-2 sm:py-3 px-3 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="flex justify-between items-center gap-2 sm:gap-6">
        <!-- Sección izquierda: hamburguesa + título -->
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <button id="openMenuBtn" class="text-gray-700 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300 text-xl sm:text-2xl focus:outline-none lg:hidden flex-shrink-0 transition-colors">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-sm sm:text-xl lg:text-2xl font-bold text-gray-800 dark:text-white tracking-wide flex items-center gap-1 sm:gap-2 truncate">
                <i class="<?php echo $iconModulo ?? 'fas fa-id-card'; ?> text-indigo-500 dark:text-indigo-400 text-xs sm:text-base lg:text-xl flex-shrink-0"></i>
                <span class="block sm:hidden truncate max-w-[120px]"><?php echo $tituloPaginaResponsive ?? 'Sistema'; ?></span>
                <span class="hidden sm:block truncate"><?php echo $tituloPagina ?? 'Sistema'; ?></span>
            </h1>
        </div>

        <!-- Sección derecha: notificaciones, ayuda, avatar -->
        <div class="flex items-center gap-3 sm:gap-5">
            <!-- NOTIFICACIONES -->
            <button id="btnNotificaciones" class="relative inline-flex items-center justify-center text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition focus:outline-none">
                <i class="fas fa-bell text-lg sm:text-xl"></i>
                <span id="badgeNotificaciones" class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center border border-white dark:border-[#0f0d23] hidden">0</span>
            </button>
            <!-- AYUDA -->
            <button id="btnAyuda" class="text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition">
                <i class="fas fa-question-circle text-lg sm:text-xl"></i>
            </button>

            <!-- Avatar con menú desplegable -->
            <div class="relative">
                <button id="btnPerfilDropdown" class="focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'Usuario'); ?>&background=4f46e5&color=fff&bold=true" 
                         class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border-2 border-indigo-500 shadow-md">
                </button>
                <div id="menuPerfilDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-xl shadow-xl z-50 transition-colors">
                    <div class="p-3 border-b border-gray-200 dark:border-[#252345]">
                        <p class="text-gray-800 dark:text-white text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1"><?php echo $_SESSION['rol'] ?? 'Usuario'; ?></p>                       
                    </div>
                    <a href="?p=salir" class="flex items-center gap-2 p-3 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 rounded-b-xl transition">
                        <i class="fas fa-sign-out-alt text-xs"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- PANEL DE NOTIFICACIONES (flotante) -->
<div id="panelNotificaciones" class="fixed right-4 sm:right-6 top-16 sm:top-20 w-80 sm:w-96 bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl shadow-2xl shadow-black/30 dark:shadow-black/50 z-50 hidden panel-transition transform scale-95 opacity-0 origin-top-right transition-colors">
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-[#252345]">
        <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-bell text-indigo-500 dark:text-indigo-400"></i> Notificaciones
        </h3>
        <button id="cerrarPanelNotif" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white text-xl">&times;</button>
    </div>
    <div class="max-h-96 overflow-y-auto divide-y divide-gray-200 dark:divide-[#252345]" id="listaNotificaciones">
        <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">Cargando notificaciones...</div>
    </div>
    <div class="p-3 text-center border-t border-gray-200 dark:border-[#252345]">
        <a href="#" class="text-[11px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium uppercase tracking-wider">Ver todas</a>
    </div>
</div>

<!-- Overlay para cerrar panel de notificaciones -->
<div id="overlayNotif" class="fixed inset-0 bg-black/30 z-40 hidden"></div>

<style>
    .panel-transition { transition: transform 0.2s ease, opacity 0.2s ease; }
    /* Mejora distribución del header en móvil */
    @media (max-width: 640px) {
        header .flex.justify-between {
            gap: 0.5rem;
        }
        header h1 span {
            max-width: 110px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        header button {
            min-width: 36px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    }
    @media (min-width: 1024px) {
        header .flex.justify-between {
            gap: 2rem;
        }
    }
    @media (max-width: 640px) {
        #btnNotificaciones span {
            top: -2px !important;
            right: -2px !important;
        }
    }
</style>

<script>
    (function() {
        // ========== CONTROL DEL MENÚ DE PERFIL (desplegable) ==========
        const btnPerfil = document.getElementById('btnPerfilDropdown');
        const menuPerfil = document.getElementById('menuPerfilDropdown');
        
        function togglePerfilMenu(e) {
            e.stopPropagation();
            menuPerfil.classList.toggle('hidden');
        }
        
        function cerrarPerfilMenu() {
            menuPerfil.classList.add('hidden');
        }
        
        if (btnPerfil) {
            btnPerfil.addEventListener('click', togglePerfilMenu);
        }
        
        document.addEventListener('click', function(event) {
            if (menuPerfil && !menuPerfil.contains(event.target) && !btnPerfil.contains(event.target)) {
                cerrarPerfilMenu();
            }
        });

        // ========== CONTROL DEL PANEL DE NOTIFICACIONES ==========
        const btnNotif = document.getElementById('btnNotificaciones');
        const panelNotif = document.getElementById('panelNotificaciones');
        const overlayNotif = document.getElementById('overlayNotif');
        const cerrarPanel = document.getElementById('cerrarPanelNotif');

        function abrirPanel() {
            if (!panelNotif || !overlayNotif) return;
            panelNotif.classList.remove('hidden');
            overlayNotif.classList.remove('hidden');
            setTimeout(() => {
                panelNotif.classList.add('scale-100', 'opacity-100');
                panelNotif.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function cerrarPanelNotif() {
            if (!panelNotif || !overlayNotif) return;
            panelNotif.classList.add('scale-95', 'opacity-0');
            panelNotif.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                panelNotif.classList.add('hidden');
                overlayNotif.classList.add('hidden');
            }, 200);
        }

        if (btnNotif) {
            btnNotif.addEventListener('click', (e) => {
                e.stopPropagation();
                if (panelNotif.classList.contains('hidden')) {
                    abrirPanel();
                } else {
                    cerrarPanelNotif();
                }
            });
        }
        if (cerrarPanel) cerrarPanel.addEventListener('click', cerrarPanelNotif);
        if (overlayNotif) overlayNotif.addEventListener('click', cerrarPanelNotif);

        // ========== BOTÓN DE AYUDA ==========
        const btnAyuda = document.getElementById('btnAyuda');
if (btnAyuda) {
    btnAyuda.addEventListener('click', () => {
        // Notificamos globalmente que se hizo clic en el botón de ayuda
        document.dispatchEvent(new CustomEvent('iniciar-tour-guiado'));
    });
}

        // ========== CARGAR NOTIFICACIONES REALES POR AJAX ==========
        async function cargarNotificaciones() {
            const contenedor = document.getElementById('listaNotificaciones');
            const badgeContador = document.getElementById('badgeNotificaciones'); 
            
            if (!contenedor) return;

            contenedor.innerHTML = '<div class="p-4 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i></div>';

            try {
                const respuesta = await fetch('index.php?p=notificaciones&accion=listar');
                const resultado = await respuesta.json();

                if (resultado.status === 'success' && resultado.data.length > 0) {
                    contenedor.innerHTML = '';

                    resultado.data.forEach(notif => {
                        const clasesEstado = (notif.leida == 1) 
                            ? 'opacity-60' 
                            : 'bg-indigo-50 dark:bg-white/5 border-l-2 border-indigo-500';
                        const ruta = notif.enlace_url ? `'${notif.enlace_url}'` : 'null';    
                        
                        contenedor.innerHTML += `
                            <div class="p-4 hover:bg-gray-100 dark:hover:bg-[#1b1937] transition flex gap-3 cursor-pointer ${clasesEstado}" onclick="marcarComoLeida(${notif.id_notificacion}, this, ${ruta})">
                                <div class="flex-shrink-0 w-8 h-8 bg-${notif.color}-500/20 rounded-full flex items-center justify-center text-${notif.color}-400">
                                    <i class="fas ${notif.icono} text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800 dark:text-white font-medium">${notif.titulo}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${notif.mensaje}</p>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-500 mt-2 block"><i class="fas fa-clock mr-1"></i> ${notif.tiempo_relativo}</span>
                                </div>
                            </div>
                        `;
                    });

                    if (badgeContador) {
                        if (resultado.no_leidas > 0) {
                            badgeContador.textContent = resultado.no_leidas;
                            badgeContador.classList.remove('hidden');
                        } else {
                            badgeContador.classList.add('hidden');
                        }
                    }

                } else {
                    contenedor.innerHTML = `
                        <div class="p-6 text-center">
                            <i class="fas fa-bell-slash text-3xl text-gray-400 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No tienes notificaciones nuevas.</p>
                        </div>`;
                }
            } catch (error) {
                console.error("Error obteniendo notificaciones", error);
                contenedor.innerHTML = '<p class="p-4 text-center text-red-500 dark:text-red-400 text-sm">Error al cargar datos.</p>';
            }
        }

        cargarNotificaciones();

        // ========== MARCAR COMO LEÍDA EN TIEMPO REAL ==========
        window.marcarComoLeida = async function(id_notif, elementoHtml, enlaceUrl = null) {
            id_notif = parseInt(id_notif);
            if (isNaN(id_notif) || id_notif <= 0) {
                if (typeof UI !== 'undefined') UI.error('Error', "Intento de manipulación de ID bloqueado.");
                return;
            }

            if (elementoHtml.classList.contains('opacity-60') || elementoHtml.dataset.procesando === 'true') {
                return;
            }

            elementoHtml.dataset.procesando = 'true';

            try {
                const respuesta = await fetch('index.php?p=notificaciones&accion=marcar_leida', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id_notif })
                });
                
                const resultado = await respuesta.json();

                if (resultado.status === 'success') {
                    if (enlaceUrl) {
                        window.location.href = enlaceUrl;
                        return;
                    }
                    elementoHtml.classList.remove('bg-indigo-50', 'dark:bg-white/5', 'border-l-2', 'border-indigo-500');
                    elementoHtml.classList.add('opacity-60');

                    const badgeContador = document.getElementById('badgeNotificaciones');
                    if (badgeContador && !badgeContador.classList.contains('hidden')) {
                        let actuales = parseInt(badgeContador.textContent) - 1;
                        if (actuales > 0) {
                            badgeContador.textContent = actuales;
                        } else {
                            badgeContador.classList.add('hidden'); 
                        }
                    }
                } else {
                    if (typeof UI !== 'undefined') UI.error('Error', resultado.message || 'No se pudo cambiar estatus.');
                }
            } catch (error) {
                console.error("Fallo de red al marcar leída", error);
            } finally {
                delete elementoHtml.dataset.procesando;
            }
        };

    })();
</script>