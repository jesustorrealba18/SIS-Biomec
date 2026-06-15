<?php
$tituloPagina = 'Calendario General';
$iconoPagina = 'fa-calendar';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script><style>#calendario-container { padding: 1.5rem; } .fc { --fc-border-color: #252345; --fc-button-bg-color: #1e1b4b; --fc-button-border-color: #3730a3; --fc-button-text-color: #c7d2fe; --fc-button-hover-bg-color: #312e81; --fc-button-hover-border-color: #4338ca; --fc-button-active-bg-color: #4338ca; --fc-button-active-border-color: #6366f1; --fc-page-bg-color: transparent; --fc-neutral-bg-color: #111026; --fc-list-event-hover-bg-color: #1e1b4b; --fc-today-bg-color: #1e1b4b44; --fc-event-border-color: transparent; --fc-button-icon-color: #c7d2fe; --fc-button-icon-hover-color: #e0e7ff; --fc-button-icon-active-color: #fff; } .fc .fc-toolbar-title { color: #fff; font-size: 1.25rem; font-weight: 700; } .fc .fc-col-header-cell-cushion { color: #6b7280; text-transform: uppercase; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; } .fc .fc-daygrid-day-number { color: #9ca3af; text-decoration: none; } .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { background: #4338ca; color: #fff; border-radius: 9999px; padding: 2px 6px; } .fc .fc-event { border-radius: 6px; padding: 2px 6px; font-size: 0.75rem; border: none; cursor: pointer; } .fc .fc-event:hover { opacity: 0.85; filter: brightness(1.1); } .fc .fc-daygrid-more-link { color: #818cf8; font-weight: 600; font-size: 0.75rem; } .fc .fc-scrollgrid { border-color: #252345; } .fc th, .fc td { border-color: #252345; }</style>';
include RAIZ . 'vista/complementos/layout.php';
?>

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

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>