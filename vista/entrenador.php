<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Entrenadores | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        /* ===== INPUTS ADAPTATIVOS ===== */
        .input-adapt {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            transition: all 0.3s ease;
        }
        .dark .input-adapt {
            background-color: #0f0d23;
            border-color: #252345;
            color: #ffffff;
        }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        .dark .input-adapt::-webkit-calendar-picker-indicator {
            filter: invert(0);
        }

        /* ===== TARJETAS ===== */
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== VALIDACIÓN ===== */
        .error-msg {
            display: none;
            font-size: 10px;
            margin-top: 4px;
        }
        .error-msg.show {
            display: block;
        }
        .border-red-500 {
            border-color: #ef4444 !important;
        }
        .border-green-500 {
            border-color: #22c55e !important;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">
        
        <!-- Overlay para móvil cuando el menú está abierto -->
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <!-- Sidebar - responsive -->
        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-[#0f0d23] border-r border-gray-200 dark:border-[#252345] z-40 transform -translate-x-full menu-transition lg:relative lg:translate-x-0 lg:flex-shrink-0 overflow-y-auto transition-colors duration-300">
            <div class="p-4 flex justify-between items-center border-b border-gray-200 dark:border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-gray-900 dark:text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <?php 
                $tituloPagina = "Gestión de Entrenadores";
                $tituloPaginaResponsive = "Entrenadores";
                $iconModulo = "fas fa-user-tie";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-user-tie text-indigo-500"></i> Repositorio de Entrenadores
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Gestión de perfiles, datos personales y contacto.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                    <button onclick="abrirModalEntrenador()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Entrenador
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                            <input type="text" id="busquedaCedula" placeholder="Buscar por cédula o nombre..."
                                   class="input-adapt w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                        </div>
                        <span id="totalEntrenador" class="flex items-center gap-2 text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/20 self-center">0 Registrados</span>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5">
                        <h3 class="text-gray-900 dark:text-white font-semibold">Listado General</h3>
                        <span id="infoTabla" class="text-xs text-gray-500 dark:text-gray-500"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left tabla-responsive">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                                <tr>
                                    <th class="p-4">Entrenador</th>
                                    <th class="p-4">Cédula</th>
                                    <th class="p-4">Teléfono</th>
                                    <th class="p-4">Dirección</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaEntrenador">
                                <tr>
                                    <td colspan="5" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Cargando datos del sistema...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pieTabla" class="p-4 border-t border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- ===== MODAL REGISTRAR/EDITAR ===== -->
    <div id="modalEntrenador" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-user-tie"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitulo">Registrar Entrenador</h2>
                </div>
                <button onclick="cerrarModalEntrenador()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="formEntrenador" class="space-y-6" novalidate>
                <input type="hidden" id="action_type" name="action_type" value="registrar">
                <input type="hidden" id="id_entrenador" name="id_entrenador" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-tighter">Información Personal</p>
                        
                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Cédula *</label>
                            <input type="text" id="cedula" name="cedula" 
                                   data-validar="requerido|numeros" data-nombre="Cédula" data-min="8" data-max="8" 
                                   maxlength="8" class="input-adapt w-full p-3 rounded-xl" placeholder="Ej: 25888999">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Nombres *</label>
                                <input type="text" id="nombres" name="nombres" 
                                       data-validar="requerido|letras" data-nombre="Nombres" data-min="2" data-max="50"
                                       maxlength="50" class="input-adapt w-full p-3 rounded-xl" placeholder="Juan">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" 
                                       data-validar="requerido|letras" data-nombre="Apellidos" data-min="2" data-max="50"
                                       maxlength="50" class="input-adapt w-full p-3 rounded-xl" placeholder="Pérez">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Fecha Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" 
                                   data-validar="requerido|mayor18" data-nombre="Fecha de Nacimiento" 
                                   class="input-adapt w-full p-3 rounded-xl">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Género *</label>
                            <select name="genero" id="genero" data-validar="requerido" data-nombre="Género" class="input-adapt w-full p-3 rounded-xl">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-tighter">Datos de Contacto</p>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Teléfono *</label>
                            <input type="text" id="telefono" name="telefono" 
                                   data-validar="requerido|numeros" data-nombre="Teléfono" data-min="11" data-max="11"
                                   maxlength="11" class="input-adapt w-full p-3 rounded-xl" placeholder="04121234567">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Correo Electrónico *</label>
                            <input type="email" id="correo" name="correo" 
                                   data-validar="requerido|email" data-nombre="Correo Electrónico"
                                   class="input-adapt w-full p-3 rounded-xl" placeholder="ejemplo@correo.com">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1">Dirección *</label>
                            <textarea id="direccion" name="direccion" rows="4" 
                                      data-validar="requerido" data-nombre="Dirección" data-min="5" data-max="50"
                                      maxlength="50" class="input-adapt w-full p-3 rounded-xl resize-none" placeholder="Calle principal #123"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" onclick="cerrarModalEntrenador()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVerEntrenador" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-user-circle"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="verNombreCompleto">Perfil del Entrenador</h2>
                </div>
                <button onclick="cerrarModalVer()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 flex flex-col items-center">
                    <div class="relative w-32 h-32 rounded-full bg-gray-200 dark:bg-[#1c1a3a] border-2 border-indigo-500/30 overflow-hidden flex items-center justify-center">
                        <img id="verFoto" src="" alt="Foto" class="w-full h-full object-cover hidden">
                        <i id="verIconoPorDefecto" class="fas fa-user text-5xl text-gray-400 dark:text-gray-600"></i>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Entrenador</span>
                </div>

                <div class="md:col-span-2 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Cédula</p>
                            <p id="verCedula" class="text-gray-900 dark:text-white font-mono text-sm">---</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Género</p>
                            <p id="verGenero" class="text-gray-900 dark:text-white text-sm">---</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Fecha de Nacimiento</p>
                        <p id="verFechaNac" class="text-gray-900 dark:text-white text-sm">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Teléfono</p>
                        <p id="verTelefono" class="text-gray-900 dark:text-white text-sm">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Correo Electrónico</p>
                        <p id="verCorreo" class="text-gray-900 dark:text-white text-sm break-all">---</p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-tighter">Dirección</p>
                        <p id="verDireccion" class="text-gray-900 dark:text-white text-sm">---</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-800 flex justify-end">
                <button type="button" onclick="cerrarModalVer()" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">
                    CERRAR <i class="fas fa-times ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
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
    <script src="assets/js/validador.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/entrenador.js"></script>
</body>
</html>