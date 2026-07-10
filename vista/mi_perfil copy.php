<?php
// Establecer el título y el ícono para el header
$tituloPagina = 'Mi Perfil';
$iconoPagina = 'fa-id-card'; // opcional, puedes usarlo en el header
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Mi Perfil Deportivo | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        /* body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; } */
        /* .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 24px; } */
        .estado-Activo { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .estado-Inactivo { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
        .menu-transition { transition: transform 0.3s ease-in-out; }
        .overlay { transition: opacity 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-[#0f0d23] text-gray-800 dark:text-[#a0a0c0] font-sans antialiased">
<!-- <body class="bg-[#0f0d23]"> -->

    <!-- Overlay para móvil cuando el menú está abierto -->
    <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

    <div class="flex flex-col lg:flex-row min-h-screen">
        
        <!-- Sidebar -->
        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-[#0f0d23] border-r border-[#252345] z-40 transform -translate-x-full menu-transition lg:relative lg:translate-x-0 lg:flex-shrink-0 overflow-y-auto">
            <div class="p-4 flex justify-between items-center border-b border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-400 hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include RAIZ . 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-1 flex flex-col min-h-screen">
            
            <!-- HEADER (incluido desde archivo aparte) -->
            <?php include RAIZ . 'vista/complementos/header.php'; ?>

            <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    
    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 bg-[#161430] dark:bg-white/5 p-1.5 rounded-xl mb-6 border border-[#252345] dark:border-white/10">
        
        <button onclick="cambiarPestana('perfil')" id="btn-tab-perfil" class="tab-btn w-full py-2.5 rounded-lg font-bold text-sm text-white bg-indigo-600 shadow-md transition-all">
            <i class="fas fa-user mr-2"></i> Mi Información
        </button>
        
        <button onclick="cambiarPestana('seguridad')" id="btn-tab-seguridad" class="tab-btn w-full py-2.5 rounded-lg font-medium text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all">
            <i class="fas fa-lock mr-2"></i> Seguridad
        </button>
        
        <button onclick="cambiarPestana('preferencias')" id="btn-tab-preferencias" class="tab-btn w-full py-2.5 rounded-lg font-medium text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all">
            <i class="fas fa-sliders-h mr-2"></i> Preferencias
        </button>

    </div>

    <div id="tab-perfil" class="tab-content block animate-fade-in">
        <div class="bg-indigo-900/20 border border-indigo-500/30 p-6 rounded-2xl">
             <!-- Contenido específico de Mi Perfil -->
            <div class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">
                    <!-- Columna izquierda (2/3 en desktop) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Tarjeta de identidad -->
                        <div class="tarjeta bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl shadow-sm p-6 sm:p-8">
                        <!-- <div class="tarjeta p-5 sm:p-6 md:p-8 relative overflow-hidden shadow-xl shadow-black/30"> -->
                            <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
                            <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-6">
                                <div id="perfilFotoContenedor" class="shrink-0"></div>
                                <div class="space-y-2 flex-1 w-full">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <h2 id="lblNombreCompleto" class="text-xl sm:text-2xl font-black text-white uppercase tracking-tight">Cargando...</h2>
                                        <span id="badgeEstado" class="inline-block text-xs px-3 py-1 rounded-full self-center sm:self-start w-fit">---</span>
                                    </div>
                                    <p id="lblCedula" class="text-indigo-400 font-mono text-sm tracking-widest">V-00000000</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-white/5">
                                        <div><p class="text-[10px] text-gray-500 uppercase font-bold">Edad</p><p id="lblEdad" class="text-white font-medium text-sm">—</p></div>
                                        <div><p class="text-[10px] text-gray-500 uppercase font-bold">Género</p><p id="lblSexo" class="text-white font-medium text-sm">—</p></div>
                                        <div><p class="text-[10px] text-gray-500 uppercase font-bold">Categoría</p><p id="lblCategoria" class="text-emerald-400 font-bold text-sm">—</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto y Técnico -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="tarjeta bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl shadow-sm p-6 sm:p-8">
                            <!-- <div class="tarjeta p-5 sm:p-6 bg-[#161430]/60"> -->
                                <h3 class="text-xs uppercase text-indigo-400 font-black tracking-widest mb-4 flex items-center gap-2">
                                    <i class="fas fa-envelope-open-text"></i> Contacto
                                </h3>
                                <div class="space-y-3">
                                    <div><p class="text-[9px] uppercase text-gray-500">Teléfono</p><p id="lblTelefono" class="text-white font-mono text-sm">—</p></div>
                                    <div><p class="text-[9px] uppercase text-gray-500">Correo</p><p id="lblCorreo" class="text-white break-all text-xs sm:text-sm">—</p></div>
                                </div>
                            </div>
                            <div class="tarjeta bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl shadow-sm p-6 sm:p-8">
                            <!-- <div class="tarjeta p-5 sm:p-6 bg-[#161430]/60"> -->
                                <h3 class="text-xs uppercase text-purple-400 font-black tracking-widest mb-4 flex items-center gap-2">
                                    <i class="fas fa-passport"></i> Registro Técnico
                                </h3>
                                <div class="space-y-3">
                                    <div><p class="text-[9px] uppercase text-gray-500">Ficha FEVEDA</p><p id="lblFeveda" class="text-purple-300 font-mono text-sm font-bold">—</p></div>
                                    <div><p class="text-[9px] uppercase text-gray-500">Club</p><p id="lblClub" class="text-white text-sm">—</p></div>
                                </div>
                            </div>
                        </div>

                        <!-- Representante (oculto si no tiene) -->
                        <div id="tarjetaRepresentante" class="tarjeta p-5 sm:p-6 bg-[#161430]/60 border-l-4 border-l-indigo-500 hidden">
                            <h3 class="text-xs uppercase text-indigo-400 font-black tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-user-shield"></i> Representante Legal
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                <div><p class="text-[9px] uppercase text-gray-500">Nombre</p><p id="lblRepNombre" class="text-white font-bold">—</p><p id="lblRepCedula" class="text-gray-500 font-mono text-[10px]">—</p></div>
                                <div><p class="text-[9px] uppercase text-gray-500">Parentesco</p><p id="lblRepParentesco" class="text-indigo-300 font-semibold">—</p></div>
                                <div><p class="text-[9px] uppercase text-gray-500">Teléfono</p><p id="lblRepTelefono" class="text-emerald-400 font-mono font-bold">—</p></div>
                            </div>
                        </div>

                        <!-- Hoja médica -->
                        <div class="tarjeta p-5 sm:p-6 border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-500/5 to-transparent">
                            <h3 class="text-xs uppercase text-emerald-400 font-black tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-notes-medical"></i> Hoja Médica
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                <div class="bg-black/20 p-3 rounded-xl border border-white/5"><p class="text-[9px] uppercase text-gray-500">Grupo Sanguíneo</p><p id="lblSangre" class="text-white text-lg font-black">—</p></div>
                                <div class="bg-black/20 p-3 rounded-xl border border-white/5 sm:col-span-2"><p class="text-[9px] uppercase text-gray-500">Alergias</p><p id="lblAlergias" class="text-gray-300 text-xs">—</p></div>
                            </div>
                            <div id="boxEmergencia" class="bg-black/30 p-4 rounded-xl border border-white/5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                <div><p class="text-[9px] uppercase text-amber-500 font-bold"><i class="fas fa-phone-alt"></i> Emergencia</p><p id="lblContactoNombre" class="text-white text-sm">No registrado</p></div>
                                <p id="lblContactoTlf" class="text-amber-400 font-mono text-sm font-bold">—</p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: QR -->
                    <div class="w-full">
                        <div class="tarjeta p-5 sm:p-6 flex flex-col items-center justify-center border-t-4 border-t-indigo-500 text-center shadow-xl shadow-black/40 bg-gradient-to-b from-[#1b1937] to-[#161430] sticky top-24">
                            <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-4 flex items-center gap-2 bg-indigo-500/10 py-1.5 px-4 rounded-full">
                                <i class="fas fa-qrcode text-xs"></i> Pase de Acceso
                            </span>
                            <p class="text-xs text-gray-400 mb-6 max-w-[200px]">Presenta este código al entrenador para registrar tu asistencia.</p>
                            <div class="bg-white p-3 rounded-2xl shadow-[0_0_30px_rgba(99,102,241,0.2)] border border-indigo-500/20">
                                <div id="contenedorQR" class="w-[140px] h-[140px] sm:w-[160px] sm:h-[160px] flex items-center justify-center text-black text-xs">
                                    <i class="fas fa-spinner animate-spin text-2xl text-indigo-500"></i>
                                </div>
                            </div>
                            <div class="mt-4 bg-black/40 border border-white/5 px-4 py-1.5 rounded-xl w-full overflow-x-auto text-center">
                                <span id="txtTokenVisible" class="text-[9px] sm:text-[10px] text-gray-500 font-mono select-all tracking-wider break-all">Cargando token...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-seguridad" class="tab-content hidden animate-fade-in">
        <div class="tarjeta p-6 sm:p-8">
            <h2 class="text-white text-xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-shield-alt text-emerald-500"></i> Cambio de Contraseña
            </h2>
            <p class="text-sm text-gray-400 mb-6">Asegúrate de usar una contraseña larga y difícil de adivinar.</p>
            <div class="p-8 border border-dashed border-gray-600 rounded-xl text-center text-gray-500">
                Formulario en construcción...
            </div>
        </div>
    </div>

    <div id="tab-preferencias" class="tab-content hidden animate-fade-in">
        <div class="tarjeta p-6 sm:p-8">
            <h2 class="text-white text-xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-paint-brush text-amber-500"></i> Ajustes del Sistema
            </h2>
            
            <div class="flex items-center justify-between p-4 bg-black/20 rounded-xl border border-white/5 mb-4">
                <div>
                    <h3 class="text-white font-medium">Apariencia del Sistema</h3>
                    <p class="text-xs text-gray-400 mt-1">Cambia entre modo claro y oscuro.</p>
                </div>
                <button onclick="alternarTemaGlobal()" id="btnTemaToggle" class="text-gray-400 hover:text-amber-400 dark:hover:text-amber-300 transition-colors p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/5" title="Cambiar Tema">
                    <i class="fas fa-sun text-xl hidden dark:block"></i>
                    <i class="fas fa-moon text-xl block dark:hidden"></i>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 bg-black/20 rounded-xl border border-white/5 opacity-50">
                <div>
                    <h3 class="text-white font-medium">Modo de Ingreso de Marcas</h3>
                    <p class="text-xs text-gray-400 mt-1">Manual vs. Cronómetro en Vivo.</p>
                </div>
                <span class="text-xs font-bold text-amber-500 bg-amber-500/10 px-2 py-1 rounded">Próximamente</span>
            </div>
        </div>
    </div>

</div>
   
        </main>
    </div>

    <!-- Scripts del menú responsive (deben estar aquí para poder cerrar el menú) -->
    <script>
        (function() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('menuOverlay');
            const openBtn = document.getElementById('openMenuBtn');
            const closeBtn = document.getElementById('closeMenuBtn');

            function openMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (overlay) {
                    overlay.classList.remove('opacity-0', 'pointer-events-none');
                    overlay.classList.add('opacity-100', 'pointer-events-auto');
                }
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('opacity-100', 'pointer-events-auto');
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                }
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            if (overlay) overlay.addEventListener('click', closeMenu);

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    if (sidebar && sidebar.classList.contains('translate-x-0')) {
                        sidebar.classList.remove('translate-x-0');
                        sidebar.classList.add('-translate-x-full');
                    }
                    if (overlay) {
                        overlay.classList.remove('opacity-100', 'pointer-events-auto');
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                    }
                    document.body.style.overflow = '';
                }
            });
        })();
    </script>

    <!-- Scripts específicos de mi_perfil -->
    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/mi_perfil.js"></script>
    <style>
    /* Animación suave al cambiar de pestaña */
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</body>
</html>