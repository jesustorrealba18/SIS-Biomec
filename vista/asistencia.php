<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Control de Asistencia | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 20px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; outline: none; }
        /* Ajustes para forzar a la librería QR a verse bien en modo oscuro */
        #visorCamara { width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #4f46e5; }
        #visorCamara video { object-fit: cover; }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-4 sm:p-8 overflow-y-auto w-full">
        
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-white tracking-wide flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-indigo-500"></i> Control de Asistencia
                </h1>
                <p class="text-sm text-gray-400 mt-1">Escaneo de código QR y validación manual por sesión.</p>
            </div>
            
            <div class="bg-[#161430] p-3 rounded-xl border border-indigo-500/30 flex items-center gap-3 w-full md:w-auto shadow-[0_0_15px_rgba(79,70,229,0.1)]">
                <i class="fas fa-stopwatch text-indigo-400"></i>
                <select id="selectSesion" class="input-dark py-2 px-3 rounded-lg text-sm font-bold text-white w-full md:w-64 cursor-pointer">
                    <option value="">Seleccione una sesión activa...</option>
                    <option value="1">Sesión Matutina - 08:00 AM (Fuerza)</option>
                    <option value="2">Sesión Vespertina - 03:00 PM (Resistencia)</option>
                </select>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <div class="xl:col-span-1 space-y-6">
                
                <div class="tarjeta p-6 text-center shadow-lg relative overflow-hidden">
                    <h2 class="text-xs uppercase font-bold text-indigo-400 tracking-widest mb-4">
                        <i class="fas fa-camera retro mr-2"></i> Escáner de Carnet
                    </h2>
                    
                    <div id="visorCamara" class="bg-black/50 aspect-square flex items-center justify-center mb-4">
                        <span class="text-gray-600 text-sm"><i class="fas fa-video-slash text-3xl mb-2 block"></i> Cámara en espera</span>
                    </div>

                    <button id="btnActivarCamara" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-indigo-500/20 uppercase text-xs tracking-widest">
                        Activar Cámara
                    </button>
                    <p id="txtScanEstado" class="text-[10px] text-gray-500 mt-3 font-mono">Esperando lectura...</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="tarjeta p-4 border-l-4 border-l-emerald-500 text-center">
                        <p class="text-[10px] uppercase text-gray-500 font-bold mb-1">Presentes</p>
                        <p id="statPresentes" class="text-3xl font-black text-emerald-400">0</p>
                    </div>
                    <div class="tarjeta p-4 border-l-4 border-l-red-500 text-center">
                        <p class="text-[10px] uppercase text-gray-500 font-bold mb-1">Ausentes / Permiso</p>
                        <p id="statAusentes" class="text-3xl font-black text-red-400">0</p>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 tarjeta p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xs uppercase font-bold text-gray-400 tracking-widest">
                        <i class="fas fa-users mr-2"></i> Listado de Convocados
                    </h2>
                    <span class="bg-indigo-500/10 text-indigo-400 px-3 py-1 rounded-full text-xs font-bold border border-indigo-500/20">
                        Total: <span id="statTotal">0</span>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-gray-500 border-b border-[#252345]">
                                <th class="pb-3 pl-2">Atleta</th>
                                <th class="pb-3">Categoría</th>
                                <th class="pb-3 text-center">Estado</th>
                                <th class="pb-3 text-right pr-2">Acción Manual</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAtletas" class="text-sm">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-600 italic">Seleccione una sesión para cargar la lista.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

   <!--  <audio id="beepSuccess" src="assets/audio/beep-success.mp3" preload="auto"></audio>
    <audio id="beepError" src="assets/audio/beep-error.mp3" preload="auto"></audio>
 -->
    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/asistencia.js"></script>
</body>
</html>