<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'usuarios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Gestión de Usuarios | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/modoInterfaz.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== ESTILOS BASE ===== */
        body { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #252345; }

        /* ===== INPUTS ADAPTATIVOS ===== */
        .input-adapt {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            transition: all 0.3s ease;
        }
        .dark .input-adapt {
            background-color: #0f0d23;
            border-color: #252345;
            color: #ffffff;
        }
        .input-adapt:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            outline: none;
        }
        .dark .input-adapt:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        /* ===== TARJETAS ===== */
        .tarjeta {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }
        .dark .tarjeta {
            background-color: #161430;
            border-color: #252345;
        }

        /* ===== TOGGLE SWITCH (adaptado) ===== */
        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #d1d5db;
            border-radius: 24px;
            transition: 0.3s;
        }
        .dark .toggle-slider {
            background: #374151;
        }
        .toggle-slider:before {
            content: '';
            position: absolute;
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider {
            background: #10b981;
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }

        /* ===== TRANSICIONES ===== */
        .menu-transition {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
    <link rel="stylesheet" href="assets/css/driver.css">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#0f0d23] dark:text-gray-300 font-sans antialiased transition-colors duration-300 overflow-x-hidden">

<?php
if (isset($_SESSION['id'])) {
    \GrupoProyecto\SisBiomec\seguridad\Autorizacion::cargarPermisos($_SESSION['id']);
}
?>

    <div class="flex h-screen overflow-hidden">

        <!-- Overlay para móvil cuando el menú está abierto -->
        <div id="menuOverlay" class="fixed inset-0 bg-black/70 z-30 opacity-0 pointer-events-none transition-opacity lg:hidden"></div>

        <!-- Sidebar - responsive -->
        <aside id="sidebarMenu" class="fixed top-0 left-0 h-full w-72 bg-white dark:bg-[#0f0d23] border-r border-gray-200 dark:border-[#252345] z-40 transform -translate-x-full menu-transition lg:relative lg:translate-x-0 lg:flex-shrink-0 overflow-y-auto transition-colors duration-300">
            <div class="p-4 flex justify-between items-center border-b border-gray-200 dark:border-[#252345] lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-600 p-1.5 rounded-lg text-white shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-swimmer text-sm"></i>
                    </div>
                    <span class="text-lg font-black text-gray-900 dark:text-white italic tracking-tighter">SGRD</span>
                </div>
                <button id="closeMenuBtn" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php include 'vista/complementos/menu_responsive.php'; ?>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <?php
                $tituloPagina = "Gestión de Usuarios";
                $tituloPaginaResponsive = "Usuarios";
                $iconModulo = "fas fa-users-cog";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-users-cog text-indigo-500"></i> Gestión de Usuarios
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Administración de cuentas, roles y permisos del sistema.</p>
                    </div>
                    <button onclick="abrirModalCrear()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Nuevo Usuario
                    </button>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 overflow-x-auto shadow-2xl transition-colors duration-300">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                        <div class="relative w-full sm:w-72">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" id="buscarUsuario" placeholder="Buscar por nombre, correo o cédula..." class="input-adapt w-full rounded-xl pl-9 pr-4 py-2.5 text-sm">
                        </div>
                        <span id="totalUsuarios" class="text-xs text-gray-500 dark:text-gray-400"></span>
                    </div>
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="pb-4 pr-4 cursor-pointer select-none hover:text-indigo-500 transition" data-orden="id_usuario" onclick="ordenarPor('id_usuario')"># <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th scope="col" class="pb-4 pr-4 cursor-pointer select-none hover:text-indigo-500 transition" data-orden="nombres" onclick="ordenarPor('nombres')">Nombre <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th scope="col" class="pb-4 pr-4 cursor-pointer select-none hover:text-indigo-500 transition" data-orden="correo" onclick="ordenarPor('correo')">Correo <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th scope="col" class="pb-4 pr-4 cursor-pointer select-none hover:text-indigo-500 transition" data-orden="cedula" onclick="ordenarPor('cedula')">Cédula <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th scope="col" class="pb-4 pr-4">Roles</th>
                                <th scope="col" class="pb-4 pr-4 cursor-pointer select-none hover:text-indigo-500 transition text-center" data-orden="activo" onclick="ordenarPor('activo')">Estado <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th scope="col" class="pb-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaUsuarios" class="text-gray-700 dark:text-gray-300"></tbody>
                    </table>
                    <div id="paginacion" class="flex justify-center items-center gap-1 mt-4"></div>
                </div>

            </main>
        </div>
    </div>

    <!-- ===== MODAL CREAR/EDITAR USUARIO ===== -->
    <div id="modalUsuario" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModal()"></div>
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h2 id="modalTitulo" class="text-xl font-bold text-gray-900 dark:text-white mb-6"></h2>
            <form id="formUsuario" class="space-y-4">
                <input type="hidden" id="id_usuario" name="id_usuario">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Nombres</label>
                        <input type="text" name="nombres" id="nombres" data-validar="requerido|letras" data-nombre="Nombres" data-min="2" data-max="30" maxlength="30" required class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" data-validar="requerido|letras" data-nombre="Apellidos" data-min="2" data-max="30" maxlength="30" required class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Cédula</label>
                    <input type="text" name="cedula" id="cedula" data-validar="cedula" data-nombre="Cédula" maxlength="20" class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Correo</label>
                    <input type="email" name="correo" id="correo" data-validar="requerido|correo" data-nombre="Correo" data-max="60" maxlength="60" required class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div id="campoContrasena">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Contraseña</label>
                    <input type="password" name="contrasena" id="contrasena" data-validar="requerido" data-nombre="Contraseña" data-min="6" data-max="128" maxlength="128" class="input-adapt w-full rounded-xl px-4 py-3 mt-2" oninput="evaluarFortaleza(this.value)">
                    <div id="fortaleza" class="mt-1 h-1.5 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700"><div id="fortalezaBarra" class="h-full rounded-full transition-all duration-300" style="width:0"></div></div>
                    <small id="fortalezaTexto" class="text-xs mt-1 block"></small>
                    <div class="mt-3">
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Confirmar Contraseña</label>
                        <input type="password" id="confirmarContrasena" class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Roles</label>
                    <div id="rolesCheckboxes" class="mt-2 space-y-2"></div>
                </div>
                <div id="vinculacionSeccion" class="hidden border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Vinculacion</label>
                    <div id="vinculacionContenido" class="mt-2 space-y-4"></div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar
                </button>
            </form>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script>
        (function() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('menuOverlay');
            const openBtn = document.getElementById('openMenuBtn');
            const closeBtn = document.getElementById('closeMenuBtn');

            function openMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (overlay) {
                    overlay.classList.remove('opacity-0', 'pointer-events-none');
                    overlay.classList.add('opacity-100', 'pointer-events-auto');
                }
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                if (!sidebar) return;
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('opacity-100', 'pointer-events-auto');
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                }
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            if (overlay) overlay.addEventListener('click', closeMenu);

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    if (sidebar && sidebar.classList.contains('translate-x-0')) {
                        sidebar.classList.remove('translate-x-0');
                        sidebar.classList.add('-translate-x-full');
                    }
                    if (overlay) {
                        overlay.classList.remove('opacity-100', 'pointer-events-auto');
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                    }
                    document.body.style.overflow = '';
                }
            });
        })();
    </script>

    <script src="assets/js/alertas.js"></script>
    <script src="assets/js/validador.js"></script>
    <script src="assets/js/tour.js"></script>
    <script>
        const tabla = document.getElementById('tablaUsuarios');
        const rolesCache = [];
        let paginaActual = 1;
        let totalRegistros = 0;
        const porPagina = 20;
        let sortCol = 'id_usuario';
        let sortDir = 'DESC';

        function esc(str) {
            if (str == null) return '';
            const d = document.createElement('div');
            d.textContent = String(str);
            return d.innerHTML;
        }

        function mostrarLoading() {
            tabla.innerHTML = '<tr><td colspan="7" class="py-8 text-center text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mr-2"></i>Cargando...</td></tr>';
        }

        function evaluarFortaleza(pass) {
            const barra = document.getElementById('fortalezaBarra');
            const texto = document.getElementById('fortalezaTexto');
            if (!barra || !texto) return;
            let puntos = 0;
            if (pass.length >= 6) puntos++;
            if (pass.length >= 10) puntos++;
            if (/[a-z]/.test(pass) && /[A-Z]/.test(pass)) puntos++;
            if (/[0-9]/.test(pass)) puntos++;
            if (/[^a-zA-Z0-9]/.test(pass)) puntos++;
            const niveles = [
                { w: '0%', bg: '#d1d5db', label: '', color: '' },
                { w: '20%', bg: '#ef4444', label: 'Muy debil', color: '#ef4444' },
                { w: '40%', bg: '#f97316', label: 'Debil', color: '#f97316' },
                { w: '60%', bg: '#eab308', label: 'Media', color: '#eab308' },
                { w: '80%', bg: '#22c55e', label: 'Fuerte', color: '#22c55e' },
                { w: '100%', bg: '#10b981', label: 'Muy fuerte', color: '#10b981' }
            ];
            const n = niveles[puntos];
            barra.style.width = n.w;
            barra.style.backgroundColor = n.bg;
            texto.textContent = n.label;
            texto.style.color = n.color;
        }

        async function cargarUsuarios(pagina) {
            if (pagina !== undefined) paginaActual = pagina;
            mostrarLoading();
            const busqueda = (document.getElementById('buscarUsuario').value || '').trim();
            const params = new URLSearchParams({
                accion: 'listarUsuarios',
                pagina: paginaActual,
                busqueda: busqueda,
                ordenar: sortCol,
                direccion: sortDir
            });
            const res = await fetch('?p=usuarios&' + params);
            const data = await res.json();
            const usuarios = data.datos || [];
            totalRegistros = data.total || 0;

            document.getElementById('totalUsuarios').textContent = totalRegistros + ' usuario' + (totalRegistros !== 1 ? 's' : '');

            tabla.innerHTML = usuarios.length ? usuarios.map(u => `
                <tr class="border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">${u.id_usuario}</td>
                    <td class="py-3 pr-4 text-gray-900 dark:text-white font-medium">${esc(u.nombres)} ${esc(u.apellidos)}</td>
                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">${esc(u.correo)}</td>
                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-400">${esc(u.cedula) || '-'}</td>
                    <td class="py-3 pr-4">${(u.roles || '').split(', ').map(r => `<span class="inline-block bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs px-2 py-1 rounded-lg mr-1 border border-indigo-200 dark:border-indigo-500/30">${esc(r)}</span>`).join('')}</td>
                    <td class="py-3 text-center">
                        <label class="toggle-switch inline-block">
                            <input type="checkbox" role="switch" aria-checked="${u.activo == 1}" ${u.activo == 1 ? 'checked' : ''} onchange="toggleEstado(${u.id_usuario}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td class="py-3 text-center whitespace-nowrap">
                        <button onclick="abrirModalEditar(${u.id_usuario})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mx-1 transition" aria-label="Editar usuario"><i class="fas fa-edit"></i></button>
                        <button onclick="resetPassword(${u.id_usuario}, '${esc(u.nombres)}')" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 mx-1 transition" aria-label="Resetear contrasena"><i class="fas fa-key"></i></button>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="7" class="py-8 text-center text-gray-400">No se encontraron usuarios.</td></tr>';

            renderPaginacion();
            actualizarIconosOrden();
        }

        function renderPaginacion() {
            const container = document.getElementById('paginacion');
            const totalPag = Math.ceil(totalRegistros / porPagina);
            if (totalPag <= 1) { container.innerHTML = ''; return; }
            let html = '';
            const btnClass = 'px-3 py-1.5 rounded-lg text-xs font-medium transition';
            for (let p = 1; p <= totalPag; p++) {
                if (totalPag > 7) {
                    if (p > 2 && p < totalPag - 1 && Math.abs(p - paginaActual) > 1) {
                        if (p === 3 || p === totalPag - 2) html += `<span class="px-1 text-gray-400">...</span>`;
                        continue;
                    }
                }
                const activa = p === paginaActual;
                html += `<button onclick="cargarUsuarios(${p})" class="${btnClass} ${activa ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-indigo-100 dark:hover:bg-gray-700'}">${p}</button>`;
            }
            container.innerHTML = html;
        }

        function ordenarPor(col) {
            if (sortCol === col) {
                sortDir = sortDir === 'DESC' ? 'ASC' : 'DESC';
            } else {
                sortCol = col;
                sortDir = 'ASC';
            }
            cargarUsuarios(1);
        }

        function actualizarIconosOrden() {
            document.querySelectorAll('th[data-orden]').forEach(th => {
                const icon = th.querySelector('i');
                if (!icon) return;
                const col = th.dataset.orden;
                if (col === sortCol) {
                    icon.className = sortDir === 'ASC' ? 'fas fa-sort-up ml-1 opacity-100' : 'fas fa-sort-down ml-1 opacity-100';
                } else {
                    icon.className = 'fas fa-sort ml-1 opacity-40';
                }
            });
        }

        let debounceTimer;
        document.getElementById('buscarUsuario').addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => cargarUsuarios(1), 300);
        });

        async function cargarRoles() {
            if (rolesCache.length) return rolesCache;
            const res = await fetch('?p=usuarios&accion=listarRoles');
            const data = await res.json();
            rolesCache.push(...data);
            return data;
        }

        function renderRolesCheckboxes(seleccionados = []) {
            const container = document.getElementById('rolesCheckboxes');
            container.innerHTML = rolesCache.map(r => `
                <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-sm cursor-pointer hover:text-gray-900 dark:hover:text-white">
                    <input type="checkbox" name="roles[]" value="${r.id_rol}" ${seleccionados.includes(String(r.id_rol)) ? 'checked' : ''} class="w-4 h-4 accent-indigo-500">
                    ${esc(r.nombre)}
                </label>
            `).join('');
        }

        async function abrirModalCrear() {
            document.getElementById('modalTitulo').textContent = 'Crear Usuario';
            document.getElementById('formUsuario').reset();
            document.getElementById('id_usuario').value = '';
            document.getElementById('campoContrasena').classList.remove('hidden');
            document.getElementById('contrasena').required = true;
            document.getElementById('contrasena').setAttribute('data-validar', 'requerido');
            evaluarFortaleza('');
            try { Validador.limpiarEstilos(document.getElementById('formUsuario')); } catch(e) {}
            await cargarRoles();
            renderRolesCheckboxes();
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

        function cerrarModal() {
            try { Validador.limpiarEstilos(document.getElementById('formUsuario')); } catch(e) {}
            document.getElementById('vinculacionSeccion').classList.add('hidden');
            document.getElementById('modalUsuario').classList.add('hidden');
        }

        document.getElementById('formUsuario').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const id = new FormData(form).get('id_usuario');
            const erroresJS = Validador.validarFormulario(form);
            if (erroresJS) {
                Swal.fire({ ...UI.config, icon: 'warning', title: 'Datos Incompletos', html: erroresJS });
                return;
            }

            if (!id) {
                const pass = document.getElementById('contrasena').value;
                const confirmar = document.getElementById('confirmarContrasena').value;
                if (pass !== confirmar) {
                    Swal.fire({ ...UI.config, icon: 'warning', title: 'Las contrasenas no coinciden.' });
                    return;
                }
            }

            const fd = new FormData(form);
            fd.append('accion', id ? 'editar' : 'guardar');

            const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({ ...UI.config, icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                cerrarModal();
                cargarUsuarios();
            } else if (data.status === 'warning') {
                const msgs = Object.values(data.errores).join('\n');
                Swal.fire({ ...UI.config, icon: 'warning', title: 'Corrige los campos', text: msgs });
            } else {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
        });

        async function toggleEstado(id, estado) {
            const label = estado ? 'activar' : 'desactivar';
            const { isConfirmed } = await Swal.fire({
                ...UI.config,
                title: `${estado ? 'Activar' : 'Desactivar'} usuario`,
                text: `¿Estas seguro de que deseas ${label} este usuario?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, ' + label,
                cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) {
                cargarUsuarios();
                return;
            }

            const fd = new FormData();
            fd.append('accion', 'toggleEstado');
            fd.append('id_usuario', id);
            fd.append('estado', estado ? '1' : '0');

            const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status !== 'success') {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
            cargarUsuarios();
        }

        async function resetPassword(id, nombre) {
            const { value: nuevaPass } = await Swal.fire({
                ...UI.config,
                title: `Resetear contrasena de ${nombre}`,
                input: 'password',
                inputLabel: 'Minimo 6 caracteres, al menos una letra y un numero',
                inputAttributes: { minlength: 6 },
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (v) => {
                    if (v.length < 6) return 'Minimo 6 caracteres';
                    if (!/[a-zA-Z]/.test(v)) return 'Debe contener al menos una letra';
                    if (!/[0-9]/.test(v)) return 'Debe contener al menos un numero';
                    return null;
                }
            });
            if (!nuevaPass) return;

            const fd = new FormData();
            fd.append('accion', 'resetPassword');
            fd.append('id_usuario', id);
            fd.append('nueva_contrasena', nuevaPass);

            const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
            const data = await res.json();
            Swal.fire({ ...UI.config, icon: data.status === 'success' ? 'success' : 'error', title: data.message, timer: 1500, showConfirmButton: false });
        }

        const vinculacionTipos = {
            atleta: { label: 'Atleta', icon: 'fa-person-running' },
            representante: { label: 'Representante', icon: 'fa-user-tie' },
            entrenador: { label: 'Entrenador', icon: 'fa-whistle' }
        };
        let vinculacionDebounce = {};

        async function abrirModalEditar(id) {
            const res = await fetch(`?p=usuarios&accion=obtenerUsuario&id=${id}`);
            const u = await res.json();
            if (!u) return;

            document.getElementById('modalTitulo').textContent = 'Editar Usuario';
            document.getElementById('id_usuario').value = u.id_usuario;
            document.getElementById('nombres').value = u.nombres;
            document.getElementById('apellidos').value = u.apellidos;
            document.getElementById('cedula').value = u.cedula || '';
            document.getElementById('correo').value = u.correo;
            document.getElementById('campoContrasena').classList.add('hidden');
            document.getElementById('contrasena').required = false;
            document.getElementById('contrasena').removeAttribute('data-validar');

            await cargarRoles();
            const seleccionados = (u.roles_ids || '').split(',').filter(Boolean);
            renderRolesCheckboxes(seleccionados);

            renderVinculacion(u.id_usuario, u.roles_ids || '');
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

        async function renderVinculacion(idUsuario, rolesIds) {
            const seccion = document.getElementById('vinculacionSeccion');
            const contenido = document.getElementById('vinculacionContenido');
            const roles = rolesIds.split(',').filter(Boolean).map(Number);
            const rolesData = rolesCache.find ? rolesCache : await cargarRoles();

            const rolATipo = {'atleta': 'atleta', 'representante': 'representante', 'entrenador': 'entrenador', 'medico': null, 'administrador': null};
            const tiposVisibles = [];
            for (const rid of roles) {
                const r = rolesData.find(x => x.id_rol === rid);
                if (r) {
                    const tipo = rolATipo[r.nombre.toLowerCase()];
                    if (tipo && !tiposVisibles.includes(tipo)) tiposVisibles.push(tipo);
                }
            }
            if (tiposVisibles.length === 0) {
                seccion.classList.add('hidden');
                return;
            }
            seccion.classList.remove('hidden');

            let html = '';
            for (const tipo of tiposVisibles) {
                const info = vinculacionTipos[tipo];
                html += `<div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas ${info.icon} text-indigo-500"></i>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">${info.label}</span>
                    </div>
                    <div id="vinculacion_${tipo}_chips" class="mb-2 flex flex-wrap gap-1"></div>
                    <div class="relative">
                        <input type="text" id="vinculacion_${tipo}_input" placeholder="Buscar ${info.label.toLowerCase()}..." class="input-adapt w-full rounded-lg px-3 py-2 text-sm pr-8" autocomplete="off">
                        <i class="fas fa-search absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                    <div id="vinculacion_${tipo}_resultados" class="mt-1 hidden border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-lg max-h-40 overflow-y-auto"></div>
                </div>`;
            }
            contenido.innerHTML = html;

            for (const tipo of tiposVisibles) {
                cargarVinculaciones(idUsuario, tipo);
                const input = document.getElementById(`vinculacion_${tipo}_input`);
                if (input) {
                    input.addEventListener('input', function() {
                        const t = tipo;
                        clearTimeout(vinculacionDebounce[t]);
                        vinculacionDebounce[t] = setTimeout(() => buscarEntidadParaVincular(t, idUsuario), 350);
                    });
                }
            }
        }

        async function cargarVinculaciones(idUsuario, tipo) {
            const chips = document.getElementById(`vinculacion_${tipo}_chips`);
            if (!chips) return;
            try {
                const res = await fetch(`?p=usuarios&accion=obtenerVinculaciones&id_usuario=${idUsuario}&tipo=${tipo}`);
                const data = await res.json();
                if (!data || data.length === 0) { chips.innerHTML = '<span class="text-xs text-gray-400">Sin vinculacion</span>'; return; }
                chips.innerHTML = data.map(v => `
                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-500/30">
                        ${esc(v.nombres)} ${esc(v.apellidos)}
                        <button type="button" onclick="desvincularEntidad(${idUsuario}, '${tipo}')" class="ml-1 text-red-400 hover:text-red-600 dark:hover:text-red-300" aria-label="Desvincular"><i class="fas fa-times text-[10px]"></i></button>
                    </span>`).join('');
            } catch(e) { chips.innerHTML = '<span class="text-xs text-red-400">Error cargando</span>'; }
        }

        async function buscarEntidadParaVincular(tipo, idUsuario) {
            const input = document.getElementById(`vinculacion_${tipo}_input`);
            const resultados = document.getElementById(`vinculacion_${tipo}_resultados`);
            if (!input || !resultados) return;
            const texto = input.value.trim();
            if (texto.length < 2) { resultados.classList.add('hidden'); return; }
            try {
                const res = await fetch(`?p=usuarios&accion=buscarEntidad&texto=${encodeURIComponent(texto)}&tipo=${tipo}`);
                const data = await res.json();
                if (!data || data.length === 0) { resultados.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">Sin resultados</div>'; resultados.classList.remove('hidden'); return; }
                resultados.innerHTML = data.map(e => `
                    <button type="button" onclick="vincularEntidad(${idUsuario}, '${tipo}', ${e.id})" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition">${esc(e.nombres)} ${esc(e.apellidos)}</button>`).join('');
                resultados.classList.remove('hidden');
            } catch(e) { resultados.classList.add('hidden'); }
        }

        async function vincularEntidad(idUsuario, tipo, idEntidad) {
            const fd = new FormData();
            fd.append('accion', 'vincularEntidad');
            fd.append('id_usuario', idUsuario);
            fd.append('tipo', tipo);
            fd.append('id_entidad', idEntidad);
            try {
                const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.status === 'success') {
                    const input = document.getElementById(`vinculacion_${tipo}_input`);
                    if (input) input.value = '';
                    const resultados = document.getElementById(`vinculacion_${tipo}_resultados`);
                    if (resultados) resultados.classList.add('hidden');
                    cargarVinculaciones(idUsuario, tipo);
                } else {
                    Swal.fire({ ...UI.config, icon: 'warning', title: data.message });
                }
            } catch(e) {}
        }

        async function desvincularEntidad(idUsuario, tipo) {
            const fd = new FormData();
            fd.append('accion', 'desvincularEntidad');
            fd.append('id_usuario', idUsuario);
            fd.append('tipo', tipo);
            try {
                const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.status === 'success') {
                    cargarVinculaciones(idUsuario, tipo);
                } else {
                    Swal.fire({ ...UI.config, icon: 'warning', title: data.message });
                }
            } catch(e) {}
        }

        try { Validador.vincularTiempoReal(document.getElementById('formUsuario')); } catch(e) {}
        cargarUsuarios();
    </script>
</body>
</html>