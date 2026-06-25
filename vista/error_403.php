<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Acceso denegado | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: radial-gradient(circle at top right, #1e1b4b, #0f0d23);
            color: #a0a0c0;
            font-family: 'Inter', sans-serif;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        .anim-up { animation: fadeIn 0.6s ease-out forwards; }
        .shake-icon { animation: shake 2s ease-in-out infinite; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="glass-card p-10 w-full max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.5)] anim-up"
         style="background: rgba(22, 20, 48, 0.7); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.05);">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-yellow-500/10 rounded-full mb-6 shake-icon">
                <i class="fas fa-lock text-yellow-400 text-3xl"></i>
            </div>

            <h1 class="text-3xl font-extrabold text-white tracking-tight mb-3">
                <?php echo $titulo ?? 'Acceso denegado'; ?>
            </h1>
            <p class="text-gray-400 mb-8 leading-relaxed">
                <?php echo $mensaje ?? 'No tienes permisos para acceder a esta sección. Si crees que es un error, contacta al administrador.'; ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="?p=inicio"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]"
                   style="background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);">
                    <i class="fas fa-home"></i>
                    Ir al inicio
                </a>
                <a href="javascript:void(0)" onclick="history.back()"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-gray-300 bg-white/5 border border-white/10 hover:bg-white/10 transition">
                    <i class="fas fa-arrow-left"></i>
                    Volver atrás
                </a>
            </div>
        </div>
    </div>
</body>
</html>
