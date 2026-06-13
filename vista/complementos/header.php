<?php
// Archivo: vista/complementos/header.php
// Barra superior común con menú desplegable para móvil
?>
<header class="sticky top-0 z-20 bg-[#0f0d23]/80 backdrop-blur-md border-b border-[#252345] py-2 sm:py-3 px-3 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center gap-2 sm:gap-6">
        <!-- Sección izquierda: hamburguesa + título -->
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <button id="openMenuBtn" class="text-indigo-400 text-xl sm:text-2xl focus:outline-none lg:hidden flex-shrink-0">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-sm sm:text-xl lg:text-2xl font-bold text-white tracking-wide flex items-center gap-1 sm:gap-2 truncate">
                <i class="fas fa-id-card text-indigo-500 text-xs sm:text-base lg:text-xl flex-shrink-0"></i>
                <span class="block sm:hidden truncate max-w-[120px]">Representantes</span>
                <span class="hidden sm:block truncate"><?php echo $tituloPagina ?? 'Sistema'; ?></span>
            </h1>
        </div>

        <!-- Sección derecha: notificaciones, ayuda, avatar -->
        <div class="flex items-center gap-3 sm:gap-5">
            <!-- NOTIFICACIONES -->
            <button id="btnNotificaciones" class="relative inline-flex items-center justify-center text-gray-400 hover:text-indigo-400 transition focus:outline-none">
    <i class="fas fa-bell text-lg sm:text-xl"></i>
     <span id="notifBadge" class="absolute -top-1 -right-2 bg-red-500 w-2.5 h-2.5 rounded-full border border-[#0f0d23]"></span>
</button>

            <!-- AYUDA -->
            <button id="btnAyuda" class="text-gray-400 hover:text-indigo-400 transition">
                <i class="fas fa-question-circle text-lg sm:text-xl"></i>
            </button>

            <!-- Avatar con menú desplegable -->
            <div class="relative">
                <button id="btnPerfilDropdown" class="focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'Usuario'); ?>&background=4f46e5&color=fff&bold=true" 
                         class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border-2 border-indigo-500 shadow-md">
                </button>
                <div id="menuPerfilDropdown" class="hidden absolute right-0 mt-2 w-48 bg-[#161430] border border-[#252345] rounded-xl shadow-xl z-50">
                    <div class="p-3 border-b border-[#252345]">
                        <p class="text-white text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></p>
                        <p class="text-[10px] text-gray-400 mt-1"><?php echo $_SESSION['rol'] ?? 'Usuario'; ?></p>
                    </div>
                    <a href="?p=salir" class="flex items-center gap-2 p-3 text-sm text-red-400 hover:bg-red-500/10 rounded-b-xl transition">
                        <i class="fas fa-sign-out-alt text-xs"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- PANEL DE NOTIFICACIONES (flotante) - igual que antes -->
<div id="panelNotificaciones" class="fixed right-4 sm:right-6 top-16 sm:top-20 w-80 sm:w-96 bg-[#161430] border border-[#252345] rounded-2xl shadow-2xl shadow-black/50 z-50 hidden panel-transition transform scale-95 opacity-0 origin-top-right">
    <div class="flex justify-between items-center p-4 border-b border-[#252345]">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-bell text-indigo-400"></i> Notificaciones
        </h3>
        <button id="cerrarPanelNotif" class="text-gray-400 hover:text-white text-xl">&times;</button>
    </div>
    <div class="max-h-96 overflow-y-auto divide-y divide-[#252345]" id="listaNotificaciones">
        <div class="p-4 text-center text-gray-400 text-sm">Cargando notificaciones...</div>
    </div>
    <div class="p-3 text-center border-t border-[#252345]">
        <a href="#" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-medium uppercase tracking-wider">Ver todas</a>
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
    /* Asegura que el título no ocupe más espacio del necesario */
    header h1 span {
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Botones táctiles más amplios */
    header button {
        min-width: 36px;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
}

/* En escritorio, más separación entre grupos */
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
        
        // Cerrar el menú al hacer clic fuera
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
        if (btnAyuda && typeof Swal !== 'undefined') {
            btnAyuda.addEventListener('click', () => {
                Swal.fire({
                    title: 'Centro de Ayuda',
                    text: 'Próximamente encontrarás guías y tutoriales interactivos.',
                    icon: 'info',
                    background: '#161430',
                    color: '#fff',
                    confirmButtonColor: '#4f46e5',
                    iconColor: '#4f46e5',
                    confirmButtonText: 'Entendido'
                });
            });
        }

        // ========== CARGAR NOTIFICACIONES DE EJEMPLO ==========
        function cargarNotificaciones() {
            const contenedor = document.getElementById('listaNotificaciones');
            if (!contenedor) return;
            const notificaciones = [
                { icono: 'fa-check-circle', color: 'emerald', titulo: 'Asistencia registrada', mensaje: 'Tu asistencia al entrenamiento de hoy ha sido confirmada.', tiempo: 'Hace 5 minutos' },
                { icono: 'fa-calendar-alt', color: 'indigo', titulo: 'Nueva sesión programada', mensaje: 'Entrenamiento especial el sábado 15 a las 8am.', tiempo: 'Hace 1 hora' },
                { icono: 'fa-exclamation-triangle', color: 'amber', titulo: 'Recordatorio médico', mensaje: 'Tu evaluación antropométrica está pendiente.', tiempo: 'Ayer' }
            ];
            contenedor.innerHTML = '';
            notificaciones.forEach(notif => {
                contenedor.innerHTML += `
                    <div class="p-4 hover:bg-[#1b1937] transition flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-${notif.color}-500/20 rounded-full flex items-center justify-center text-${notif.color}-400">
                            <i class="fas ${notif.icono} text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm text-white font-medium">${notif.titulo}</p>
                            <p class="text-xs text-gray-400">${notif.mensaje}</p>
                            <span class="text-[10px] text-gray-500 mt-1 block">${notif.tiempo}</span>
                        </div>
                    </div>
                `;
            });
        }
        cargarNotificaciones();
    })();
</script>