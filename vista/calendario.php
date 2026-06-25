<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Calendario General | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        #calendario-container { padding: 1.5rem; }
        .fc { --fc-border-color: #252345; --fc-button-bg-color: #1e1b4b; --fc-button-border-color: #3730a3; --fc-button-text-color: #c7d2fe; --fc-button-hover-bg-color: #312e81; --fc-button-hover-border-color: #4338ca; --fc-button-active-bg-color: #4338ca; --fc-button-active-border-color: #6366f1; --fc-page-bg-color: transparent; --fc-neutral-bg-color: #111026; --fc-list-event-hover-bg-color: #1e1b4b; --fc-today-bg-color: #1e1b4b44; --fc-event-border-color: transparent; --fc-button-icon-color: #c7d2fe; --fc-button-icon-hover-color: #e0e7ff; --fc-button-icon-active-color: #fff; }
        .fc .fc-toolbar-title { color: #fff; font-size: 1.25rem; font-weight: 700; }
        .fc .fc-col-header-cell-cushion { color: #6b7280; text-transform: uppercase; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; }
        .fc .fc-daygrid-day-number { color: #9ca3af; text-decoration: none; }
        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { background: #4338ca; color: #fff; border-radius: 9999px; padding: 2px 6px; }
        .fc .fc-event { border-radius: 6px; padding: 2px 6px; font-size: 0.75rem; border: none; cursor: pointer; }
        .fc .fc-event:hover { opacity: 0.85; filter: brightness(1.1); }
        .fc .fc-daygrid-more-link { color: #818cf8; font-weight: 600; font-size: 0.75rem; }
        .fc .fc-scrollgrid { border-color: #252345; }
        .fc th, .fc td { border-color: #252345; }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">

        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-calendar text-indigo-500"></i> Calendario General
            </h1>

            <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                <div class="text-right mr-2">
                    <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                    <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                        Cerrar Sesion <i class="fas fa-right-from-bracket ml-1"></i>
                    </a>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff"
                     class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
            </div>
        </header>

        <div class="tarjeta overflow-hidden">
            <div id="calendario-container"></div>
        </div>

        <div class="mt-6 tarjeta p-4">
            <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-3">Leyenda por Tipo de Evento</p>
            <div class="flex flex-wrap gap-4 text-sm">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#3b82f6]"></span> Regional</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#10b981]"></span> Nacional</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f59e0b]"></span> Internacional</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#f97316]"></span> Selectivo</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-[#6b7280]"></span> Control</span>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendario-container');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                height: 'auto',
                firstDay: 1,
                eventSources: [
                    {
                        url: 'index.php?p=eventos&accion=calendario',
                        color: '#6366f1'
                    }
                ],
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault();
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
