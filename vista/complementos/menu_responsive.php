<aside class="sidebar flex flex-col p-6 overflow-y-auto w-full h-full">
    <!-- Este bloque del logo SOLO se muestra en escritorio (lg:flex) para no duplicar en móvil -->
    <div class="flex items-center gap-3 mb-10 hidden lg:flex">
        <div class="bg-indigo-600 p-2 rounded-lg text-white shadow-lg shadow-indigo-500/20">
            <i class="fas fa-swimmer text-xl"></i>
        </div>
        <span class="text-2xl font-black text-gray-900 dark:text-white italic tracking-tighter">SGRD</span>
    </div>
    
    <nav class="space-y-1">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-500 font-bold mb-4">Menú Principal</p>
        
        <a href="index.php?p=inicio" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'inicio') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-home w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'inicio') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i>
            <span>Inicio</span>
        </a>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
        <div class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                    text-gray-700 dark:text-gray-300 
                    hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white">
            <i class="fas fa-chart-pie w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"></i> 
            <span>Analítica</span>
        </div>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'gestionar')): ?>
       <a href="?p=entrenador" 
          class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                 text-gray-700 dark:text-gray-300 
                 hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                 <?php echo ($pagina == 'entrenador') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user-tie w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'entrenador') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Entrenadores</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('drills', 'ver')): ?>
         <a href="?p=drills" 
            class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                   text-gray-700 dark:text-gray-300 
                   hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                   <?php echo ($pagina == 'drills') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user-tie w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'drills') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Drills</span>
        </a>
        <?php endif; ?>
        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('atletas', 'ver')): ?>
        <a href="?p=atleta" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'atleta') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-swimmer w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'atleta') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Atleta</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('representantes', 'ver')): ?>
        <a href="?p=representante" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'representante') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'representante') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Representantes</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('grupo', 'ver')): ?>
        <a href="?p=grupo" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'grupo') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'grupo') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Grupo</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('sesiones', 'ver')): ?>
        <a href="?p=sesiones" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'sesiones') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user-shield w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'sesiones') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Sesiones</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('antropometria', 'ver')): ?>
        <a href="?p=antropometria" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'antropometria') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-ruler-combined w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'antropometria') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Expediente Antropométrico</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('lesiones', 'ver')): ?>
        <a href="?p=lesion" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'lesion') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-notes-medical w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'lesion') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Control de Lesiones</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('rpe', 'ver')): ?>
        <a href="?p=cargaBienestar" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'cargaBienestar') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-notes-medical w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'cargaBienestar') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Monitoreo de Carga y Bienestar</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('periodizacion', 'ver')): ?>
        <a href="?p=periodizacion" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'periodizacion') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-project-diagram w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'periodizacion') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Periodizacion ATR</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('temporadas', 'ver')): ?>
        <a href="?p=temporadas" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'temporadas') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-calendar-check w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'temporadas') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Temporadas</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('marcas', 'ver')): ?>
        <a href="?p=marcas" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'marcas') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-stopwatch w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'marcas') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Marcas y Tiempos</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'ver')): ?>
        <a href="?p=eventos" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'eventos') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-calendar-alt w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'eventos') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Eventos y Metas</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('eventos', 'ver')): ?>
        <a href="?p=calendario" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'calendario') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-calendar-week w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'calendario') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Calendario</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('asistencia', 'ver')): ?>
        <a href="?p=asistencia" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'asistencia') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-clipboard-check w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'asistencia') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Asistencia</span>
        </a>
        <?php endif; ?>

         <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('mi_perfil', 'ver')): ?>
        <a href="?p=mi_perfil" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'mi_perfil') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-user w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'mi_perfil') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Mi Perfil</span>
        </a>
        <?php endif; ?>


        <?php $tieneAdmin = \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'usuarios')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'roles')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'mantenimiento')
            || \GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'bitacora');
        ?>
        <?php if ($tieneAdmin): ?>
        <p class="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-500 font-bold mt-8 mb-4">Administración</p>
        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'usuarios')): ?>
        <a href="?p=usuarios" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'usuarios') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-users-cog w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'usuarios') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Usuarios</span>
        </a>
        <?php endif; ?>

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'roles')): ?>
        <a href="?p=roles" 
           class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                  text-gray-700 dark:text-gray-300 
                  hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                  <?php echo ($pagina == 'roles') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-shield-alt w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'roles') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Roles y Permisos</span>
        </a>
        <?php endif; ?>

        
        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'mantenimiento')): ?>
         <a href="?p=mantenimiento" 
            class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                   text-gray-700 dark:text-gray-300 
                   hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                   <?php echo ($pagina == 'mantenimiento') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
             <i class="fas fa-database w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                       <?php echo ($pagina == 'mantenimiento') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
             <span class="font-medium">Mantenimiento</span>
        </a>
        <?php endif; ?>
        

        <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('seguridad', 'bitacora')): ?>
         <a href="?p=bitacora" 
            class="flex items-center gap-3 p-3 rounded-xl transition cursor-pointer group 
                   text-gray-700 dark:text-gray-300 
                   hover:bg-gray-200 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white
                   <?php echo ($pagina == 'bitacora') ? 'bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : ''; ?>">
            <i class="fas fa-book-open w-5 text-center text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white 
                      <?php echo ($pagina == 'bitacora') ? 'text-indigo-600 dark:text-indigo-400' : ''; ?>"></i> 
            <span class="font-medium">Bitácora</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
    </nav>
</aside>