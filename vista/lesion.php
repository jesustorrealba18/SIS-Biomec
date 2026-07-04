<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control Clínico de Lesiones | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
    </style>
</head>
<body class="flex min-h-screen selection:bg-indigo-500/30">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <!-- Header unificado (sin botón de registrar) -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Control de Lesiones</h1>
                <p class="text-sm text-gray-400 mt-1">Gestión de Lesiones y Estados de Salud (RF-10)</p>
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

        <!-- Barra de acciones (similar a atleta.php: indicador + botón) -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-notes-medical"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Módulo de Control Clínico</span>
            </div>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'registrar')): ?>
            <button onclick="abrirModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                <i class="fas fa-plus"></i> Registrar Lesión
            </button>
            <?php endif; ?>
        </div>

        <!-- Contenido principal de lesiones (KPIs, filtros, tabla) -->
        <div class="space-y-6">
            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-indigo-500/10 group-hover:text-indigo-500/20 transition-colors">
                        <i class="fas fa-user-injured text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xl z-10">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Lesiones Activas</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_activas">0</h3>
                    </div>
                </div>

                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-red-500/10 group-hover:text-red-500/20 transition-colors">
                        <i class="fas fa-exclamation-triangle text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 text-xl z-10">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Molestia Alta (>7)</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_molestia_alta">0</h3>
                    </div>
                </div>

                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                        <i class="fas fa-calendar-week text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl z-10">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Reposo Promedio (días)</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_reposo_promedio">0</h3>
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
                    <button id="btnMostrarPapelera" class="text-xs bg-red-500/20 hover:bg-red-500/40 text-red-300 px-3 py-1 rounded-full transition flex items-center gap-1 cursor-pointer">
                        <i class="fas fa-trash-alt"></i> Ver Papelera
                    </button>
                </div>

                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                    </div>
                    <select id="filtroAtleta" class="w-full input-dark pl-12 pr-10 py-3 rounded-xl text-sm bg-[#0f0d23] border border-[#252345] hover:border-indigo-500/50 focus:border-indigo-500 transition-all cursor-pointer appearance-none shadow-inner">
                        <option value="">👤 Todos los Atletas</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full">
                    <select id="filtroEstadoClinico" class="w-full input-dark px-4 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] cursor-pointer">
                        <option value="">🏥 Todos los Estados Clínicos</option>
                        <option value="Activa">🟢 Activa</option>
                        <option value="EnRehabilitacion">🟡 En Rehabilitación</option>
                        <option value="Recuperada">✅ Recuperada</option>
                        <option value="Cronica">⚠️ Crónica</option>
                    </select>

                    <select id="filtroTipo" class="w-full input-dark px-4 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] cursor-pointer">
                        <option value="">📌 Todos los Tipos</option>
                        <option value="Sobreuso">Sobrecarga/Sobreuso</option>
                        <option value="Aguda">Aguda (Traumática)</option>
                        <option value="Recidiva">Recidiva (Reincidente)</option>
                    </select>

                    <select id="filtroZona" class="w-full input-dark px-4 py-2.5 rounded-xl text-xs bg-[#0f0d23] border border-[#252345] cursor-pointer">
                        <option value="">🦴 Todas las Zonas</option>
                        <option value="Hombro">Hombro</option>
                        <option value="Rodilla">Rodilla</option>
                        <option value="Espalda">Espalda</option>
                        <option value="Tobillo">Tobillo</option>
                        <option value="Otra">Otra</option>
                    </select>

                    <button onclick="cargarTabla()" class="bg-[#252345] hover:bg-indigo-600 text-white rounded-xl flex items-center justify-center gap-2 transition cursor-pointer py-2.5 px-4 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-sync-alt"></i> Filtrar
                    </button>
                </div>
            </div>

            <!-- Tabla de lesiones -->
            <div class="mt-2">
                <h2 id="tituloTablaState" class="text-lg font-bold text-emerald-400 mb-3 ml-2 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Mostrando Registros Activos
                </h2>
                <div class="tarjeta overflow-hidden shadow-lg border-t-2 border-t-indigo-500">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-[#0f0d23] text-gray-400 border-b border-[#252345] uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 font-bold">Fecha Inicio</th>
                                    <th class="px-6 py-4 font-bold">Atleta</th>
                                    <th class="px-6 py-4 font-bold">Zona / Lado</th>
                                    <th class="px-6 py-4 font-bold">Molestia</th>
                                    <th class="px-6 py-4 font-bold">Estado Clínico</th>
                                    <th class="px-6 py-4 font-bold text-center">Status DB</th>
                                    <th class="px-6 py-4 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCuerpo" class="divide-y divide-[#252345] text-gray-300">
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando registros médicos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para formulario de lesión (sin cambios) -->
    <div id="modalFormulario" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="bg-[#111026] border border-white/10 w-full max-w-4xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] flex flex-col max-h-[90vh]">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 relative modal-header-sticky">
                <button type="button" onclick="cerrarModal()" class="absolute top-6 right-6 text-white/70 hover:text-white hover:rotate-90 transition-all duration-300 cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-black text-white" id="tituloModal">Registrar Nueva Lesión</h2>
                <p class="text-indigo-100 text-sm mt-1">Componente del Sistema Inteligente de Prevención.</p>
            </div>

            <div class="overflow-y-auto p-8 modal-scroll">
                <form id="formularioLesion" class="space-y-6">
                    <input type="hidden" name="id_lesion" id="id_lesion">
                    <input type="hidden" name="accion" id="accion" value="registrar">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Atleta Afectado *</label>
                            <select name="id_atleta" id="id_atleta" class="w-full input-dark rounded-xl px-4 py-3" required data-validar="requerido" data-nombre="Atleta">
                                <option value="">Seleccione el atleta...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="w-full input-dark rounded-xl px-4 py-3" required data-validar="requerido|fecha_logica" data-nombre="Fecha de inicio">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Fecha Estimada Recuperación</label>
                            <input type="date" name="fecha_estimada_recup" id="fecha_estimada_recup" class="w-full input-dark rounded-xl px-4 py-3" data-validar="fecha_logica" data-nombre="Fecha estimada de recuperación" data-depende="fecha_inicio" data-mensaje="La fecha estimada no puede ser anterior a la fecha de inicio">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Zona Anatómica *</label>
                            <select name="zona_anatomica" id="zona_anatomica" class="w-full input-dark rounded-xl px-4 py-3" required data-validar="requerido" data-nombre="Zona anatómica">
                                <option value="">Seleccione...</option>
                                <option value="Hombro">Hombro</option>
                                <option value="Rodilla">Rodilla</option>
                                <option value="Espalda">Espalda</option>
                                <option value="Codo">Codo</option>
                                <option value="Tobillo">Tobillo</option>
                                <option value="Cervical">Cervical</option>
                                <option value="Lumbar">Lumbar</option>
                                <option value="Muslo">Muslo</option>
                                <option value="Gemelo">Gemelo</option>
                                <option value="Pie">Pie</option>
                                <option value="Otra">Otra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Lado Afectado</label>
                            <select name="lado" id="lado" class="w-full input-dark rounded-xl px-4 py-3" data-validar="" data-nombre="Lado afectado">
                                <option value="">No especificado</option>
                                <option value="Izquierdo">Izquierdo</option>
                                <option value="Derecho">Derecho</option>
                                <option value="Bilateral">Bilateral</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Tipo de Lesión *</label>
                            <select name="tipo" id="tipo" class="w-full input-dark rounded-xl px-4 py-3" required data-validar="requerido" data-nombre="Tipo de lesión">
                                <option value="">Seleccione el tipo...</option>
                                <option value="Sobreuso">Sobrecarga / Sobreuso</option>
                                <option value="Aguda">Aguda (Traumática)</option>
                                <option value="Recidiva">Recidiva (Reincidente)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Nivel de Molestia (1-10) *</label>
                            <input type="number" name="nivel_molestia" id="nivel_molestia" min="1" max="10" class="w-full input-dark rounded-xl px-4 py-3" required data-validar="requerido|rango" data-nombre="Nivel de molestia" data-min-num="1" data-max-num="10">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Diagnóstico Clínico *</label>
                            <textarea name="diagnostico" id="diagnostico" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" required data-validar="requerido|texto" data-nombre="Diagnóstico" data-min="10" data-max="500"></textarea>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Tratamiento Asignado</label>
                            <textarea name="tratamiento" id="tratamiento" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" data-validar="texto" data-nombre="Tratamiento" data-max="1000"></textarea>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Profesional Responsable</label>
                            <input type="text" name="profesional" id="profesional" class="w-full input-dark rounded-xl px-4 py-3" data-validar="letras|texto" data-nombre="Profesional responsable" data-min="3" data-max="100">
                        </div>

                        <div class="md:col-span-2" id="campoEstadoEdicion" style="display:none;">
                            <label class="block text-xs font-bold text-amber-400 uppercase tracking-wide mb-2">Actualizar Estado Clínico</label>
                            <select name="estado" id="estado" class="w-full input-dark rounded-xl px-4 py-3 border-amber-500/50 focus:border-amber-500" data-validar="requerido" data-nombre="Estado clínico">
                                <option value="Activa">🟢 Activa</option>
                                <option value="EnRehabilitacion">🟡 En Rehabilitación</option>
                                <option value="Recuperada">✅ Recuperada</option>
                                <option value="Cronica">⚠️ Crónica</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" data-validar="texto" data-nombre="Observaciones" data-max="500"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="cerrarModal()" class="flex-1 border border-[#252345] text-gray-300 py-3 rounded-xl font-bold hover:bg-[#252345] transition uppercase text-xs">Cancelar</button>
                        <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3 rounded-xl font-bold uppercase text-xs shadow-lg shadow-indigo-500/20">
                            Guardar Informe <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles (sin cambios) -->
    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <div class="p-8 md:p-10">
                <div id="contenidoDetalle"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        // Mapeo seguro de permisos del módulo
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'eliminar') ? 'true' : 'false'; ?>,
            eliminardb: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'eliminardb') ? 'true' : 'false'; ?>,
            reactivar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'reactivar') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/lesion.js"></script>
</body>
</html>