<?php if (!isset($tituloPagina)) $tituloPagina = 'SGRD'; ?>
<?php if (!isset($iconoPagina)) $iconoPagina = 'fa-id-card'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?> | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <?php if (isset($headExtra)) echo $headExtra; ?>
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
        .menu-transition { transition: transform 0.3s ease-in-out; }
    </style>
    <?php if (isset($styleExtra)) echo $styleExtra; ?>
</head>
<body class="bg-[#0f0d23]">

    <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

    <div class="flex flex-col lg:flex-row min-h-screen">

        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-[#0f0d23] border-r border-[#252345] z-40 transform -translate-x-full menu-transition lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:flex-shrink-0 flex flex-col overflow-hidden">
            <div class="p-4 flex justify-between items-center border-b border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-400 hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include RAIZ . 'vista/complementos/menu.php'; ?>
        </aside>

        <main class="flex-1 flex flex-col min-h-screen">

            <?php include RAIZ . 'vista/complementos/header.php'; ?>

            <div class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
