<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Periodizacion ATR | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }

        .timeline-container {
            position: relative;
            height: 60px;
            background: #0f0d23;
            border-radius: 12px;
            overflow: hidden;
        }
        .timeline-bar {
            position: absolute;
            top: 4px;
            bottom: 4px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            cursor: pointer;
            min-width: 20px;
        }
        .timeline-bar:hover {
            transform: scaleY(1.1);
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .timeline-bar.fase-activa {
            outline: 2px solid white;
            outline-offset: 2px;
            box-shadow: 0 0 15px rgba(255,255,255,0.3);
            z-index: 5;
        }
        .timeline-semanas {
            position: absolute;
            bottom: -22px;
            left: 0;
            right: 0;
            display: flex;
            font-size: 9px;
            color: #4b5563;
        }
        .timeline-semanas span {
            flex: 1;
            text-align: center;
            border-left: 1px solid #1f1d38;
        }
        .fase-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
        }
        .tooltip-timeline {
            display: none;
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a1840;
            border: 1px solid #252345;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 100;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .timeline-bar:hover .tooltip-timeline {
            display: block;
        }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-project-diagram text-indigo-500"></i> Periodizacion ATR
            </h1>
            <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                <div class="text-right mr-2">
                    <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                    <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                        Cerrar Sesion <i class="fas fa-sign-out-alt ml-1"></i>
                    </a>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff"
                     class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <p class="text-sm text-gray-400 mt-1">Planificacion Acumulacion / Transmutacion / Realizacion por macrociclo.</p>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'editar')): ?>
            <button onclick="abrirModalMacro()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> CREAR MACROCICLO
            </button>
            <?php endif; ?>
        </div>

        <div class="tarjeta p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                <input type="text" id="busquedaMacro" onkeyup="filtrarTablaMacro()" placeholder="Buscar por nombre..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <select id="filtroTemporada" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todas las Temporadas</option>
                </select>
                <select id="filtroGrupo" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Grupos</option>
                </select>
                <select id="filtroEstado" onchange="cargarTablaMacro()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Estados</option>
                    <option value="Planificado">Planificado</option>
                    <option value="En Progreso">En Progreso</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Macrociclo</th>
                            <th class="p-4">Temporada</th>
                            <th class="p-4">Grupo</th>
                            <th class="p-4">Evento Objetivo</th>
                            <th class="p-4">Fechas</th>
                            <th class="p-4 text-center">Semanas</th>
                            <th class="p-4">Fase Actual</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMacro" class="divide-y divide-[#252345] text-sm text-gray-300">
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL CREAR/EDITAR MACROCICLO -->
    <div id="modalMacro" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalMacroTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-project-diagram text-emerald-400"></i> Crear Macrociclo
                </h3>
                <button onclick="cerrarModalMacro()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formMacro" autocomplete="off">
                <input type="hidden" id="id_macrociclo" name="id_macrociclo" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre del Macrociclo</label>
                        <input type="text" id="nombre" name="nombre" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Preparacion Nacional 2026">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Temporada *</label>
                        <select id="id_temporada" name="id_temporada" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Grupo *</label>
                        <select id="id_grupo" name="id_grupo" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Fin *</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Evento Objetivo (Competencia Principal)</label>
                        <select id="id_evento_objetivo" name="id_evento_objetivo" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Sin evento objetivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalMacro()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR MACROCICLO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETALLE -->
    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 relative z-10" id="detalleContenido">
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'editar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/periodizacion.js"></script>
</body>
</html>
