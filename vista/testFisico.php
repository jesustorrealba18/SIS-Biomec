<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Tests Fisicos | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <?php $PERMISOS_MODULO_REGISTRAR = \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('testFisico', 'registrar'); ?>

        <header class="flex justify-between items-center mb-12">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-dumbbell text-indigo-500"></i> Tests Fisicos Complementarios
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
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guia de ayuda
                    </div>
                </div>
                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesion <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'U'); ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <p class="text-sm text-gray-400">Registro de pruebas fisicas fuera del agua (Dryland, Lactato, Cooper, etc.)</p>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('testFisico', 'registrar')): ?>
            <button onclick="abrirModalTest()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR TEST
            </button>
            <?php endif; ?>
        </div>

        <div class="tarjeta p-5 flex flex-col gap-4 border border-white/5 shadow-lg shadow-black/20 mb-6">
            <div class="flex items-center gap-2 border-b border-[#252345] pb-2">
                <i class="fas fa-filter text-indigo-400 text-sm"></i>
                <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest">Filtros de Busqueda</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-circle text-gray-400 text-xs"></i>
                    </div>
                    <select id="filtroAtleta" onchange="cargarTabla()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                        <option value="">Todos los Atletas</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-flask text-cyan-400/70 text-xs"></i>
                    </div>
                    <select id="filtroTipoTest" onchange="cargarTabla()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                        <option value="">Todos los Tests</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-clipboard-check text-amber-400/70 text-xs"></i>
                    </div>
                    <select id="filtroEstado" onchange="cargarTabla()" class="w-full input-dark pl-9 pr-8 py-2.5 rounded-xl text-xs appearance-none cursor-pointer">
                        <option value="">Todos los Estados</option>
                        <option value="Completo">Completo</option>
                        <option value="Parcial">Parcial</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-600 text-[10px]"></i>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                    <input type="text" id="busquedaGeneral" placeholder="Buscar..." class="w-full input-dark pl-9 pr-4 py-2.5 rounded-xl text-xs" oninput="filtrarTabla()">
                </div>
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345] sticky top-0">
                            <th class="p-4">Fecha</th>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Tipo de Test</th>
                            <th class="p-4">Valor Principal</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTests" class="divide-y divide-[#252345] text-sm text-gray-300">
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($PERMISOS_MODULO_REGISTRAR): ?>
        <div class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-cogs text-indigo-400"></i>
                    <h2 class="text-lg font-bold text-white">Gestionar Tipos de Tests</h2>
                </div>
                <div class="flex gap-2">
                    <button onclick="abrirModalTipo()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer text-xs">
                        <i class="fas fa-plus"></i> Nuevo Tipo Predefinido
                    </button>
                    <button onclick="abrirModalPersonalizado()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/20 active:scale-95 cursor-pointer text-xs">
                        <i class="fas fa-plus"></i> Test Personalizado
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="tarjeta p-5">
                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-flask text-cyan-400 text-xs"></i> Tests Predefinidos
                    </h3>
                    <div id="tablaTiposPredefinidos" class="space-y-2 max-h-80 overflow-y-auto"></div>
                </div>
                <div class="tarjeta p-5">
                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="fas fa-user-pen text-emerald-400 text-xs"></i> Tests Personalizados
                    </h3>
                    <div id="tablaTestsPersonalizados" class="space-y-2 max-h-80 overflow-y-auto"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- Modal Crear/Editar -->
    <div id="modalTest" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">

            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-dumbbell text-indigo-400"></i> Registrar Test Fisico
                </h3>
                <button onclick="cerrarModalTest()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formTest" autocomplete="off">
                <input type="hidden" id="accion_form" name="accion" value="registrar">
                <input type="hidden" id="id_registro_test" name="id_registro_test" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Atleta *</label>
                        <input type="hidden" id="id_atleta" name="id_atleta" data-validar="requerido" data-nombre="Atleta Seleccionado">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-500"></i>
                            <input type="text" id="inputBuscarAtleta" placeholder="Escriba nombre o cedula..." class="w-full input-dark pl-10 pr-4 py-3 rounded-xl text-sm" autocomplete="off" required>
                            <button type="button" id="btnLimpiarAtleta" class="absolute right-3 top-3.5 text-gray-500 hover:text-red-400 hidden transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="dropdownAtletas" class="absolute z-50 w-full mt-1 bg-[#111026] border border-[#252345] rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] max-h-52 overflow-y-auto hidden">
                            <ul id="ulAtletas" class="text-sm text-gray-300 divide-y divide-[#252345]"></ul>
                        </div>
                    </div>

                     <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Origen del Test *</label>
                        <select id="origen_test" onchange="cambiarOrigenTest()" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="predefinido">Test Predefinido</option>
                            <option value="personalizado">Test Personalizado</option>
                        </select>
                    </div>

                    <div id="contenedorPredefinido">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Test *</label>
                        <select id="id_tipo_test" name="id_tipo_test" onchange="cargarVariables()" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Seleccione un test...</option>
                        </select>
                    </div>

                    <div id="contenedorPersonalizado" class="hidden">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Test Personalizado *</label>
                        <select id="id_test_pers" name="id_test_pers" onchange="cargarVariables()" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha del Test *</label>
                        <input type="date" id="fecha" name="fecha" max="<?php echo date('Y-m-d'); ?>" data-validar="requerido" data-nombre="Fecha" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Estado</label>
                        <select id="estado" name="estado" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Completo">Completo</option>
                            <option value="Parcial">Parcial</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <div id="contenedorVariables" class="hidden mt-6 bg-black/30 p-4 rounded-2xl border border-dashed border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-emerald-400 font-bold tracking-widest">
                            <i class="fas fa-vials mr-2"></i>Variables del Test
                        </p>
                        <span id="contadorVariables" class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded font-mono font-bold">0 Variables</span>
                    </div>
                    <div id="rejillaVariables" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" data-validar="texto" data-max="500" data-nombre="Observaciones" rows="2" maxlength="500" placeholder="Notas sobre las condiciones del test..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalTest()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR TEST <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Gestionar Tipo Predefinido -->
    <div id="modalTipo" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalTipoTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-flask text-cyan-400"></i> Nuevo Tipo Predefinido
                </h3>
                <button onclick="cerrarModalTipo()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formTipo" autocomplete="off">
                <input type="hidden" id="id_tipo_test_edit" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre *</label>
                        <input type="text" id="tipo_nombre" name="nombre" class="w-full input-dark p-3 rounded-xl text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Medicion</label>
                        <input type="text" id="tipo_medicion" name="tipo_medicion" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Lactato, Distancia, Tiempo">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Unidad de Medida</label>
                        <input type="text" id="tipo_unidad" name="unidad_medida" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: mmol/L, cm, seg">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Valores de Referencia</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" step="0.01" id="tipo_ref_min" name="valor_referencia_min" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Min">
                            <input type="number" step="0.01" id="tipo_ref_max" name="valor_referencia_max" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Max">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Descripcion</label>
                    <textarea id="tipo_descripcion" name="descripcion" rows="2" class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>
                <div class="mt-4 bg-black/30 p-4 rounded-2xl border border-dashed border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-cyan-400 font-bold tracking-widest">
                            <i class="fas fa-vials mr-2"></i>Variables del Tipo
                        </p>
                        <button type="button" onclick="agregarVariableTipo()" class="text-xs bg-cyan-600/20 text-cyan-400 px-3 py-1 rounded-lg hover:bg-cyan-600/30 transition cursor-pointer">
                            <i class="fas fa-plus mr-1"></i> Agregar Variable
                        </button>
                    </div>
                    <div id="rejillaVariablesTipo" class="space-y-2">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalTipo()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-cyan-600 hover:bg-cyan-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-cyan-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR TIPO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Test Personalizado -->
    <div id="modalPersonalizado" class="fixed inset-0 z-50 hidden bg-black/20 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-2xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-pen text-emerald-400"></i> <span id="persModalTitulo">Nuevo Test Personalizado</span>
                </h3>
                <button onclick="cerrarModalPersonalizado()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formPersonalizado" autocomplete="off">
                <input type="hidden" id="pers_id_edit" value="">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre del Test *</label>
                        <input type="text" id="pers_nombre" name="nombre" class="w-full input-dark p-3 rounded-xl text-sm" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo de Medicion</label>
                            <input type="text" id="pers_tipo_medicion" name="tipo_medicion" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Velocidad, Fuerza">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Unidad de Medida</label>
                            <input type="text" id="pers_unidad" name="unidad_medida" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: seg, cm, reps">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Referencia Min</label>
                            <input type="number" step="0.01" id="pers_ref_min" name="valor_referencia_min" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Min">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Referencia Max</label>
                            <input type="number" step="0.01" id="pers_ref_max" name="valor_referencia_max" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Descripcion</label>
                        <textarea id="pers_descripcion" name="descripcion" rows="2" class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-4 bg-black/30 p-4 rounded-2xl border border-dashed border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-emerald-400 font-bold tracking-widest">
                            <i class="fas fa-vials mr-2"></i>Variables del Test
                        </p>
                        <button type="button" onclick="agregarVariablePers()" class="text-xs bg-emerald-600/20 text-emerald-400 px-3 py-1 rounded-lg hover:bg-emerald-600/30 transition cursor-pointer">
                            <i class="fas fa-plus mr-1"></i> Agregar Variable
                        </button>
                    </div>
                    <div id="rejillaVariablesPers" class="space-y-2">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalPersonalizado()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" id="persBtnSubmit" class="flex-[2] bg-emerald-600 hover:bg-emerald-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        CREAR TEST <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-3xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto">
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
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('testFisico', 'registrar') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/testFisico.js"></script>
</body>
</html>
