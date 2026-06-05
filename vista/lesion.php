<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        /* Custom Scrollbar para el modal y la tabla */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6366f1; }
    </style>
</head>
<body class="flex h-screen overflow-hidden selection:bg-indigo-500/30">

    <?php 
    // Mantenemos la inclusión de tu menú lateral intacta
    require_once 'vista/comunes/menu.php'; 
    ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative z-10">
        
        <header class="bg-[#161430]/80 backdrop-blur-md border-b border-white/5 p-6 flex justify-between items-center z-20">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                    <i class="fas fa-notes-medical text-indigo-500"></i> Control Clínico
                </h1>
                <p class="text-sm text-gray-400 mt-1">Gestión de Lesiones y Estados de Salud (RF-10)</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="abrirModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-[0_0_20px_rgba(79,70,229,0.3)] transition-all cursor-pointer flex items-center gap-2">
                    <i class="fas fa-plus"></i> Registrar Lesión
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <i class="fas fa-radiation"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Casos Graves</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_graves">0</h3>
                    </div>
                </div>

                <div class="tarjeta p-5 flex items-center gap-4 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 text-emerald-500/10 group-hover:text-emerald-500/20 transition-colors">
                        <i class="fas fa-bed text-8xl"></i>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl z-10">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Promedio Reposo (Días)</p>
                        <h3 class="text-2xl font-black text-white mt-1" id="kpi_reposo">0</h3>
                    </div>
                </div>
            </div>

            <div class="tarjeta p-4 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                        <input type="text" id="buscadorGeneral" placeholder="Buscar por diagnóstico o zona..." 
                               class="w-full bg-[#0f0d23] border border-[#252345] text-white text-sm rounded-xl pl-10 pr-4 py-2.5 focus:border-indigo-500 outline-none transition">
                    </div>
                    
                    <select id="filtroAtleta" class="bg-[#0f0d23] border border-[#252345] text-white text-sm rounded-xl px-4 py-2.5 focus:border-indigo-500 outline-none transition cursor-pointer">
                        <option value="">Todos los Atletas</option>
                        </select>

                    <select id="filtroEstado" class="bg-[#0f0d23] border border-[#252345] text-white text-sm rounded-xl px-4 py-2.5 focus:border-indigo-500 outline-none transition cursor-pointer">
                        <option value="Activo">Estado: Activo</option>
                        <option value="Anulado">Estado: Anulado (IA)</option>
                    </select>
                </div>
                
                <button onclick="cargarTabla()" class="bg-[#252345] hover:bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center transition cursor-pointer" title="Actualizar Datos">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>

            <div class="tarjeta overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-[#0f0d23] text-gray-400 border-b border-[#252345] uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4 font-bold">Fecha</th>
                                <th class="px-6 py-4 font-bold">Atleta</th>
                                <th class="px-6 py-4 font-bold">Tipo / Zona</th>
                                <th class="px-6 py-4 font-bold">Gravedad</th>
                                <th class="px-6 py-4 font-bold">Reposo</th>
                                <th class="px-6 py-4 font-bold text-center">Estado</th>
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
    </main>

    <div id="modalFormulario" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="bg-[#111026] border border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] transform transition-all">
            
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 relative">
                <button type="button" onclick="cerrarModal()" class="absolute top-6 right-6 text-white/70 hover:text-white hover:rotate-90 transition-all duration-300 cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <h2 class="text-2xl font-black text-white" id="tituloModal">Registrar Nuevo Evento Médico</h2>
                <p class="text-indigo-100 text-sm mt-1">Los datos alimentarán el algoritmo de prevención de lesiones.</p>
            </div>

            <form id="formularioLesion" class="p-8">
                <input type="hidden" name="id_lesion" id="id_lesion">
                <input type="hidden" name="accion" id="accion" value="registrar">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Atleta Afectado *</label>
                        <select name="id_atleta" id="id_atleta" class="w-full input-dark rounded-xl px-4 py-3" required>
                            <option value="">Seleccione el atleta...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Fecha de Lesión *</label>
                        <input type="date" name="fecha_lesion" id="fecha_lesion" class="w-full input-dark rounded-xl px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Tipo de Lesión *</label>
                        <select name="tipo_lesion" id="tipo_lesion" class="w-full input-dark rounded-xl px-4 py-3" required>
                            <option value="">Seleccione el tipo...</option>
                            <option value="Muscular">Muscular (Desgarro, Contractura)</option>
                            <option value="Articular">Articular (Esguince, Luxación)</option>
                            <option value="Ósea">Ósea (Fractura, Fisura)</option>
                            <option value="Tendinosa">Tendinosa (Tendinitis)</option>
                            <option value="Enfermedad General">Enfermedad General (Fiebre, Infección)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Zona Corporal *</label>
                        <input type="text" name="zona_corporal" id="zona_corporal" placeholder="Ej. Hombro derecho, Manguito rotador..." class="w-full input-dark rounded-xl px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Gravedad *</label>
                        <select name="gravedad" id="gravedad" class="w-full input-dark rounded-xl px-4 py-3" required>
                            <option value="">Seleccione nivel...</option>
                            <option value="Leve">Leve (Puede entrenar con molestias)</option>
                            <option value="Moderada">Moderada (Requiere bajar carga)</option>
                            <option value="Grave">Grave (Cese total de actividad)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Diagnóstico Clínico *</label>
                        <textarea name="diagnostico" id="diagnostico" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" required placeholder="Descripción detallada del diagnóstico..."></textarea>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Tratamiento Asignado</label>
                        <textarea name="tratamiento" id="tratamiento" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" placeholder="Fisioterapia, hielo, medicamentos..."></textarea>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Días Reposo Estimados</label>
                        <input type="number" name="dias_reposo_estimados" id="dias_reposo_estimados" min="0" placeholder="0" class="w-full input-dark rounded-xl px-4 py-3">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Observaciones Generales</label>
                        <textarea name="observaciones" id="observaciones" rows="2" class="w-full input-dark rounded-xl px-4 py-3 resize-none" placeholder="Cualquier nota extra para el entrenador principal..."></textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <button type="button" onclick="cerrarModal()" class="flex-1 bg-transparent border border-[#252345] hover:bg-[#252345] text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR INFORME CLÍNICO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
            <button type="button" onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer p-2">
                <i class="fas fa-times text-2xl"></i>
            </button>

            <div class="p-8 md:p-10">
                <div class="flex items-center gap-4 mb-8 border-b border-white/10 pb-6">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-3xl shrink-0">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-white leading-tight" id="ver_atleta">Cargando...</h2>
                        <p class="text-indigo-400 font-medium flex items-center gap-2 mt-1">
                            <i class="fas fa-calendar-alt"></i> <span id="ver_fecha">00/00/0000</span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-[#161430] p-4 rounded-xl border border-white/5">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Zona Afectada</p>
                        <p class="text-white font-medium" id="ver_zona">...</p>
                    </div>
                    <div class="bg-[#161430] p-4 rounded-xl border border-white/5">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Gravedad</p>
                        <p class="text-white font-medium" id="ver_gravedad">...</p>
                    </div>
                    <div class="col-span-2 bg-[#161430] p-4 rounded-xl border border-white/5">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Diagnóstico Clínico</p>
                        <p class="text-gray-300" id="ver_diagnostico">...</p>
                    </div>
                    <div class="col-span-2 bg-[#161430] p-4 rounded-xl border border-white/5">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Tratamiento y Reposo</p>
                        <p class="text-gray-300" id="ver_tratamiento">...</p>
                    </div>
                </div>

                <div class="bg-[#161430] border border-white/5 rounded-2xl p-5 mb-8 hidden" id="contenedorGraficaImpacto">
                    <h3 class="text-sm font-bold text-gray-300 mb-4 uppercase tracking-wider">Impacto en la Carga (RPE)</h3>
                    <div class="h-48 w-full">
                        <canvas id="graficaImpacto"></canvas>
                    </div>
                </div>
                
                <div class="flex justify-end pt-4">
                    <button onclick="cerrarModalVer()" class="bg-[#252345] hover:bg-white/10 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-colors cursor-pointer">
                        Cerrar Ficha
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script> 
    <script src="assets/js/lesion.js"></script>
</body>
</html>