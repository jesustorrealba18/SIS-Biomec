<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Eventos y Metas | SGRD</title>
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
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-500"></i> Planificacion de Eventos y Metas
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
            <p class="text-sm text-gray-400 mt-1">Calendario competitivo, metas por atleta y tiempos de corte.</p>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'crear')): ?>
            <button onclick="abrirModalEvento()" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR EVENTO
            </button>
            <?php endif; ?>
        </div>

        <div class="tarjeta p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                <input type="text" id="busquedaEvento" onkeyup="filtrarTablaEventos()" placeholder="Buscar por nombre o sede..." class="w-full input-dark pl-11 pr-4 py-2.5 rounded-xl text-sm">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <select id="filtroTipo" onchange="cargarTablaEventos()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Tipos</option>
                    <option value="Regional">Regional</option>
                    <option value="Nacional">Nacional</option>
                    <option value="Internacional">Internacional</option>
                    <option value="Selectivo">Selectivo</option>
                    <option value="Control">Control</option>
                </select>

                <select id="filtroEstado" onchange="cargarTablaEventos()" class="input-dark p-2.5 rounded-xl text-xs bg-[#0f0d23]">
                    <option value="">Todos los Estados</option>
                    <option value="Planificado">Planificado</option>
                    <option value="Inscrito">Inscrito</option>
                    <option value="En Progreso">En Progreso</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
        </div>

        <div class="tarjeta overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0f0d23] text-gray-400 uppercase text-[11px] font-bold tracking-wider border-b border-[#252345]">
                            <th class="p-4">Evento</th>
                            <th class="p-4">Fechas</th>
                            <th class="p-4">Sede</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Nivel</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-center">Inscritos</th>
                            <th class="p-4 text-center">Metas</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyEventos" class="divide-y divide-[#252345] text-sm text-gray-300">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalEvento" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-3xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 id="modalEventoTitulo" class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-calendar-plus text-emerald-400"></i> Registrar Evento
                </h3>
                <button onclick="cerrarModalEvento()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formEvento" autocomplete="off">
                <input type="hidden" id="id_evento" name="id_evento" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nombre del Evento *</label>
                        <input type="text" id="nombre" name="nombre" data-validar="requerido|texto" data-nombre="Nombre del evento" data-min="2" data-max="200" maxlength="200" required class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Gala Regional Miranda 2026">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Inicio *</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" data-validar="requerido" data-nombre="Fecha de inicio" required class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" data-validar="fecha_logica" data-nombre="Fecha de fin" class="w-full input-dark p-3 rounded-xl text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Sede</label>
                        <input type="text" id="sede" name="sede" data-validar="texto" data-nombre="Sede" data-max="200" maxlength="200" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: Complejo Acuatico de Barinas">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Organizador</label>
                        <input type="text" id="organizador" name="organizador" data-validar="texto" data-nombre="Organizador" data-max="200" maxlength="200" class="w-full input-dark p-3 rounded-xl text-sm" placeholder="Ej: FEVEDA">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Tipo *</label>
                        <select id="tipo" name="tipo" data-validar="requerido" data-nombre="Tipo" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Control">Control Tecnico</option>
                            <option value="Regional">Regional</option>
                            <option value="Nacional">Nacional</option>
                            <option value="Internacional">Internacional</option>
                            <option value="Selectivo">Selectivo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Nivel</label>
                        <select id="nivel" name="nivel" class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="">Sin asignar</option>
                            <option value="A">Nivel A</option>
                            <option value="B">Nivel B</option>
                            <option value="C">Nivel C</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Estado *</label>
                        <select id="estado" name="estado" data-validar="requerido" data-nombre="Estado" required class="w-full input-dark p-3 rounded-xl text-sm">
                            <option value="Planificado">Planificado</option>
                            <option value="Inscrito">Inscrito</option>
                            <option value="En Progreso">En Progreso</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-2">&nbsp;</label>
                        <div></div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs text-gray-400 uppercase font-bold mb-2">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" data-validar="texto" data-nombre="Observaciones" data-max="500" maxlength="500" rows="2" placeholder="Detalles adicionales del evento..." class="w-full input-dark p-3 rounded-xl text-sm"></textarea>
                </div>

                <div class="mt-6 bg-black/20 p-4 rounded-xl border border-dashed border-amber-500/30">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[11px] uppercase text-amber-400 font-bold tracking-widest">
                            <i class="fas fa-cut mr-2"></i>Tiempos de Corte (CA-09.5)
                        </p>
                        <button type="button" onclick="agregarFilaTiempoCorte()" class="text-xs bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 px-3 py-1 rounded-lg transition cursor-pointer font-bold">
                            <i class="fas fa-plus mr-1"></i> Agregar
                        </button>
                    </div>

                    <div id="contenedorTiemposCorte" class="space-y-2">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalEvento()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR EVENTO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalMetas" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-4xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-bullseye text-amber-400"></i> <span id="tituloModalMetas">Metas Competitivas</span>
                </h3>
                <button onclick="cerrarModalMetas()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="formMetas" autocomplete="off">
                <input type="hidden" id="id_evento_metas" name="id_evento" value="">

                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-500">Estilo / Distancia / Marca Objetivo / PB Actual / Diferencia %</p>
                    <button type="button" onclick="agregarFilaMeta()" class="text-xs bg-indigo-500/20 text-indigo-400 hover:bg-indigo-500/30 px-3 py-1 rounded-lg transition cursor-pointer font-bold">
                        <i class="fas fa-plus mr-1"></i> Agregar Meta
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[10px] text-gray-500 uppercase tracking-wider border-b border-[#252345]">
                                <th class="p-2">Atleta</th>
                                <th class="p-2">Estilo</th>
                                <th class="p-2">Distancia</th>
                                <th class="p-2">Objetivo (s)</th>
                                <th class="p-2">PB Actual (s)</th>
                                <th class="p-2">Dif %</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyMetas" class="divide-y divide-[#252345]">
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="cerrarModalMetas()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                    <button type="submit" class="flex-[2] bg-amber-600 hover:bg-amber-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-amber-500/20 cursor-pointer uppercase text-xs tracking-wider">
                        GUARDAR METAS <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalInscripcion" class="fixed inset-0 bg-[#060512]/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-40 transition-all duration-300">
        <div class="relative bg-[#161430] border border-white/5 w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 max-h-[92vh] overflow-y-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-check text-cyan-400"></i> Inscribir Atletas
                </h3>
                <button onclick="cerrarModalInscripcion()" class="text-gray-400 hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <input type="hidden" id="id_evento_inscripcion" value="">
            <div class="mb-4">
                <input type="text" id="busquedaInscripcion" onkeyup="filtrarInscripciones()" placeholder="Buscar atleta..." class="w-full input-dark p-3 rounded-xl text-sm">
            </div>
            <div id="listaAtletasInscripcion" class="space-y-2 max-h-80 overflow-y-auto">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="cerrarModalInscripcion()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 py-3.5 rounded-xl font-bold transition cursor-pointer uppercase text-xs tracking-wider">CANCELAR</button>
                <button type="button" onclick="inscribirAtletas()" class="flex-[2] bg-cyan-600 hover:bg-cyan-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-cyan-500/20 cursor-pointer uppercase text-xs tracking-wider">
                    INSCRIBIR SELECCIONADOS <i class="fas fa-user-plus ml-2"></i>
                </button>
            </div>
        </div>
    </div>

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
            gestionar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'crear') ? 'true' : 'false'; ?>,

        };

    </script>
    <script src="assets/js/eventos.js"></script>
</body>
</html> 