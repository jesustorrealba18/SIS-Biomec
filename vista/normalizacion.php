<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Normalización de Tiempos | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; width: 100%; padding: 0.75rem; border-radius: 0.5rem; outline: none; }
        .input-dark:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79,70,229,0.2); }
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    </style>
</head>
<body class="antialiased">

    <div class="p-6 max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Normalización de Tiempos</h1>
                <p class="text-sm text-gray-400 mt-1">Conversión algorítmica entre Piscina Corta y Larga (IA)</p>
            </div>
            <button onclick="abrirModalFormulario()" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold shadow-[0_0_15px_rgba(79,70,229,0.3)] transition-all duration-300 uppercase text-xs tracking-wider flex items-center justify-center gap-2">
                NUEVA CONVERSIÓN <i class="fas fa-plus"></i>
            </button>
        </div>

        <div class="tarjeta overflow-hidden shadow-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tablaNormalizacion">
                    <thead>
                        <tr class="bg-[#0f0d23]/50 border-b border-[#252345] text-xs uppercase tracking-wider text-gray-400">
                            <th class="p-4 font-semibold">Atleta</th>
                            <th class="p-4 font-semibold">Prueba</th>
                            <th class="p-4 font-semibold">Origen</th>
                            <th class="p-4 font-semibold">T. Original (s)</th>
                            <th class="p-4 font-semibold text-indigo-400">T. Normalizado</th>
                            <th class="p-4 font-semibold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaNormalizacion" class="divide-y divide-[#252345] text-sm">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalFormulario" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity duration-300 opacity-0">
        <div class="relative bg-[#161430] border border-[#252345] w-full max-w-lg rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] transform scale-95 transition-transform duration-300" id="modalFormularioContent">
            
            <button type="button" onclick="cerrarModalFormulario()" class="absolute top-6 right-6 text-gray-400 hover:text-white hover:rotate-90 transition-all duration-300 z-[100] cursor-pointer">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="p-8">
                <h2 class="text-2xl font-bold text-white mb-6 border-b border-[#252345] pb-4" id="tituloModal">Registrar Tiempo</h2>
                
                <form id="formularioNormalizacion" autocomplete="off">
                    <input type="hidden" id="accion" name="accion" value="registrar">
                    <input type="hidden" id="id_normalizacion" name="id_normalizacion" value="">

                    <div class="space-y-5">
                        <div>
                            <label for="id_atleta" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Atleta</label>
                            <select id="id_atleta" name="id_atleta" class="input-dark" required>
                                <option value="">Seleccione un atleta...</option>
                                </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="estilo" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estilo</label>
                                <select id="estilo" name="estilo" class="input-dark" required>
                                    <option value="Libre">Libre</option>
                                    <option value="Espalda">Espalda</option>
                                    <option value="Pecho">Pecho</option>
                                    <option value="Mariposa">Mariposa</option>
                                    <option value="Combinado">Combinado</option>
                                </select>
                            </div>
                            <div>
                                <label for="distancia_m" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Distancia (m)</label>
                                <select id="distancia_m" name="distancia_m" class="input-dark" required>
                                    <option value="50">50m</option>
                                    <option value="100">100m</option>
                                    <option value="200">200m</option>
                                    <option value="400">400m</option>
                                    <option value="800">800m</option>
                                    <option value="1500">1500m</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="tipo_piscina_origen" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Piscina Origen</label>
                                <select id="tipo_piscina_origen" name="tipo_piscina_origen" class="input-dark" required>
                                    <option value="25m">Corta (25m)</option>
                                    <option value="50m">Larga (50m)</option>
                                </select>
                            </div>
                            <div>
                                <label for="tiempo_original_seg" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Tiempo (Segundos)</label>
                                <input type="number" step="0.01" id="tiempo_original_seg" name="tiempo_original_seg" class="input-dark" placeholder="Ej. 24.50" required>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-3.5 rounded-xl font-bold shadow-[0_0_15px_rgba(79,70,229,0.3)] transition-all duration-300 uppercase text-xs tracking-wider">
                                GUARDAR Y CONVERTIR <i class="fas fa-bolt ml-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalVer" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50 transition-opacity duration-300 opacity-0">
        <div class="relative bg-[#111026] border border-white/10 w-full max-w-2xl rounded-[2rem] overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.15)] max-h-[92vh] overflow-y-auto transform scale-95 transition-transform duration-300" id="modalVerContent">
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
        // Permisos del módulo (se pueden ampliar según el controlador)
        const PERMISOS_MODULO = {
            registrar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'registrar') ? 'true' : 'false'; ?>,
            editar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'editar') ? 'true' : 'false'; ?>,
            eliminar: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'eliminar') ? 'true' : 'false'; ?>,
            anular: <?php echo \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('normalizacion', 'anular') ? 'true' : 'false'; ?>
        };
    </script>
    <script src="assets/js/normalizacion.js"></script>

</body>
</html>