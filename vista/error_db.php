<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Error de conexión | SGRD</title>
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
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .anim-up { animation: fadeIn 0.6s ease-out forwards; }
        .pulse-icon { animation: pulse-slow 2s ease-in-out infinite; }
        .glass-card {
            backdrop-filter: blur(12px);
            border-radius: 24px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-gray-100 dark:bg-[#0f0d23] text-gray-800 dark:text-gray-300 transition-colors duration-300">
    <div class="glass-card p-10 w-full max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.5)] anim-up bg-white/90 dark:bg-[#161430]/90 border border-gray-200 dark:border-[#252345]">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-500/10 dark:bg-red-500/20 rounded-full mb-6 pulse-icon">
                <i class="fas fa-database text-red-500 dark:text-red-400 text-3xl"></i>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">
                <?php echo $titulo ?? 'Servicio no disponible'; ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                <?php echo $mensaje ?? 'El sistema no pudo establecer conexión con la base de datos. Por favor, intente nuevamente en unos momentos.'; ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="javascript:void(0)" onclick="location.reload()"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98] bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-rotate-right"></i>
                    Reintentar
                </a>
                <a href="?p=inicio"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-white/5 border border-gray-300 dark:border-white/10 hover:bg-gray-300 dark:hover:bg-white/10 transition">
                    <i class="fas fa-home"></i>
                    Ir al inicio
                </a>
            </div>

            <?php if (!empty($codigo)): ?>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-8">
                    Código de referencia: <?php echo htmlspecialchars($codigo); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
