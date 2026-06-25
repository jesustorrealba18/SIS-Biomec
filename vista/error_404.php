<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Página no encontrada | SGRD</title>
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
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .anim-up { animation: fadeIn 0.6s ease-out forwards; }
        .float-icon { animation: float 3s ease-in-out infinite; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="glass-card p-10 w-full max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.5)] anim-up"
         style="background: rgba(22, 20, 48, 0.7); backdrop-filter: blur(12px); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.05);">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-500/10 rounded-full mb-6 float-icon">
                <i class="fas fa-magnifying-glass text-orange-400 text-3xl"></i>
            </div>

            <p class="text-8xl font-black text-white/5 absolute">404</p>
            <h1 class="text-3xl font-extrabold text-white tracking-tight mb-3 relative">
                <?php echo $titulo ?? 'Página no encontrada'; ?>
            </h1>
            <p class="text-gray-400 mb-8 leading-relaxed">
                <?php echo $mensaje ?? 'La sección que buscas no existe o ha sido removida.'; ?>
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
