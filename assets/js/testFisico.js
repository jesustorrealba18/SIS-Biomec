const modalTest = document.getElementById('modalTest');
const formTest = document.getElementById('formTest');
const btnGuardar = document.getElementById('btnGuardar');
const modalVer = document.getElementById('modalVer');

const API_URL = 'index.php?p=testFisico';

let atletasGlobal = [];
let tiposGlobal = [];
let variablesCache = {};

const BADGES_ESTADO = {
    'Completo': 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
    'Parcial': 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    'Cancelado': 'bg-red-500/20 text-red-400 border border-red-500/30'
};

async function peticionAjax(accion, datos = null) {
    const opciones = { method: datos ? 'POST' : 'GET' };
    if (datos) opciones.body = datos;

    try {
        const respuesta = await fetch(`${API_URL}&accion=${accion}`, opciones);
        if (!respuesta.ok) throw new Error('Error de comunicacion con el servidor');
        return await respuesta.json();
    } catch (error) {
        console.error("Error Fetch:", error);
        UI.error('Error del Servidor', 'No se pudo procesar la solicitud.');
        return null;
    }
}

function cerrarModalTest() {
    modalTest.classList.add('hidden');
    modalTest.firstElementChild.classList.add('scale-95', 'opacity-0');
    formTest.reset();
    document.getElementById('id_registro_test').value = '';
    document.getElementById('id_atleta').value = '';
    document.getElementById('rejillaVariables').innerHTML = '';
    document.getElementById('contenedorVariables').classList.add('hidden');
    document.getElementById('contadorVariables').textContent = '0 Variables';
    const inputBuscar = document.getElementById('inputBuscarAtleta');
    if (inputBuscar) {
        inputBuscar.value = '';
        inputBuscar.classList.remove('text-emerald-400', 'font-bold', 'opacity-50', 'cursor-not-allowed', 'bg-gray-800');
        inputBuscar.removeAttribute('readonly');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');
    }
    try { Validador.limpiarEstilos(formTest); } catch(e) {}
}

function cerrarModalVer() {
    modalVer.classList.add('hidden');
    document.getElementById('detalleContenido').innerHTML = '';
    if (window.graficoEvolucion) {
        window.graficoEvolucion.destroy();
        window.graficoEvolucion = null;
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modalTest.classList.contains('hidden')) cerrarModalTest();
        else if (!modalVer.classList.contains('hidden')) cerrarModalVer();
    }
});

async function abrirModalTest(id_registro = null) {
    cerrarModalTest();
    modalTest.classList.remove('hidden');
    setTimeout(() => {
        modalTest.firstElementChild.classList.remove('scale-95', 'opacity-0');
    }, 10);

    cargarAtletasBuscador();
    await cargarTiposSelect();

    if (id_registro) {
        const data = await peticionAjax(`obtenerDetalle&id=${id_registro}`);
        if (!data || !data.id_registro_test) {
            UI.error('Error', 'No se pudieron cargar los datos.');
            cerrarModalTest();
            return;
        }

        document.getElementById('id_registro_test').value = data.id_registro_test;
        document.getElementById('id_atleta').value = data.id_atleta;
        const inputAtleta = document.getElementById('inputBuscarAtleta');
        inputAtleta.value = `${data.nombre_atleta} (CI: ${data.cedula})`;
        inputAtleta.readOnly = true;
        inputAtleta.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-800');
        document.getElementById('btnLimpiarAtleta').classList.add('hidden');

        document.getElementById('id_tipo_test').value = data.id_tipo_test || '';
        document.getElementById('fecha').value = data.fecha || '';
        document.getElementById('estado').value = data.estado || 'Completo';
        document.getElementById('observaciones').value = data.observaciones || '';

        if (data.id_tipo_test) {
            await cargarVariables();
            if (data.valores_detalle && data.valores_detalle.length > 0) {
                data.valores_detalle.forEach(v => {
                    const input = document.querySelector(`[name="valores[${v.id_variable}]"]`);
                    if (input) input.value = v.valor;
                });
            }
        }

        document.getElementById('accion_form').value = 'actualizar';
        btnGuardar.innerHTML = 'ACTUALIZAR TEST <i class="fas fa-sync-alt ml-2"></i>';
        btnGuardar.classList.remove('bg-indigo-600', 'hover:bg-indigo-500');
        btnGuardar.classList.add('bg-emerald-600', 'hover:bg-emerald-500');
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit text-amber-400"></i> Editar Test Fisico';
    } else {
        document.getElementById('accion_form').value = 'registrar';
        document.getElementById('id_registro_test').value = '';
        document.getElementById('origen_test').value = 'predefinido';
        document.getElementById('contenedorPredefinido').classList.remove('hidden');
        document.getElementById('contenedorPersonalizado').classList.add('hidden');
        document.getElementById('id_test_pers').value = '';
        document.getElementById('rejillaVariables').innerHTML = '';
        document.getElementById('contenedorVariables').classList.add('hidden');
        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-dumbbell text-indigo-400"></i> Registrar Test Fisico';
        btnGuardar.innerHTML = 'GUARDAR TEST <i class="fas fa-save ml-2"></i>';
        btnGuardar.classList.remove('bg-emerald-600', 'hover:bg-emerald-500');
        btnGuardar.classList.add('bg-indigo-600', 'hover:bg-indigo-500');
        document.getElementById('fecha').value = new Date().toISOString().split('T')[0];
    }
}

async function cargarAtletasBuscador() {
    const respuesta = await peticionAjax('listarAtletasSelect');
    if (respuesta) atletasGlobal = respuesta;
}

async function cargarTiposSelect() {
    const tipos = await peticionAjax('listarTiposTests');
    if (!tipos) return;
    tiposGlobal = tipos;

    const selectForm = document.getElementById('id_tipo_test');
    const filtroSelect = document.getElementById('filtroTipoTest');

    [selectForm, filtroSelect].forEach((sel, idx) => {
        if (!sel) return;
        const valorActual = sel.value;
        while (sel.options.length > (idx === 0 ? 1 : 1)) sel.remove(sel.options.length - 1);
        tipos.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id_tipo_test;
            opt.textContent = t.nombre;
            sel.appendChild(opt);
        });
        if (valorActual) sel.value = valorActual;
    });
}

async function cargarVariables() {
    const origen = document.getElementById('origen_test').value;
    const id_tipo_test = parseInt(document.getElementById('id_tipo_test').value);
    const id_test_pers = parseInt(document.getElementById('id_test_pers').value);
    const rejilla = document.getElementById('rejillaVariables');
    const contenedor = document.getElementById('contenedorVariables');
    const contador = document.getElementById('contadorVariables');

    let idVar, origenLabel;
    if (origen === 'personalizado') {
        if (!id_test_pers || id_test_pers <= 0) {
            rejilla.innerHTML = '';
            contenedor.classList.add('hidden');
            return;
        }
        idVar = `id_test_pers=${id_test_pers}`;
        origenLabel = 'personalizado';
    } else {
        if (!id_tipo_test || id_tipo_test <= 0) {
            rejilla.innerHTML = '';
            contenedor.classList.add('hidden');
            return;
        }
        idVar = `id_tipo_test=${id_tipo_test}`;
        origenLabel = 'predefinido';
    }

    const cacheKey = `${origenLabel}_${origenLabel === 'personalizado' ? id_test_pers : id_tipo_test}`;
    if (variablesCache[cacheKey]) {
        renderVariables(variablesCache[cacheKey]);
        return;
    }

    const variables = await peticionAjax(`obtenerVariables&${idVar}`);
    if (!variables || variables.length === 0) {
        rejilla.innerHTML = '<p class="text-gray-500 text-xs col-span-full">No hay variables configuradas para este test.</p>';
        contenedor.classList.remove('hidden');
        return;
    }

    variablesCache[cacheKey] = variables;
    renderVariables(variables);
}

function cambiarOrigenTest() {
    const origen = document.getElementById('origen_test').value;
    const contPredefinido = document.getElementById('contenedorPredefinido');
    const contPersonalizado = document.getElementById('contenedorPersonalizado');

    if (origen === 'personalizado') {
        contPredefinido.classList.add('hidden');
        contPersonalizado.classList.remove('hidden');
        document.getElementById('id_tipo_test').value = '';
        document.getElementById('rejillaVariables').innerHTML = '';
        document.getElementById('contenedorVariables').classList.add('hidden');
        cargarVariables();
    } else {
        contPredefinido.classList.remove('hidden');
        contPersonalizado.classList.add('hidden');
        document.getElementById('id_test_pers').value = '';
        document.getElementById('rejillaVariables').innerHTML = '';
        document.getElementById('contenedorVariables').classList.add('hidden');
        cargarVariables();
    }
}

async function cargarTestsPersonalizadosDropdown() {
    const select = document.getElementById('id_test_pers');
    if (!select) return;
    while (select.options.length > 1) select.remove(select.options.length - 1);
    const tests = await peticionAjax('listarTestsPersonalizados');
    if (!tests || tests.length === 0) return;
    tests.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id_test_pers;
        opt.textContent = t.nombre + (t.unidad_medida ? ' (' + t.unidad_medida + ')' : '');
        select.appendChild(opt);
    });
}

function renderVariables(variables) {
    const rejilla = document.getElementById('rejillaVariables');
    const contenedor = document.getElementById('contenedorVariables');
    const contador = document.getElementById('contadorVariables');

    rejilla.innerHTML = '';
    variables.forEach(v => {
        const caja = document.createElement('div');
        caja.className = 'relative';
        caja.innerHTML = `
            <label class="block text-[10px] text-gray-400 uppercase font-bold mb-1">${v.nombre_variable}</label>
            <div class="relative">
                <input type="number" step="0.01" name="valores[${v.id_variable}]" 
                       data-validar="requerido" data-nombre="${v.nombre_variable}"
                       placeholder="0.00" class="w-full input-dark p-2.5 rounded-lg text-sm text-center font-mono pr-12">
                <span class="absolute right-3 top-2.5 text-gray-600 text-xs">${v.unidad || ''}</span>
            </div>`;
        rejilla.appendChild(caja);
    });

    contador.textContent = `${variables.length} Variable${variables.length > 1 ? 's' : ''}`;
    contenedor.classList.remove('hidden');
    contenedor.style.opacity = 0;
    setTimeout(() => {
        contenedor.style.transition = "opacity 0.3s ease-in-out";
        contenedor.style.opacity = 1;
    }, 50);
}

const inputBuscar = document.getElementById('inputBuscarAtleta');
const dropdown = document.getElementById('dropdownAtletas');
const ulAtletas = document.getElementById('ulAtletas');
const inputIdOculto = document.getElementById('id_atleta');
const btnLimpiar = document.getElementById('btnLimpiarAtleta');

function renderizarDropdown(lista) {
    ulAtletas.innerHTML = '';
    if (lista.length === 0) {
        ulAtletas.innerHTML = '<li class="p-4 text-gray-500 text-center text-xs">No se encontraron coincidencias</li>';
        return;
    }
    lista.forEach(atleta => {
        const li = document.createElement('li');
        li.className = 'p-3 hover:bg-indigo-600/20 hover:text-indigo-300 cursor-pointer transition-colors flex justify-between items-center';
        li.innerHTML = `<div><div class="font-bold text-white">${atleta.nombres} ${atleta.apellidos}</div><div class="text-[10px] text-gray-500 font-mono mt-0.5">C.I: ${atleta.cedula}</div></div>`;
        li.onclick = () => seleccionarAtleta(atleta);
        ulAtletas.appendChild(li);
    });
}

function seleccionarAtleta(atleta) {
    inputIdOculto.value = atleta.id_atleta;
    inputBuscar.value = `${atleta.nombres} ${atleta.apellidos}`;
    inputBuscar.classList.add('text-emerald-400', 'font-bold');
    inputBuscar.setAttribute('readonly', true);
    dropdown.classList.add('hidden');
    btnLimpiar.classList.remove('hidden');
}

btnLimpiar.onclick = () => {
    inputIdOculto.value = '';
    inputBuscar.value = '';
    inputBuscar.classList.remove('text-emerald-400', 'font-bold');
    inputBuscar.removeAttribute('readonly');
    btnLimpiar.classList.add('hidden');
    inputBuscar.focus();
};

inputBuscar.addEventListener('input', (e) => {
    const texto = e.target.value.toLowerCase();
    const filtrados = atletasGlobal.filter(a =>
        a.nombres.toLowerCase().includes(texto) ||
        a.apellidos.toLowerCase().includes(texto) ||
        a.cedula.includes(texto)
    );
    dropdown.classList.remove('hidden');
    renderizarDropdown(filtrados);
});

inputBuscar.addEventListener('focus', () => {
    if (!inputIdOculto.value) {
        dropdown.classList.remove('hidden');
        renderizarDropdown(atletasGlobal);
    }
});

document.addEventListener('click', (e) => {
    if (!inputBuscar.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

formTest.addEventListener('submit', async (e) => {
    e.preventDefault();

    const erroresFormulario = Validador.validarFormulario(formTest);
    if (erroresFormulario) {
        UI.error('Datos Incompletos', `<div class="text-left text-sm mt-2 text-gray-300"><p class="mb-2 font-bold text-white">Corrige lo siguiente:</p>${erroresFormulario}</div>`);
        return;
    }

    if (!inputIdOculto.value) {
        UI.error('Atleta Requerido', 'Debe seleccionar un atleta.');
        return;
    }

    const origen = document.getElementById('origen_test').value;
    const idTipoTest = parseInt(document.getElementById('id_tipo_test').value);
    const idTestPers = parseInt(document.getElementById('id_test_pers').value);

    if (origen === 'personalizado') {
        if (!idTestPers || idTestPers <= 0) {
            UI.error('Test Requerido', 'Debe seleccionar un test personalizado.');
            return;
        }
    } else {
        if (!idTipoTest || idTipoTest <= 0) {
            UI.error('Test Requerido', 'Debe seleccionar un tipo de test.');
            return;
        }
    }

    let datosFormulario = new FormData(formTest);
    const accionActual = document.getElementById('accion_form').value;
    datosFormulario.set('accion', accionActual);

    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO...';
    btnGuardar.disabled = true;

    const resultado = await peticionAjax(accionActual, datosFormulario);

    if (resultado) {
        if (resultado.status === 'success') {
            const msj = accionActual === 'actualizar' ? 'Test actualizado correctamente.' : 'Test registrado correctamente.';
            UI.exito('Operacion Exitosa', msj);
            cerrarModalTest();
            cargarTabla();
        } else if (resultado.status === 'warning') {
            let mensajesError = Object.values(resultado.errores).join('<br>');
            UI.error('Datos Incompletos', mensajesError);
        } else {
            UI.error('Error', resultado.message || 'Ocurrio un error inesperado.');
        }
    }

    btnGuardar.innerHTML = textoOriginal;
    btnGuardar.disabled = false;
});

async function cargarTabla() {
    const id_atleta = document.getElementById('filtroAtleta')?.value || '';
    const id_tipo_test = document.getElementById('filtroTipoTest')?.value || '';
    const estado = document.getElementById('filtroEstado')?.value || '';

    let params = new URLSearchParams({});
    if (id_atleta) params.append('id_atleta', id_atleta);
    if (id_tipo_test) params.append('id_tipo_test', id_tipo_test);
    if (estado) params.append('estado', estado);

    const tbody = document.getElementById('tbodyTests');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Cargando...</td></tr>';

    const tests = await peticionAjax(`listarTests&${params.toString()}`);

    if (!tests || tests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-500 font-mono text-xs">No hay tests registrados.</td></tr>';
        return;
    }

    let html = '';
    tests.forEach(test => {
        const fecha = formatearFecha(test.fecha);
        const badgeEstado = BADGES_ESTADO[test.estado] || BADGES_ESTADO['Completo'];

        const puedeEditar = typeof PERMISOS_MODULO !== 'undefined' && PERMISOS_MODULO.registrar;

        html += `<tr class="hover:bg-white/5 transition-colors duration-200 border-b border-[#252345]">
            <td class="p-4 text-xs font-mono text-gray-400">${fecha}</td>
            <td class="p-4">
                <div class="font-bold text-white text-sm">${test.nombre_atleta}</div>
                <div class="text-[10px] text-gray-500 font-mono">C.I: ${test.cedula}</div>
            </td>
            <td class="p-4">
                <span class="text-indigo-300 text-sm font-medium">${test.nombre_test || 'N/A'}</span>
            </td>
            <td class="p-4 text-sm font-mono text-gray-300">—</td>
            <td class="p-4">
                <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${badgeEstado}">${test.estado}</span>
            </td>
            <td class="p-4 text-right space-x-1">
                <button onclick="verDetalle(${test.id_registro_test})" class="text-indigo-400 hover:bg-indigo-500/10 p-2 rounded-lg transition" title="Ver Detalle">
                    <i class="fas fa-eye text-base"></i>
                </button>
                ${puedeEditar ? `
                <button onclick="abrirModalTest(${test.id_registro_test})" class="text-amber-400 hover:bg-amber-500/10 p-2 rounded-lg transition" title="Editar">
                    <i class="fas fa-edit text-base"></i>
                </button>
                <button onclick="eliminarTest(${test.id_registro_test})" class="text-red-400 hover:bg-red-500/10 p-2 rounded-lg transition" title="Eliminar">
                    <i class="fas fa-trash-alt text-base"></i>
                </button>` : ''}
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function filtrarTabla() {
    const texto = document.getElementById('busquedaGeneral').value.toLowerCase();
    const filas = document.querySelectorAll('#tbodyTests tr');
    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        fila.style.display = contenido.includes(texto) ? '' : 'none';
    });
}

async function verDetalle(id) {
    const contenedor = document.getElementById('detalleContenido');
    contenedor.innerHTML = '<div class="text-center p-12 text-gray-500"><i class="fas fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i><p class="text-xs font-mono uppercase tracking-widest">Cargando detalle...</p></div>';
    modalVer.classList.remove('hidden');

    const data = await peticionAjax(`obtenerDetalle&id=${id}`);
    if (!data || !data.id_registro_test) {
        UI.error('Error', 'No se pudo cargar el detalle.');
        cerrarModalVer();
        return;
    }

    const fecha = formatearFecha(data.fecha);
    const badgeEstado = BADGES_ESTADO[data.estado] || BADGES_ESTADO['Completo'];

    let html = `
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center">
                    <i class="fas fa-dumbbell text-indigo-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">${data.nombre_test || 'Test Fisico'}</h2>
                    <p class="text-xs text-gray-400">${fecha}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-black/20 rounded-xl p-4 border border-white/5">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Atleta</p>
                <p class="text-white font-bold">${data.nombre_atleta}</p>
                <p class="text-xs text-gray-500 font-mono">C.I: ${data.cedula}</p>
            </div>
            <div class="bg-black/20 rounded-xl p-4 border border-white/5">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Tipo de Medicion</p>
                <p class="text-indigo-300 font-bold">${data.tipo_medicion || 'N/A'}</p>
                <p class="text-xs text-gray-500">Unidad: ${data.unidad_test || 'N/A'}</p>
            </div>
        </div>

        <div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6 text-center">
            <span class="px-2 py-1 rounded-lg text-[10px] font-bold ${badgeEstado}">${data.estado}</span>
        </div>`;

    if (data.valores_detalle && data.valores_detalle.length > 0) {
        html += `<div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6">
            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-3">
                <i class="fas fa-vials mr-2 text-emerald-400"></i>Valores Registrados
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">`;

        data.valores_detalle.forEach(v => {
            const valorMostrar = parseFloat(v.valor).toFixed(2);
            html += `<div class="bg-black/30 rounded-lg p-3 border border-white/5 text-center">
                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">${v.nombre_variable}</p>
                <p class="text-lg font-bold text-white font-mono">${valorMostrar}</p>
                <p class="text-[10px] text-gray-600">${v.unidad_medida || v.variable_unidad || ''}</p>
            </div>`;
        });

        html += '</div></div>';
    }

    if (data.valor_referencia_min !== null && data.valor_referencia_max !== null) {
        html += `<div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6">
            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-2">
                <i class="fas fa-ruler mr-2 text-amber-400"></i>Rango Normativo de Referencia
            </p>
            <p class="text-sm text-gray-400">Min: <span class="text-amber-400 font-bold font-mono">${parseFloat(data.valor_referencia_min).toFixed(2)}</span> 
               | Max: <span class="text-amber-400 font-bold font-mono">${parseFloat(data.valor_referencia_max).toFixed(2)}</span> 
               ${data.unidad_test ? '(' + data.unidad_test + ')' : ''}</p>
        </div>`;
    }

    if (data.observaciones) {
        html += `<div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6">
            <p class="text-[10px] text-gray-500 uppercase font-bold mb-2">Observaciones</p>
            <p class="text-gray-300 text-sm leading-relaxed">${data.observaciones}</p>
        </div>`;
    }

    if (data.historial_evolucion && data.historial_evolucion.length > 1) {
        html += `<div class="mt-6 border-t border-[#252345] pt-4">
            <p class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-3">
                <i class="fas fa-chart-line mr-2 text-emerald-400"></i>Evolucion Temporal
            </p>
            <canvas id="graficoEvolucion" height="200"></canvas>
        </div>`;
    }

    contenedor.innerHTML = html;

    if (data.historial_evolucion && data.historial_evolucion.length > 1) {
        setTimeout(() => renderGraficoEvolucion(data.historial_evolucion), 100);
    }
}

function renderGraficoEvolucion(historial) {
    const canvas = document.getElementById('graficoEvolucion');
    if (!canvas) return;

    if (window.graficoEvolucion) {
        window.graficoEvolucion.destroy();
    }

    const etiquetas = historial.map(h => formatearFecha(h.fecha));
    const valores = historial.map(h => parseFloat(h.valor));

    window.graficoEvolucion = new Chart(canvas, {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Valor',
                data: valores,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#6366f1',
                pointRadius: 5,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { color: '#6b7280', font: { size: 10 } },
                    grid: { color: '#25234520' }
                },
                y: {
                    ticks: { color: '#6b7280', font: { size: 10 } },
                    grid: { color: '#25234540' }
                }
            }
        }
    });
}

async function eliminarTest(id) {
    const confirmado = await UI.confirmar(
        'Eliminar Test',
        'Esta accion eliminara permanentemente el registro del test y todos sus valores. Desea continuar?'
    );

    if (!confirmado.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_registro_test', id);

    const resultado = await peticionAjax('eliminar', formData);
    if (resultado && resultado.status === 'success') {
        UI.exito('Eliminado', 'El test fue eliminado correctamente.');
        cargarTabla();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo eliminar el test.');
    }
}

async function cargarFiltrosAtletas() {
    const atletas = await peticionAjax('listarAtletasSelect');
    if (!atletas) return;

    const filtroAtleta = document.getElementById('filtroAtleta');
    if (!filtroAtleta) return;

    while (filtroAtleta.options.length > 1) filtroAtleta.remove(filtroAtleta.options.length - 1);
    atletas.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id_atleta;
        opt.textContent = `${a.nombres} ${a.apellidos} - CI: ${a.cedula}`;
        filtroAtleta.appendChild(opt);
    });
}

// =====================================================================
// GESTION DE TIPOS DE TESTS
// =====================================================================

function abrirModalTipo(id = null) {
    const modal = document.getElementById('modalTipo');
    document.getElementById('formTipo').reset();
    document.getElementById('id_tipo_test_edit').value = '';
    document.getElementById('rejillaVariablesTipo').innerHTML = '';
    document.getElementById('modalTipoTitulo').innerHTML = '<i class="fas fa-flask text-cyan-400"></i> Nuevo Tipo Predefinido';

    if (id) {
        document.getElementById('modalTipoTitulo').innerHTML = '<i class="fas fa-edit text-cyan-400"></i> Editar Tipo Predefinido';
        cargarDatosTipo(id);
    }

    modal.classList.remove('hidden');
    setTimeout(() => { modal.querySelector('.relative').classList.remove('scale-95', 'opacity-0'); }, 10);
}

function cerrarModalTipo() {
    const modal = document.getElementById('modalTipo');
    modal.querySelector('.relative').classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function agregarVariableTipo() {
    const cont = document.getElementById('rejillaVariablesTipo');
    const idx = cont.children.length + 1;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <input type="text" placeholder="Nombre variable" class="flex-1 input-dark p-2.5 rounded-lg text-xs nombre_var" required>
        <input type="text" placeholder="Unidad" class="w-24 input-dark p-2.5 rounded-lg text-xs unidad_var">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 p-2 cursor-pointer"><i class="fas fa-trash text-xs"></i></button>
    `;
    cont.appendChild(row);
}

async function cargarDatosTipo(id) {
    const datos = await peticionAjax(`obtenerTipoTest&id=${id}`);
    if (!datos || !datos.id_tipo_test) return;

    document.getElementById('id_tipo_test_edit').value = datos.id_tipo_test;
    document.getElementById('tipo_nombre').value = datos.nombre;
    document.getElementById('tipo_medicion').value = datos.tipo_medicion || '';
    document.getElementById('tipo_unidad').value = datos.unidad_medida || '';
    document.getElementById('tipo_ref_min').value = datos.valor_referencia_min || '';
    document.getElementById('tipo_ref_max').value = datos.valor_referencia_max || '';
    document.getElementById('tipo_descripcion').value = datos.descripcion || '';

    const cont = document.getElementById('rejillaVariablesTipo');
    cont.innerHTML = '';
    if (datos.variables) {
        datos.variables.forEach(v => {
            agregarVariableTipo();
            const row = cont.lastElementChild;
            row.querySelector('.nombre_var').value = v.nombre_variable;
            row.querySelector('.unidad_var').value = v.unidad || '';
        });
    }
}

async function guardarTipo(e) {
    e.preventDefault();
    const id = document.getElementById('id_tipo_test_edit').value;
    const accion = id ? 'editarTipoTest' : 'crearTipoTest';

    const datos = {
        nombre: document.getElementById('tipo_nombre').value.trim(),
        descripcion: document.getElementById('tipo_descripcion').value.trim(),
        tipo_medicion: document.getElementById('tipo_medicion').value.trim(),
        unidad_medida: document.getElementById('tipo_unidad').value.trim(),
        valor_referencia_min: document.getElementById('tipo_ref_min').value,
        valor_referencia_max: document.getElementById('tipo_ref_max').value,
    };

    const vars = [];
    document.querySelectorAll('#rejillaVariablesTipo > div').forEach(row => {
        const nombre = row.querySelector('.nombre_var').value.trim();
        if (nombre) {
            vars.push({ nombre_variable: nombre, unidad: row.querySelector('.unidad_var').value.trim() });
        }
    });

    if (!datos.nombre) { UI.error('Error', 'El nombre es obligatorio.'); return; }
    if (vars.length === 0) { UI.error('Error', 'Debe agregar al menos una variable.'); return; }

    const formData = new FormData();
    formData.append('accion', accion);
    formData.append('datos', JSON.stringify(datos));
    formData.append('variables', JSON.stringify(vars));
    if (id) formData.append('id_tipo_test', id);

    const resultado = await peticionAjax(accion, formData);
    if (resultado && resultado.status === 'success') {
        UI.exito('Exito', resultado.message);
        cerrarModalTipo();
        cargarTiposPredefinidos();
        cargarTiposSelect();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo guardar el tipo.');
    }
}

async function eliminarTipoPredefinido(id, nombre) {
    const confirmado = await Swal.fire({
        title: 'Eliminar tipo',
        text: `Se eliminara "${nombre}" y sus variables. Esta accion no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        ...UI.config
    });
    if (!confirmado.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminarTipoTest');
    formData.append('id_tipo_test', id);

    const resultado = await peticionAjax('eliminarTipoTest', formData);
    if (resultado && resultado.status === 'success') {
        UI.exito('Eliminado', resultado.message);
        cargarTiposPredefinidos();
        cargarTiposSelect();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo eliminar.');
    }
}

async function cargarTiposPredefinidos() {
    const cont = document.getElementById('tablaTiposPredefinidos');
    if (!cont) return;

    const tipos = await peticionAjax('listarTiposTests');
    if (!tipos || tipos.length === 0) {
        cont.innerHTML = '<p class="text-gray-500 text-xs text-center py-4">No hay tipos predefinidos.</p>';
        return;
    }

    cont.innerHTML = tipos.map(t => `
        <div class="flex items-center justify-between p-3 bg-[#0f0d23] rounded-xl border border-[#252345]">
            <div class="flex-1 min-w-0">
                <p class="text-sm text-white font-medium truncate">${t.nombre}</p>
                <p class="text-[10px] text-gray-500">${t.tipo_medicion || ''} ${t.unidad_medida ? '(' + t.unidad_medida + ')' : ''}</p>
            </div>
            <div class="flex gap-1 ml-2">
                <button onclick="abrirModalTipo(${t.id_tipo_test})" class="p-1.5 text-gray-400 hover:text-cyan-400 hover:bg-cyan-500/10 rounded-lg transition cursor-pointer" title="Editar">
                    <i class="fas fa-pen text-xs"></i>
                </button>
                <button onclick="eliminarTipoPredefinido(${t.id_tipo_test}, '${t.nombre.replace(/'/g, "\\'")}')" class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition cursor-pointer" title="Eliminar">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
        </div>
    `).join('');
}

async function cargarTestsPersonalizados() {
    const cont = document.getElementById('tablaTestsPersonalizados');
    if (!cont) return;

    const tests = await peticionAjax('listarTestsPersonalizados');
    if (!tests || tests.length === 0) {
        cont.innerHTML = '<p class="text-gray-500 text-xs text-center py-4">No hay tests personalizados.</p>';
        return;
    }

    cont.innerHTML = tests.map(t => `
        <div class="flex items-center justify-between p-3 bg-[#0f0d23] rounded-xl border border-[#252345]">
            <div class="flex-1 min-w-0">
                <p class="text-sm text-white font-medium truncate">${t.nombre}</p>
                <p class="text-[10px] text-gray-500">${t.variables ? t.variables.length + ' variables' : ''} ${t.unidad_medida ? '(' + t.unidad_medida + ')' : ''} - Creado: ${t.fecha_creacion ? t.fecha_creacion.substring(0, 10) : ''}</p>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="editarTestPers(${t.id_test_pers})" class="p-1.5 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition cursor-pointer" title="Editar">
                    <i class="fas fa-pen text-xs"></i>
                </button>
                <button onclick="eliminarTestPers(${t.id_test_pers}, '${t.nombre.replace(/'/g, "\\'")}')" class="p-1.5 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition cursor-pointer" title="Eliminar">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
        </div>
    `).join('');
}

async function editarTestPers(id) {
    const datos = await peticionAjax(`obtenerTestPersonalizado&id=${id}`);
    if (!datos || datos.status === 'error') {
        UI.error('Error', datos?.message || 'No se pudo cargar el test.');
        return;
    }

    const modal = document.getElementById('modalPersonalizado');
    document.getElementById('pers_id_edit').value = datos.id_test_pers;
    document.getElementById('pers_nombre').value = datos.nombre || '';
    document.getElementById('pers_tipo_medicion').value = datos.tipo_medicion || '';
    document.getElementById('pers_unidad').value = datos.unidad_medida || '';
    document.getElementById('pers_ref_min').value = datos.valor_referencia_min || '';
    document.getElementById('pers_ref_max').value = datos.valor_referencia_max || '';
    document.getElementById('pers_descripcion').value = datos.descripcion || '';

    document.getElementById('rejillaVariablesPers').innerHTML = '';
    if (datos.variables && datos.variables.length > 0) {
        datos.variables.forEach(v => agregarVariablePers(v.nombre_variable, v.unidad || ''));
    }

    document.getElementById('persModalTitulo').textContent = 'Editar Test Personalizado';
    document.getElementById('persBtnSubmit').innerHTML = 'ACTUALIZAR TEST <i class="fas fa-save ml-2"></i>';

    modal.classList.remove('hidden');
    setTimeout(() => { modal.querySelector('.relative').classList.remove('scale-95', 'opacity-0'); }, 10);
}

async function eliminarTestPers(id, nombre) {
    const confirmado = await Swal.fire({
        title: 'Eliminar test personalizado',
        text: `Se eliminara "${nombre}" y sus variables. Esta accion no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        ...UI.config
    });
    if (!confirmado.isConfirmed) return;

    const formData = new FormData();
    formData.append('accion', 'eliminarTestPersonalizado');
    formData.append('id_test_pers', id);

    const resultado = await peticionAjax('eliminarTestPersonalizado', formData);
    if (resultado && resultado.status === 'success') {
        UI.exito('Eliminado', resultado.message);
        cargarTestsPersonalizados();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo eliminar.');
    }
}

function abrirModalPersonalizado() {
    const modal = document.getElementById('modalPersonalizado');
    document.getElementById('formPersonalizado').reset();
    document.getElementById('pers_id_edit').value = '';
    document.getElementById('rejillaVariablesPers').innerHTML = '';
    document.getElementById('persModalTitulo').textContent = 'Nuevo Test Personalizado';
    document.getElementById('persBtnSubmit').innerHTML = 'CREAR TEST <i class="fas fa-save ml-2"></i>';

    modal.classList.remove('hidden');
    setTimeout(() => { modal.querySelector('.relative').classList.remove('scale-95', 'opacity-0'); }, 10);
}

function cerrarModalPersonalizado() {
    const modal = document.getElementById('modalPersonalizado');
    modal.querySelector('.relative').classList.add('scale-95', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function agregarVariablePers(nombre = '', unidad = '') {
    const cont = document.getElementById('rejillaVariablesPers');
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <input type="text" placeholder="Nombre variable" class="flex-1 input-dark p-2.5 rounded-lg text-xs nombre_var" required>
        <input type="text" placeholder="Unidad" class="w-24 input-dark p-2.5 rounded-lg text-xs unidad_var">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300 p-2 cursor-pointer"><i class="fas fa-trash text-xs"></i></button>
    `;
    if (nombre) row.querySelector('.nombre_var').value = nombre;
    if (unidad) row.querySelector('.unidad_var').value = unidad;
    cont.appendChild(row);
}

async function guardarPersonalizado(e) {
    e.preventDefault();

    const editandoId = document.getElementById('pers_id_edit').value;
    const datos = {
        nombre: document.getElementById('pers_nombre').value.trim(),
        descripcion: document.getElementById('pers_descripcion').value.trim(),
        tipo_medicion: document.getElementById('pers_tipo_medicion').value.trim(),
        unidad_medida: document.getElementById('pers_unidad').value.trim(),
        valor_referencia_min: document.getElementById('pers_ref_min').value,
        valor_referencia_max: document.getElementById('pers_ref_max').value,
    };

    const vars = [];
    document.querySelectorAll('#rejillaVariablesPers > div').forEach(row => {
        const nombre = row.querySelector('.nombre_var').value.trim();
        if (nombre) {
            vars.push({ nombre_variable: nombre, unidad: row.querySelector('.unidad_var').value.trim() });
        }
    });

    if (!datos.nombre) { UI.error('Error', 'El nombre es obligatorio.'); return; }
    if (vars.length === 0) { UI.error('Error', 'Debe agregar al menos una variable.'); return; }

    const formData = new FormData();
    formData.append('datos', JSON.stringify(datos));
    formData.append('variables', JSON.stringify(vars));

    let resultado;
    if (editandoId) {
        formData.append('accion', 'editarTestPersonalizado');
        formData.append('id_test_pers', editandoId);
        resultado = await peticionAjax('editarTestPersonalizado', formData);
    } else {
        formData.append('accion', 'crearTestPersonalizado');
        resultado = await peticionAjax('crearTestPersonalizado', formData);
    }

    if (resultado && resultado.status === 'success') {
        UI.exito('Exito', resultado.message);
        cerrarModalPersonalizado();
        cargarTestsPersonalizados();
        cargarTestsPersonalizadosDropdown();
    } else {
        UI.error('Error', resultado?.message || 'No se pudo guardar el test personalizado.');
    }
}

async function cargarRecursos() {
    await Promise.all([
        cargarFiltrosAtletas(),
        cargarTiposSelect(),
        cargarTestsPersonalizadosDropdown()
    ]);
    cargarTiposPredefinidos();
    cargarTestsPersonalizados();
}

document.getElementById('formTipo').addEventListener('submit', guardarTipo);
document.getElementById('formPersonalizado').addEventListener('submit', guardarPersonalizado);

cargarRecursos().then(() => cargarTabla());
Validador.vincularTiempoReal(formTest);
