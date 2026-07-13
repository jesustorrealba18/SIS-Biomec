<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control de Asistencia | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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
            border-radius: 20px;
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
            outline: none;
            transition: all 0.3s ease;
        }
        .dark .input-adapt {
            background-color: #0f0d23;
            border-color: #252345;
            color: #ffffff;
        }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }

        /* ===== VISOR DE CÁMARA ===== */
        #visorCamara {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #4f46e5;
        }
        #visorCamara video { object-fit: cover; }

        /* ===== DATATABLES ADAPTATIVO ===== */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151 !important;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #a0a0c0 !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-radius: 0.75rem;
            padding: 0.4rem 0.75rem;
            outline: none;
            margin-left: 0.5rem;
        }
        .dark .dataTables_wrapper .dataTables_filter input {
            background-color: #0f0d23;
            border-color: #252345;
            color: white;
        }

        .dataTables_wrapper .dataTables_length select {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-radius: 0.75rem;
            padding: 0.3rem 0.75rem;
        }
        .dark .dataTables_wrapper .dataTables_length select {
            background-color: #0f0d23;
            border-color: #252345;
            color: white;
        }

        /* Remover bordes y fondos blancos nativos */
        table.dataTable tbody tr { background-color: transparent !important; }
        table.dataTable.no-footer { border-bottom: 1px solid #e5e7eb !important; }
        .dark table.dataTable.no-footer { border-bottom-color: #252345 !important; }

        table.dataTable thead th {
            background-color: #f3f4f6 !important;
            color: #374151 !important;
            border-bottom: 1px solid #e5e7eb !important;
        }
        .dark table.dataTable thead th {
            background-color: #0f0d23 !important;
            color: #94a3b8 !important;
            border-bottom-color: #252345 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #374151 !important;
            background: #f3f4f6 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.3rem 0.8rem !important;
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

        /* Responsive (control +) */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
            background-color: #22c55e !important;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased flex h-screen overflow-hidden transition-colors duration-300">

    <div id="sidebar-container" class="hidden lg:block w-64 bg-white dark:bg-[#161430] border-r border-gray-200 dark:border-[#252345] flex-shrink-0 z-20 transition-colors duration-300">
        <?php 
        $pagina = 'asistencia';
        include RAIZ . 'vista/complementos/menu_responsive.php'; 
        ?>
    </div>

    <div id="mobile-overlay" class="fixed inset-0 bg-black/60 z-10 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
        
        <?php 
        $tituloPagina = 'Control de Asistencia';
        $tituloPaginaResponsive = 'Asistencia';
        $iconModulo = 'fas fa-clipboard-check';
        include RAIZ . 'vista/complementos/header.php'; 
        ?>

        <main class="flex-1 p-4 sm:p-8 w-full max-w-[1600px] mx-auto">
            
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-wide flex items-center gap-3">
                        <i class="fas fa-clipboard-check text-indigo-500"></i> Registro por Sesión
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Escaneo de código QR y validación manual.</p>
                </div>
                
                <div class="tarjeta p-3 rounded-xl border-indigo-500/30 flex items-center gap-3 w-full md:w-auto shadow-[0_0_15px_rgba(79,70,229,0.1)]">
                    <i class="fas fa-stopwatch text-indigo-500 dark:text-indigo-400"></i>
                    <select id="selectSesion" class="input-adapt py-2 px-3 rounded-lg text-sm font-bold w-full md:w-64 cursor-pointer">
                        <option value="">Seleccione una sesión activa...</option>
                        <option value="1">Sesión Matutina - 08:00 AM</option>
                        <option value="2">Sesión Vespertina - 03:00 PM</option>
                    </select>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div class="tarjeta p-6 text-center shadow-lg relative overflow-hidden transition-colors duration-300">
                        <h2 class="text-xs uppercase font-bold text-indigo-600 dark:text-indigo-400 tracking-widest mb-4">
                            <i class="fas fa-camera mr-2"></i> Escáner de Carnet
                        </h2>
                        
                        <div id="visorCamara" class="bg-gray-200 dark:bg-black/50 aspect-square flex items-center justify-center mb-4 rounded-xl transition-colors duration-300">
                            <span class="text-gray-500 dark:text-gray-600 text-sm"><i class="fas fa-video-slash text-3xl mb-2 block"></i> Cámara en espera</span>
                        </div>

                        <button id="btnActivarCamara" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-indigo-500/20 uppercase text-xs tracking-widest cursor-pointer">
                            Activar Cámara
                        </button>
                        <p id="txtScanEstado" class="text-[10px] text-gray-500 dark:text-gray-500 mt-3 font-mono"><i class="fas fa-power-off mr-1"></i> Cámara apagada.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="tarjeta p-4 border-l-4 border-l-emerald-500 text-center transition-colors duration-300">
                            <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400 font-bold mb-1">Presentes</p>
                            <p id="statPresentes" class="text-3xl font-black text-emerald-600 dark:text-emerald-400">0</p>
                        </div>
                        <div class="tarjeta p-4 border-l-4 border-l-red-500 text-center transition-colors duration-300">
                            <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400 font-bold mb-1">Ausentes / Just.</p>
                            <p id="statAusentes" class="text-3xl font-black text-red-600 dark:text-red-400">0</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 tarjeta p-4 sm:p-6 overflow-hidden w-full transition-colors duration-300">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xs uppercase font-bold text-gray-600 dark:text-gray-400 tracking-widest">
                            <i class="fas fa-users mr-2"></i> Listado de Convocados
                        </h2>
                        <span class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full text-xs font-bold border border-indigo-200 dark:border-indigo-500/20">
                            Total: <span id="statTotal">0</span>
                        </span>
                    </div>

                    <table id="tablaAsistenciaDT" class="w-full text-left border-collapse display responsive nowrap" style="width:100%">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-[#252345]">
                                <th class="pb-3 pl-2">Atleta</th>
                                <th class="pb-3">Categoría</th>
                                <th class="pb-3 text-center">Estado</th>
                                <th class="pb-3 text-right pr-2">Acción Manual</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAtletas" class="text-sm text-gray-800 dark:text-gray-300">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-500 italic">Seleccione una sesión para cargar la lista.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script>
        // ===== MENÚ RESPONSIVE =====
        const openMenuBtn = document.getElementById('openMenuBtn');
        const sidebarContainer = document.getElementById('sidebar-container');
        const mobileOverlay = document.getElementById('mobile-overlay');

        if (openMenuBtn) {
            openMenuBtn.addEventListener('click', () => {
                sidebarContainer.classList.remove('hidden');
                sidebarContainer.classList.add('fixed', 'inset-y-0', 'left-0', 'z-40');
                mobileOverlay.classList.remove('hidden');
            });
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', () => {
                sidebarContainer.classList.add('hidden');
                sidebarContainer.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-40');
                mobileOverlay.classList.add('hidden');
            });
        }
    </script>
    
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/asistencia.js"></script>
</body>
</html>