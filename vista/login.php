<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: radial-gradient(circle at top right, #1e1b4b, #0f0d23); 
            color: #a0a0c0; 
            font-family: 'Inter', sans-serif; 
        }
        .glass-card { 
            background: rgba(22, 20, 48, 0.7); 
            backdrop-filter: blur(12px);
            border-radius: 24px; 
            border: 1px solid rgba(255, 255, 255, 0.05); 
        }
        .input-dark {
            background: rgba(15, 13, 35, 0.8);
            border: 1px solid #252345;
            transition: all 0.3s ease;
        }
        .input-dark:focus {
            border-color: #3a7bd5;
            box-shadow: 0 0 15px rgba(58, 123, 213, 0.2);
        }
        .gradiente-boton { 
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); 
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim-up { animation: fadeIn 0.6s ease-out forwards; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="glass-card p-10 w-full max-w-md shadow-[0_20px_50px_rgba(0,0,0,0.5)] anim-up">
        
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-indigo-600 to-blue-400 rounded-2xl mb-4 shadow-lg">
                <i class="fas fa-swimmer text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-extrabold text-white tracking-tight">SGRD</h1>
            <p class="text-sm text-gray-400 mt-2 uppercase tracking-[0.2em]">Acceso al Sistema</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-500/10 border-l-4 border-red-500 text-red-400 text-sm p-4 rounded-r-xl mb-6 flex items-center">
                <i class="fas fa-circle-exclamation mr-3 text-lg"></i> 
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="?p=login" method="POST" class="space-y-6">
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Usuario</label>
                <div class="relative group mt-2">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 group-focus-within:text-blue-400">
                        <i class="fas fa-user text-sm"></i>
                    </span>
                    <input type="text" name="usuario" required placeholder="jesus"
                        class="input-dark w-full rounded-2xl py-4 pl-12 pr-4 text-white focus:outline-none">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Contraseña</label>
                <div class="relative group mt-2">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 group-focus-within:text-blue-400">
                        <i class="fas fa-key text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                        class="input-dark w-full rounded-2xl py-4 pl-12 pr-12 text-white focus:outline-none">
                    <button type="button" onclick="togglePass()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500">
                        <i class="fas fa-eye" id="eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full gradiente-boton py-4 rounded-2xl font-bold text-white text-lg hover:scale-[1.01] transition active:scale-[0.98]">
                INGRESAR
            </button>
        </form>
    </div>

    <script>
        function togglePass() {
            const p = document.getElementById('password');
            const i = document.getElementById('eye');
            p.type = p.type === 'password' ? 'text' : 'password';
            i.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>