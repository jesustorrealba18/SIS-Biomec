<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" type="image/png" href="assets/img/logo_nadador.png">
    <title>Gestion de Usuarios | SGRD</title>
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
            <h1 class="text-2xl font-bold text-white">Gestion de Usuarios</h1>
            <button onclick="abrirModalCrear()" class="gradiente-boton px-6 py-3 rounded-xl font-bold text-white hover:scale-[1.02] transition">
                <i class="fas fa-plus mr-2"></i>Nuevo Usuario
            </button>
        </header>

        <div class="tarjeta p-6 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase border-b border-gray-800">
                    <tr>
                        <th class="pb-4 pr-4">#</th>
                        <th class="pb-4 pr-4">Nombre</th>
                        <th class="pb-4 pr-4">Correo</th>
                        <th class="pb-4 pr-4">Cedula</th>
                        <th class="pb-4 pr-4">Roles</th>
                        <th class="pb-4 pr-4 text-center">Estado</th>
                        <th class="pb-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios" class="text-gray-300"></tbody>
            </table>
        </div>
    </main>

    <div id="modalUsuario" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/60" onclick="cerrarModal()"></div>
        <div class="relative bg-[#161430] border border-[#252345] rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <button onclick="cerrarModal()" class="absolute top-4 right-4 text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
            <h2 id="modalTitulo" class="text-xl font-bold text-white mb-6"></h2>
            <form id="formUsuario" class="space-y-4">
                <input type="hidden" id="id_usuario" name="id_usuario">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Nombres</label>
                        <input type="text" name="nombres" id="nombres" required class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" required class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Cedula</label>
                    <input type="text" name="cedula" id="cedula" class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Correo</label>
                    <input type="email" name="correo" id="correo" required class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div id="campoContrasena">
                    <label class="text-xs font-bold text-gray-400 uppercase">Contrasena</label>
                    <input type="password" name="contrasena" id="contrasena" class="input-dark w-full rounded-xl px-4 py-3 mt-2">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Roles</label>
                    <div id="rolesCheckboxes" class="mt-2 space-y-2"></div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition">
                    Guardar
                </button>
            </form>
        </div>
    </div>

    <script src="assets/js/alertas.js"></script>
    <script>
        const tabla = document.getElementById('tablaUsuarios');
        const rolesCache = [];

        async function cargarUsuarios() {
            const res = await fetch('?p=usuarios&accion=listarUsuarios');
            const data = await res.json();
            tabla.innerHTML = data.map((u, i) => `
                <tr class="border-b border-gray-800/50 hover:bg-white/5">
                    <td class="py-3 pr-4">${u.id_usuario}</td>
                    <td class="py-3 pr-4 text-white font-medium">${u.nombres} ${u.apellidos}</td>
                    <td class="py-3 pr-4">${u.correo}</td>
                    <td class="py-3 pr-4">${u.cedula || '-'}</td>
                    <td class="py-3 pr-4">${(u.roles || '').split(', ').map(r => `<span class="inline-block bg-indigo-500/20 text-indigo-400 text-xs px-2 py-1 rounded-lg mr-1">${r}</span>`).join('')}</td>
                    <td class="py-3 text-center">
                        <label class="toggle-switch inline-block">
                            <input type="checkbox" ${u.activo == 1 ? 'checked' : ''} onchange="toggleEstado(${u.id_usuario}, this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td class="py-3 text-center">
                        <button onclick="abrirModalEditar(${u.id_usuario})" class="text-indigo-400 hover:text-indigo-300 mx-1" title="Editar"><i class="fas fa-edit"></i></button>
                        <button onclick="resetPassword(${u.id_usuario}, '${u.nombres}')" class="text-yellow-400 hover:text-yellow-300 mx-1" title="Resetear contrasena"><i class="fas fa-key"></i></button>
                        <button onclick="eliminarUsuario(${u.id_usuario}, '${u.nombres} ${u.apellidos}')" class="text-red-400 hover:text-red-300 mx-1" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `).join('');
        }

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
                <label class="flex items-center gap-2 text-gray-300 text-sm cursor-pointer">
                    <input type="checkbox" name="roles[]" value="${r.id_rol}" ${seleccionados.includes(String(r.id_rol)) ? 'checked' : ''} class="w-4 h-4 accent-indigo-500">
                    ${r.nombre}
                </label>
            `).join('');
        }

        async function abrirModalCrear() {
            document.getElementById('modalTitulo').textContent = 'Crear Usuario';
            document.getElementById('formUsuario').reset();
            document.getElementById('id_usuario').value = '';
            document.getElementById('campoContrasena').classList.remove('hidden');
            document.getElementById('contrasena').required = true;
            await cargarRoles();
            renderRolesCheckboxes();
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

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

            await cargarRoles();
            const seleccionados = (u.roles_ids || '').split(',').filter(Boolean);
            renderRolesCheckboxes(seleccionados);
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

        function cerrarModal() {
            document.getElementById('modalUsuario').classList.add('hidden');
        }

        document.getElementById('formUsuario').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const id = fd.get('id_usuario');
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
            const fd = new FormData();
            fd.append('accion', 'toggleEstado');
            fd.append('id_usuario', id);
            fd.append('estado', estado ? '1' : '0');

            const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.status !== 'success') {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
                cargarUsuarios();
            }
        }

        async function resetPassword(id, nombre) {
            const { value: nuevaPass } = await Swal.fire({
                ...UI.config,
                title: `Resetear contrasena de ${nombre}`,
                input: 'password',
                inputLabel: 'Nueva contrasena (minimo 6 caracteres)',
                inputAttributes: { minlength: 6 },
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (v) => v.length < 6 ? 'Minimo 6 caracteres' : null
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

        async function eliminarUsuario(id, nombre) {
            const { isConfirmed } = await Swal.fire({
                ...UI.config,
                title: `Eliminar a ${nombre}`,
                text: 'Esta accion eliminara permanentemente el usuario y sus asignaciones de roles. No se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'Cancelar'
            });
            if (!isConfirmed) return;

            const fd = new FormData();
            fd.append('accion', 'eliminar');
            fd.append('id_usuario', id);

            const res = await fetch('?p=usuarios', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                Swal.fire({ ...UI.config, icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                cargarUsuarios();
            } else {
                Swal.fire({ ...UI.config, icon: 'error', title: data.message });
            }
        }

        cargarUsuarios();
    </script>
</body>
</html>
