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

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5">
            <i class="fas fa-chart-pie w-5 text-center text-indigo-400 group-hover:text-white"></i> 
            <span>Analítica</span>
        </div>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sistemaExperto', 'ver')): ?>
        <a href="?p=sistemaExperto" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'sistemaExperto') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-brain w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'sistemaExperto') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Sistema Experto</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
       <a href="?p=entrenador" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'entrenador') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-tie w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'entrenador') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Entrenadores</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('drills', 'ver')): ?>
         <a href="?p=drills" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'drills') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-tie w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'drills') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Drills</span>
        </a>
        <?php endif; ?>
        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver')): ?>
<a href="?p=atleta" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'atleta') ? 'bg-white/10 text-white' : ''; ?>">
    <i class="fas fa-swimmer w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'atleta') ? 'text-white' : ''; ?>"></i> 
    <span class="font-medium">Atleta</span>
</a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('representantes', 'ver')): ?>
        <a href="?p=representante" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'representante') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'representante') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Representantes</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('grupo', 'ver')): ?>
        <a href="?p=grupo" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'grupo') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'sesiones') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Grupo</span>
        </a>
        <?php endif; ?>

 <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'ver')): ?>
        <a href="?p=sesiones" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'sesiones') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'sesiones') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Sesiones</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('carrriles', 'ver')): ?>
        <a href="?p=carriles" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'carriles') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'carriles') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Carriles</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('horario', 'ver')): ?>
        <a href="?p=horario" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'horario') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'horario') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Horario</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asignacion', 'ver')): ?>
        <a href="?p=asignacion" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'asignacion') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'asignacion') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Asignacion de Carriles</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'ver')): ?>
        <a href="?p=antropometria" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'antropometria') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-ruler-combined w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'antropometria') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Expediente Antropométrico</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'ver')): ?>
        <a href="?p=lesion" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'lesion') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-notes-medical w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'lesion') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Control de Lesiones</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('testFisico', 'ver')): ?>
        <a href="?p=testFisico" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'testFisico') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-heartbeat w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'testFisico') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Tests Fisicos</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'ver')): ?>
        <a href="?p=cargaBienestar" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'cargaBienestar') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-notes-medical w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'cargaBienestar') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Monitoreo de Carga y Bienestar</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'ver')): ?>
        <a href="?p=periodizacion" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'periodizacion') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-project-diagram w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'periodizacion') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Periodizacion ATR</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'ver')): ?>
        <a href="?p=temporadas" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'temporadas') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-calendar-check w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'temporadas') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Temporadas</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'ver')): ?>
        <a href="?p=marcas" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'marcas') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-stopwatch w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'marcas') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Marcas y Tiempos</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'ver')): ?>
        <a href="?p=eventos" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'eventos') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-calendar-alt w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'eventos') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Eventos y Metas</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'ver')): ?>
        <a href="?p=calendario" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'calendario') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-calendar-week w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'calendario') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Calendario</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asistencia', 'ver')): ?>
        <a href="?p=asistencia" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'asistencia') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-calendar-week w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'asistencia') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Asistencia</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('observacionesTecnicas', 'ver')): ?>
        <a href="?p=observacionesTecnicas" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'observacionesTecnicas') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-eye w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'observacionesTecnicas') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Observaciones Tecnicas</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('mi_perfil', 'ver')): ?>
        <a href="?p=mi_perfil" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'mi_perfil') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-user w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'mi_perfil') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Mi Perfil</span>
        </a>
        <?php endif; ?>


        <?php $tieneAdmin = \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'usuarios')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'roles')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'mantenimiento')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'bitacora');
        ?>
        <?php if ($tieneAdmin): ?>
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mt-8 mb-4">Administración</p>
        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'usuarios')): ?>
        <a href="?p=usuarios" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'usuarios') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-users-cog w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'usuarios') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Usuarios</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'roles')): ?>
        <a href="?p=roles" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'roles') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-shield-alt w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'roles') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Roles y Permisos</span>
        </a>
        <?php endif; ?>

        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'mantenimiento')): ?>
         <a href="?p=mantenimiento" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'mantenimiento') ? 'bg-white/10 text-white' : ''; ?>">
             <i class="fas fa-database w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'mantenimiento') ? 'text-white' : ''; ?>"></i> 
             <span class="font-medium">Mantenimiento</span>
        </a>
        <?php endif; ?>
        

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'bitacora')): ?>
         <a href="?p=bitacora" class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group hover:text-white hover:bg-white/5 <?php echo ($pagina == 'bitacora') ? 'bg-white/10 text-white' : ''; ?>">
            <i class="fas fa-book-open w-5 text-center text-indigo-400 group-hover:text-white <?php echo ($pagina == 'bitacora') ? 'text-white' : ''; ?>"></i> 
            <span class="font-medium">Bitácora</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>