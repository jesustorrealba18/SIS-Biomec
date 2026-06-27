// assets/js/asistencia.js

const API_ASISTENCIA = 'index.php?p=asistencia';
let html5QrCode; // Variable global para la cámara

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos; 
    try {
        const res = await fetch(`${API_ASISTENCIA}&accion=${accion}`, opciones);
        return await res.json();
    } catch (error) {
        UI.error('Error de Conexión', 'No se pudo comunicar con el servidor.');
        return null;
    }
}

const Asistencia = {
    idSesionActual: null,
    procesandoLectura: false,
    camaraActiva: false,

    init: () => {
        // NUEVO: Apenas cargue el módulo, consultamos las sesiones a la base de datos
        Asistencia.cargarSesionesDropdown();

        // Evento al cambiar de sesión en el dropdown
        document.getElementById('selectSesion').addEventListener('change', (e) => {
            Asistencia.idSesionActual = e.target.value;

            if (Asistencia.idSesionActual) {
                Asistencia.cargarListaAtletas();
            } else {
                document.getElementById('tablaAtletas').innerHTML = '<tr><td colspan="4" class="py-8 text-center text-gray-600 italic">Seleccione una sesión para cargar la lista.</td></tr>';
            }
        });

        // Evento para arrancar la cámara
        //document.getElementById('btnActivarCamara').addEventListener('click', Asistencia.iniciarCamara);

        const btnCamara = document.getElementById('btnActivarCamara');
        if (btnCamara) {
            // Removemos eventos anteriores si los hay y asignamos el nuevo
            btnCamara.replaceWith(btnCamara.cloneNode(true));
            document.getElementById('btnActivarCamara').addEventListener('click', Asistencia.toggleCamara);
        }
    },

    /**
     * Renderiza las sesiones activas dentro del select del header
     */
    cargarSesionesDropdown: async () => {
        const select = document.getElementById('selectSesion');
        
        // Petición GET limpia al controlador pivote
        const respuesta = await peticionAjax('listar_sesiones_activas');

        if (!respuesta || respuesta.status !== 'success') {
            select.innerHTML = '<option value="">Error al cargar cronograma</option>';
            return;
        }

        const sesiones = respuesta.data;

        if (sesiones.length === 0) {
            select.innerHTML = '<option value="">No hay entrenamientos para hoy</option>';
            return;
        }

       
        select.innerHTML = '<option value="">Seleccione una sesión activa...</option>';
        sesiones.forEach(sesion => {
            // El texto de la opción mostrará: "Grupo Juvenil - 15:00:00 (Planificada)"
            select.innerHTML += `
                <option value="${sesion.id_sesion}">
                    ${sesion.grupo_nombre} - ${sesion.fecha} (${sesion.estado})
                </option>
            `;
        });
    },

    iniciarCamara: () => {
        if (!Asistencia.idSesionActual) {
            UI.advertencia('Falta Información', 'Debe seleccionar una sesión de entrenamiento antes de activar el escáner.');
            return;
        }

        const btn = document.getElementById('btnActivarCamara');
        btn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Conectando...';
        btn.disabled = true;

        html5QrCode = new Html5Qrcode("visorCamara");

        html5QrCode.start(
            { facingMode: "environment" }, // Prioriza la cámara trasera en celulares
            { fps: 10, qrbox: { width: 250, height: 250 } },
            Asistencia.alDetectarQR,
            (error) => { /* Ignoramos los frames donde no hay QR */ }
        ).then(() => {
            btn.innerHTML = '<i class="fas fa-camera text-emerald-400"></i> Cámara Activa';
            document.getElementById('txtScanEstado').textContent = 'Apunte el QR del atleta a la cámara...';
        }).catch(err => {
            console.error("Motivo exacto del bloqueo de cámara:", err);
            
            // Le damos al usuario un mensaje más inteligente
            let msjError = 'No se pudo acceder a la cámara. Verifique los Justificados del navegador.';
            if (err && err.name === 'NotAllowedError') msjError = 'El navegador bloqueó el Justificado de la cámara.';
            if (err && err.name === 'NotFoundError') msjError = 'No se detectó ninguna cámara en este dispositivo.';
            if (err && err.name === 'NotReadableError') msjError = 'La cámara ya está siendo usada por otra aplicación.';
            
            UI.error('Error de Hardware', msjError);
            btn.innerHTML = 'Reintentar Cámara';
            btn.disabled = false;


            /* UI.error('Error de Hardware', 'No se pudo acceder a la cámara. Verifique los Justificados del navegador.');
            btn.innerHTML = 'Reintentar Cámara';
            btn.disabled = false; */
        });
    },

    /**
     * Se dispara automáticamente cuando la cámara lee un código válido
     */
  /*   alDetectarQR: async (tokenCapturado) => {
        if (Asistencia.procesandoLectura) return; // Evita lecturas dobles muy rápidas
        Asistencia.procesandoLectura = true;

        // Feedback sonoro (Opcional si agregas los mp3, o visual)
        const txtEstado = document.getElementById('txtScanEstado');
        txtEstado.innerHTML = '<span class="text-indigo-400">Procesando token...</span>';

        const formData = new FormData();
       // alert( Asistencia.idSesionActua+' token'+tokenCapturado);
        formData.append('id_sesion', Asistencia.idSesionActual);
        formData.append('token_qr', tokenCapturado);

        const respuesta = await peticionAjax('registrar_por_qr', formData);

        if (respuesta && respuesta.status === 'success') {
            document.getElementById('beepSuccess')?.play();
            // Actualizamos la tabla visualmente
            Asistencia.cargarListaAtletas();
            
            // Usamos Toast (alerta pequeña superior) en lugar del alert gigante para no interrumpir el flujo de escanear a 20 niños
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: `Asistencia: ${respuesta.nombre_atleta}`,
                showConfirmButton: false, timer: 2000, background: '#10b981', color: '#fff'
            });
        } else if (respuesta && respuesta.status === 'info') {
            // REGLA 2: Si el QR se escaneó por segunda vez, sale en azulito
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: respuesta.message,
                showConfirmButton: false, timer: 3000, background: '#3b82f6', color: '#fff'
            });
        }else {
            document.getElementById('beepError')?.play();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: respuesta?.message || ' Token inválido ',
                showConfirmButton: false, timer: 3000, background: '#ef4444', color: '#fff'
            });
        }

        // Liberamos el candado para la siguiente lectura después de 2 segundos
        setTimeout(() => { 
            Asistencia.procesandoLectura = false; 
            txtEstado.textContent = 'Apunte el QR del atleta a la cámara...';
        }, 2000);
    }, */

    alDetectarQR: async (tokenCapturado) => {
        if (Asistencia.procesandoLectura) return; // Evita lecturas dobles muy rápidas
        Asistencia.procesandoLectura = true;

        const txtEstado = document.getElementById('txtScanEstado');
        txtEstado.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> <span class="text-indigo-400">Procesando token...</span>';

        const formData = new FormData();
        formData.append('id_sesion', Asistencia.idSesionActual);
        formData.append('token_qr', tokenCapturado);

        const respuesta = await peticionAjax('registrar_por_qr', formData);

        if (respuesta && respuesta.status === 'success') {
            document.getElementById('beepSuccess')?.play();
            Asistencia.cargarListaAtletas(); // Refrescamos la tabla
            
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: `Asistencia: ${respuesta.nombre_atleta}`,
                showConfirmButton: false, timer: 2000, background: '#10b981', color: '#fff'
            });
        } else if (respuesta && respuesta.status === 'info') {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: respuesta.message,
                showConfirmButton: false, timer: 3000, background: '#3b82f6', color: '#fff'
            });
        } else {
            document.getElementById('beepError')?.play();
            Swal.fire({
                toast: true, position: 'top-end', icon: 'error',
                title: respuesta?.message || 'Token inválido',
                showConfirmButton: false, timer: 3000, background: '#ef4444', color: '#fff'
            });
        }

        // Liberamos el candado para la siguiente lectura después de 2 segundos
        setTimeout(() => { 
            Asistencia.procesandoLectura = false; 
            // Restauramos el mensaje si la cámara sigue activa
            if(Asistencia.camaraActiva){
                txtEstado.innerHTML = '<i class="fas fa-qrcode fa-fade mr-1"></i> Apunte el QR del atleta a la cámara...';
            }
        }, 2000);
    },

   toggleCamara: async () => {
        // 1. Validación de negocio (Tu código intacto)
        if (!Asistencia.idSesionActual) {
            UI.advertencia('Falta Información', 'Debe seleccionar una sesión de entrenamiento antes de activar el escáner.');
            return;
        }

        const btn = document.getElementById('btnActivarCamara');
        const txtEstado = document.getElementById('txtScanEstado');
        const visor = document.getElementById('visorCamara');

        // ==============================================================
        // 2. SI LA CÁMARA ESTÁ ACTIVA -> LA APAGAMOS
        // ==============================================================
        if (Asistencia.camaraActiva) {
            try {
                btn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Deteniendo...';
                btn.disabled = true;

                if (html5QrCode) {
                    await html5QrCode.stop();
                    html5QrCode.clear(); // Limpia el canvas de video
                }
                Asistencia.camaraActiva = false;
                
                // Restauramos el Botón a su estado original (Azul/Indigo)
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-camera mr-2"></i> Activar Cámara';
                btn.className = "w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-indigo-500/20 uppercase text-xs tracking-widest";
                
                // Restauramos textos e interfaz negra
                txtEstado.innerHTML = '<i class="fas fa-power-off mr-1"></i> Cámara apagada.';
                txtEstado.className = "text-[10px] text-gray-500 mt-3 font-mono";
                visor.innerHTML = '<span class="text-gray-600 text-sm"><i class="fas fa-video-slash text-3xl mb-2 block"></i> Cámara en espera</span>';
                
            } catch (err) {
                console.error("Error al detener la cámara", err);
                btn.disabled = false;
            }
            return; // Salimos de la función
        }

        // ==============================================================
        // 3. SI LA CÁMARA ESTÁ APAGADA -> LA ENCENDEMOS (Tu lógica adaptada)
        // ==============================================================
        btn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Conectando...';
        btn.disabled = true;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("visorCamara");
        }

        try {
            await html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                Asistencia.alDetectarQR, // Tu función intacta conectada aquí
                (error) => { /* Ignoramos los frames donde no hay QR */ }
            );

            // Si llegamos aquí, el hardware se encendió con éxito
            Asistencia.camaraActiva = true;
            btn.disabled = false;
            
            // Cambiamos el Botón a modo "Detener" (Rojo)
            btn.innerHTML = '<i class="fas fa-times-circle mr-2"></i> Detener Cámara';
            btn.className = "w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-red-500/20 uppercase text-xs tracking-widest";
            
            // Animamos el texto de abajo en verde fluorescente
            txtEstado.innerHTML = '<i class="fas fa-qrcode fa-fade mr-1"></i> Apunte el QR del atleta a la cámara...';
            txtEstado.className = "text-[10px] text-emerald-400 mt-3 font-mono transition-colors";

        } catch (err) {
            // TU EXCELENTE BLOQUE DE MANEJO DE ERRORES DE HARDWARE
            console.error("Motivo exacto del bloqueo de cámara:", err);
            Asistencia.camaraActiva = false;
            
            let msjError = 'No se pudo acceder a la cámara. Verifique los permisos del navegador.';
            if (err && err.name === 'NotAllowedError') msjError = 'El navegador bloqueó el permiso de la cámara.';
            if (err && err.name === 'NotFoundError') msjError = 'No se detectó ninguna cámara en este dispositivo.';
            if (err && err.name === 'NotReadableError') msjError = 'La cámara ya está siendo usada por otra aplicación.';
            
            UI.error('Error de Hardware', msjError);
            
            // Devolvemos el botón a un estado de reintento
            btn.innerHTML = '<i class="fas fa-camera mr-2"></i> Reintentar Cámara';
            btn.className = "w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition duration-300 shadow-lg shadow-indigo-500/20 uppercase text-xs tracking-widest";
            btn.disabled = false;
            
            txtEstado.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i> Error de hardware.';
        }
    },

    // =================================================================
    // ACCIONES MANUALES (Ley de Murphy)
    // =================================================================

    accionManual: async (id_atleta, estado, nombre_atleta) => {
        if (!Asistencia.idSesionActual) return;

        const accionVisual = estado === 'Ausente' ? 'FALTA' : estado.toUpperCase();
        const colorConf = estado === 'Presente' ? '#10b981' : (estado === 'Ausente' ? '#ef4444' : '#f59e0b');

        // REGLA 3: Evitar el error de "dedo gordo"
        const confirmacion = await Swal.fire({
            title: `¿Registrar ${accionVisual}?`,
            html: `Atleta seleccionado:<br><b class="text-lg text-indigo-400">${nombre_atleta}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: colorConf,
            cancelButtonColor: '#374151',
            confirmButtonText: `Sí, aplicar`,
            cancelButtonText: 'Cancelar',
            background: '#161430', color: '#fff'
        });

        if (!confirmacion.isConfirmed) return;

        let justificacion = 'Asistencia validada manualmente';

        // Si es Inasistencia o Justificado, OBLIGAMOS a que escriba algo
        if (estado === 'Justificado') {
            const result = await UI.pedirJustificacion(
                `Motivo del Estado`, 
                `Explique el motivo por el cual ${nombre_atleta} no participará.`
            );
            
            if (!result.isConfirmed) return; // Canceló la acción
            justificacion = result.value;
        }

        const formData = new FormData();
        formData.append('id_sesion', Asistencia.idSesionActual);
        formData.append('id_atleta', id_atleta);
        formData.append('estado_asistencia', estado);
        formData.append('justificacion', justificacion);
        formData.append('tipo', 'Manual');

        const respuesta = await peticionAjax('registrar_manual', formData);

        if (respuesta && respuesta.status === 'success') {
            UI.exito('Guardado', 'El estado fue actualizado correctamente.');
            Asistencia.cargarListaAtletas();
        } else {
           
            UI.error('Error', respuesta.message || 'Ocurrió un error inesperado al procesar los datos.');
        }
    },

    cargarListaAtletas: async () => {
        if (!Asistencia.idSesionActual) return;

        const tbody = document.getElementById('tablaAtletas');
        
        // 1. Destruimos la instancia de DataTables temporalmente si existe
        if ($.fn.DataTable.isDataTable('#tablaAsistenciaDT')) {
            $('#tablaAsistenciaDT').DataTable().destroy();
        }

        // 2. Mostrar estado de carga interactivo
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-8 text-center text-gray-500">
                    <i class="fas fa-spinner animate-spin mr-2 text-indigo-500"></i> Cargando convocados...
                </td>
            </tr>`;

        // 3. Petición GET al controlador pivote
        const respuesta = await peticionAjax(`cargar_atletas&id_sesion=${Asistencia.idSesionActual}`);

        if (!respuesta || respuesta.status !== 'success') {
            tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-red-500">${respuesta?.message || 'Error al cargar los datos.'}</td></tr>`;
            return;
        }

        const atletas = respuesta.data;
        
        // 4. Validar si la sesión no tiene atletas activos
        if (atletas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-gray-500 italic">No hay atletas activos registrados para esta sesión.</td></tr>';
            document.getElementById('statTotal').textContent = '0';
            document.getElementById('statPresentes').textContent = '0';
            document.getElementById('statAusentes').textContent = '0';
            return;
        }

        let html = '';
        let contPresentes = 0;
        let contAusentes = 0;

        // 5. Construcción dinámica de la tabla
        atletas.forEach(atleta => {
            
            // Lógica de contadores
            if (atleta.estado === 'Presente') contPresentes++;
            else if (atleta.estado === 'Ausente' || atleta.estado === 'Justificado') contAusentes++;

            // Lógica visual del Badge (Etiqueta de estado)
            let badgeClass = 'bg-gray-500/10 text-gray-400 border-gray-500/20'; // Por defecto: Pendiente
            let textoEstado = '<i class="fas fa-minus mr-1"></i> Pendiente';
            
            // Identificador extra visual si fue por QR
            let etiquetaQR = atleta.tipo === 'QR' ? ' <span class="ml-1 text-[9px] text-blue-400 font-bold">(QR)</span>' : '';
            
            if (atleta.estado === 'Presente') {
                badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                textoEstado = `<i class="fas fa-check-circle mr-1"></i> Presente${etiquetaQR}`;
            } else if (atleta.estado === 'Ausente') {
                badgeClass = 'bg-red-500/10 text-red-400 border-red-500/30';
                textoEstado = '<i class="fas fa-times-circle mr-1"></i> Faltó';
            } else if (atleta.estado === 'Justificado') {
                badgeClass = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                textoEstado = '<i class="fas fa-user-clock mr-1"></i> Justificado';
            }

            // MOTOR INTELIGENTE DE BOTONES (Inmutabilidad visual)
            const esInmutableQR = (atleta.tipo === 'QR' && atleta.estado === 'Presente');

            const renderBtn = (accionBtn, iconoNormal) => {
                const baseClass = "w-7 h-7 rounded border transition-all shadow-sm flex items-center justify-center";
                
                if (esInmutableQR) {
                    return `
                        <button class="${baseClass} opacity-30 cursor-not-allowed border-gray-500/30 bg-gray-500/10 text-gray-500" 
                                disabled title="Inmutable: Validado mediante Escáner QR">
                            <i class="fas fa-lock text-xs"></i>
                        </button>`;
                }

                const estilos = {
                    'Presente': { color: 'emerald', bg: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' },
                    'Ausente': { color: 'red', bg: 'bg-red-500/10 text-red-400 border-red-500/20' },
                    'Justificado': { color: 'amber', bg: 'bg-amber-500/10 text-amber-400 border-amber-500/20' }
                };

                const cfg = estilos[accionBtn];

                if (atleta.estado === accionBtn) {
                    return `
                        <button class="${baseClass} opacity-30 cursor-not-allowed ${cfg.bg}" 
                                disabled title="El atleta ya se encuentra registrado como ${accionBtn}">
                            <i class="fas ${iconoNormal} text-xs"></i>
                        </button>`;
                }

                return `
                    <button onclick="Asistencia.accionManual(${atleta.id_atleta}, '${accionBtn}', '${atleta.nombres} ${atleta.apellidos}')" 
                            class="${baseClass} ${cfg.bg} hover:bg-${cfg.color}-500 hover:text-white cursor-pointer" 
                            title="Marcar como ${accionBtn}">
                        <i class="fas ${iconoNormal} text-xs"></i>
                    </button>`;
            };

            // Inyección del <tr>
            html += `
                <tr class="border-b border-[#252345] hover:bg-[#1b1937] transition-colors">
                    <td class="py-3 pl-2">
                        <p class="text-white font-bold text-xs">${atleta.nombres} ${atleta.apellidos}</p>
                        <p class="text-[10px] text-gray-500 font-mono tracking-wider">${atleta.cedula}</p>
                    </td>
                    <td class="py-3 text-indigo-300 font-medium text-xs">
                        ${atleta.categoria_nombre || 'S/C'}
                    </td>
                    <td class="py-3 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <span class="px-2 py-1 rounded-full text-[9px] uppercase font-bold tracking-widest border ${badgeClass}">
                                ${textoEstado}
                            </span>
                            ${atleta.justificacion && atleta.estado !== 'Presente' && atleta.justificacion !== 'No aplica' 
                                ? `<p class="text-[9px] text-gray-500 mt-1 truncate max-w-[120px] italic" title="${atleta.justificacion}">${atleta.justificacion}</p>` 
                                : ''}
                        </div>
                    </td>
                    <td class="py-3 pr-2 text-right">
                        <div class="flex justify-end gap-1.5">
                            ${renderBtn('Presente', 'fa-check')}
                            ${renderBtn('Ausente', 'fa-times')}
                            ${renderBtn('Justificado', 'fa-clock')}
                        </div>
                    </td>
                </tr>
            `;
        });

        // 6. Renderizar el HTML en la tabla
        tbody.innerHTML = html;

        // 7. INICIALIZAR DATATABLES (Ahora que ya existe el HTML en el DOM)
        $('#tablaAsistenciaDT').DataTable({
            responsive: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            info: true,
            searching: true,
           /*  language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }, */
            language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ representantes",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
            // Configuramos las prioridades de responsive (si no cabe en móvil, esconde lo menos importante)
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // Atleta (Siempre visible)
                { responsivePriority: 3, targets: 1 }, // Categoría (Se esconde primero)
                { responsivePriority: 2, targets: 2 }, // Estado
                { responsivePriority: 1, targets: 3, orderable: false } // Acciones (Siempre visible, no se puede ordenar por aquí)
            ]
        });

        // 8. Actualizar las Estadísticas Visuales en los cuadros de arriba
        document.getElementById('statPresentes').textContent = contPresentes;
        document.getElementById('statAusentes').textContent = contAusentes;
        document.getElementById('statTotal').textContent = atletas.length;
    }

/*     cargarListaAtletas: async () => {
        if (!Asistencia.idSesionActual) return;

        const tbody = document.getElementById('tablaAtletas');
        
        // 1. Mostrar estado de carga interactivo
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-8 text-center text-gray-500">
                    <i class="fas fa-spinner animate-spin mr-2 text-indigo-500"></i> Cargando convocados...
                </td>
            </tr>`;

        // 2. Petición GET al controlador pivote
        const respuesta = await peticionAjax(`cargar_atletas&id_sesion=${Asistencia.idSesionActual}`);

        if (!respuesta || respuesta.status !== 'success') {
            tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-red-500">${respuesta?.message || 'Error al cargar los datos.'}</td></tr>`;
            return;
        }

        const atletas = respuesta.data;
        
        // 3. Validar si la sesión no tiene atletas activos
        if (atletas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-gray-500 italic">No hay atletas activos registrados para esta sesión.</td></tr>';
            document.getElementById('statTotal').textContent = '0';
            document.getElementById('statPresentes').textContent = '0';
            document.getElementById('statAusentes').textContent = '0';
            return;
        }

        let html = '';
        let contPresentes = 0;
        let contAusentes = 0;

        // 4. Construcción dinámica de la tabla
        atletas.forEach(atleta => {
            
            // Lógica de contadores
            if (atleta.estado === 'Presente') contPresentes++;
            else if (atleta.estado === 'Ausente' || atleta.estado === 'Justificado') contAusentes++;

            // Lógica visual del Badge (Etiqueta de estado)
            let badgeClass = 'bg-gray-500/10 text-gray-400 border-gray-500/20'; // Por defecto: Pendiente
            let textoEstado = '<i class="fas fa-minus mr-1"></i> Pendiente';
            
            // Identificador extra visual si fue por QR
            let etiquetaQR = atleta.tipo === 'QR' ? ' <span class="ml-1 text-[9px] text-blue-400 font-bold">(QR)</span>' : '';
            
            if (atleta.estado === 'Presente') {
                badgeClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                textoEstado = `<i class="fas fa-check-circle mr-1"></i> Presente${etiquetaQR}`;
            } else if (atleta.estado === 'Ausente') {
                badgeClass = 'bg-red-500/10 text-red-400 border-red-500/30';
                textoEstado = '<i class="fas fa-times-circle mr-1"></i> Faltó';
            } else if (atleta.estado === 'Justificado') {
                badgeClass = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                textoEstado = '<i class="fas fa-user-clock mr-1"></i> Justificado';
            }

            // ====================================================================
            // MOTOR INTELIGENTE DE BOTONES (Inmutabilidad visual)
            // ====================================================================
            // Verificamos si la asistencia está blindada por QR
            const esInmutableQR = (atleta.tipo === 'QR' && atleta.estado === 'Presente');

            // Función auxiliar para renderizar cada botón con las reglas de negocio
            const renderBtn = (accionBtn, iconoNormal) => {
                const baseClass = "w-7 h-7 rounded border transition-all shadow-sm flex items-center justify-center";
                
                // REGLA 1: Si es QR, todo está bloqueado con candados
                if (esInmutableQR) {
                    return `
                        <button class="${baseClass} opacity-30 cursor-not-allowed border-gray-500/30 bg-gray-500/10 text-gray-500" 
                                disabled title="Inmutable: Validado mediante Escáner QR">
                            <i class="fas fa-lock text-xs"></i>
                        </button>`;
                }

                // Definimos los colores estéticos por cada tipo de botón
                const estilos = {
                    'Presente': { color: 'emerald', bg: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' },
                    'Ausente': { color: 'red', bg: 'bg-red-500/10 text-red-400 border-red-500/20' },
                    'Justificado': { color: 'amber', bg: 'bg-amber-500/10 text-amber-400 border-amber-500/20' }
                };

                const cfg = estilos[accionBtn];

                // REGLA 2: Si el botón representa el estado actual, se bloquea (Para no registrar lo que ya está)
                if (atleta.estado === accionBtn) {
                    return `
                        <button class="${baseClass} opacity-30 cursor-not-allowed ${cfg.bg}" 
                                disabled title="El atleta ya se encuentra registrado como ${accionBtn}">
                            <i class="fas ${iconoNormal} text-xs"></i>
                        </button>`;
                }

                // REGLA 3: Si no aplica ninguna de las anteriores, el botón está 100% habilitado
                return `
                    <button onclick="Asistencia.accionManual(${atleta.id_atleta}, '${accionBtn}', '${atleta.nombres} ${atleta.apellidos}')" 
                            class="${baseClass} ${cfg.bg} hover:bg-${cfg.color}-500 hover:text-white cursor-pointer" 
                            title="Marcar como ${accionBtn}">
                        <i class="fas ${iconoNormal} text-xs"></i>
                    </button>`;
            };

            // Inyección del <tr>
            html += `
                <tr class="border-b border-[#252345] hover:bg-[#1b1937] transition-colors">
                    <td class="py-3 pl-2">
                        <p class="text-white font-bold text-xs">${atleta.nombres} ${atleta.apellidos}</p>
                        <p class="text-[10px] text-gray-500 font-mono tracking-wider">${atleta.cedula}</p>
                    </td>
                    <td class="py-3 text-indigo-300 font-medium text-xs">
                        ${atleta.categoria_nombre || 'S/C'}
                    </td>
                    <td class="py-3 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <span class="px-2 py-1 rounded-full text-[9px] uppercase font-bold tracking-widest border ${badgeClass}">
                                ${textoEstado}
                            </span>
                            ${atleta.justificacion && atleta.estado !== 'Presente' && atleta.justificacion !== 'No aplica' 
                                ? `<p class="text-[9px] text-gray-500 mt-1 truncate max-w-[120px] italic" title="${atleta.justificacion}">${atleta.justificacion}</p>` 
                                : ''}
                        </div>
                    </td>
                    <td class="py-3 pr-2 text-right">
                        <div class="flex justify-end gap-1.5">
                            ${renderBtn('Presente', 'fa-check')}
                            ${renderBtn('Ausente', 'fa-times')}
                            ${renderBtn('Justificado', 'fa-clock')}
                        </div>
                    </td>
                </tr>
            `;
        });

        // 5. Renderizar el HTML en la tabla
        tbody.innerHTML = html;

        // 6. Actualizar las Estadísticas Visuales en los cuadros de arriba
        document.getElementById('statPresentes').textContent = contPresentes;
        document.getElementById('statAusentes').textContent = contAusentes;
        document.getElementById('statTotal').textContent = atletas.length;
    } */

};

document.addEventListener('DOMContentLoaded', Asistencia.init);