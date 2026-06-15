<?php
$tituloPagina = 'Panel de Inicio';
$iconoPagina = 'fa-home';
$headExtra = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
$styleExtra = '<style>.gradiente-boton { background: linear-gradient(90deg, #00d2ff 0%, #3a7bd5 100%); }</style>';
include RAIZ . 'vista/complementos/layout.php';
?>

        <div class="grid grid-cols-12 gap-6 mb-8">
            <div class="col-span-12 md:col-span-4 tarjeta">
                <h3 class="text-sm font-semibold mb-2 text-gray-400">Calificaciones de clientes</h3>
                <div class="flex items-end gap-2 mb-4">
                    <span class="text-4xl font-bold text-white">4.0</span>
                    <span class="text-yellow-400 text-sm mb-1">★★★★☆</span>
                </div>
                <div class="h-32">
                    <canvas id="graficaRating"></canvas>
                </div>
            </div>

            <div class="col-span-12 md:col-span-4 tarjeta">
                <h3 class="text-sm font-semibold mb-4 text-gray-400">Ventas Mensuales</h3>
                <div class="h-40">
                    <canvas id="graficaVentas"></canvas>
                </div>
            </div>

            <div class="col-span-12 md:col-span-4 flex flex-col gap-4">
                <div class="tarjeta flex justify-between items-center p-4">
                    <div>
                        <p class="text-xs text-gray-500">Sesiones Totales</p>
                        <h4 class="text-2xl font-bold text-white">2,845</h4>
                    </div>
                    <div class="text-green-500 bg-green-500/10 p-2 rounded-lg"><i class="fas fa-users"></i></div>
                </div>
                <div class="tarjeta flex justify-between items-center p-4 border-l-4 border-red-500">
                    <div>
                        <p class="text-xs text-gray-500">Órdenes Nuevas</p>
                        <h4 class="text-2xl font-bold text-white">$1,286</h4>
                        <span class="text-red-500 text-[10px]">-13.2% vs ayer</span>
                    </div>
                    <i class="fas fa-shopping-bag text-gray-700 text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 lg:col-span-8 tarjeta">
                <h3 class="font-bold text-white mb-6">Atletas destacados en el mes</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="text-xs text-gray-500 border-b border-gray-800">
                            <tr>
                                <th class="pb-4 uppercase">Nombre</th>
                                <th class="pb-4 uppercase text-center">Rendimiento</th>
                                <th class="pb-4 uppercase text-right">Edad</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-gray-800/50 hover:bg-white/5 transition">
                                <td class="py-4 flex items-center gap-3">
                                    <div class="bg-indigo-500/20 p-2 rounded text-indigo-400"><i class="fas fa-swimmer text-xl"></i></div>
                                    <div>Jesus <br><small class="text-gray-500 italic">Hernandez</small></div>
                                </td>
                                <td class="text-center">12.4k <span class="text-green-500 ml-2">↑</span></td>
                                <td class="text-right font-bold text-white">19</td>
                            </tr>
                            <tr class="border-b border-gray-800/50 hover:bg-white/5 transition">
                                <td class="py-4 flex items-center gap-3">
                                    <div class="bg-orange-500/20 p-2 rounded text-orange-400"><i class="fas fa-swimmer text-xl"></i></div>
                                    <div>Maikol <br><small class="text-gray-500 italic">Parra</small></div>
                                </td>
                                <td class="text-center">8.2k <span class="text-red-500 ml-2">↓</span></td>
                                <td class="text-right font-bold text-white">21</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 tarjeta flex flex-col items-center justify-center">
                <h3 class="text-sm font-semibold mb-6 text-gray-400">Objetivo de Ventas</h3>
                <div class="relative w-48 h-48">
                    <canvas id="graficaProgreso"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold text-white">75%</span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-widest">Logrado</span>
                    </div>
                </div>
            </div>
        </div>

        <button class="fixed bottom-8 right-8 gradiente-boton px-8 py-4 rounded-2xl font-bold text-white shadow-2xl hover:scale-110 transition duration-300">
            Generar Reporte
        </button>

    <script>
        new Chart(document.getElementById('graficaRating'), {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    data: [3.5, 3.8, 3.2, 4.0, 3.7, 4.0],
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    pointRadius: 0
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
        });

        new Chart(document.getElementById('graficaVentas'), {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    data: [45, 70, 55, 90, 65, 80],
                    backgroundColor: '#f87171',
                    borderRadius: 8,
                    barThickness: 12
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { display: false } } }
        });

        new Chart(document.getElementById('graficaProgreso'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [75, 25],
                    backgroundColor: ['#10b981', '#252345'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '85%', plugins: { tooltip: { enabled: false } } }
        });
    </script>

<?php include RAIZ . 'vista/complementos/layout_cierre.php'; ?>
