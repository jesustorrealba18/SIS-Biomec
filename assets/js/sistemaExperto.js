(function() {
    'use strict';

    const ATLETAS_FALLBACK = [
        { id: 0, nombre: 'Jesús Hernández', categoria: 'Junior', sexo: 'M' },
        { id: 0, nombre: 'Maikol Parra', categoria: 'Juvenil', sexo: 'M' },
        { id: 0, nombre: 'Ana García', categoria: 'Élite', sexo: 'F' },
        { id: 0, nombre: 'Carlos Rodríguez', categoria: 'Junior', sexo: 'M' },
        { id: 0, nombre: 'Luisa Martínez', categoria: 'Juvenil', sexo: 'F' },
        { id: 0, nombre: 'Pedro Sánchez', categoria: 'Élite', sexo: 'M' }
    ];

    let atletas = Array.isArray(window.SIS_ATLETAS) && window.SIS_ATLETAS.length > 0
        ? window.SIS_ATLETAS
        : ATLETAS_FALLBACK;

    const RECOMENDACIONES = [
        {
            severidad: 'alerta', tipo: 'Carga',
            titulo: 'Reducir carga de inmediato',
            descripcion: 'El atleta acumula un RPE promedio de 8.7 en los últimos 3 días junto con una lesión activa en hombro derecho (sobreuso).',
            evidencia: ['RPE promedio 3 días: 8.7 (> 8.0)', 'Lesión activa: Hombro der. (Sobreuso)', 'sRPE semanal: +18% vs media'],
            arbol: 'alerta', modulo: 'cargaBienestar', moduloTxt: 'Ir a Carga y Bienestar', fecha: 'Hoy, 07:15'
        },
        {
            severidad: 'alerta', tipo: 'Lesion',
            titulo: 'Posible recidiva en rodilla',
            descripcion: 'Tras reiniciar cargas post-rehabilitación, el nivel de molestia subió a 7/10 en la rodilla izquierda. Patrón compatible con recidiva.',
            evidencia: ['Molestia: 7/10 (≥ 6)', 'Estado: EnRehabilitacion', 'RPE previo a lesión: 8.6 prom.'],
            arbol: 'alerta', modulo: 'lesion', moduloTxt: 'Ir a Control de Lesiones', fecha: 'Hoy, 06:40'
        },
        {
            severidad: 'aviso', tipo: 'Descanso',
            titulo: 'Déficit de sueño sostenido',
            descripcion: 'Cuatro días consecutivos con menos de 6 horas de sueño y calidad percibida de 4/10. Riesgo de fatiga acumulada.',
            evidencia: ['Horas de sueño: 5.2 prom. (< 6h)', 'Calidad de sueño: 4/10', 'Estrés percibido: 7/10'],
            arbol: 'aviso', modulo: 'cargaBienestar', moduloTxt: 'Ir a Carga y Bienestar', fecha: 'Ayer, 20:05'
        },
        {
            severidad: 'aviso', tipo: 'Carga',
            titulo: 'Monotonía de entrenamiento elevada',
            descripcion: 'El sRPE semanal supera en 15% la media de las últimas 4 semanas sin semana de descarga intermedia.',
            evidencia: ['sRPE semanal: +15% vs media 4 sem.', 'Semanas sin descarga: 3', 'Monotonía: 2.1 (> 2.0)'],
            arbol: 'aviso', modulo: 'cargaBienestar', moduloTxt: 'Ir a Carga y Bienestar', fecha: 'Ayer, 19:30'
        },
        {
            severidad: 'aviso', tipo: 'Rendimiento',
            titulo: 'Caída en observaciones técnicas',
            descripcion: 'La valoración técnica de brazada descendió de 4 a 2 en las últimas dos observaciones. Revisar técnica antes de subir carga.',
            evidencia: ['Observación técnica: 4 → 2', 'Tendencia: descendente', 'PB reciente: hace 12 días'],
            arbol: 'aviso', modulo: 'observacionesTecnicas', moduloTxt: 'Ir a Observaciones Técnicas', fecha: 'Hace 2 días'
        },
        {
            severidad: 'info', tipo: 'Rendimiento',
            titulo: 'Progreso positivo en tests físicos',
            descripcion: 'Mejora sostenida en los últimos 3 controles de fuerza y resistencia. El plan actual está produciendo adaptaciones.',
            evidencia: ['Tests: 3 mejoras consecutivas', 'Fuerza: +6.2% en 4 semanas', 'Bienestar: estable'],
            arbol: 'info', modulo: 'testFisico', moduloTxt: 'Ir a Tests Físicos', fecha: 'Hace 2 días'
        },
        {
            severidad: 'info', tipo: 'Rendimiento',
            titulo: 'Proyección de marca personal',
            descripcion: 'La tendencia de tiempos en 100m libre proyecta un nuevo récord personal en las próximas 3 semanas si se mantiene la carga.',
            evidencia: ['100m libre: -0.8s en 6 semanas', 'Proyección PB: 3 semanas', 'Consistencia técnica: 4/5'],
            arbol: 'info', modulo: 'marcas', moduloTxt: 'Ir a Marcas y Tiempos', fecha: 'Hace 3 días'
        },
        {
            severidad: 'info', tipo: 'Descanso',
            titulo: 'Recuperación completa',
            descripcion: 'Cinco días con bienestar estable y sin molestias referidas. El atleta está en condiciones de asumir progresión de carga.',
            evidencia: ['Bienestar estable: 5 días', 'Sin lesiones activas', 'Sueño: 7.8h promedio'],
            arbol: 'info', modulo: 'inicio', moduloTxt: 'Ir al Inicio', fecha: 'Hace 4 días'
        }
    ];

    const ARBOLES = {
        alerta: {
            titulo: 'Árbol de decisión · R-01 Alerta roja',
            pasos: [
                { q: '¿RPE promedio (3 días) > 8.0?', no: 'Evaluar regla R-02 (aviso)' },
                { q: '¿Lesión activa registrada?', no: 'Continuar a R-02 (aviso)' },
                { q: '¿Molestia referida en la zona de lesión?', no: 'Vigilancia cada 48h' }
            ],
            final: 'ALERTA: Suspender cargas altas y derivar a revisión médica'
        },
        aviso: {
            titulo: 'Árbol de decisión · R-02 Aviso',
            pasos: [
                { q: '¿RPE promedio (3 días) > 7.0?', no: 'Evaluar regla R-03 (refuerzo)' },
                { q: '¿Sueño < 6h o calidad < 5/10?', no: 'Continuar evaluación' },
                { q: '¿sRPE semanal > +15% vs media 4 semanas?', no: 'Vigilancia semanal' }
            ],
            final: 'AVISO: Reducir volumen 20–30% y reforzar bienestar'
        },
        info: {
            titulo: 'Árbol de decisión · R-03 Refuerzo positivo',
            pasos: [
                { q: '¿≥ 2 marcas mejorando en 4 semanas?', no: 'Sin acción' },
                { q: '¿Observaciones técnicas ≥ 4/5?', no: 'Priorizar corrección técnica' },
                { q: '¿Bienestar estable ≥ 5 días?', no: 'Reforzar descanso' }
            ],
            final: 'INFO: Mantener plan y considerar progresión de carga'
        }
    };

    const ESTILOS_SEVERIDAD = {
        alerta: {
            borde: 'border-l-4 border-l-red-500',
            icono: 'fa-triangle-exclamation',
            iconoColor: 'text-red-500 bg-red-500/10',
            badge: 'bg-red-500/15 text-red-600 dark:text-red-400',
            badgeTxt: 'Alerta'
        },
        aviso: {
            borde: 'border-l-4 border-l-amber-500',
            icono: 'fa-circle-exclamation',
            iconoColor: 'text-amber-500 bg-amber-500/10',
            badge: 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
            badgeTxt: 'Aviso'
        },
        info: {
            borde: 'border-l-4 border-l-emerald-500',
            icono: 'fa-arrow-trend-up',
            iconoColor: 'text-emerald-500 bg-emerald-500/10',
            badge: 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
            badgeTxt: 'Info'
        }
    };

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    function atletaDeCard(i) {
        return atletas[i % atletas.length];
    }

    function renderFeed() {
        const cont = document.getElementById('feedRecomendaciones');
        if (!cont) return;

        const fAtleta = document.getElementById('filtroAtleta').value;
        const fTipo = document.getElementById('filtroTipo').value;
        const fSev = document.getElementById('filtroSeveridad').value;

        let visibles = 0;
        const html = RECOMENDACIONES.map((r, i) => {
            const at = atletaDeCard(i);
            const st = ESTILOS_SEVERIDAD[r.severidad];
            const visible = (!fAtleta || String(at.id || 0) === fAtleta)
                && (!fTipo || r.tipo === fTipo)
                && (!fSev || r.severidad === fSev);
            if (!visible) return '';
            visibles++;

            const evidencia = r.evidencia.map((e) =>
                `<span class="text-[10px] px-2 py-1 rounded-lg bg-gray-100 dark:bg-[#252345] text-gray-600 dark:text-gray-300">${esc(e)}</span>`
            ).join('');

            return `
            <div class="border border-gray-200 dark:border-[#252345] rounded-xl p-4 ${st.borde} hover:shadow-md transition">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${st.iconoColor}">
                        <i class="fas ${st.icono}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">${esc(r.titulo)}</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold ${st.badge}">${st.badgeTxt}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-500 dark:text-indigo-300 font-bold">${esc(r.tipo)}</span>
                            <span class="text-[10px] text-gray-400 sm:ml-auto">${esc(r.fecha)}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            <i class="fas fa-swimmer mr-1 text-gray-400"></i><strong class="text-gray-800 dark:text-gray-200">${esc(at.nombre)}</strong>
                            <span class="text-gray-400">· ${esc(at.categoria)}</span>
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${esc(r.descripcion)}</p>
                        <div class="flex flex-wrap gap-2 mt-2">${evidencia}</div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button type="button" data-arbol="${r.arbol}" class="btnVerArbol text-[11px] font-bold px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-500 dark:text-indigo-300 hover:bg-indigo-500/20 transition">
                                <i class="fas fa-diagram-project mr-1"></i> Ver árbol de decisión
                            </button>
                            <a href="index.php?p=${r.modulo}" class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-[#252345] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#3a3663] transition">
                                <i class="fas fa-arrow-right mr-1"></i> ${esc(r.moduloTxt)}
                            </a>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        cont.innerHTML = html || '<p class="text-xs text-gray-400 text-center py-6">Sin recomendaciones para los filtros seleccionados.</p>';
        document.getElementById('contadorFeed').textContent = `Mostrando ${visibles} de ${RECOMENDACIONES.length} recomendaciones`;

        cont.querySelectorAll('.btnVerArbol').forEach((b) => {
            b.addEventListener('click', () => abrirArbol(b.dataset.arbol));
        });
    }

    function abrirArbol(clave) {
        const arbol = ARBOLES[clave];
        const modal = document.getElementById('modalArbol');
        if (!arbol || !modal) return;

        document.getElementById('modalArbolTitulo').textContent = arbol.titulo;

        const pasos = arbol.pasos.map((p) => `
            <div class="arbol-nodo">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">${esc(p.q)}</p>
                <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">Sí ↓</span>
                <p class="arbol-no">No → ${esc(p.no)}</p>
            </div>
            <div class="text-center text-gray-400 text-xs py-0.5"><i class="fas fa-arrow-down"></i></div>
        `).join('');

        const colorFinal = clave === 'alerta'
            ? { borde: 'border-red-500', fondo: 'bg-red-500/10', texto: 'text-red-600 dark:text-red-400', icono: 'fa-triangle-exclamation' }
            : clave === 'aviso'
                ? { borde: 'border-amber-500', fondo: 'bg-amber-500/10', texto: 'text-amber-600 dark:text-amber-400', icono: 'fa-circle-exclamation' }
                : { borde: 'border-emerald-500', fondo: 'bg-emerald-500/10', texto: 'text-emerald-600 dark:text-emerald-400', icono: 'fa-circle-check' };

        document.getElementById('modalArbolContenido').innerHTML = pasos + `
            <div class="arbol-nodo final ${colorFinal.borde} ${colorFinal.fondo}">
                <p class="text-xs font-bold ${colorFinal.texto}">
                    <i class="fas ${colorFinal.icono} mr-1"></i>${esc(arbol.final)}
                </p>
            </div>`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function cerrarArbol() {
        const modal = document.getElementById('modalArbol');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function mulberry32(semilla) {
        let a = semilla >>> 0;
        return function() {
            a |= 0; a = (a + 0x6D2B79F5) | 0;
            let t = Math.imul(a ^ (a >>> 15), 1 | a);
            t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    }

    function serieRiesgo(atleta) {
        if (!atleta) return [42, 45, 44, 48, 50, 46, 40, 34];
        const rnd = mulberry32((atleta.id || atleta.nombre.length * 7) + 13);
        const base = 38 + rnd() * 30;
        return Array.from({ length: 8 }, (_, i) => {
            const v = base + (rnd() - 0.4) * 18 + (i - 4) * (rnd() > 0.5 ? 1.5 : -1.2);
            return Math.max(10, Math.min(92, Math.round(v)));
        });
    }

    let graficaRiesgo = null;

    function renderGrafica() {
        const canvas = document.getElementById('graficaRiesgo');
        if (!canvas || typeof Chart === 'undefined') return;

        const sel = document.getElementById('selectorAtletaGrafica');
        const atleta = sel.value === '' ? null : atletas.find((a) => String(a.id) === sel.value);
        const datos = serieRiesgo(atleta);
        const ultimo = datos[datos.length - 1];
        const color = ultimo >= 60 ? '#ef4444' : ultimo >= 40 ? '#f59e0b' : '#10b981';

        const esOscuro = document.documentElement.classList.contains('dark');
        const colorTexto = esOscuro ? '#6b7280' : '#4b5563';
        const colorGrid = esOscuro ? '#25234540' : '#e5e7eb40';

        if (graficaRiesgo) graficaRiesgo.destroy();

        graficaRiesgo = new Chart(canvas, {
            type: 'line',
            data: {
                labels: ['S-7', 'S-6', 'S-5', 'S-4', 'S-3', 'S-2', 'S-1', 'Actual'],
                datasets: [{
                    label: 'Índice de riesgo',
                    data: datos,
                    borderColor: color,
                    backgroundColor: color + '1A',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: color
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: colorGrid }, ticks: { color: colorTexto } },
                    y: {
                        grid: { color: colorGrid },
                        ticks: { color: colorTexto },
                        min: 0, max: 100
                    }
                }
            }
        });
    }

    function poblarSelects() {
        const filtro = document.getElementById('filtroAtleta');
        const selector = document.getElementById('selectorAtletaGrafica');
        if (!filtro || !selector) return;

        atletas.forEach((a) => {
            filtro.insertAdjacentHTML('beforeend',
                `<option value="${esc(a.id)}">${esc(a.nombre)} · ${esc(a.categoria)}</option>`);
            selector.insertAdjacentHTML('beforeend',
                `<option value="${esc(a.id)}">${esc(a.nombre)}</option>`);
        });
    }

    function mostrarToast(texto) {
        const toast = document.getElementById('toastDemo');
        const span = document.getElementById('toastDemoTexto');
        if (!toast || !span) return;
        span.textContent = texto;
        toast.classList.remove('hidden');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    function init() {
        poblarSelects();
        renderFeed();
        renderGrafica();

        ['filtroAtleta', 'filtroTipo', 'filtroSeveridad'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', renderFeed);
        });

        const selG = document.getElementById('selectorAtletaGrafica');
        if (selG) selG.addEventListener('change', renderGrafica);

        const btnCerrar = document.getElementById('modalArbolCerrar');
        const backdrop = document.getElementById('modalArbolBackdrop');
        if (btnCerrar) btnCerrar.addEventListener('click', cerrarArbol);
        if (backdrop) backdrop.addEventListener('click', cerrarArbol);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') cerrarArbol();
        });

        const btnEvaluar = document.getElementById('btnEvaluar');
        if (btnEvaluar) {
            btnEvaluar.addEventListener('click', () => {
                const icono = btnEvaluar.querySelector('i');
                btnEvaluar.disabled = true;
                icono.classList.remove('fa-sync-alt');
                icono.classList.add('fa-spinner', 'fa-spin');
                setTimeout(() => {
                    icono.classList.add('fa-sync-alt');
                    icono.classList.remove('fa-spinner', 'fa-spin');
                    btnEvaluar.disabled = false;
                    renderGrafica();
                    mostrarToast('Motor de inferencia ejecutado · 8 reglas evaluadas · 8 recomendaciones vigentes');
                }, 900);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
