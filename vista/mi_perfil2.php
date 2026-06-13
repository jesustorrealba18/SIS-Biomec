<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil Deportivo | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs@master/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 24px; }
        /* .estado-badge { padding: 4px 12px; border-radius: 9999px; font-weight: 700; font-size: 0.75rem; tracking-wide; } */
        .estado-Activo { background-color: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .estado-Inactivo { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
         ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-4 sm:p-8 overflow-y-auto max-w-7xl mx-auto w-full">

        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-white tracking-wide flex items-center gap-3">
                    <i class="fas fa-user-circle text-indigo-500"></i> Mi Perfil Biométrico
                </h1>
                <p class="text-sm text-gray-400 mt-1">Ficha técnica oficial del atleta y pase de acceso digital.</p>
            </div>
            
            <div class="flex items-center gap-3 bg-[#161430] py-2 px-4 rounded-xl border border-[#252345]">
                <span class="text-xs text-gray-400 font-medium">Rol actual:</span>
                <span class="text-xs text-indigo-400 font-bold uppercase tracking-wider">Atleta</span>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="tarjeta p-6 sm:p-8 relative overflow-hidden shadow-xl shadow-black/30">
                    <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-6">
                        <div id="perfilFotoContenedor">
                            </div>
                        
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <h2 id="lblNombreCompleto" class="text-2xl font-black text-white uppercase tracking-tight">Cargando Nombre...</h2>
                                <span id="badgeEstado" class="estado-badge inline-block self-center sm:self-start">---</span>
                            </div>
                            <p id="lblCedula" class="text-indigo-400 font-mono text-sm tracking-widest">V-00000000</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-white/5">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-bold">Edad</p>
                                    <p id="lblEdad" class="text-white font-medium text-sm">—</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-bold">Género</p>
                                    <p id="lblSexo" class="text-white font-medium text-sm">—</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase font-bold">Categoría</p>
                                    <p id="lblCategoria" class="text-emerald-400 font-bold text-sm">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="tarjeta p-6 bg-[#161430]/60">
                        <h3 class="text-xs uppercase text-indigo-400 font-black tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-envelope-open-text"></i> Información de Contacto
                        </h3>
                        <div class="space-y-3 font-medium text-sm">
                            <div>
                                <p class="text-[9px] uppercase text-gray-500">Teléfono Celular</p>
                                <p id="lblTelefono" class="text-white font-mono">—</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase text-gray-500">Correo Electrónico</p>
                                <p id="lblCorreo" class="text-white break-all text-xs sm:text-sm">—</p>
                            </div>
                        </div>
                    </div>

                    <div class="tarjeta p-6 bg-[#161430]/60">
                        <h3 class="text-xs uppercase text-purple-400 font-black tracking-widest mb-4 flex items-center gap-2">
                            <i class="fas fa-passport"></i> Registro Técnico
                        </h3>
                        <div class="space-y-3 font-medium text-sm">
                            <div>
                                <p class="text-[9px] uppercase text-gray-500">Ficha FEVEDA</p>
                                <p id="lblFeveda" class="text-purple-300 font-mono text-sm font-bold">—</p>
                            </div>
                            <div>
                                <p class="text-[9px] uppercase text-gray-500">Club de Procedencia</p>
                                <p id="lblClub" class="text-white">—</p>
                            </div>
                        </div>
                    </div>

<div id="tarjetaRepresentante" class="tarjeta p-6 bg-[#161430]/60 border-l-4 border-l-indigo-500 hidden sm:col-span-2">
        <h3 class="text-xs uppercase text-indigo-400 font-black tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-user-shield"></i> Representante Legal Asignado
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm font-medium">
            <div>
                <p class="text-[9px] uppercase text-gray-500">Nombre del Representante</p>
                <p id="lblRepNombre" class="text-white font-bold mt-0.5">—</p>
                <p id="lblRepCedula" class="text-gray-500 font-mono text-[10px] mt-0.5">V-00000000</p>
            </div>
            <div>
                <p class="text-[9px] uppercase text-gray-500">Parentesco / Vínculo</p>
                <p id="lblRepParentesco" class="text-indigo-300 mt-0.5 font-semibold">—</p>
            </div>
            <div>
                <p class="text-[9px] uppercase text-gray-500">Teléfono de Contacto</p>
                <p id="lblRepTelefono" class="text-emerald-400 font-mono mt-0.5 font-bold">—</p>
            </div>
        </div>
    </div>
                    


                </div>

                <div class="tarjeta p-6 border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-500/5 to-transparent shadow-md">
                    <h3 class="text-xs uppercase text-emerald-400 font-black tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-notes-medical"></i> Hoja Médica y de Emergencia
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-black/20 p-3 rounded-xl border border-white/5">
                            <p class="text-[9px] uppercase text-gray-500">Grupo Sanguíneo</p>
                            <p id="lblSangre" class="text-white text-lg font-black tracking-tighter">—</p>
                        </div>
                        <div class="bg-black/20 p-3 rounded-xl border border-white/5 sm:col-span-2">
                            <p class="text-[9px] uppercase text-gray-500">Alergias Conocidas</p>
                            <p id="lblAlergias" class="text-gray-300 text-xs mt-0.5">—</p>
                        </div>
                    </div>
                    
                    <div id="boxEmergencia" class="bg-black/30 p-4 rounded-xl border border-white/5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                        <div>
                            <p class="text-[9px] uppercase text-amber-500 font-bold tracking-wider"><i class="fas fa-phone-alt"></i> En Caso de Emergencia</p>
                            <p id="lblContactoNombre" class="text-white text-sm mt-0.5">No registrado</p>
                        </div>
                        <p id="lblContactoTlf" class="text-amber-400 font-mono text-sm font-bold">—</p>
                    </div>
                </div>

            </div>

            <div class="w-full">
                <div class="tarjeta p-6 flex flex-col items-center justify-center border-t-4 border-t-indigo-500 text-center shadow-xl shadow-black/40 bg-gradient-to-b from-[#1b1937] to-[#161430] sticky top-4">
                    <span class="text-[10px] text-indigo-400 font-black uppercase tracking-widest mb-4 flex items-center gap-2 bg-indigo-500/10 py-1.5 px-4 rounded-full">
                        <i class="fas fa-qrcode text-xs"></i> Pase de Acceso Digital
                    </span>
                    
                    <p class="text-xs text-gray-400 mb-6 max-w-[200px]">Presenta este código en la taquilla o al entrenador para validar tu asistencia del día.</p>
                    
                    <div class="bg-white p-3 rounded-2xl shadow-[0_0_30px_rgba(99,102,241,0.2)] border border-indigo-500/20">
                        <div id="contenedorQR" class="w-[160px] h-[160px] flex items-center justify-center text-black text-xs">
                            <i class="fas fa-spinner animate-spin text-2xl text-indigo-500"></i>
                        </div>
                    </div>
                    
                    <div class="mt-4 bg-black/40 border border-white/5 px-4 py-1.5 rounded-xl">
                        <span id="txtTokenVisible" class="text-[10px] text-gray-500 font-mono select-all tracking-wider">Cargando token...</span>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/mi_perfil.js"></script>
</body>
</html>