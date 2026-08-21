
        (function() {
            const contenedorLobby = document.getElementById('contenedorLobbyLive');
            let peticionEnCurso = false;

            async function actualizarLobby() {
                if (peticionEnCurso) return;
                peticionEnCurso = true;

                try {
                    const res = await fetch('index.php?p=live&accion=get_lobby');
                    if (!res.ok) throw new Error('Error de red');
                    
                    const data = await res.json();
                    
                    if (data.status === 'success') {
                        renderizarTarjetasLive(data.salas);
                    }
                } catch (error) {
                    console.error("Error obteniendo el Lobby Live:", error);
                } finally {
                    peticionEnCurso = false;
                }
            }

            function renderizarTarjetasLive(salas) {
                if (!salas || salas.length === 0) {
                    contenedorLobby.innerHTML = `
                        <div class="col-span-full p-8 text-center bg-gray-50 dark:bg-[#0f0d23]/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                            <i class="fas fa-tv text-4xl text-gray-400 dark:text-gray-600 mb-3"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No hay carreras en vivo en este momento.</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Los cronómetros activos aparecerán aquí automáticamente.</p>
                        </div>`;
                    return;
                }

                let html = '';
                salas.forEach(sala => {
                    // Determinar estado visual
                    let colorEstado = 'bg-amber-500';
                    let textoEstado = 'ESPERANDO';
                    let iconoEstado = 'fa-hourglass-half';
                    let animacion = 'animate-pulse';

                    if (sala.estado_carrera === 'iniciando') {
                        colorEstado = 'bg-amber-500'; textoEstado = 'EN SUS MARCAS'; animacion = 'animate-pulse';
                    } else if (sala.estado_carrera === 'en_curso') {
                        colorEstado = 'bg-emerald-500'; textoEstado = 'NADANDO'; iconoEstado = 'fa-swimmer'; animacion = '';
                    } else if (sala.estado_carrera === 'en_viraje') {
                        colorEstado = 'bg-blue-500'; textoEstado = 'EN VIRAJE'; iconoEstado = 'fa-undo'; animacion = 'animate-spin-slow';
                    } else if (sala.estado_carrera === 'finalizado') {
                        colorEstado = 'bg-purple-500'; textoEstado = 'FINALIZADO'; iconoEstado = 'fa-flag-checkered'; animacion = '';
                    }

                    html += `
                        <div class="group bg-white dark:bg-[#0f0d23] border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all shadow-sm hover:shadow-lg hover:shadow-indigo-500/10 flex flex-col justify-between h-full relative overflow-hidden">
                            <!-- Indicador Lateral -->
                            <div class="absolute left-0 top-0 bottom-0 w-1 ${colorEstado}"></div>
                            
                            <div class="pl-3 relative z-10">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="${colorEstado} text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase tracking-wider shadow-sm flex items-center gap-1 w-max">
                                            <i class="fas ${iconoEstado} ${animacion}"></i> ${textoEstado}
                                        </span>
                                    </div>
                                    <div class="text-[10px] font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                        ID: ${sala.id_atleta}
                                    </div>
                                </div>
                                
                                <h4 class="text-lg font-black text-gray-900 dark:text-white uppercase truncate pr-2" title="${sala.nombres} ${sala.apellidos}">
                                    ${sala.nombres} ${sala.apellidos}
                                </h4>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold tracking-wide mt-0.5">
                                    <i class="fas fa-water mr-1"></i> ${sala.distancia_total}m ${sala.estilo} (${sala.tipo_piscina})
                                </p>
                            </div>

                            <div class="pl-3 mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 relative z-10">
                                <a href="?p=live&id_atleta=${sala.id_atleta}" target="_blank" class="w-full block text-center py-2 bg-gray-100 hover:bg-indigo-600 text-gray-700 hover:text-white dark:bg-gray-800 dark:hover:bg-indigo-600 dark:text-gray-300 font-bold text-xs uppercase tracking-widest rounded-lg transition-colors duration-300">
                                    <i class="fas fa-vr-cardboard mr-1"></i> Ver Simulador 3D
                                </a>
                            </div>
                        </div>
                    `;
                });

                contenedorLobby.innerHTML = html;
            }

            // Iniciar el ciclo de refresco (cada 3 segundos)
            actualizarLobby();
            setInterval(actualizarLobby, 3000);
        })();
    