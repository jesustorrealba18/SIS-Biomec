<aside class="sidebar hidden lg:flex flex-col p-6 overflow-y-auto max-h-screen">
    <div class="flex items-center gap-3 mb-10">
        <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-lg shadow-indigo-500/20">
            <i class="fas fa-swimmer text-xl"></i>
        </div>
        <span class="text-2xl font-black text-white italic tracking-tighter">SGRD</span>
    </div>
    
    <nav class="space-y-1">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-4">Menú Principal</p>
        
        <a href="index.php?p=inicio" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group <?php echo ($pagina == 'inicio') ? 'text-indigo-400 bg-indigo-500/10' : 'hover:text-white hover:bg-white/5'; ?>">
            <i class="fas fa-home w-5 text-center"></i> <span>Inicio</span>
        </a>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-chart-pie w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Analítica</span>
        </div>

       <a href="?p=entrenador" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'entrenador') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-tie w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'entrenador') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Entrenadores</span>
        </a>
        
<!-- Enlace al módulo de Atletas -->
<a href="?p=atleta" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'atleta') ? 'bg-white/10 text-white' : ''; ?>">
    <i class="fas fa-swimmer w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'atleta') ? 'text-white' : ''; ?>"></i> 
    <span class="font-medium">Atleta</span>
</a>

<!-- NUEVO: Enlace al módulo de Representantes -->
        <a href="?p=representante" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'representante') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'representante') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Representantes</span>
        </a>
        

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-address-book w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Miembros</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-layer-group w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <a href="vista/categorias.php"><span>Categorías</span></a>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-water w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Carriles y Horarios</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-clipboard-check w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Control de Asistencia</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-ruler-combined w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Expediente Antropométrico</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-dumbbell w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Planificación de Cargas</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-stopwatch w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Marcas y Tiempos</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-trophy w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Rankings FEVEDA</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-video w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Análisis Biomecánico</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-brain w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Diagnóstico Inteligente</span>
        </div>
        
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mt-8 mb-4">Administración</p>
        
        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-users-cog w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Usuarios</span>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-cogs w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Configuración</span>
        </div>
    </nav>
</aside>