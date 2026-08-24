<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Atletas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
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

        /* ===== TABS ===== */
        /* ===== STEPPER ===== */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 1.5rem;
            padding: 0 1rem;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0;
        }
        .step-item + .step-item {
            margin-left: 0;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            border: 2px solid #d1d5db;
            background: #f3f4f6;
            color: #9ca3af;
            transition: all 0.3s ease;
            cursor: pointer;
            flex-shrink: 0;
        }
        .dark .step-circle {
            border-color: #252345;
            background: #0f0d23;
            color: #4b5563;
        }
        .step-circle.active {
            border-color: #6366f1;
            background: #6366f1;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        .dark .step-circle.active {
            border-color: #818cf8;
            background: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }
        .step-circle.completed {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }
        .dark .step-circle.completed {
            border-color: #34d399;
            background: #10b981;
        }
        .step-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            margin-left: 8px;
            white-space: nowrap;
            transition: color 0.3s ease;
            cursor: pointer;
        }
        .dark .step-label {
            color: #4b5563;
        }
        .step-label.active {
            color: #4f46e5;
        }
        .dark .step-label.active {
            color: #a5b4fc;
        }
        .step-label.completed {
            color: #10b981;
        }
        .dark .step-label.completed {
            color: #34d399;
        }
        .step-line {
            width: 40px;
            height: 2px;
            background: #d1d5db;
            margin: 0 12px;
            flex-shrink: 0;
            transition: background 0.3s ease;
        }
        .dark .step-line {
            background: #252345;
        }
        .step-line.completed {
            background: #10b981;
        }
        .dark .step-line.completed {
            background: #34d399;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .stepper-btn {
            display: none;
        }
        .stepper-btn.visible {
            display: flex;
        }

        /* ===== BADGES DE ESTADO ===== */
        .estado-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .estado-Activo {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .estado-Activo {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        .estado-Inactivo {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .estado-Inactivo {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        .estado-Retirado {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .dark .estado-Retirado {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }
        .estado-Transferido {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .dark .estado-Transferido {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }

        /* ===== RESPONSIVE TABLE ===== */
        @media (max-width: 768px) {
            .tabla-responsive thead { display: none; }
            .tabla-responsive tbody tr {
                display: block;
                padding: 12px;
                margin-bottom: 8px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #ffffff;
            }
            .dark .tabla-responsive tbody tr {
                border-color: #252345;
                background: #161430;
            }
            .tabla-responsive tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 6px 0;
                border: none;
            }
            .tabla-responsive tbody td::before {
                content: attr(data-label);
                font-size: 10px;
                text-transform: uppercase;
                color: #6b7280;
                font-weight: 700;
                letter-spacing: 0.05em;
                margin-right: 8px;
            }
            .dark .tabla-responsive tbody td::before {
                color: #6b7280;
            }
            .tabla-responsive tbody td:first-child::before { content: ''; }
        }

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
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
                $tituloPagina = "Gestión de Atletas";
                $tituloPaginaResponsive = "Atletas";
                $iconModulo = "fas fa-swimmer";
                include 'vista/complementos/header.php'; 
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">
                
                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-swimmer text-indigo-500"></i> Repositorio de Nadadores
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Módulo de Control de Atletas, datos médicos y federativos.</p>
                    </div>
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'crear')): ?>
                    <button onclick="abrirModal()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Registrar Nuevo Atleta
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Buscador -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                            <input type="text" id="busquedaAtleta" placeholder="Buscar por nombre o cédula..."
                                   class="input-adapt w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                        </div>
                        <span id="totalAtletas" class="flex items-center gap-2 text-xs bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full border border-indigo-200 dark:border-indigo-500/20 self-center">0 Registrados</span>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl overflow-hidden shadow-2xl transition-colors duration-300" id="contenedorTabla">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-wrap justify-between items-center gap-4 bg-gray-50 dark:bg-white/5">
                        <h3 class="text-gray-900 dark:text-white font-semibold">Listado General</h3>
                        <span id="infoTabla" class="text-xs text-gray-500 dark:text-gray-500"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left tabla-responsive">
                            <thead class="bg-gray-100 dark:bg-[#1c1a3a] text-gray-600 dark:text-gray-400 text-xs uppercase tracking-widest">
                                <tr>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="nombre">
                                        Atleta <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="cedula">
                                        Cédula <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="categoria">
                                        Categoría <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="feveda">
                                        FEVEDA <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors" data-sort="estado">
                                        Estado <i class="fas fa-sort ml-1 text-gray-400 dark:text-gray-600 text-[10px]"></i>
                                    </th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-800" id="listaAtletas">
                                <tr>
                                    <td colspan="6" class="text-center p-12 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                        <span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span>
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
    <div id="modalAtleta" class="fixed inset-0 z-50 hidden bg-black/20 dark:bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8 transition-colors duration-300">
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-swimmer"></i></div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitulo">Registrar Atleta</h2>
                </div>
                <button onclick="cerrarModal()" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors cursor-pointer"><i class="fas fa-times text-2xl"></i></button>
            </div>

            <form id="formAtleta" enctype="multipart/form-data">
                <input type="hidden" name="id_atleta" id="id_atleta">

                <!-- Stepper -->
                <div class="stepper" id="stepper">
                    <div class="step-item">
                        <div class="step-circle active" data-step="1" onclick="irAPaso(1)">1</div>
                        <span class="step-label active" data-step="1" onclick="irAPaso(1)">Datos Personales</span>
                    </div>
                    <div class="step-line" data-line="1"></div>
                    <div class="step-item">
                        <div class="step-circle" data-step="2" onclick="irAPaso(2)">2</div>
                        <span class="step-label" data-step="2" onclick="irAPaso(2)">Datos Médicos</span>
                    </div>
                    <div class="step-line" data-line="2"></div>
                    <div class="step-item">
                        <div class="step-circle" data-step="3" onclick="irAPaso(3)">3</div>
                        <span class="step-label" data-step="3" onclick="irAPaso(3)">Datos Federativos</span>
                    </div>
                </div>

                <!-- Tab Personal -->
                <div id="tab-personal" class="tab-content active">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Cédula de Identidad</label>
                            <input type="text" name="cedula" id="cedula" placeholder="V-12345678"
                                   data-validar="requerido|cedula" data-nombre="Cédula" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Nombres</label>
                            <input type="text" name="nombres" id="nombres"
                                   data-validar="requerido|letras" data-nombre="Nombres" data-min="2" data-max="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos"
                                   data-validar="requerido|letras" data-nombre="Apellidos" data-min="2" data-max="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                   data-validar="requerido" data-nombre="Fecha de nacimiento" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Sexo</label>
                            <select name="sexo" id="sexo"
                                    data-validar="requerido" data-nombre="Sexo" class="input-adapt w-full p-3 rounded-xl">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Estado</label>
                            <select name="estado" id="estado" class="input-adapt w-full p-3 rounded-xl">
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Retirado">Retirado</option>
                                <option value="Transferido">Transferido</option>
                            </select>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Dirección</label>
                            <input type="text" name="direccion" id="direccion" placeholder="Dirección de residencia"
                                   data-validar="requerido|texto" data-nombre="Dirección" data-max="200" maxlength="200" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" placeholder="0412-1234567"
                                   data-validar="requerido|telefono" data-nombre="Teléfono" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Correo Electrónico</label>
                            <input type="email" name="correo" id="correo" placeholder="correo@ejemplo.com"
                                   data-validar="requerido|correo" data-nombre="Correo electrónico" data-max="100" maxlength="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Fecha Registro Club</label>
                            <input type="date" name="fecha_registro_club" id="fecha_registro_club"
                                   data-validar="requerido" data-nombre="Fecha registro club" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Fotografía</label>
                            <div class="flex items-center gap-3">
                                <div id="fotoPreview" class="w-16 h-16 rounded-full bg-gray-200 dark:bg-[#0f0d23] border-2 border-gray-300 dark:border-gray-800 overflow-hidden flex items-center justify-center shrink-0 transition-colors duration-300">
                                    <i class="fas fa-camera text-gray-400 dark:text-gray-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png"
                                           class="text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-500/10 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-500/20 w-full">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-500 mt-1">JPG/PNG, máx 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Médico -->
                <div id="tab-medico" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Grupo Sanguíneo</label>
                            <select name="grupo_sanguineo" id="grupo_sanguineo"
                                    data-validar="requerido" data-nombre="Grupo sanguíneo" class="input-adapt w-full p-3 rounded-xl">
                                <option value="">Sin especificar</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Seguro Médico</label>
                            <input type="text" name="seguro_medico" id="seguro_medico" placeholder="Nombre del seguro"
                                   data-validar="requerido|texto" data-nombre="Seguro médico" data-max="100" maxlength="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Alergias Conocidas</label>
                            <textarea name="alergias" id="alergias" rows="2" placeholder="Describa las alergias conocidas..."
                                      data-validar="requerido|texto" data-nombre="Alergias" data-max="500" maxlength="500" class="input-adapt w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Condiciones Médicas Preexistentes</label>
                            <textarea name="condiciones_previas" id="condiciones_previas" rows="2" placeholder="Asma, lesiones crónicas, etc..."
                                      data-validar="requerido|texto" data-nombre="Condiciones médicas" data-max="500" maxlength="500" class="input-adapt w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 p-4 rounded-xl bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-white/5 transition-colors duration-300">
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest mb-4"><i class="fas fa-phone-alt mr-2"></i>Contacto de Emergencia</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">Nombre</label>
                                <input type="text" name="contacto_emergencia_nombre" id="contacto_emergencia_nombre" placeholder="Nombre completo"
                                       data-validar="requerido|letras" data-nombre="Contacto emergencia" data-max="100" maxlength="100" class="input-adapt w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">Teléfono</label>
                                <input type="text" name="contacto_emergencia_telefono" id="contacto_emergencia_telefono" placeholder="0412-1234567"
                                       data-validar="requerido|telefono" data-nombre="Teléfono emergencia" class="input-adapt w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold tracking-widest">Parentesco</label>
                                <select name="contacto_emergencia_parentesco" id="contacto_emergencia_parentesco"
                                        data-validar="requerido" data-nombre="Parentesco" class="input-adapt w-full p-3 rounded-xl">
                                    <option value="">Seleccione...</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Tutor">Tutor</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Federativo -->
                <div id="tab-federativo" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Número de Registro FEVEDA</label>
                            <input type="text" name="numero_feveda" id="numero_feveda" placeholder="Ej: FED-00123"
                                   data-validar="requerido|texto" data-nombre="Número FEVEDA" data-max="50" maxlength="50" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Club de Procedencia</label>
                            <input type="text" name="club_procedencia" id="club_procedencia" placeholder="Club anterior"
                                   data-validar="requerido|texto" data-nombre="Club procedencia" data-max="100" maxlength="100" class="input-adapt w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] text-indigo-600 dark:text-indigo-400 uppercase font-bold tracking-widest">Categoría Deportiva</label>
                            <select name="id_categoria" id="id_categoria"
                                    data-validar="requerido" data-nombre="Categoría deportiva" class="input-adapt w-full p-3 rounded-xl">
                                <option value="">Seleccione una categoría...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="button" id="btnAtras" onclick="retrocederPaso()" class="stepper-btn items-center justify-center gap-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 py-4 px-6 rounded-xl font-bold transition-all cursor-pointer uppercase text-xs tracking-wider"><i class="fas fa-arrow-left"></i> ATRÁS</button>
                    <button type="button" id="btnSiguiente" onclick="avanzarPaso()" class="stepper-btn visible flex-[2] items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer uppercase text-xs tracking-wider">SIGUIENTE <i class="fas fa-arrow-right"></i></button>
                    <button type="submit" id="btnGuardar" class="stepper-btn flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-indigo-500/20 active:scale-95 transition-all cursor-pointer uppercase text-xs tracking-wider">GUARDAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL VER DETALLE ===== -->
    <div id="modalVer" class="fixed inset-0 bg-black/60 dark:bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-white dark:bg-[#111026] border border-gray-200 dark:border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transition-colors duration-300">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
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
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/tour.js"></script>
    <script>
        const PERMISOS_MODULO = {
            ver: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver') ? 'true' : 'false'; ?>,
            crear: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'crear') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'eliminar') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/atleta.js"></script>
</body>
</html>