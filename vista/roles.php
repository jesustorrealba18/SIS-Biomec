<?php
// Declaramos la variable para que el menú sepa qué botón iluminar
$pagina = 'roles';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Roles y Permisos | SGRD</title>
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
                $tituloPagina = "Roles y Permisos";
                $tituloPaginaResponsive = "Roles";
                $iconModulo = "fas fa-shield-alt";
                include 'vista/complementos/header.php';
            ?>

            <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-[1600px] w-full mx-auto space-y-6">

                <!-- Encabezado -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-[#161430] p-6 rounded-2xl border border-gray-200 dark:border-[#252345] transition-colors duration-300">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i class="fas fa-shield-alt text-indigo-500"></i> Roles y Permisos
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Gestión de roles, permisos y asignaciones de seguridad.</p>
                    </div>
                    <button onclick="abrirModalCrear()" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs tracking-wider uppercase shadow-lg shadow-indigo-500/20 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-plus-circle text-sm"></i> Nuevo Rol
                    </button>
                </div>

                <!-- Tabla -->
                <div class="bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-6 overflow-x-auto shadow-2xl transition-colors duration-300">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="pb-4 pr-4">Rol</th>
                                <th class="pb-4 pr-4">Descripción</th>
                                <th class="pb-4 pr-4 text-center">Permisos</th>
                                <th class="pb-4 pr-4 text-center">Estado</th>
                                <th class="pb-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRoles" class="text-gray-700 dark:text-gray-300"></tbody>
                    </table>
                </div>

            </main>
        </div>
    </div>

    <!-- ===== MODAL CREAR/EDITAR ROL ===== -->
    <div id="modalRol" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModal()"></div>
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h2 id="modalTitulo" class="text-xl font-bold text-gray-900 dark:text-white mb-6"></h2>
            <form id="formRol" class="space-y-4">
                <input type="hidden" id="id_rol" name="id_rol">
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Nombre del Rol</label>
                    <input type="text" name="nombre" id="nombre" required class="input-adapt w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="input-adapt w-full rounded-xl px-4 py-3 mt-2 resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar
                </button>
            </form>
        </div>
    </div>

    <!-- ===== MODAL GESTIONAR PERMISOS ===== -->
    <div id="modalPermisos" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModalPermisos()"></div>
        <div class="relative bg-white dark:bg-[#161430] border border-gray-200 dark:border-[#252345] rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto transition-colors duration-300">
            <button onclick="cerrarModalPermisos()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h2 id="modalPermisosTitulo" class="text-xl font-bold text-gray-900 dark:text-white mb-6"></h2>
            <form id="formPermisos" class="space-y-4">
                <input type="hidden" id="permisos_id_rol" name="id_rol">
                <div id="permisosAgrupados" class="space-y-4"></div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar Permisos
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
    <script>
        // =================================================================
        // CONFIGURACIÓN DE UI PARA SWEETALERT (se usa UI.config de alertas.js)
        // =================================================================

        const tabla = document.getElementById('tablaRoles');
        let permisosCache = {};

        async function cargarRoles() {
            const res = await fetch('?p=roles&accion=listarRoles');
            const data = await res.json();
            tabla.innerHTML = data.map(r => `
                <tr class="border-b border-gray-200 dark:border-gray-800/50 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <td class="py-3 pr-4 text-gray-900 dark:text-white font-medium">${r.nombre}</td>
                    <td class="py-3 pr-4 text-gray-500 dark:text-gray-400">${r.descripcion || '-'}</td>
                    <td class="py-3 pr-4 text-center">
                        <span class="bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs px-3 py-1 rounded-lg border border-indigo-200 dark:border-indigo-500/30">${r.total_permisos}</span>
                    </td>
                    <td class="py-3 text-center">
                        <label class="toggle-switch inline-block">
                            <input type="checkbox" ${r.activo == 1 ? 'checked' : ''} onchange="toggleEstado(${r.id_rol}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td class="py-3 text-center">
                        <button onclick="abrirModalEditar(${r.id_rol})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mx-1 transition" title="Editar"><i class="fas fa-edit"></i></button>
                        <button onclick="abrirModalPermisos(${r.id_rol}, '${r.nombre}')" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 mx-1 transition" title="Gestionar permisos"><i class="fas fa-shield-alt"></i></button>
                        <button onclick="eliminarRol(${r.id_rol}, '${r.nombre}')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 mx-1 transition" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        async function cargarPermisos() {
            if (Object.keys(permisosCache).length) return permisosCache;
            const res = await fetch('?p=roles&accion=listarPermisos');
            permisosCache = await res.json();
            return permisosCache;
        }

        function abrirModalCrear() {
            document.getElementById('modalTitulo').textContent = 'Crear Rol';
            document.getElementById('formRol').reset();
            document.getElementById('id_rol').value = '';
            document.getElementById('modalRol').classList.remove('hidden');
        }

        async function abrirModalEditar(id) {
            const res = await fetch(`?p=roles&accion=obtenerRol&id=${id}`);
            const data = await res.json();
            if (!data.rol) return;

            document.getElementById('modalTitulo').textContent = 'Editar Rol';
            document.getElementById('id_rol').value = data.rol.id_rol;
            document.getElementById('nombre').value = data.rol.nombre;
            document.getElementById('descripcion').value = data.rol.descripcion || '';
            document.getElementById('modalRol').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modalRol').classList.add('hidden');
        }

        document.getElementById('formRol').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const id = fd.get('id_rol');
            fd.append('accion', id ? 'editar' : 'guardar');

            const res = await fetch('?p=roles', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({ ...UI.config, icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                cerrarModal();
                cargarRoles();
            } else if (data.status === 'warning') {
                const msgs = Object.values(data.errores).join('\n');
                Swal.fire({ ...UI.config, icon: 'warning', title: 'Corrige los campos', text: msgs });
            } else {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
        });

        async function abrirModalPermisos(id, nombre) {
            const res = await fetch(`?p=roles&accion=obtenerRol&id=${id}`);
            const data = await res.json();
            const seleccionados = data.permisos || [];

            await cargarPermisos();

            document.getElementById('modalPermisosTitulo').textContent = `Permisos: ${nombre}`;
            document.getElementById('permisos_id_rol').value = id;

            const container = document.getElementById('permisosAgrupados');
            container.innerHTML = Object.entries(permisosCache).map(([modulo, perms]) => `
                <div class="tarjeta p-4 transition-colors duration-300">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase mb-3">${modulo}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        ${perms.map(p => `
                            <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300 text-xs cursor-pointer hover:text-gray-900 dark:hover:text-white">
                                <input type="checkbox" name="permisos[]" value="${p.id_permiso}" ${seleccionados.includes(p.id_permiso) ? 'checked' : ''} class="w-3.5 h-3.5 accent-indigo-500">
                                ${p.accion}
                            </label>
                        `).join('')}
                    </div>
                </div>
            `).join('');

            document.getElementById('modalPermisos').classList.remove('hidden');
        }

        function cerrarModalPermisos() {
            document.getElementById('modalPermisos').classList.add('hidden');
        }

        document.getElementById('formPermisos').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            fd.append('accion', 'guardarPermisos');

            const res = await fetch('?p=roles', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({ ...UI.config, icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                cerrarModalPermisos();
                cargarRoles();
            } else {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
        });

        async function toggleEstado(id, estado) {
            const fd = new FormData();
            fd.append('accion', 'toggleEstado');
            fd.append('id_rol', id);
            fd.append('estado', estado ? '1' : '0');

            const res = await fetch('?p=roles', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status !== 'success') {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
                cargarRoles();
            }
        }

        async function eliminarRol(id, nombre) {
            const { isConfirmed } = await Swal.fire({
                ...UI.config,
                title: `Eliminar rol "${nombre}"`,
                text: 'Se eliminarán las asignaciones de permisos y de usuarios a este rol. No se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) return;

            const fd = new FormData();
            fd.append('accion', 'eliminar');
            fd.append('id_rol', id);

            const res = await fetch('?p=roles', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire({ ...UI.config, icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                cargarRoles();
            } else {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
        }

        cargarRoles();
    </script>
</body>
</html>