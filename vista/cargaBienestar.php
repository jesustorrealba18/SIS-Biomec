<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Carga y Bienestar (RPE) | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <!-- DataTables CSS + Responsive -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 15px rgba(99,102,241,0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6366f1; }
        .modal-scroll { max-height: 90vh; overflow-y: auto; }
        .modal-header-sticky { position: sticky; top: 0; z-index: 20; }
        
        /* Botón parpadeante para inconsistencias */
        @keyframes blink {
            0% { background-color: rgba(239,68,68,0.2); border-color: #ef4444; }
            50% { background-color: rgba(239,68,68,0.8); border-color: #ef4444; color: white; }
            100% { background-color: rgba(239,68,68,0.2); border-color: #ef4444; }
        }
        .btn-blink { animation: blink 1s infinite; font-weight: bold; }

        /* Toggle papelera */
        #toggleEstadoRPEBtn.active {
            border-color: #ef4444;
            background: rgba(239,68,68,0.1);
        }
        #toggleEstadoRPEBtn.active #toggleIconoRPE { color: #ef4444; }
        #toggleEstadoRPEBtn.active #toggleTextoRPE { color: #ef4444; }
    </style>
</head>
<body class="flex min-h-screen selection:bg-indigo-500/30">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <!-- Header unificado (igual que lesion.php) -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Carga Interna y Bienestar (RPE)</h1>
                <p class="text-sm text-gray-400 mt-1">Monitoreo de fatiga, sueño y percepción de esfuerzo (RF-11)</p>
            </div>
            <div class="flex items-center gap-6">
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>
                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre']); ?>&background=4f46e5&color=fff"
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <!-- Barra de acciones: indicador + toggle papelera + botón nuevo -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-heartbeat"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Monitoreo de Carga Interna (RPE)</span>
            </div>
            <div class="flex gap-3">
                <!-- Toggle papelera -->
                <button id="toggleEstadoRPEBtn" class="group relative flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#161430] border border-[#252345] hover:border-red-500/50 transition-all duration-200">
                    <i id="toggleIconoRPE" class="fas fa-trash-alt text-gray-400 group-hover:text-red-400 transition-colors"></i>
                    <span id="toggleTextoRPE" class="text-xs font-medium text-gray-300">Activos</span>
                    <div class="absolute -top-2 -right-2">
                        <span id="estadoBadgeRPE" class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">A</span>
                    </div>
                </button>
                <!-- Botón nuevo registro -->
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar')): ?>
                <button onclick="abrirModalRPE()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="space-y-6">

            <!-- KPIs (adaptados a RPE) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                        <i class="fas fa-chart-line text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xl z-10">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">RPE Promedio (Últ. 30d)</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_rpe_promedio">--</h3>
                    </div>
                </div>
                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-amber-500/10 group-hover:text-amber-500/20 transition-colors">
                        <i class="fas fa-moon text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 text-xl z-10">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Horas Sueño Promedio</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_sueno_promedio">--</h3>
                    </div>
                </div>
                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                        <i class="fas fa-charging-station text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl z-10">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">sRPE Semanal Promedio</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_srpe_semanal">--</h3>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20">
                <div class="flex items-center justify-between gap-2 border-b border-[#252345] pb-2">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-filter text-indigo-400 text-sm"></i>
                        <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Búsqueda</h3>
                    </div>
                </div>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                    </div>
                    <select id="filtroAtletaRPE" class="w-full input-dark pl-12 pr-10 py-3 rounded-xl text-sm bg-[#0f0d23] border border-[#252345] hover:border-indigo-500/50 focus:border-indigo-500 transition-all cursor-pointer appearance-none shadow-inner">
                        <option value="">👤 Todos los Atletas</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full">
                    <input type="date" id="filtroFechaInicio" class="input-dark px-4 py-2.5 rounded-xl text-sm" placeholder="Fecha inicio">
                    <input type="date" id="filtroFechaFin" class="input-dark px-4 py-2.5 rounded-xl text-sm" placeholder="Fecha fin">
                    <button onclick="cargarTablaRPE()" class="bg-[#252345] hover:bg-indigo-600 text-white rounded-xl flex items-center justify-center gap-2 transition cursor-pointer py-2.5 px-4 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-sync-alt"></i> Filtrar
                    </button>
                </div>
            </div>

            <!-- Tabla de registros RPE -->
            <div class="mt-2">
                <h2 id="tituloTablaRPE" class="text-lg font-bold text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                </h2>
                <div class="tarjeta overflow-hidden shadow-lg border-t-2 border-t-indigo-500">
                    <div class="overflow-x-auto">
                        <table id="tablaRPE" class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-[#0f0d23] text-gray-400 border-b border-[#252345] uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Fecha</th>
                                    <th class="px-6 py-4 font-bold">Atleta</th>
                                    <th class="px-6 py-4 font-bold">RPE (0-10)</th>
                                    <th class="px-6 py-4 font-bold">sRPE</th>
                                    <th class="px-6 py-4 font-bold">Sueño (h)</th>
                                    <th class="px-6 py-4 font-bold">Estado DB</th>
                                    <th class="px-6 py-4 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCuerpoRPE" class="divide-y divide-[#252345] text-gray-300">
                                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sección de Inconsistencias Biológicas -->
            <div class="tarjeta overflow-hidden shadow-lg">
                <div class="p-5 border-b border-[#252345] flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <h3 class="text-md font-bold text-white flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-400"></i> 
                            Alertas de Inconsistencia Biológica
                        </h3>
                        <p class="text-xs text-gray-500">Registros RPE = 1 (reposo total) con récord personal el mismo día</p>
                    </div>
                    <button onclick="cargarTablaInconsistenciasRPE()" class="text-indigo-400 hover:text-indigo-300 text-sm">
                        <i class="fas fa-sync-alt"></i> Refrescar
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#0f0d23] text-gray-400 border-b border-[#252345] uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-bold">Fecha</th>
                                <th class="px-6 py-4 font-bold">Atleta</th>
                                <th class="px-6 py-4 font-bold">RPE</th>
                                <th class="px-6 py-4 font-bold">Récord Personal</th>
                                <th class="px-6 py-4 font-bold">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listaInconsistenciasRPE" class="divide-y divide-[#252345]">
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin"></i> Cargando alertas...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL REGISTRO/EDICIÓN RPE (mismo estilo que lesion) -->
    <div id="modalRPE" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="bg-[#111026] border border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] flex flex-col max-h-[90vh]">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 relative modal-header-sticky">
                <button type="button" onclick="cerrarModalRPE()" class="absolute top-6 right-6 text-white/70 hover:text-white hover:rotate-90 transition-all duration-300 cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-black text-white" id="modalTituloRPE">Registrar Carga Interna (RPE)</h2>
                <p class="text-indigo-100 text-sm mt-1">Complete los datos de fatiga y bienestar subjetivo.</p>
            </div>
            <div class="overflow-y-auto p-8 modal-scroll">
                <form id="formRPE" class="space-y-6">
                    <input type="hidden" name="id_rpe" id="id_rpe">
                    <input type="hidden" name="accion" id="accionRPE" value="registrar">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Atleta *</label>
                            <select name="id_atleta" id="id_atleta_rpe" class="w-full input-dark rounded-xl px-4 py-3" required>
                                <option value="">Seleccione atleta...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Fecha *</label>
                            <input type="date" name="fecha" id="fecha_rpe" class="w-full input-dark rounded-xl px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">RPE (1-10) *</label>
                            <input type="number" name="rpe" id="rpe_valor" min="1" max="10" class="w-full input-dark rounded-xl px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Duración (minutos)</label>
                            <input type="number" name="duracion_minutos" id="duracion_minutos" class="w-full input-dark rounded-xl px-4 py-3" placeholder="Opcional, se calcula sRPE">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Metros nadados</label>
                            <input type="number" name="metros_nadados" id="metros_nadados" class="w-full input-dark rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Horas de sueño</label>
                            <input type="number" step="0.5" name="horas_sueno" id="horas_sueno" class="w-full input-dark rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Calidad de sueño (1-10)</label>
                            <input type="number" name="calidad_sueno" id="calidad_sueno" min="1" max="10" class="w-full input-dark rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Sensación muscular (1-10)</label>
                            <input type="number" name="sensacion_muscular" id="sensacion_muscular" min="1" max="10" class="w-full input-dark rounded-xl px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Estrés percibido (1-10)</label>
                            <input type="number" name="estres_percibido" id="estres_percibido" min="1" max="10" class="w-full input-dark rounded-xl px-4 py-3">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Observaciones</label>
                            <textarea name="observaciones" id="observaciones_rpe" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="cerrarModalRPE()" class="flex-1 border border-[#252345] text-gray-300 py-3 rounded-xl font-bold hover:bg-[#252345] transition uppercase text-xs">Cancelar</button>
                        <button type="submit" id="btnGuardarRPE" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold uppercase text-xs shadow-lg shadow-indigo-500/20">
                            Guardar Registro <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL VER DETALLE -->
    <div id="modalVerRPE" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVerRPE()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 md:p-10" id="contenidoDetalleRPE"></div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>

    <!-- Permisos inyectados desde PHP -->
    <script>
        const PERMISOS_RPE = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>

    <!-- Nuestro JS refactorizado (cargaBienestar.js) -->
    <script src="assets/js/cargaBienestar.js"></script>

    <!-- Script para calcular KPIs y actualizar el toggle de papelera (ya va dentro de cargaBienestar.js, pero lo dejamos por si acaso) -->
    <script>
        // El JS de cargaBienestar.js ya tiene la lógica completa.
        // Solo aseguramos que al cargar la página se calcule el título de la tabla según el modo.
        function actualizarTituloTabla() {
            const titulo = document.getElementById('tituloTablaRPE');
            if (titulo) {
                if (modoPapeleraRPE) {
                    titulo.innerHTML = '<i class="fas fa-trash-alt text-red-400"></i> Mostrando Registros Anulados (Papelera)';
                    titulo.classList.remove('text-emerald-400');
                    titulo.classList.add('text-red-400');
                } else {
                    titulo.innerHTML = '<i class="fas fa-check-circle text-emerald-400"></i> Mostrando Registros Activos';
                    titulo.classList.remove('text-red-400');
                    titulo.classList.add('text-emerald-400');
                }
            }
        }
        // Sobrescribimos la función de toggle para que también actualice el título
        const toggleOriginal = window.actualizarUIRPE;
        window.actualizarUIRPE = function() {
            if (toggleOriginal) toggleOriginal();
            actualizarTituloTabla();
        };
        document.addEventListener('DOMContentLoaded', () => {
            actualizarTituloTabla();
        });
    </script>
</body>
</html>