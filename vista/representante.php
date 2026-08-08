<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Representantes | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS + Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }

        /* ===== TARJETAS ===== */
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

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

        /* ===== DATATABLES ADAPTATIVO ===== */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151 !important;
        }
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_processing,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #a0a0c0 !important;
        }

        /* Input de búsqueda */
        .dataTables_wrapper .dataTables_filter input {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem 0.75rem 2.5rem !important;
            font-size: 0.875rem;
            transition: all 0.2s;
            width: 280px;
            max-width: 100%;
        }
        .dark .dataTables_wrapper .dataTables_filter input {
            background: #0f0d23;
            border-color: #252345;
            color: white;
        }

        /* Select de cantidad de registros */
        .dataTables_wrapper .dataTables_length select {
            padding: 0.6rem 1.5rem 0.6rem 0.75rem !important;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
        }
        .dark .dataTables_wrapper .dataTables_length select {
            background-color: #0f0d23;
            border-color: #252345;
            color: white;
        }

        /* Paginación */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #374151 !important;
            background: #f3f4f6 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.4rem 0.8rem !important;
            margin: 0 0.2rem !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #a0a0c0 !important;
            background: #161430 !important;
            border-color: #252345 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e5e7eb !important;
            color: #1f2937 !important;
        }
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #252345 !important;
            color: white !important;
        }

        /* Tabla */
        table.dataTable tbody tr {
            background-color: transparent;
        }
        table.dataTable tbody tr:hover {
            background-color: rgba(0,0,0,0.05);
        }
        .dark table.dataTable tbody tr:hover {
            background-color: rgba(255,255,255,0.05);
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark table.dataTable thead th {
            border-bottom-color: #252345;
            color: #9ca3af;
        }
        .dataTables_wrapper .dataTables_scroll {
            border-radius: 24px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
            }
            .dataTables_wrapper .dataTables_length {
                width: 100%;
                margin-bottom: 0.75rem;
            }
            .dataTables_wrapper .dataTables_length label {
                display: flex;
                justify-content: space-between;
                width: 100%;
            }
            .dataTables_wrapper .dataTables_paginate {
                margin-top: 1rem;
                text-align: center;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.5rem 0.75rem !important;
                margin: 0.2rem !important;
                font-size: 0.75rem;
            }
            .dataTables_wrapper {
                padding: 0.5rem 0.25rem;
            }
            .flex.flex-col.sm\:flex-row.items-stretch.sm\:items-center.gap-3.w-full.sm\:w-auto {
                gap: 0.75rem !important;
            }
            #toggleEstadoBtn,
            .bg-indigo-600 {
                width: 100% !important;
                justify-content: center !important;
                margin: 0 !important;
            }
            .text-indigo-400 {
                margin-bottom: 0.25rem;
            }
        }
        @media (min-width: 641px) {
            .dataTables_wrapper .dataTables_filter {
                text-align: right;
            }
            .dataTables_wrapper .dataTables_length {
                text-align: left;
            }
            .tarjeta .dataTables_wrapper {
                padding: 1rem 0.75rem 0.75rem 0.75rem;
            }
            #toggleEstadoBtn {
                margin-right: 0.5rem !important;
            }
            .bg-indigo-600 {
                margin-left: 0.25rem !important;
            }
        }

        /* ===== TOGGLE ESTADO ===== */
        #toggleEstadoBtn {
            transition: all 0.2s ease;
        }
        #toggleEstadoBtn.active {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        #toggleEstadoBtn.active #toggleIcono {
            color: #ef4444;
        }
        #toggleEstadoBtn.active #toggleTexto {
            color: #ef4444;
        }
        #estadoBadge {
            transition: all 0.2s;
        }

        /* ===== MODALES ===== */
        .modal-content {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }
        .dark .modal-content {
            background-color: #161430;
            border-color: #252345;
        }
        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .modal-header {
            border-bottom-color: #1f2937;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300">

    <!-- Overlay para móvil cuando el menú está abierto -->
    <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

    <!-- Contenedor principal -->
    <div class="flex flex-col lg:flex-row min-h-screen">
        
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
            <?php include RAIZ . 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-1 flex flex-col min-h-screen">
            
            <!-- Header común -->
            <?php 
                $tituloPagina = 'Gestión de Representantes';
                $tituloPaginaResponsive = "Representantes";
                $iconModulo = "fas fa-user-shield";
                include RAIZ . 'vista/complementos/header.php'; 
            ?>

            <!-- Contenido específico -->
            <div class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
                
                <!-- Barra de herramientas -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5 mb-6">
                    <div class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-users"></i>
                        <span class="font-medium tracking-wide uppercase text-xs">Directorio Familiar</span>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                        <!-- Botón toggle -->
                        <button id="toggleEstadoBtn" class="group relative flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] hover:border-red-500/50 transition-all duration-200 w-full sm:w-auto">
                            <i id="toggleIcono" class="fas fa-trash-alt text-gray-500 dark:text-gray-400 group-hover:text-red-400 transition-colors"></i>
                            <span id="toggleTexto" class="text-xs font-medium text-gray-700 dark:text-gray-300">Activos</span>
                            <div class="absolute -top-2 -right-2">
                                <span id="estadoBadge" class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">A</span>
                            </div>
                        </button>
                        
                        <!-- Botón nuevo representante -->
                        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
                        <button onclick="abrirModalRepresentante()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 w-full sm:w-auto cursor-pointer">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="tarjeta overflow-hidden shadow-2xl transition-colors duration-300">
                    <div class="overflow-x-auto">
                        <table id="tablaRepresentantes" class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 w-full">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Representante</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Cédula</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Teléfono</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Parentesco</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Atletas Vinculados</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800" id="listaRepresentantes">
                                <tr><td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ===== MODALES ===== -->
    
    <!-- Modal de representante -->
    <div id="modalRepresentante" class="fixed inset-0 bg-black/60 dark:bg-[#0f0d23]/95 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
        <div class="modal-content w-full max-w-4xl shadow-2xl overflow-y-auto max-h-[90vh] rounded-2xl transition-colors duration-300">
            <!-- Cabecera -->
            <div class="modal-header sticky top-0 z-10 flex justify-between items-center p-4 sm:p-6 rounded-t-2xl bg-white dark:bg-[#161430]">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-id-card text-indigo-500"></i> 
                    <span>Ficha del Representante</span>
                </h2>
                <button onclick="cerrarModalRepresentante()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formRepresentante" class="p-4 sm:p-6 space-y-6 bg-white dark:bg-[#161430] rounded-b-2xl">
                <input type="hidden" id="cedula_original" name="cedula_original" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                            <i class="fas fa-user-circle"></i> Información de Identidad
                        </p>
                        
                        <div>
                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">CÉDULA *</label>
                            <input type="text" id="cedula" name="cedula" 
                                   data-validar="requerido|cedula" data-nombre="Cédula" data-min="6" data-max="10"
                                   minlength="9" maxlength="10"
                                   class="input-adapt w-full p-3 rounded-xl text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">NOMBRES *</label>
                                <input type="text" id="nombres" name="nombres" 
                                       data-validar="requerido|letras" data-nombre="Nombres" data-min="3" data-max="40"
                                       minlength="3" maxlength="40"
                                       class="input-adapt w-full p-3 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">APELLIDOS *</label>
                                <input type="text" id="apellidos" name="apellidos" 
                                       data-validar="requerido|letras" data-nombre="Apellidos" data-min="3" data-max="40"
                                       minlength="3" maxlength="40"
                                       class="input-adapt w-full p-3 rounded-xl text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">TELÉFONO PRINCIPAL *</label>
                                <input type="text" id="telefono_principal" name="telefono_principal" 
                                       data-validar="requerido|numeros" data-nombre="Teléfono Principal" data-min="11" data-max="11"
                                       minlength="11" maxlength="11"
                                       class="input-adapt w-full p-3 rounded-xl text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">TELÉFONO EMERGENCIA</label>
                                <input type="text" id="telefono_emergencia" name="telefono_emergencia" 
                                       data-validar="numeros" data-nombre="Teléfono de Emergencia" data-min="11" data-max="11"
                                       minlength="11" maxlength="11"
                                       class="input-adapt w-full p-3 rounded-xl text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">CORREO ELECTRÓNICO *</label>
                            <input type="email" id="correo" name="correo" 
                                   data-validar="requerido|correo" data-nombre="Correo Electrónico" data-min="5" data-max="40"
                                   minlength="5" maxlength="40"
                                   class="input-adapt w-full p-3 rounded-xl text-sm">
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-tighter flex items-center gap-2">
                            <i class="fas fa-home"></i> Localización y Parentesco
                        </p>

                        <div>
                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">PARENTESCO CON EL ATLETA *</label>
                            <select id="parentesco" name="parentesco" 
                                    data-validar="requerido" data-nombre="Parentesco"
                                    class="input-adapt w-full p-3 rounded-xl text-sm">
                                <option value="">Seleccione una opción...</option>
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Tutor">Tutor</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">DIRECCIÓN DE RESIDENCIA *</label>
                            <textarea id="direccion_residencia" name="direccion_residencia" rows="2" 
                                      data-validar="requerido" data-nombre="Dirección de Residencia" data-min="10" data-max="200"
                                      minlength="10" maxlength="200"
                                      class="input-adapt w-full p-3 rounded-xl resize-none text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] text-gray-500 dark:text-gray-400 font-bold ml-1 mb-1">SELECCIONAR ATLETAS A CARGO</label>
                            <div id="contenedorCheckboxes" class="input-adapt rounded-xl max-h-40 overflow-y-auto p-2 space-y-1">
                                <div class="flex items-center gap-3 p-2">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Cargando lista...</span>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-500 dark:text-gray-500 mt-2">* Se muestran atletas sin representante asignado.</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" onclick="cerrarModalRepresentante()" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 rounded-xl font-bold transition order-2 sm:order-1 cursor-pointer">
                        CANCELAR
                    </button>
                    <button type="submit" id="btnGuardar" 
                            class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition flex items-center justify-center gap-2 order-1 sm:order-2 cursor-pointer">
                        <i class="fas fa-save"></i> GUARDAR Y ASOCIAR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de ver perfil -->
    <div id="modalVer" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-3xl rounded-2xl sm:rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <!-- Efectos decorativos -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            
            <button onclick="cerrarModalVer()" class="absolute top-4 right-4 z-50 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 bg-white/80 dark:bg-black/30 rounded-full w-8 h-8 flex items-center justify-center backdrop-blur-sm cursor-pointer">
                <i class="fas fa-times text-lg"></i>
            </button>
            
            <div class="p-5 sm:p-8 relative z-10" id="detalleContenido"></div>
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
    <script src="assets/js/tour.js"></script>
    <script>
        // Definir permisos para el frontend
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/representante.js"></script>

    <!-- ===== CONFIGURACIÓN DE DATATABLES Y TOGGLE ===== -->
    <script>
        let dataTableRepresentantes = null;
        let estadoActual = 'Activo';

        // Control del toggle
        const toggleBtn = document.getElementById('toggleEstadoBtn');

        function actualizarToggleUI() {
            const isActive = estadoActual === 'Activo';
            if (toggleBtn) {
                if (isActive) {
                    toggleBtn.classList.remove('active');
                    document.getElementById('toggleTexto').innerText = 'Activos';
                    document.getElementById('toggleIcono').className = 'fas fa-trash-alt text-gray-500 dark:text-gray-400 group-hover:text-red-400';
                    document.getElementById('estadoBadge').innerHTML = 'A';
                    document.getElementById('estadoBadge').classList.remove('bg-red-500', 'text-white');
                    document.getElementById('estadoBadge').classList.add('bg-indigo-500', 'text-white');
                } else {
                    toggleBtn.classList.add('active');
                    document.getElementById('toggleTexto').innerText = 'Inactivos';
                    document.getElementById('toggleIcono').className = 'fas fa-trash-restore text-red-400';
                    document.getElementById('estadoBadge').innerHTML = 'I';
                    document.getElementById('estadoBadge').classList.remove('bg-indigo-500', 'text-white');
                    document.getElementById('estadoBadge').classList.add('bg-red-500', 'text-white');
                }
            }
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                estadoActual = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
                actualizarToggleUI();
                window.cargarTablaRepresentantes();
            });
        }

        // Sobrescribir la función cargarTablaRepresentantes con DataTables
        const originalCargarTabla = window.cargarTablaRepresentantes;
        window.cargarTablaRepresentantes = async function() {
            const tbody = document.getElementById('listaRepresentantes');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i><span class="text-xs uppercase tracking-wider block">Cargando datos...</span></td></tr>';
            
            const representantes = await peticionAjax(`listarRepresentantes&estado=${estadoActual}`);
            
            if (!representantes || representantes.length === 0) {
                if (dataTableRepresentantes) {
                    dataTableRepresentantes.clear().draw();
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">No hay representantes registrados</td></tr>';
                }
                return;
            }
            
            let htmlFilas = '';
            representantes.forEach(rep => {
                let htmlAtletas = '<span class="text-[10px] text-gray-500 dark:text-gray-500 italic">Sin vinculaciones</span>';
                let textoBusquedaAtletas = '';
                if (rep.atletas_vinculados) {
                    textoBusquedaAtletas = rep.atletas_vinculados.toLowerCase();
                    const listaAtletas = rep.atletas_vinculados.split('|');
                    htmlAtletas = listaAtletas.map(item => {
                        const partes = item.split(':');
                        const idAtleta = partes[0];
                        const nombreAtleta = partes[1];
                        return `<button onclick="verMiniPerfilAtleta(${idAtleta})" type="button" class="inline-block px-2 py-1 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 rounded-md text-[10px] font-bold uppercase tracking-wider mb-1 mr-1 transition-colors cursor-pointer shadow-sm active:scale-95"><i class="fas fa-swimmer mr-1"></i> ${nombreAtleta}</button>`;
                    }).join('');
                }
                
                let botonAccion = '';
                let mostrarEditar = true;

                if (rep.estado === 'Activo' || !rep.estado) {
                    botonAccion = `<button onclick="eliminarRepresentante(${rep.id_representante})" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition" title="Archivar"><i class="fas fa-trash-alt text-base"></i></button>`;
                    mostrarEditar = true;
                } else {
                    botonAccion = `<button onclick="reactivarRepresentante(${rep.id_representante})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition" title="Reactivar"><i class="fas fa-undo-alt text-base"></i></button>`;
                    mostrarEditar = false;
                }

                let acciones = '';
                if (PERMISOS_MODULO.gestionar) {
                    if (mostrarEditar) {
                        acciones = `<button onclick="abrirModalRepresentante(${rep.id_representante})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition" title="Editar"><i class="fas fa-edit text-base"></i></button> ${botonAccion}`;
                    } else {
                        acciones = botonAccion;
                    }
                } else {
                    acciones = '<span class="text-gray-500 dark:text-gray-500 text-xs">Solo lectura</span>';
                }
                
                htmlFilas += `
                    <tr class="representante-row" data-busqueda="${rep.cedula} ${rep.nombres} ${rep.apellidos} ${textoBusquedaAtletas}".toLowerCase()>
                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">${rep.nombres} ${rep.apellidos}</td>
                        <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-300">${rep.cedula}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${rep.telefono_principal}</td>
                        <td class="px-4 py-3"><span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">${rep.parentesco}</span></td>
                        <td class="px-4 py-3"><div class="flex flex-wrap max-w-xs">${htmlAtletas}</div></td>
                        <td class="px-4 py-3 text-right space-x-1">${acciones}</td>
                    </tr>
                `;
            });
            
            if (dataTableRepresentantes) {
                dataTableRepresentantes.destroy();
                $('#tablaRepresentantes tbody').html(htmlFilas);
            } else {
                $('#tablaRepresentantes tbody').html(htmlFilas);
            }
            
            dataTableRepresentantes = $('#tablaRepresentantes').DataTable({
                responsive: true,
                dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-2"lf>rtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ representantes",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 4 },
                    { responsivePriority: 3, targets: 5 },
                    { responsivePriority: 4, targets: [1,2,3] }
                ],
                autoWidth: false
            });
        };

        // Sobrescribir funciones de eliminación/reactivación para recargar la tabla
        const originalEliminar = window.eliminarRepresentante;
        if (originalEliminar) {
            window.eliminarRepresentante = async function(id) {
                await originalEliminar(id);
                window.cargarTablaRepresentantes();
            };
        }
        const originalReactivar = window.reactivarRepresentante;
        if (originalReactivar) {
            window.reactivarRepresentante = async function(id) {
                await originalReactivar(id);
                window.cargarTablaRepresentantes();
            };
        }

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            window.cargarTablaRepresentantes();
        });
    </script>
</body>
</html>