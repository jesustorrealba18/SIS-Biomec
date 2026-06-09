<?php
// Declaramos la variable para que tu menu.php sepa qué botón iluminar
// (Si ya la declaras en el controlador, puedes omitir esta línea)
$pagina = 'antropometria'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento Antropométrico | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); }
        .table-header { background: rgba(99, 102, 241, 0.1); border-bottom: 2px solid #252345; }
          
        /* Scrollbar personalizado para que haga match con tu menú */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6366f1; }
    </style>
</head>
<body class="antialiased h-screen overflow-hidden flex">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">

        <header class="flex justify-between items-center mb-20">
             
                <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                    <i class="fas fa-stopwatch text-indigo-500"></i> Expedientes Antropométricos
                </h1>
                
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
                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto space-y-6">
            
            <div class="tarjeta p-6 flex flex-col md:flex-row justify-between items-center shadow-2xl relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl"></div>
                <div class="relative z-10 text-center md:text-left mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-white tracking-tight">
                        <i class="fas fa-child text-indigo-500 mr-3"></i>Dashboard Antropométrico
                    </h1>
                    <p class="text-gray-400 mt-2 text-sm">Control y evolución biológica de atletas (RF-05)</p>
                </div>
                <div class="relative z-10 flex gap-3">
                    <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar')): ?>
                    <button onclick="abrirModalMedicion()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-xl font-semibold shadow-lg shadow-indigo-500/30 transition-all flex items-center transform hover:scale-105 cursor-pointer">
                        <i class="fas fa-plus mr-2"></i> Nueva Medición
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tarjeta overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="table-header text-indigo-300 text-sm uppercase tracking-wider">
                                <th class="p-4 font-semibold">Atleta</th>
                                <th class="p-4 font-semibold">Categoría</th>
                                <th class="p-4 font-semibold text-center">Última Eval.</th>
                                <th class="p-4 font-semibold text-center">Peso / Talla</th>
                                <th class="p-4 font-semibold text-center">IMC</th>
                                <th class="p-4 font-semibold text-center">Estado</th>
                                <th class="p-4 font-semibold text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDashboardBody" class="text-sm divide-y divide-[#252345]">
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando datos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="modalMedicion" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            
            <button type="button" onclick="cerrarModalMedicion()" class="absolute top-6 right-6 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-[100]">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-[#161430] p-6 border-b border-white/5 flex items-center relative z-10">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-weight text-indigo-400"></i>
                </div>
                <h2 id="modalMedicionTitulo" class="text-2xl font-bold text-white">Registrar Medición</h2>
            </div>

            <form id="formMedicion" class="p-8 space-y-6 relative z-10">
                <input type="hidden" id="accion" name="accion" value="guardar">
                <input type="hidden" id="id_medicion" name="id_medicion">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-user text-indigo-500 w-5"></i> Atleta *
                        </label>
                        <select id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta" class="w-full p-3.5 rounded-xl input-dark cursor-pointer text-sm font-medium">
                            <option value="">Seleccione un atleta...</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-calendar-alt text-indigo-500 w-5"></i> Fecha de Evaluación *
                        </label>
                        <input type="date" id="fecha" name="fecha" data-validar="requerido" data-nombre="Fecha" class="w-full p-3.5 rounded-xl input-dark text-sm" max="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-weight-hanging text-indigo-500 w-5"></i> Peso (kg) *
                        </label>
                        <input type="number" step="0.1" id="peso" name="peso" data-validar="requerido" data-nombre="Peso" class="w-full p-3.5 rounded-xl input-dark text-sm" placeholder="Ej: 75.5">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-vertical text-indigo-500 w-5"></i> Talla (cm) *
                        </label>
                        <input type="number" step="0.1" id="talla" name="talla" data-validar="requerido" data-nombre="Talla" class="w-full p-3.5 rounded-xl input-dark text-sm" placeholder="Ej: 180">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-ruler-horizontal text-indigo-500 w-5"></i> Envergadura (cm) *
                        </label>
                        <input type="number" step="0.1" id="envergadura" name="envergadura" data-validar="requerido" data-nombre="Envergadura" class="w-full p-3.5 rounded-xl input-dark text-sm" placeholder="Ej: 185">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-circle-notch text-indigo-500 w-5"></i> Perím. Abdominal (cm) *
                        </label>
                        <input type="number" step="0.1" id="perimetro_abdominal" name="perimetro_abdominal" data-validar="requerido" data-nombre="Perímetro Abdominal" class="w-full p-3.5 rounded-xl input-dark text-sm" placeholder="Ej: 80">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                            <i class="fas fa-percent text-indigo-500 w-5"></i> % Grasa Corporal
                        </label>
                        <input type="number" step="0.1" id="grasa_corporal" name="grasa_corporal" class="w-full p-3.5 rounded-xl input-dark text-sm" placeholder="Opcional">
                    </div>
                </div>

                <div class="p-4 bg-indigo-900/20 border border-indigo-500/30 rounded-xl flex justify-between items-center mt-4">
                    <span class="text-sm text-indigo-300"><i class="fas fa-calculator mr-2"></i>IMC Proyectado:</span>
                    <span id="imc_preview" class="text-xl font-bold text-white">--</span>
                </div>

                <div id="contenedorJustificacion" class="space-y-2 hidden mt-4">
                    <label class="text-xs font-bold text-orange-400 uppercase tracking-wider flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Justificación de la Modificación *
                    </label>
                    <textarea id="justificacion" name="justificacion" rows="2" class="w-full p-3.5 rounded-xl input-dark text-sm border-orange-500/50 focus:border-orange-500" placeholder="Auditoría: Explique brevemente por qué corrige este registro."></textarea>
                </div>

                <div class="flex gap-4 pt-6 border-t border-white/5">
                    <button type="button" onclick="cerrarModalMedicion()" class="flex-1 bg-[#252345] hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR MEDICIÓN <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalGraficas" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-5xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            
            <button type="button" onclick="cerrarModalGraficas()" class="absolute top-6 right-6 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-[100]">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="bg-[#161430] p-6 border-b border-white/5 flex items-center relative z-10">
                <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center mr-4">
                    <i class="fas fa-chart-line text-green-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Evolución Antropométrica</h2>
            </div>

            <div class="p-6 space-y-8 relative z-10">
                <div class="flex items-center gap-4 p-4 bg-white/5 rounded-xl border border-white/10">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/30 flex items-center justify-center text-xl font-bold text-indigo-300">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                    <div>
                        <h3 id="graficaAtletaNombre" class="text-lg font-bold text-white">Cargando...</h3>
                        <p class="text-sm text-gray-400">Historial de mediciones corporales</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="tarjeta p-4">
                        <h4 class="text-center text-sm font-bold text-gray-300 mb-2">Evolución de Peso (kg) y Talla (cm)</h4>
                        <canvas id="chartPesoTalla" height="200"></canvas>
                    </div>
                    <div class="tarjeta p-4">
                        <h4 class="text-center text-sm font-bold text-gray-300 mb-2">Curva del Índice de Masa Corporal (IMC)</h4>
                        <canvas id="chartIMC" height="200"></canvas>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold text-white mb-3 border-b border-gray-700 pb-2">Registros Históricos</h4>
                    <div class="overflow-x-auto rounded-xl border border-[#252345]">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-[#252345] text-indigo-200">
                                <tr>
                                    <th class="p-3">Fecha</th>
                                    <th class="p-3">Peso</th>
                                    <th class="p-3">Talla</th>
                                    <th class="p-3">Envergadura</th>
                                    <th class="p-3">IMC</th>
                                    <th class="p-3">Responsable</th>
                                    <th class="p-3 text-center">Edición</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialBody" class="divide-y divide-[#252345] bg-[#161430]">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/antropometria.js"></script>
</body>
</html>