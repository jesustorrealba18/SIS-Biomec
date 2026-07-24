<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Página no encontrada | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .anim-up { animation: fadeIn 0.6s ease-out forwards; }
        .float-icon { animation: float 3s ease-in-out infinite; }
        .glass-card {
            backdrop-filter: blur(12px);
            border-radius: 24px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-gray-100 dark:bg-[#0f0d23] text-gray-800 dark:text-gray-300 transition-colors duration-300">
    <div class="glass-card p-10 w-full max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.5)] anim-up bg-white/90 dark:bg-[#161430]/90 border border-gray-200 dark:border-[#252345] relative overflow-hidden">
        
        <!-- Fondo decorativo "404" -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none">
            <span class="text-9xl font-black text-gray-200/30 dark:text-white/5">404</span>
        </div>

        <!-- Contenido principal (por encima del fondo) -->
        <div class="relative z-10 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-500/10 dark:bg-orange-500/20 rounded-full mb-6 float-icon">
                <i class="fas fa-magnifying-glass text-orange-500 dark:text-orange-400 text-3xl"></i>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">
                <?php echo $titulo ?? 'Página no encontrada'; ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                <?php echo $mensaje ?? 'La sección que buscas no existe o ha sido removida.'; ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="?p=inicio"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98] bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-home"></i>
                    Ir al inicio
                </a>
                <a href="javascript:void(0)" onclick="history.back()"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-white/5 border border-gray-300 dark:border-white/10 hover:bg-gray-300 dark:hover:bg-white/10 transition">
                    <i class="fas fa-arrow-left"></i>
                    Volver atrás
                </a>
            </div>
        </div>
    </div>
</body>
</html>
