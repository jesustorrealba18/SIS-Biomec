<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Roles y Permisos | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Inter', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 10px; }
        .toggle-switch { position: relative; width: 44px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #374151; border-radius: 24px; transition: 0.3s; }
        .toggle-slider:before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
        .toggle-switch input:checked + .toggle-slider { background: #10b981; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
    </style>
</head>
<body class="flex min-h-screen">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white">Roles y Permisos</h1>
            <button onclick="abrirModalCrear()" class="gradiente-boton px-6 py-3 rounded-xl font-bold text-white hover:scale-[1.02] transition">
                <i class="fas fa-plus mr-2"></i>Nuevo Rol
            </button>
        </header>

        <div class="tarjeta p-6 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase border-b border-gray-800">
                    <tr>
                        <th class="pb-4 pr-4">Rol</th>
                        <th class="pb-4 pr-4">Descripcion</th>
                        <th class="pb-4 pr-4 text-center">Permisos</th>
                        <th class="pb-4 pr-4 text-center">Estado</th>
                        <th class="pb-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaRoles" class="text-gray-300"></tbody>
            </table>
        </div>
    </main>

    <div id="modalRol" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModal()"></div>
        <div class="relative bg-[#161430] border border-[#252345] rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            <h2 id="modalTitulo" class="text-xl font-bold text-white mb-6"></h2>
            <form id="formRol" class="space-y-4">
                <input type="hidden" id="id_rol" name="id_rol">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Nombre del Rol</label>
                    <input type="text" name="nombre" id="nombre" required class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Descripcion</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="input-dark w-full rounded-xl px-4 py-3 mt-2 resize-none"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar
                </button>
            </form>
        </div>
    </div>

    <div id="modalPermisos" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModalPermisos()"></div>
        <div class="relative bg-[#161430] border border-[#252345] rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <button onclick="cerrarModalPermisos()" class="absolute top-4 right-4 text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            <h2 id="modalPermisosTitulo" class="text-xl font-bold text-white mb-6"></h2>
            <form id="formPermisos" class="space-y-4">
                <input type="hidden" id="permisos_id_rol" name="id_rol">
                <div id="permisosAgrupados" class="space-y-4"></div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar Permisos
                </button>
            </form>
        </div>
    </div>

    <script src="assets/js/alertas.js"></script>
    <script>
        const tabla = document.getElementById('tablaRoles');
        let permisosCache = {};

        async function cargarRoles() {
            const res = await fetch('?p=roles&accion=listarRoles');
            const data = await res.json();
            tabla.innerHTML = data.map(r => `
                <tr class="border-b border-gray-800/50 hover:bg-white/5">
                    <td class="py-3 pr-4 text-white font-medium">${r.nombre}</td>
                    <td class="py-3 pr-4 text-gray-400">${r.descripcion || '-'}</td>
                    <td class="py-3 pr-4 text-center">
                        <span class="bg-indigo-500/20 text-indigo-400 text-xs px-3 py-1 rounded-lg">${r.total_permisos}</span>
                    </td>
                    <td class="py-3 text-center">
                        <label class="toggle-switch inline-block">
                            <input type="checkbox" ${r.activo == 1 ? 'checked' : ''} onchange="toggleEstado(${r.id_rol}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td class="py-3 text-center">
                        <button onclick="abrirModalEditar(${r.id_rol})" class="text-indigo-400 hover:text-indigo-300 mx-1" title="Editar"><i class="fas fa-edit"></i></button>
                        <button onclick="abrirModalPermisos(${r.id_rol}, '${r.nombre}')" class="text-green-400 hover:text-green-300 mx-1" title="Gestionar permisos"><i class="fas fa-shield-alt"></i></button>
                        <button onclick="eliminarRol(${r.id_rol}, '${r.nombre}')" class="text-red-400 hover:text-red-300 mx-1" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
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
                <div class="tarjeta p-4">
                    <h3 class="text-sm font-bold text-white uppercase mb-3">${modulo}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        ${perms.map(p => `
                            <label class="flex items-center gap-2 text-gray-300 text-xs cursor-pointer">
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
                text: 'Se eliminaran las asignaciones de permisos y de usuarios a este rol. No se puede deshacer.',
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
