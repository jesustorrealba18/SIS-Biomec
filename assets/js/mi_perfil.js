// =====================================================================
// CONTROL DE PESTAÑAS (TABS) EN MI PERFIL
// =====================================================================
window.cambiarPestana = function(idPestana) {
    // 1. Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
        tab.classList.remove('block');
    });
    
    // 2. Resetear el diseño de todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'font-bold', 'shadow-md');
        btn.classList.add('text-gray-400', 'font-medium');
    });
    
    // 3. Mostrar el contenido seleccionado
    const tabSeleccionado = document.getElementById('tab-' + idPestana);
    if(tabSeleccionado) {
        tabSeleccionado.classList.remove('hidden');
        tabSeleccionado.classList.add('block');
    }
    
    // 4. Resaltar el botón seleccionado
    const btnActivo = document.getElementById('btn-tab-' + idPestana);
    if(btnActivo) {
        btnActivo.classList.remove('text-gray-400', 'font-medium');
        btnActivo.classList.add('bg-indigo-600', 'text-white', 'font-bold', 'shadow-md');
    }
};  

// =====================================================================
// CONTROL DEL MODO DE CRONÓMETRO
// =====================================================================
window.cambiarModoCrono = function(modo) {
    const btnManual = document.getElementById('btnCronoManual');
    const btnLive = document.getElementById('btnCronoLive');

    // Clases de estilo activo e inactivo
    const clasesActivo = ['bg-white', 'dark:bg-[#161430]', 'text-gray-800', 'dark:text-white', 'shadow'];
    const clasesInactivo = ['text-gray-500', 'dark:text-gray-500'];

    if (modo === 'live') {
        // Activar Live, Desactivar Manual
        btnLive.classList.add(...clasesActivo);
        btnLive.classList.remove(...clasesInactivo);
        
        btnManual.classList.remove(...clasesActivo);
        btnManual.classList.add(...clasesInactivo);
    } else {
        // Activar Manual, Desactivar Live
        btnManual.classList.add(...clasesActivo);
        btnManual.classList.remove(...clasesInactivo);
        
        btnLive.classList.remove(...clasesActivo);
        btnLive.classList.add(...clasesInactivo);
    }

    // Guardar en el navegador (opcional, para lectura rápida)
    localStorage.setItem('sgrd_crono_mode', modo);

    // Guardar en Base de Datos usando la función global que ya creaste
    if (typeof guardarPreferenciaGlobal === 'function') {
        guardarPreferenciaGlobal('crono_mode', modo);
    }
};
  
/*   document.addEventListener('DOMContentLoaded', async () => {
            // El controlador debe responder a esta acción buscando los datos del atleta en sesión
            const API_URL = 'index.php?p=mi_perfil&accion=obtener_mi_ficha';
            
            try {
                const respuesta = await fetch(API_URL);
                if (!respuesta.ok) throw new Error('Error en infraestructura.');
                const datos = await respuesta.json();
                
                if (!datos || datos.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fallo de Acceso',
                        text: datos.message || 'No se pudo asociar tu usuario con un expediente de atleta.',
                        background: '#161430', color: '#fff', confirmButtonColor: '#6366f1'
                    });
                    return;
                }

                // 1. Hidratación de la Imagen / Avatar
                const fotoContenedor = document.getElementById('perfilFotoContenedor');
                if (datos.foto) {
                    fotoContenedor.innerHTML = `<img src="${datos.foto}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-indigo-500/20 shadow-xl object-cover">`;
                } else {
                    fotoContenedor.innerHTML = `<div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-indigo-500/10 border-2 border-dashed border-indigo-500/30 flex items-center justify-center text-3xl text-indigo-400"><i class="fas fa-swimmer"></i></div>`;
                }

                // 2. Hidratación de Labels Textuales
                document.getElementById('lblNombreCompleto').textContent = `${datos.nombres} ${datos.apellidos}`;
                document.getElementById('lblCedula').innerHTML = `<i class="fas fa-id-card text-gray-600 mr-1"></i> ${datos.cedula}`;
                document.getElementById('lblEdad').textContent = `${datos.edad || '—'} años`;
                document.getElementById('lblSexo').textContent = datos.sexo === 'M' ? 'Masculino' : 'Femenino';
                document.getElementById('lblCategoria').textContent = datos.categoria_nombre || 'No asignada';
                document.getElementById('lblTelefono').textContent = datos.telefono || '—';
                document.getElementById('lblCorreo').textContent = datos.correo || '—';
                document.getElementById('lblFeveda').textContent = datos.numero_feveda || 'S/F';
                document.getElementById('lblClub').textContent = datos.club_procedencia || 'Atleta Libre';
                document.getElementById('lblSangre').textContent = datos.grupo_sanguineo || '—';
                document.getElementById('lblAlergias').textContent = datos.alergias || 'Ninguna registrada en la ficha médica.';
                
                // Estado del Atleta
                const badge = document.getElementById('badgeEstado');
                badge.textContent = datos.estado;
                badge.className = `estado-badge estado-${datos.estado}`;

                // Contacto de Emergencia Condicional
                if (datos.contacto_emergencia_nombre) {
                    document.getElementById('lblContactoNombre').textContent = `${datos.contacto_emergencia_nombre} (${datos.contacto_emergencia_parentesco || 'Familiar'})`;
                    document.getElementById('lblContactoTlf').textContent = datos.contacto_emergencia_telefono || '—';
                }

                // ==========================================================
                // CONTROL DE MENORES: HIDRATACIÓN DE REPRESENTANTE (RF-01)
                // ==========================================================
                const tarjetaRep = document.getElementById('tarjetaRepresentante');
                
                if (datos.representante_nombre) {
                    // Si el backend envía un nombre de representante, mapeamos los campos
                    document.getElementById('lblRepNombre').textContent = datos.representante_nombre;
                    document.getElementById('lblRepCedula').textContent = datos.representante_cedula ? `V-${datos.representante_cedula}` : '—';
                    document.getElementById('lblRepParentesco').textContent = datos.representante_parentesco || 'Representante Legal';
                    document.getElementById('lblRepTelefono').textContent = datos.representante_telefono || '—';
                    
                    // Mostramos la tarjeta removiendo la clase oculta
                    tarjetaRep.classList.remove('hidden');
                } else {
                    // Si es mayor de edad o no tiene vinculación, aseguramos que permanezca oculta
                    tarjetaRep.classList.add('hidden');
                }

                // ==========================================================
                // 3. GENERACIÓN DEL CÓDIGO QR EN TIEMPO REAL
                // ==========================================================
                const contenedorQR = document.getElementById('contenedorQR');
                const txtToken = document.getElementById('txtTokenVisible');
                
                contenedorQR.innerHTML = ''; // Limpiamos el spinner de carga
                
                if (datos.token_asistencia) {
                    new QRCode(contenedorQR, {
                        text: datos.token_asistencia,
                        width: 160,
                        height: 160,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    txtToken.textContent = `ID-PASS: ${datos.token_asistencia.substring(0, 12).toUpperCase()}`;
                } else {
                    contenedorQR.innerHTML = '<span class="text-xs text-red-500 font-bold p-2">QR NO GENERADO</span>';
                    txtToken.textContent = 'Solicite asistencia administrativa';
                }

            } catch (error) {
                console.error(error);
                document.getElementById('contenedorQR').innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
            }

            const modoGuardado = localStorage.getItem('sgrd_crono_mode') || 'manual';
            cambiarModoCrono(modoGuardado);
        }); */

document.addEventListener('DOMContentLoaded', async () => {
    const API_URL = 'index.php?p=mi_perfil&accion=obtener_mi_ficha';
    
    try {
        const respuesta = await fetch(API_URL);
        if (!respuesta.ok) throw new Error('Error en red.');
        const datos = await respuesta.json();
        
        if (!datos || !datos.usuario) {
            Swal.fire({ icon: 'error', title: 'Fallo de Acceso', text: 'No se pudo cargar tu perfil.' });
            return;
        }

        // ==========================================================
        // 1. PREPARAR EL LIENZO (Columnas del Grid)
        // ==========================================================
        // Vaciamos las columnas actuales para construirlas como Legos
        const gridPrincipal = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-3');
        gridPrincipal.innerHTML = `
            <div id="colIzquierda" class="lg:col-span-2 space-y-6"></div>
            <div id="colDerecha" class="w-full space-y-6"></div>
        `;
        
        const colIzq = document.getElementById('colIzquierda');
        const colDer = document.getElementById('colDerecha');

        // ==========================================================
        // 2. WIDGET: IDENTIDAD (Siempre visible para TODOS)
        // ==========================================================
        const rolesBadges = datos.roles.map(r => `<span class="bg-indigo-500/10 text-indigo-500 border border-indigo-500/20 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider">${r}</span>`).join('');
        const estado = datos.usuario.activo ? 'Activo' : 'Inactivo';
        
        // Si es atleta o entrenador y tiene foto, la usamos. Si no, icono genérico.
        let fotoURL = datos.atleta?.foto || datos.entrenador?.foto || null;
        let htmlFoto = fotoURL 
            ? `<img src="${fotoURL}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-indigo-500/20 shadow-xl object-cover">`
            : `<div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-indigo-500/10 border-2 border-dashed border-indigo-500/30 flex items-center justify-center text-3xl text-indigo-400"><i class="fas fa-user-circle"></i></div>`;

        colIzq.innerHTML += `
            <div class="tarjeta bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl shadow-sm p-5 sm:p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left gap-6">
                    <div class="shrink-0">${htmlFoto}</div>
                    <div class="space-y-2 flex-1 w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">${datos.usuario.nombres} ${datos.usuario.apellidos}</h2>
                            <span class="estado-badge estado-${estado}">${estado}</span>
                        </div>
                        <p class="text-indigo-500 dark:text-indigo-400 font-mono text-sm tracking-widest"><i class="fas fa-id-card mr-1"></i> ${datos.usuario.cedula || 'S/C'}</p>
                        <div class="flex flex-wrap gap-2 pt-2">${rolesBadges}</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-white/5 mt-4">
                            <div><p class="text-[9px] uppercase text-gray-500">Correo Electrónico</p><p class="text-gray-900 dark:text-white text-sm break-all">${datos.usuario.correo}</p></div>
                            <div><p class="text-[9px] uppercase text-gray-500">Miembro desde</p><p class="text-gray-900 dark:text-white text-sm">${datos.usuario.fecha_creacion.split(' ')[0]}</p></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // ==========================================================
        // 3. WIDGETS POR FACETAS (Polimorfismo Visual)
        // ==========================================================

        // --- FACETA: ATLETA ---
        if (datos.atleta) {
            colIzq.innerHTML += `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-5 shadow-sm">
                        <h3 class="text-xs uppercase text-purple-500 font-black tracking-widest mb-4"><i class="fas fa-passport"></i> Técnico</h3>
                        <p class="text-[9px] uppercase text-gray-500">Categoría</p><p class="text-sm font-bold mb-2">${datos.atleta.categoria || 'N/A'}</p>
                        <p class="text-[9px] uppercase text-gray-500">Ficha / Club</p><p class="text-sm">${datos.atleta.numero_feveda || 'S/F'} | ${datos.atleta.club_procedencia || 'Libre'}</p>
                    </div>
                    <div class="bg-white dark:bg-[#161430] border-l-4 border-l-emerald-500 border border-gray-200 dark:border-[#252345] rounded-2xl p-5 shadow-sm">
                        <h3 class="text-xs uppercase text-emerald-500 font-black tracking-widest mb-4"><i class="fas fa-notes-medical"></i> Médico</h3>
                        <p class="text-[9px] uppercase text-gray-500">Sangre</p><p class="text-sm font-bold mb-2">${datos.atleta.grupo_sanguineo || 'N/A'}</p>
                        <p class="text-[9px] uppercase text-gray-500">Alergias</p><p class="text-sm line-clamp-2">${datos.atleta.alergias || 'Ninguna'}</p>
                    </div>
                </div>
            `;
            
            // Pase QR (Columna Derecha)
            colDer.innerHTML += `
                <div class="tarjeta bg-white dark:bg-transparent border border-gray-200 dark:border-[#252345] rounded-2xl p-5 sm:p-6 flex flex-col items-center justify-center border-t-4 border-t-indigo-500 text-center shadow-xl sticky top-24">
                    <span class="text-[10px] text-indigo-500 font-black uppercase tracking-widest mb-4 bg-indigo-500/10 py-1.5 px-4 rounded-full"><i class="fas fa-qrcode"></i> Pase de Acceso</span>
                    <div class="bg-white p-3 rounded-2xl shadow-[0_0_30px_rgba(99,102,241,0.2)] border border-indigo-500/20 mb-4">
                        <div id="contenedorQR" class="w-[140px] h-[140px] flex justify-center items-center"></div>
                    </div>
                    <span class="text-[10px] text-gray-500 font-mono tracking-wider break-all bg-gray-100 dark:bg-white/5 p-2 rounded-lg w-full">${datos.atleta.token_asistencia || 'Token no generado'}</span>
                </div>
            `;
            if (datos.atleta.token_asistencia) {
                setTimeout(() => {
                    new QRCode(document.getElementById('contenedorQR'), {
                        text: datos.atleta.token_asistencia, width: 140, height: 140, colorDark: "#000", colorLight: "#fff", correctLevel: QRCode.CorrectLevel.H
                    });
                }, 100);
            }
        }

        // --- FACETA: REPRESENTANTE ---
        if (datos.representante) {
            let htmlHijos = `<div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 shadow-sm">
                <h3 class="text-xs uppercase text-indigo-500 font-black tracking-widest mb-4"><i class="fas fa-users"></i> Atletas a mi cargo</h3>`;
            
            if (datos.representante.hijos && datos.representante.hijos.length > 0) {
                htmlHijos += `<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">`;
                datos.representante.hijos.forEach(hijo => {
                    htmlHijos += `
                    <div class="border border-gray-200 dark:border-[#252345] p-4 rounded-xl flex items-center gap-4 hover:border-indigo-500 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500"><i class="fas fa-swimmer"></i></div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">${hijo.nombres} ${hijo.apellidos}</p>
                            <p class="text-[10px] text-gray-500 font-mono">V-${hijo.cedula} | ${hijo.categoria}</p>
                        </div>
                    </div>`;
                });
                htmlHijos += `</div>`;
            } else {
                htmlHijos += `<p class="text-sm text-amber-500"><i class="fas fa-exclamation-triangle"></i> No hay atletas vinculados.</p>`;
            }
            htmlHijos += `</div>`;
            colIzq.innerHTML += htmlHijos;
        }

        // --- FACETA: ENTRENADOR ---
        if (datos.entrenador) {
            let htmlGrupos = `<div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 shadow-sm border-l-4 border-l-emerald-500">
                <h3 class="text-xs uppercase text-emerald-500 font-black tracking-widest mb-4"><i class="fas fa-stopwatch"></i> Mis Grupos Asignados</h3>`;
            
            if (datos.entrenador.grupos && datos.entrenador.grupos.length > 0) {
                htmlGrupos += `<div class="grid grid-cols-1 gap-3">`;
                datos.entrenador.grupos.forEach(grupo => {
                    htmlGrupos += `
                    <div class="bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 p-3 rounded-xl">
                        <p class="text-sm font-bold text-gray-900 dark:text-white"><i class="fas fa-users text-emerald-500/50 mr-2"></i>${grupo.nombre}</p>
                        <p class="text-[10px] text-gray-500 mt-1 line-clamp-1">${grupo.descripcion || 'Sin descripción'}</p>
                    </div>`;
                });
                htmlGrupos += `</div>`;
            } else {
                htmlGrupos += `<p class="text-sm text-gray-500">No tienes grupos asignados actualmente.</p>`;
            }
            htmlGrupos += `</div>`;
            colIzq.innerHTML += htmlGrupos;
        }

        // --- FACETA: ROLES DE SISTEMA (Médicos / Administradores) ---
        // Si el usuario no tiene ninguna faceta "Física" (Atleta, Rep, Entrenador), llenamos la columna derecha para equilibrar.
        if (!datos.atleta && !datos.entrenador && !datos.representante) {
            colDer.innerHTML += `
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg sticky top-24">
                    <h3 class="text-xs uppercase font-black tracking-widest mb-4 text-indigo-200"><i class="fas fa-server"></i> Acceso de Sistema</h3>
                    <p class="text-sm text-indigo-100 mb-4">Tu cuenta está configurada como personal administrativo o de soporte especializado.</p>
                    <ul class="space-y-2 text-xs font-medium">
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> Credenciales Validadas</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> Acceso a Módulos Centrales</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-400"></i> Conexión Segura</li>
                    </ul>
                </div>
            `;
        }

    } catch (error) {
        console.error("Error renderizando perfil modular:", error);
    }

    const modoGuardado = localStorage.getItem('sgrd_crono_mode') || 'manual';
    cambiarModoCrono(modoGuardado);
});