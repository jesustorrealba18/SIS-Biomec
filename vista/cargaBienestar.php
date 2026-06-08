<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo de Carga y Bienestar | SGRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0f0d23; color: #a0a0c0; font-family: 'Segoe UI', sans-serif; }
        .tarjeta { background-color: #161430; border: 1px solid #252345; border-radius: 15px; }
        .input-dark { background: #0f0d23; border: 1px solid #252345; color: white; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #6366f1; box-shadow: 0 0 10px rgba(99, 102, 241, 0.2); outline: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f0d23; }
        ::-webkit-scrollbar-thumb { background: #252345; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4f46e5; }
    </style>
</head>
<body class="flex min-h-screen bg-[#0f0d23]">

    <?php include RAIZ . 'vista/complementos/menu.php'; ?>

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-20">
            <h1 class="text-2xl font-bold text-white tracking-wide flex items-center gap-2">
                <i class="fas fa-heartbeat text-indigo-500"></i> Monitoreo de Carga y Bienestar (RF-11)
            </h1>
            <div class="flex items-center gap-6">
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Notificaciones
                    </div>
                </div>
                <div class="relative group flex items-center justify-center w-32 h-10 transition-all duration-300 cursor-pointer">
                    <div class="absolute inset-0 flex items-center justify-center transition-all duration-300 group-hover:opacity-0 group-hover:scale-50 text-gray-400">
                        <i class="fas fa-question-circle text-xl"></i>
                        <span class="absolute top-2 right-12 bg-red-500 w-2 h-2 rounded-full border border-[#0f0d23]"></span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 text-white font-bold text-xs uppercase tracking-tighter whitespace-nowrap">
                        Guía de ayuda
                    </div>
                </div>
                <div class="flex items-center gap-3 border-l border-gray-700 pl-6">
                    <div class="text-right mr-2">
                        <p class="text-sm text-white font-medium"><?php echo $_SESSION['nombre']; ?></p>
                        <a href="?p=salir" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-widest transition">
                            Cerrar Sesión <i class="fas fa-sign-out-alt ml-1"></i>
                        </a>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['nombre']; ?>&background=4f46e5&color=fff" 
                         class="w-10 h-10 rounded-full border-2 border-indigo-500 shadow-lg shadow-indigo-500/20">
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div>
                <p class="text-sm text-gray-400 mt-1">Registro diario de percepción de esfuerzo (RPE), calidad de sueño, fatiga y observaciones médicas.</p>
            </div>
            <?php if (\GrupoProyecto\SisBiomec\seguridad\Autorizacion::verificar('cargaBienestar', 'registrar')): ?>
            <button id="btnAbrirRegistro" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-5 py-3 rounded-xl transition duration-200 flex items-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95 cursor-pointer">
                <i class="fas fa-plus"></i> REGISTRAR EVENTO
            </button>
            <?php endif; ?>
        </div>

        <!-- Formulario de registro (oculto inicialmente) -->
        <div id="formRegistroContainer" class="tarjeta p-6 mb-8 hidden transition-all duration-300">
            <div class="flex justify-between items-center border-b border-white/5 pb-3 mb-4">
                <h3 class="text-lg font-semibold text-white"><i class="fas fa-edit mr-2"></i> Nuevo Registro de Bienestar</h3>
                <button id="btnCerrarRegistro" class="text-gray-400 hover:text-white transition"><i class="fas fa-times"></i></button>
            </div>
            <form id="formRegistro" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Atleta *</label>
                    <select name="id_atleta" id="id_atleta" class="input-dark p-3 rounded-xl w-full" required>
                        <option value="">Seleccionar Atleta...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Tipo de Evento *</label>
                    <select name="tipo_evento" class="input-dark p-3 rounded-xl w-full" required>
                        <option value="Fatiga">Fatiga</option>
                        <option value="Lesion">Lesión</option>
                        <option value="Recuperacion">Recuperación</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Fecha *</label>
                    <input type="date" name="fecha" max="<?php echo date('Y-m-d'); ?>" class="input-dark p-3 rounded-xl w-full" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">RPE (1-10)</label>
                    <input type="number" name="rpe" min="1" max="10" step="1" class="input-dark p-3 rounded-xl w-full" placeholder="Ej: 7">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Calidad Sueño (1-10)</label>
                    <input type="number" name="calidad_sueno" min="1" max="10" step="1" class="input-dark p-3 rounded-xl w-full" placeholder="Ej: 8">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Nivel Fatiga (1-10)</label>
                    <input type="number" name="nivel_fatiga" min="1" max="10" step="1" class="input-dark p-3 rounded-xl w-full" placeholder="Ej: 5">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Descripción / Observaciones</label>
                    <textarea name="descripcion" rows="3" class="input-dark p-3 rounded-xl w-full" placeholder="Detalles adicionales..."></textarea>
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold uppercase text-xs tracking-wider">
                        GUARDAR EVENTO <i class="fas fa-save ml-2"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de historial -->
        <div class="tarjeta p-6">
            <div class="flex items-center gap-2 border-b border-white/5 pb-3 mb-4">
                <i class="fas fa-history text-indigo-400"></i>
                <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Historial de Carga y Bienestar</h3>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-2">Seleccionar Atleta</label>
                <select id="filtroAtletaHistorial" class="input-dark p-3 rounded-xl w-full md:w-1/2">
                    <option value="">-- Elija un atleta para ver su historial --</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-indigo-400 border-b border-white/5 uppercase text-xs tracking-widest">
                            <th class="p-3">Fecha</th>
                            <th class="p-3">Tipo</th>
                            <th class="p-3">RPE</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaHistorial">
                        <tr><td colspan="5" class="text-center p-4 text-gray-500">Seleccione un atleta para ver su historial</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal de Edición -->
    <div id="modalEdit" class="fixed inset-0 bg-[#060512]/90 backdrop-blur-xl hidden flex items-center justify-center p-4 z-50">
        <div class="tarjeta w-full max-w-lg p-8">
            <div class="flex justify-between items-center border-b border-white/5 pb-3 mb-4">
                <h3 class="text-lg font-bold text-white">Editar Registro</h3>
                <button onclick="cerrarModalEdit()" class="text-gray-400 hover:text-white transition"><i class="fas fa-times"></i></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_evento" id="edit_id_evento">
                <input type="hidden" name="accion" value="editar">
                <div class="mb-4">
                    <label class="block text-sm text-red-400 mb-2 font-semibold">Justificación del Cambio <span class="text-red-500">*</span></label>
                    <input type="text" name="justificacion_cambio" id="edit_justificacion" class="input-dark p-3 rounded-xl w-full" required placeholder="Ej: Corrección de valor reportado por el atleta">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">RPE (1-10)</label>
                    <input type="number" name="rpe" id="edit_rpe" min="1" max="10" class="input-dark p-3 rounded-xl w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">Calidad Sueño (1-10)</label>
                    <input type="number" name="calidad_sueno" id="edit_calidad_sueno" min="1" max="10" class="input-dark p-3 rounded-xl w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">Nivel Fatiga (1-10)</label>
                    <input type="number" name="nivel_fatiga" id="edit_nivel_fatiga" min="1" max="10" class="input-dark p-3 rounded-xl w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-300 mb-2">Descripción</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="3" class="input-dark p-3 rounded-xl w-full"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="cerrarModalEdit()" class="px-4 py-2 text-gray-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-bold uppercase text-xs">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/validador.js"></script>
    <script src="assets/js/utilidades.js"></script>
    <script src="assets/js/alertas.js"></script>
    <script>
        // Definimos constantes y funciones globales necesarias
        const API_URL = 'index.php?p=cargaBienestar';

        // Cargar atletas en los selects
        async function cargarAtletas() {
            try {
                const response = await fetch(`${API_URL}&accion=listarAtletasSelect`);
                const atletas = await response.json();
                const selectRegistro = document.querySelector('#id_atleta');
                const selectHistorial = document.querySelector('#filtroAtletaHistorial');
                if (selectRegistro) {
                    selectRegistro.innerHTML = '<option value="">Seleccionar Atleta...</option>';
                    atletas.forEach(a => {
                        selectRegistro.innerHTML += `<option value="${a.id_atleta}">${a.nombres} ${a.apellidos} (${a.cedula})</option>`;
                    });
                }
                if (selectHistorial) {
                    selectHistorial.innerHTML = '<option value="">-- Elija un atleta para ver su historial --</option>';
                    atletas.forEach(a => {
                        selectHistorial.innerHTML += `<option value="${a.id_atleta}">${a.nombres} ${a.apellidos} (${a.cedula})</option>`;
                    });
                }
            } catch (error) {
                console.error('Error cargando atletas:', error);
            }
        }

        // Cargar tabla de historial
        async function cargarTablaHistorial(idAtleta) {
            if (!idAtleta) {
                document.getElementById('tablaHistorial').innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">Seleccione un atleta para ver su historial</td></tr>';
                return;
            }
            try {
                const response = await fetch(`${API_URL}&accion=listarHistorial&id_atleta=${idAtleta}`);
                const data = await response.json();
                const tbody = document.getElementById('tablaHistorial');
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-gray-500">No hay registros para este atleta</td></tr>';
                    return;
                }
                let html = '';
                data.forEach(item => {
                    html += `<tr class="border-b border-white/5">
                        <td class="p-3">${item.fecha}</td>
                        <td class="p-3">${item.tipo_evento}</td>
                        <td class="p-3">${item.rpe}</td>
                        <td class="p-3"><span class="text-${item.estado === 'Activo' ? 'green' : 'red'}-400">${item.estado}</span></td>
                        <td class="p-3">
                            <button onclick="abrirEdicion(${item.id_evento})" class="text-indigo-400 hover:text-white mr-2"><i class="fas fa-edit"></i></button>
                            <button onclick="anularEvento(${item.id_evento})" class="text-red-400 hover:text-white"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } catch (error) {
                console.error(error);
            }
        }

        // Registrar evento
        async function registrarEvento(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.append('accion', 'registrar');
            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    UI.exito('Éxito', result.message);
                    form.reset();
                    document.getElementById('formRegistroContainer').classList.add('hidden');
                    const atletaId = document.getElementById('filtroAtletaHistorial').value;
                    if (atletaId) cargarTablaHistorial(atletaId);
                } else if (result.status === 'warning') {
                    UI.error('Validación', Object.values(result.errores).join('<br>'));
                } else {
                    UI.error('Error', result.message || 'Error al guardar');
                }
            } catch (error) {
                UI.error('Error', 'No se pudo conectar con el servidor');
            }
        }

        // Abrir modal de edición
        async function abrirEdicion(id) {
            try {
                const response = await fetch(`${API_URL}&accion=obtenerEvento&id_evento=${id}`);
                const evento = await response.json();
                if (evento.status === 'error') {
                    UI.error('Error', evento.message);
                    return;
                }
                document.getElementById('edit_id_evento').value = evento.id_evento;
                document.getElementById('edit_rpe').value = evento.rpe;
                document.getElementById('edit_calidad_sueno').value = evento.calidad_sueno;
                document.getElementById('edit_nivel_fatiga').value = evento.nivel_fatiga;
                document.getElementById('edit_descripcion').value = evento.descripcion || '';
                document.getElementById('edit_justificacion').value = '';
                document.getElementById('modalEdit').classList.remove('hidden');
            } catch (error) {
                UI.error('Error', 'No se pudo cargar el evento');
            }
        }

        function cerrarModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

        // Enviar edición
        async function enviarEdicion(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            if (!formData.get('justificacion_cambio')) {
                UI.error('Validación', 'La justificación es obligatoria');
                return;
            }
            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    UI.exito('Éxito', result.message);
                    cerrarModalEdit();
                    const atletaId = document.getElementById('filtroAtletaHistorial').value;
                    if (atletaId) cargarTablaHistorial(atletaId);
                } else {
                    UI.error('Error', result.message);
                }
            } catch (error) {
                UI.error('Error', 'Error de conexión');
            }
        }

        // Anular evento con justificación
        async function anularEvento(id) {
            const { value: justificacion } = await Swal.fire({
                title: 'Anular Registro',
                input: 'textarea',
                inputLabel: 'Justificación obligatoria',
                inputPlaceholder: 'Explique el motivo de la anulación...',
                inputAttributes: { required: true },
                showCancelButton: true,
                confirmButtonText: 'Anular',
                cancelButtonText: 'Cancelar',
                preConfirm: (text) => {
                    if (!text || !text.trim()) {
                        Swal.showValidationMessage('La justificación es requerida');
                    }
                    return text;
                }
            });
            if (!justificacion) return;
            const formData = new FormData();
            formData.append('accion', 'anular');
            formData.append('id_evento', id);
            formData.append('justificacion_cambio', justificacion);
            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    UI.exito('Anulado', result.message);
                    const atletaId = document.getElementById('filtroAtletaHistorial').value;
                    if (atletaId) cargarTablaHistorial(atletaId);
                } else {
                    UI.error('Error', result.message);
                }
            } catch (error) {
                UI.error('Error', 'Error de conexión');
            }
        }

        // Mostrar/ocultar formulario de registro
        document.addEventListener('DOMContentLoaded', () => {
            cargarAtletas();
            const btnAbrir = document.getElementById('btnAbrirRegistro');
            const contenedor = document.getElementById('formRegistroContainer');
            const btnCerrar = document.getElementById('btnCerrarRegistro');
            if (btnAbrir) {
                btnAbrir.addEventListener('click', () => contenedor.classList.remove('hidden'));
            }
            if (btnCerrar) {
                btnCerrar.addEventListener('click', () => contenedor.classList.add('hidden'));
            }
            const formRegistro = document.getElementById('formRegistro');
            if (formRegistro) formRegistro.addEventListener('submit', registrarEvento);
            const formEdit = document.getElementById('formEdit');
            if (formEdit) formEdit.addEventListener('submit', enviarEdicion);
            const selectHistorial = document.getElementById('filtroAtletaHistorial');
            if (selectHistorial) {
                selectHistorial.addEventListener('change', (e) => cargarTablaHistorial(e.target.value));
            }
        });
        
    </script>
</body>
</html>