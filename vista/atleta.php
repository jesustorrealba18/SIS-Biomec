<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Atletas | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .sidebar { background-color: #161430; width: 260px; border-right: 1px solid #252345; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        .tab-btn { padding: 10px 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: 0.1em; color: #6b7280; border-bottom: 2px solid transparent;
                   transition: all 0.3s; cursor: pointer; }
        .tab-btn:hover { color: #c7d2fe; }
        .tab-btn.active { color: #818cf8; border-bottom-color: #6366f1; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .estado-badge { padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .estado-Activo { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .estado-Inactivo { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .estado-Retirado { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .estado-Transferido { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-20">
            <h1 class="text-2xl font-bold text-white">Gestión de atletas</h1>
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

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2 text-sm text-indigo-400">
                <i class="fas fa-swimmer"></i>
                <span class="font-medium tracking-wide uppercase text-xs">Módulo de Control de Nadadores</span>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    <input type="text" id="busquedaAtleta" placeholder="Buscar por nombre o cédula..."
                           class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm shadow-inner">
                </div>
                <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'crear')): ?>
                <button onclick="abrirModal()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                    <i class="fas fa-plus"></i> Nuevo Atleta
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tarjeta overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-white/5">
                <h3 class="text-white font-semibold">Listado General</h3>
                <span id="totalAtletas" class="text-xs bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full border border-indigo-500/20">0 Registrados</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1c1a3a] text-gray-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="p-4">Atleta</th>
                            <th class="p-4">Cédula</th>
                            <th class="p-4">Categoría</th>
                            <th class="p-4">FEVEDA</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800" id="listaAtletas">
                        <tr>
                            <td colspan="6" class="text-center p-12 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-3xl mb-3 text-indigo-500"></i>
                                <span class="text-xs uppercase tracking-wider block">Sincronizando datos...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalAtleta" class="fixed inset-0 bg-[#0f0d23]/90 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-4xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl scale-95 opacity-0 transition-all duration-200">
            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg text-white"><i class="fas fa-swimmer"></i></div>
                    <h2 class="text-xl font-bold text-white" id="modalTitulo">Registrar Atleta</h2>
                </div>
                <button onclick="cerrarModal()" class="text-gray-500 hover:text-white transition-colors"><i class="fas fa-times text-2xl"></i></button>
            </div>

            <form id="formAtleta" enctype="multipart/form-data">
                <input type="hidden" name="id_atleta" id="id_atleta">

                <div class="flex border-b border-gray-800 mb-6">
                    <button type="button" onclick="cambiarTab('personal')" class="tab-btn active" data-tab="personal">
                        <i class="fas fa-user mr-2"></i>Datos Personales
                    </button>
                    <button type="button" onclick="cambiarTab('medico')" class="tab-btn" data-tab="medico">
                        <i class="fas fa-heartbeat mr-2"></i>Datos Médicos
                    </button>
                    <button type="button" onclick="cambiarTab('federativo')" class="tab-btn" data-tab="federativo">
                        <i class="fas fa-trophy mr-2"></i>Datos Federativos
                    </button>
                </div>

                <div id="tab-personal" class="tab-content active">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Cédula de Identidad</label>
                            <input type="text" name="cedula" id="cedula" placeholder="V-12345678"
                                   data-validar="requerido|cedula" data-nombre="Cédula" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Nombres</label>
                            <input type="text" name="nombres" id="nombres"
                                   data-validar="requerido|letras" data-nombre="Nombres" data-min="2" data-max="100" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos"
                                   data-validar="requerido|letras" data-nombre="Apellidos" data-min="2" data-max="100" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                   data-validar="requerido" data-nombre="Fecha de nacimiento" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Sexo</label>
                            <select name="sexo" id="sexo"
                                    data-validar="requerido" data-nombre="Sexo" class="input-dark w-full p-3 rounded-xl">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Estado</label>
                            <select name="estado" id="estado" class="input-dark w-full p-3 rounded-xl">
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Retirado">Retirado</option>
                                <option value="Transferido">Transferido</option>
                            </select>
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Dirección</label>
                            <input type="text" name="direccion" id="direccion" placeholder="Dirección de residencia"
                                   data-validar="requerido|texto" data-nombre="Dirección" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" placeholder="0412-1234567"
                                   data-validar="requerido|telefono" data-nombre="Teléfono" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Correo Electrónico</label>
                            <input type="email" name="correo" id="correo" placeholder="correo@ejemplo.com"
                                   data-validar="requerido|correo" data-nombre="Correo electrónico" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Fecha Registro Club</label>
                            <input type="date" name="fecha_registro_club" id="fecha_registro_club"
                                   data-validar="requerido" data-nombre="Fecha registro club" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Fotografía</label>
                            <div class="flex items-center gap-3">
                                <div id="fotoPreview" class="w-16 h-16 rounded-full bg-[#0f0d23] border-2 border-gray-800 overflow-hidden flex items-center justify-center shrink-0">
                                    <i class="fas fa-camera text-gray-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png"
                                           class="text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/10 file:text-indigo-400 hover:file:bg-indigo-500/20 w-full">
                                    <p class="text-[10px] text-gray-600 mt-1">JPG/PNG, máx 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-medico" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Grupo Sanguíneo</label>
                            <select name="grupo_sanguineo" id="grupo_sanguineo"
                                    data-validar="requerido" data-nombre="Grupo sanguíneo" class="input-dark w-full p-3 rounded-xl">
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
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Seguro Médico</label>
                            <input type="text" name="seguro_medico" id="seguro_medico" placeholder="Nombre del seguro"
                                   data-validar="requerido|texto" data-nombre="Seguro médico" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Alergias Conocidas</label>
                            <textarea name="alergias" id="alergias" rows="2" placeholder="Describa las alergias conocidas..."
                                      data-validar="requerido|texto" data-nombre="Alergias" class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Condiciones Médicas Preexistentes</label>
                            <textarea name="condiciones_previas" id="condiciones_previas" rows="2" placeholder="Asma, lesiones crónicas, etc..."
                                      data-validar="requerido|texto" data-nombre="Condiciones médicas" class="input-dark w-full p-3 rounded-xl resize-none"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 p-4 rounded-xl bg-black/20 border border-white/5">
                        <p class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest mb-4"><i class="fas fa-phone-alt mr-2"></i>Contacto de Emergencia</p>
                        <div class="grid grid-cols-3 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Nombre</label>
                                <input type="text" name="contacto_emergencia_nombre" id="contacto_emergencia_nombre" placeholder="Nombre completo"
                                       data-validar="requerido|letras" data-nombre="Contacto emergencia" class="input-dark w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Teléfono</label>
                                <input type="text" name="contacto_emergencia_telefono" id="contacto_emergencia_telefono" placeholder="0412-1234567"
                                       data-validar="requerido|telefono" data-nombre="Teléfono emergencia" class="input-dark w-full p-3 rounded-xl">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Parentesco</label>
                                <select name="contacto_emergencia_parentesco" id="contacto_emergencia_parentesco"
                                        data-validar="requerido" data-nombre="Parentesco" class="input-dark w-full p-3 rounded-xl">
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

                <div id="tab-federativo" class="tab-content">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Número de Registro FEVEDA</label>
                            <input type="text" name="numero_feveda" id="numero_feveda" placeholder="Ej: FED-00123"
                                   data-validar="requerido|texto" data-nombre="Número FEVEDA" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Club de Procedencia</label>
                            <input type="text" name="club_procedencia" id="club_procedencia" placeholder="Club anterior"
                                   data-validar="requerido|texto" data-nombre="Club procedencia" class="input-dark w-full p-3 rounded-xl">
                        </div>
                        <div class="space-y-2 col-span-2">
                            <label class="text-[10px] text-indigo-400 uppercase font-bold tracking-widest">Categoría Deportiva</label>
                            <select name="id_categoria" id="id_categoria"
                                    data-validar="requerido" data-nombre="Categoría deportiva" class="input-dark w-full p-3 rounded-xl">
                                <option value="">Seleccione una categoría...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="cerrarModal()" class="flex-1 bg-gray-800 text-gray-400 py-4 rounded-xl font-bold transition-all hover:bg-gray-700">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="flex-[2] bg-indigo-600 py-4 rounded-xl font-bold text-white shadow-lg shadow-indigo-500/20 active:scale-95 transition-all hover:bg-indigo-500">GUARDAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[90vh] overflow-y-auto scale-95 opacity-0 transition-all duration-200">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-600/10 rounded-full blur-3xl"></div>
            <button onclick="cerrarModalVer()" class="absolute top-6 right-6 text-gray-500 hover:text-white hover:rotate-90 transition-all duration-300 z-10">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div id="detalleContenido" class="relative p-8"></div>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        const PERMISOS_ATLETA = {
            crear: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'crear') ? 'true' : 'false'; ?>,
        };
    </script>
    <script src="assets/js/atleta.js"></script>
</body>
</html>
