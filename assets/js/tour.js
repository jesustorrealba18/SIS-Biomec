/**
 * tour.js - Sistema Centralizado de Tours Interactivos con Driver.js
 */
document.addEventListener('DOMContentLoaded', () => {

    document.addEventListener('iniciar-tour-guiado', () => {
        iniciarTourModuloActual();
    });

    function iniciarTourModuloActual() {
        const driverFn = window.driver?.js?.driver || window.driver;

        if (typeof driverFn !== 'function') {
            console.error('Driver.js no está cargado correctamente.');
            return;
        }

        const esMovil = window.innerWidth < 1024;
        
        // En escritorio enfocamos el nav interno para un resaltado preciso
        const selectorMenu = esMovil ? '#openMenuBtn' : '#sidebarMenu nav';

        // Pasos del Header / Navegación
        const pasosHeader = [
            {
                element: selectorMenu,
                popover: {
                    title: 'Menú de Navegación',
                    description: esMovil 
                        ? 'Abre o cierra el menú lateral en dispositivos móviles.' 
                        : 'Accede rápidamente a los módulos del sistema desde esta barra.',
                    side: esMovil ? 'bottom' : 'right',
                    align: 'start'
                }
            },
            {
                element: '#btnNotificaciones',
                popover: {
                    title: 'Notificaciones',
                    description: 'Revisa las alertas e información relevante enviada por el sistema.',
                    side: 'bottom'
                }
            },
            {
                element: '#btnPerfilDropdown',
                popover: {
                    title: 'Perfil de Usuario',
                    description: 'Accede a tus opciones personales o cierra sesión.',
                    side: 'bottom',
                    align: 'end'
                }
            }
        ].filter(paso => {
            const el = document.querySelector(paso.element);
            return el && el.offsetParent !== null;
        });

        const pasosModulo = obtenerPasosModulo();
        const todosLosPasos = [...pasosHeader, ...pasosModulo];

        if (todosLosPasos.length === 0) return;

        const tourDriver = driverFn({
            showProgress: true,
            animate: true,
            nextBtnText: 'Siguiente →',
            prevBtnText: '← Anterior',
            doneBtnText: 'Entendido',
            progressText: 'Paso {{current}} de {{total}}',
            steps: todosLosPasos
        });

        tourDriver.drive();
    }

    function obtenerPasosModulo() {

        // ==========================================
        // MÓDULO: INICIO / DASHBOARD
        // ==========================================
        if (window.location.search === '' || window.location.search.includes('p=inicio')) {
            return [
                {
                    element: 'main .rounded-2xl:first-child',
                    popover: {
                        title: 'Panel de Bienvenida',
                        description: 'Muestra un saludo personalizado según la hora del día, junto con la fecha y hora actual del sistema.',
                        side: 'bottom'
                    }
                },
                // --- ACCESOS RÁPIDOS INDIVIDUALES ---
                {
                    element: 'main a[href*="p=atleta"]',
                    popover: {
                        title: 'Módulo de Atletas',
                        description: 'Acceso directo a la gestión de deportistas, expedientes antropométricos y fichas personales.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main a[href*="p=entrenador"]',
                    popover: {
                        title: 'Módulo de Entrenadores',
                        description: 'Consulta y gestiona la información del personal técnico y entrenadores a cargo.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main a[href*="p=sesiones"]',
                    popover: {
                        title: 'Módulo de Sesiones',
                        description: 'Planifica y revisa las sesiones de entrenamiento diarias y cargas de trabajo.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main a[href*="p=marcas"]',
                    popover: {
                        title: 'Control de Marcas',
                        description: 'Registra y analiza los tiempos e indicadores de rendimiento de los atletas.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main a[href*="p=eventos"]',
                    popover: {
                        title: 'Calendario de Eventos',
                        description: 'Gestiona las competencias, metas y fechas importantes del calendario deportivo.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main a[href*="p=analitica"]',
                    popover: {
                        title: 'Módulo de Analítica',
                        description: 'Visualiza reportes de rendimiento y gráficas evolutivas del equipo.',
                        side: 'bottom'
                    }
                },
                // --- SECCIONES INFERIORES ---
                {
                    element: 'main .lg\\:col-span-2',
                    popover: {
                        title: 'Últimas Actividades',
                        description: 'Bitácora en tiempo real con las acciones recientes ejecutadas en el sistema.',
                        side: 'top'
                    }
                },
                {
                    element: 'main .space-y-4',
                    popover: {
                        title: 'Resumen Rápido',
                        description: 'Métricas clave sobre el total de atletas, sesiones del mes, eventos y marcas.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }

        // ==========================================
        // MÓDULO 1: GRUPOS DE ENTRENAMIENTO
        // ==========================================
        if (document.getElementById('tablaGrupos')) {
            return [
                {
                    element: 'button[onclick*="abrirModalGrupo"]',
                    popover: {
                        title: 'Registrar Nuevo Grupo',
                        description: 'Haz clic en este botón para abrir el formulario de registro y crear un grupo de entrenamiento con su entrenador asignado.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaNombre',
                    popover: {
                        title: 'Buscador de Grupos',
                        description: 'Filtra la lista escribiendo el nombre de algún grupo específico en tiempo real.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroEstado',
                    popover: {
                        title: 'Estado del Grupo',
                        description: 'Alterna entre ver los grupos activos o revisar los grupos que han sido archivados o desactivados.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#tablaGrupos',
                    popover: {
                        title: 'Tabla Principal de Grupos',
                        description: 'Muestra la lista de grupos registrados, detallando la descripción, el entrenador a cargo y el total de atletas.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaGrupos button[onclick*="abrirModalVerGrupo"], #listaGrupos button[onclick*="ver"]',
                    popover: {
                        title: 'Ver Detalles del Grupo',
                        description: 'Este botón (icono de ojo) permite consultar la información detallada del grupo y el listado de atletas que pertenecen a él.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaGrupos button[onclick*="editarGrupo"], #listaGrupos button[onclick*="abrirModalGrupo"]',
                    popover: {
                        title: 'Editar Grupo',
                        description: 'Usa este botón (icono de lápiz) para modificar el nombre, la descripción o cambiar el entrenador del grupo.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaGrupos button[onclick*="abrirModalAsignacion"]',
                    popover: {
                        title: 'Asignar Atletas',
                        description: 'Haz clic aquí (icono de usuario) para vincular nuevos atletas a este grupo filtrándolos por categoría o edad.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaGrupos button[onclick*="eliminarGrupo"], #listaGrupos button[onclick*="cambiarEstado"]',
                    popover: {
                        title: 'Archivar / Eliminar Grupo',
                        description: 'Con esta opción (icono de papelera) puedes cambiar el estado del grupo a inactivo o eliminarlo del listado activo.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación de Resultados',
                        description: 'Utiliza estos controles para navegar entre las distintas páginas del catálogo de entrenamientos.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }

        // ==========================================
        // MÓDULO 2: REPOSITORIO DE ENTRENAMIENTOS (DRILLS)
        // ==========================================
        if (document.getElementById('listaDrills')) {
            return [
                {
                    element: 'button[onclick*="abrirModalDrills"]',
                    popover: {
                        title: 'Registrar Entrenamiento',
                        description: 'Abre el formulario para añadir nuevos ejercicios técnicos, de fuerza, velocidad o resistencia al catálogo.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaID',
                    popover: {
                        title: 'Buscador Inteligente',
                        description: 'Encuentra rápidamente ejercicios escribiendo su nombre o el estilo de natación asociado.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#totalDrills',
                    popover: {
                        title: 'Total de Registros',
                        description: 'Muestra el número total de entrenamientos registrados disponibles.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#listaDrills',
                    popover: {
                        title: 'Tabla de Entrenamientos',
                        description: 'Listado completo con la información de estilo, categoría, dificultad y material necesario.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaDrills tr:first-child button[onclick*="ver"], #listaDrills tr:first-child button[onclick*="Ver"], #listaDrills tr:first-child .fa-eye',
                    popover: {
                        title: 'Ver Detalle del Drill',
                        description: 'Consulta las instrucciones completas de ejecución, metraje y parámetros técnicos del entrenamiento.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaDrills tr:first-child button[onclick*="editar"], #listaDrills tr:first-child button[onclick*="Editar"], #listaDrills tr:first-child .fa-edit, #listaDrills tr:first-child .fa-pencil-alt',
                    popover: {
                        title: 'Editar Entrenamiento',
                        description: 'Usa este botón (icono de lápiz) para modificar la información técnica, instrucciones o configuración del ejercicio.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaDrills tr:first-child button[onclick*="eliminar"], #listaDrills tr:first-child button[onclick*="cambiarEstado"], #listaDrills tr:first-child .fa-trash, #listaDrills tr:first-child .fa-toggle-on',
                    popover: {
                        title: 'Estado / Eliminar',
                        description: 'Con estas opciones puedes cambiar el estado del entrenamiento o removerlo del catálogo.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación de Resultados',
                        description: 'Utiliza estos controles para navegar entre las distintas páginas del catálogo de entrenamientos.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('listaEntrenador')) {
            return [
                {
                    element: 'button[onclick*="abrirModalEntrenador"]',
                    popover: {
                        title: 'Registrar Entrenador',
                        description: 'Abre el formulario para añadir nuevos entrenadores en el sistema.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaCedula',
                    popover: {
                        title: 'Buscador Inteligente',
                        description: 'Encuentra rápidamente un entrenador escribiendo su nombre o su cedula.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#totalEntrenador',
                    popover: {
                        title: 'Total de Registros',
                        description: 'Muestra el número total de entrenadores registrados disponibles.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#listaEntrenador',
                    popover: {
                        title: 'Tabla de Entrenadores',
                        description: 'Listado completo con la información del entrenador nombre, apellido, cedula, telefono y direccion.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaEntrenador tr:first-child button[onclick*="ver"], #listaEntrenador tr:first-child button[onclick*="Ver"], #listaEntrenador tr:first-child .fa-eye',
                    popover: {
                        title: 'Ver Detalle del Entrenador',
                        description: 'Consulta los datos completos de un entrenador.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaEntrenador tr:first-child button[onclick*="editar"], #listaEntrenador tr:first-child button[onclick*="Editar"], #listaEntrenador tr:first-child .fa-edit, #listaDrills tr:first-child .fa-pencil-alt',
                    popover: {
                        title: 'Editar Entrenador',
                        description: 'Usa este botón (icono de lápiz) para modificar la información de un entrenador.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaEntrenador tr:first-child button[onclick*="eliminar"], #listaEntrenador tr:first-child button[onclick*="cambiarEstado"], #listaEntrenador tr:first-child .fa-trash, #listaEntrenador tr:first-child .fa-toggle-on',
                    popover: {
                        title: 'Eliminar Entrenador',
                        description: 'Usa este boton (icono de papelera) para remover un entrenador del catálogo.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación de Resultados',
                        description: 'Utiliza estos controles para navegar entre las distintas páginas del catálogo de entrenadores.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodySesiones') || window.location.search.includes('p=sesiones')) {
            return [
                {
                    element: 'button[onclick*="abrirModalSesion"]',
                    popover: {
                        title: 'Registrar Entrenamiento',
                        description: 'Abre el formulario completo para planificar una nueva sesión de entrenamiento, asignar grupos, microciclos y diseñar las series.',
                        side: 'left'
                    }
                },
                {
                    element: '#filtroGrupo',
                    popover: {
                        title: 'Filtrar por Grupo',
                        description: 'Selecciona un grupo de entrenamiento específico para acotar la lista de sesiones planificadas.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroTipoSesion',
                    popover: {
                        title: 'Filtrar por Estado',
                        description: 'Filtra las sesiones según su estado actual (Planificada, Completada, Parcial o Cancelada).',
                        side: 'bottom'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Tabla de Sesiones',
                        description: 'Visualiza la fecha, duración, grupo, tipo, volumen planificado y ejecutado de cada sesión.',
                        side: 'top'
                    }
                },
                {
                    element: '#tbodySesiones tr:first-child button[onclick*="verDetalleSesion"]',
                    popover: {
                        title: 'Ver Detalles',
                        description: 'Consulta la información detallada, desglose por bloques y series planificadas de la sesión.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodySesiones tr:first-child button[onclick*="abrirModalCompletarSesion"]',
                    popover: {
                        title: 'Completar Sesión',
                        description: 'Registra el volumen real ejecutado y las observaciones de rendimiento una vez finalizado el entrenamiento.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodySesiones tr:first-child button[onclick*="abrirModalSesion("]',
                    popover: {
                        title: 'Editar Sesión',
                        description: 'Modifica los parámetros, horarios o la estructura de series de una sesión que se encuentre en estado planificado.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodySesiones tr:first-child button[onclick*="cancelarSesion"]',
                    popover: {
                        title: 'Cancelar Sesión',
                        description: 'Anula la planificación de la sesión cambiando su estado a cancelada de forma directa.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación',
                        description: 'Navega cómodamente entre las distintas páginas del listado de sesiones.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('listaAsignaciones') || window.location.search.includes('p=asignacion')) {
            return [
                {
                    element: 'button[onclick*="abrirModalAsignacion"]',
                    popover: {
                        title: 'Nueva Asignación',
                        description: 'Abre el formulario para vincular un grupo de entrenamiento a un carril y bloque horario específico.',
                        side: 'left'
                    }
                },
                {
                    element: 'button[onclick*="verHistorialCompletadas"]',
                    popover: {
                        title: 'Historial de Asignaciones',
                        description: 'Permite consultar el registro histórico de todas las asignaciones que ya han sido completadas.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaAsignacion',
                    popover: {
                        title: 'Buscador de Asignaciones',
                        description: 'Filtra rápidamente las asignaciones escribiendo el carril, día u horario.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroEstado',
                    popover: {
                        title: 'Filtro por Estado',
                        description: 'Permite alternar entre visualizar asignaciones activas, inactivas o todas en conjunto.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Tabla de Asignaciones',
                        description: 'Muestra el listado completo con la relación de carriles, horarios, grupos, vigencias y estados.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child td:nth-child(1)',
                    popover: {
                        title: 'Detalle del Carril',
                        description: 'Haz clic en el icono de información para abrir el modal con las especificaciones y capacidad del carril.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child td:nth-child(2)',
                    popover: {
                        title: 'Detalle del Horario',
                        description: 'Muestra un modal informativo con el rango de horas y el día correspondiente al bloque seleccionado.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child td:nth-child(3)',
                    popover: {
                        title: 'Detalle del Grupo',
                        description: 'Consulta mediante una ventana emergente los datos del grupo y su entrenador asignado.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child button[onclick*="verDetalle("]',
                    popover: {
                        title: 'Ver Asignación Completa',
                        description: 'Despliega el resumen general de toda la asignación registrada utilizando el botón de visualización.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child button[onclick*="abrirModalAsignacion"]',
                    popover: {
                        title: 'Editar Asignación',
                        description: 'Modifica los parámetros de fechas, carriles o grupos de una asignación existente.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child button[onclick*="completarAsignacion"]',
                    popover: {
                        title: 'Completar Asignación',
                        description: 'Finaliza la asignación actual y libera los recursos de carriles y horarios para otros grupos.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaAsignaciones tr:first-child button[onclick*="eliminarAsignacion"]',
                    popover: {
                        title: 'Desactivar Asignación',
                        description: 'Permite dar de baja o desactivar la asignación actual dentro del sistema.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación',
                        description: 'Navega de manera fluida entre los distintos registros de la tabla.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tablaDashboardBody') || window.location.search.includes('p=antropometria')) {
            return [
                {
                    element: 'button[onclick*="abrirModalMedicion"]',
                    popover: {
                        title: 'Nueva Medición',
                        description: 'Abre el formulario para registrar un nuevo control y evolución biológica del atleta.',
                        side: 'left'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Dashboard Antropométrico',
                        description: 'Muestra el listado de atletas con su última evaluación, peso, talla, IMC y estado actual.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaDashboardBody tr:first-child td:nth-child(1)',
                    popover: {
                        title: 'Información del Atleta',
                        description: 'Visualiza el nombre y los datos principales del deportista registrado.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaDashboardBody tr:first-child td:nth-child(3)',
                    popover: {
                        title: 'Última Evaluación',
                        description: 'Indica la fecha de la última revisión y alertas de tiempo transcurrido sin evaluar.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaDashboardBody tr:first-child td:nth-child(5)',
                    popover: {
                        title: 'Índice de Masa Corporal (IMC)',
                        description: 'Muestra el cálculo del IMC basado en las últimas medidas corporales ingresadas.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaDashboardBody tr:first-child button[onclick*="verHistorial"]',
                    popover: {
                        title: 'Gráficas e Historial',
                        description: 'Accede al historial completo de mediciones y gráficos de evolución de peso, talla e IMC del atleta.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tablaCuerpo') || window.location.search.includes('p=lesion')) {
            return [
                {
                    element: 'button[onclick*="abrirModal()"]',
                    popover: {
                        title: 'Registrar Lesión',
                        description: 'Abre el formulario para registrar un nuevo informe de lesión o estado clínico del atleta.',
                        side: 'left'
                    }
                },
                {
                    element: '#btnMostrarPapelera',
                    popover: {
                        title: 'Gestión de Papelera',
                        description: 'Permite alternar entre la vista de registros activos y la papelera de registros anulados.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroAtleta',
                    popover: {
                        title: 'Filtros de Búsqueda',
                        description: 'Filtra las lesiones de forma rápida por atleta, estado clínico, tipo de lesión o zona anatómica.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaLesionesContainer table',
                    popover: {
                        title: 'Historial de Lesiones',
                        description: 'Muestra el listado detallado con la fecha de inicio, atleta, zona afectada, nivel de molestia y estado clínico.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaCuerpo tr:first-child td:nth-child(4)',
                    popover: {
                        title: 'Nivel de Molestia',
                        description: 'Indica mediante una escala de color el nivel de dolor o molestia reportado (1 al 10).',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaCuerpo tr:first-child td:last-child',
                    popover: {
                        title: 'Acciones del Registro',
                        description: 'Permite ver la ficha médica detallada, editar el diagnóstico, o gestionar su eliminación lógica/restauración.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodyTests') || window.location.search.includes('p=testFisico')) {
    return [
        {
            element: 'button[onclick*="abrirModalTest"]',
            popover: {
                title: '1. Registrar Test',
                description: 'Haz clic aquí para abrir el formulario y registrar un nuevo test físico para un atleta.',
                side: 'bottom'
            }
        },
        {
            element: '#tbodyTests tr:first-child',
            popover: {
                title: '2. Historial de Tests',
                description: 'Aquí visualizas los tests físicos ya realizados con sus detalles generales.',
                side: 'top'
            }
        },
        {
            element: '#tbodyTests tr:first-child .fa-eye',
            popover: {
                title: '3. Ver Detalles del Test',
                description: 'Consulta los valores específicos, rangos y gráficas de evolución de este registro.',
                side: 'left'
            }
        },
        {
            element: '#tbodyTests tr:first-child .fa-edit',
            popover: {
                title: '4. Editar Test Registrado',
                description: 'Permite modificar la información o los datos cargados en este test.',
                side: 'left'
            }
        },
        {
            element: '#tbodyTests tr:first-child .fa-trash, #tbodyTests tr:first-child .fa-trash-alt',
            popover: {
                title: '5. Eliminar Test Registrado',
                description: 'Usa este botón rojo de papelera si necesitas borrar por completo este registro de test.',
                side: 'left'
            }
        },
        {
            element: 'button:nth-of-type(1)[onclick*="Predefinido"], button.btn[class*="purple"], .card + div button:first-child, h2 ~ div button:first-child, histic ~ div button, div[style*="flex"] > button:nth-last-child(2)',
            popover: {
                title: '6. Nuevo Tipo Predefinido',
                description: 'Da de alta un nuevo tipo de test basado en métricas estándar ya establecidas.',
                side: 'bottom'
            }
        },
        {
            element: 'button:nth-of-type(2)[onclick*="Personalizado"], button.btn[class*="success"], div[style*="flex"] > button:last-child',
            popover: {
                title: '7. Test Personalizado',
                description: 'Crea un tipo de test totalmente adaptado a variables particulares que necesites evaluar.',
                side: 'bottom'
            }
        },
        {
            element: '#tablaTiposPredefinidos, .card:has(.fa-pen), div.card:first-of-type',
            popover: {
                title: '8. Listado de Tipos Predefinidos',
                description: 'Visualiza la lista de los diferentes tipos de tests estandarizados disponibles en el sistema.',
                side: 'top'
            }
        },
        {
            element: '.fa-trash',
            popover: {
                title: '10. Eliminar Tipo de Test',
                description: 'Borra de la lista este tipo de test si ya no se va a utilizar.',
                side: 'left'
            }
        }
    ].filter(paso => {
        try {
            const el = document.querySelector(paso.element);
            return el && el.offsetParent !== null;
        } catch (e) {
            return false; // Evita que un selector inválido rompa todo el tour
        }
    });
}
    if (document.getElementById('tablaRPE') || window.location.search.includes('p=cargaBienestar')) {
    return [
        {
            element: 'button[onclick*="abrirModalRPE"]',
            popover: {
                title: 'Nuevo Registro RPE',
                description: 'Abre el formulario para registrar la percepción subjetiva del esfuerzo, horas de sueño y estrés del atleta.',
                side: 'left'
            }
        },
        {
            element: '#toggleEstadoRPEBtn',
            popover: {
                title: 'Gestión de Papelera',
                description: 'Alterna entre la visualización de registros activos y los registros anulados (papelera).',
                side: 'bottom'
            }
        },
        {
            element: '.grid.grid-cols-1.sm\\:grid-cols-3', // Contenedor de KPIs
            popover: {
                title: 'Indicadores Clave (KPIs)',
                description: 'Visualiza en tiempo real el RPE promedio, horas de sueño y el sRPE semanal del equipo.',
                side: 'top'
            }
        },
        {
            element: '#tablaRPEContainer',
            popover: {
                title: 'Tabla de Registros',
                description: 'Lista detallada de las cargas registradas. Puedes filtrar por atleta y fechas para analizar la evolución.',
                side: 'top'
            }
        },
        {
            element: '#listaInconsistenciasRPE',
            popover: {
                title: 'Alertas de Inconsistencia',
                description: 'Aquí aparecerán alertas si detectamos registros de reposo (RPE=1) con récords personales, permitiéndote auditar la data.',
                side: 'top'
            }
        }
    ].filter(paso => {
        const el = document.querySelector(paso.element);
        return el && el.offsetParent !== null;
    });
}
if (document.getElementById('tbodyMacro') || window.location.search.includes('p=periodizacion')) {
            return [
                {
                    element: 'button[onclick*="abrirModalMacro"]',
                    popover: {
                        title: 'Crear Macrociclo',
                        description: 'Abre el formulario de registro para configurar un nuevo macrociclo, definiendo su temporada, grupo y fechas clave.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaMacro',
                    popover: {
                        title: 'Buscador de Macrociclos',
                        description: 'Filtra la tabla en tiempo real escribiendo el nombre del macrociclo que deseas consultar.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroTemporada',
                    popover: {
                        title: 'Filtrar por Temporada',
                        description: 'Selecciona una temporada específica para acotar el listado de macrociclos mostrados.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroGrupo',
                    popover: {
                        title: 'Filtrar por Grupo',
                        description: 'Visualiza los macrociclos asociados a un grupo de entrenamiento en concreto.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroEstado',
                    popover: {
                        title: 'Filtrar por Estado',
                        description: 'Alterna la visualización según el estado actual de los planes (Planificado, En Progreso o Finalizado).',
                        side: 'bottom'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Tabla Principal de Macrociclos',
                        description: 'Muestra el resumen de cada plan, incluyendo su temporada, grupo, evento objetivo, duración en semanas y fase actual.',
                        side: 'top'
                    }
                },
                {
                    element: '#tbodyMacro tr:first-child button[onclick*="verDetalle"]',
                    popover: {
                        title: 'Ver Detalle y Timeline',
                        description: 'Abre una vista completa con el desglose de fases ATR, el diagrama de tiempo interactivo y los mesociclos.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyMacro tr:first-child button[onclick*="generarPeriodizacion"]',
                    popover: {
                        title: 'Generar o Regenerar Plan ATR',
                        description: 'Despliega el asistente automático para configurar las proporciones de acumulación, transmutación y realización.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyMacro tr:first-child button[onclick*="abrirModalMacro"]',
                    popover: {
                        title: 'Editar Macrociclo',
                        description: 'Modifica los parámetros principales del macrociclo como fechas, nombre o evento objetivo.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyMacro tr:first-child button[onclick*="accionEstado"]',
                    popover: {
                        title: 'Cambiar Estado',
                        description: 'Permite transicionar el estado del macrociclo (ej. pasar de Planificado a En Progreso o Finalizado).',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
         if (document.getElementById('listaHorario')) {
            return [
                {
                    element: 'button[onclick*="abrirModalHorario"]',
                    popover: {
                        title: 'Registrar Horario',
                        description: 'Abre el formulario para añadir nuevos horarios en el sistema.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaHorario',
                    popover: {
                        title: 'Buscador Inteligente',
                        description: 'Encuentra rápidamente un horario escribiendo el dia o la hora.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#totalHorario',
                    popover: {
                        title: 'Total de Registros',
                        description: 'Muestra el número total de los horarios registrados disponibles.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#listaHorario',
                    popover: {
                        title: 'Tabla de Horarios',
                        description: 'Listado completo con la información del horario.',
                        side: 'top'
                    }
                },
                {
                    element: '#listaHorario tr:first-child button[onclick*="ver"], #listaHorario tr:first-child button[onclick*="Ver"], #listaHorario tr:first-child .fa-eye',
                    popover: {
                        title: 'Ver Detalle del Horario',
                        description: 'Consulta los datos completos de un horario.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaHorario tr:first-child button[onclick*="editar"], #listaHorario tr:first-child button[onclick*="Editar"], #listaHorario tr:first-child .fa-edit, #listaHorario tr:first-child .fa-pencil-alt',
                    popover: {
                        title: 'Editar Horario',
                        description: 'Usa este botón (icono de lápiz) para modificar la información de un horario.',
                        side: 'left'
                    }
                },
                {
                    element: '#listaHorario tr:first-child button[onclick*="eliminar"], #listaHorario tr:first-child button[onclick*="cambiarEstado"], #listaHorario tr:first-child .fa-trash, #listaHorario tr:first-child .fa-toggle-on',
                    popover: {
                        title: 'Eliminar Horario',
                        description: 'Usa este boton (icono de papelera) para remover un horario del catálogo.',
                        side: 'left'
                    }
                },
                {
                    element: '#pieTabla',
                    popover: {
                        title: 'Paginación de Resultados',
                        description: 'Utiliza estos controles para navegar entre las distintas páginas del catálogo de entrenadores.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodyTemporadas') || window.location.search.includes('p=temporadas')) {
            return [
                {
                    element: 'button[onclick*="abrirModalTemporada"]',
                    popover: {
                        title: 'Nueva Temporada',
                        description: 'Haz clic aquí para registrar una nueva temporada deportiva configurando sus fechas de inicio y fin.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaTemporada',
                    popover: {
                        title: 'Buscador',
                        description: 'Escribe el nombre de la temporada para filtrar rápidamente los resultados en la tabla.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Listado de Temporadas',
                        description: 'Visualiza todas las temporadas registradas, su estado (Activa/Inactiva) y la cantidad de macrociclos asociados.',
                        side: 'top'
                    }
                },
                {
                    element: '.fila-temporada:first-child button[onclick*="abrirModalTemporada"]',
                    popover: {
                        title: 'Editar',
                        description: 'Modifica los datos de una temporada específica.',
                        side: 'left'
                    }
                },
                {
                    element: '.fila-temporada:first-child button[onclick*="activarTemporada"]',
                    popover: {
                        title: 'Activar Temporada',
                        description: 'Cambia el estado de la temporada a activa. Nota: esto desactivará automáticamente la temporada anterior.',
                        side: 'left'
                    }
                },
                {
                    element: '.fila-temporada:first-child button[onclick*="eliminarTemporada"]',
                    popover: {
                        title: 'Eliminar',
                        description: 'Elimina una temporada del sistema. Se solicitará una confirmación por seguridad.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (window.location.search.includes('p=marcas')) {
            return [
                {
                    element: 'button[onclick*="iniciarRegistroMarca"], button.bg-blue-600',
                    popover: {
                        title: 'Registrar Marca',
                        description: 'Abre el formulario para ingresar un nuevo registro cronometrado y asociarlo al atleta.',
                        side: 'left'
                    }
                },
                {
                    element: 'main > div:nth-child(2)',
                    popover: {
                        title: 'Filtros y Búsqueda',
                        description: 'Filtra rápidamente los registros por atleta, estilo o tipo de alberca.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'main > div:nth-child(3)',
                    popover: {
                        title: 'Historial de Marcas',
                        description: 'Visualiza el listado en formato de tarjetas con los tiempos, marcas personales (PB) y datos de cédula de cada atleta.',
                        side: 'top'
                    }
                },
                {
                    element: 'main > div:nth-child(3) .fa-chart-line',
                    popover: {
                        title: 'Análisis y Gráficas',
                        description: 'Consulta el historial gráfico de progresión y la evolución de los tiempos del deportista.',
                        side: 'left'
                    }
                },
                {
                    element: 'main > div:nth-child(3) button.bg-amber-100, main > div:nth-child(3) button.text-amber-600, main > div:nth-child(3) button:has(.fa-pen), main > div:nth-child(3) button:has(.fa-edit)',
                    popover: {
                        title: 'Editar Registro',
                        description: 'Modifica los valores de tiempo o las condiciones técnicas de la marca registrada.',
                        side: 'left'
                    }
                },
                {
                    element: 'main > div:nth-child(3) button.bg-red-100, main > div:nth-child(3) button.text-red-600, main > div:nth-child(3) button:has(.fa-trash)',
                    popover: {
                        title: 'Eliminar Registro',
                        description: 'Permite remover o dar de baja un registro cronometrado erróneo del sistema.',
                        side: 'left'
                    }
                },
                {
                    element: 'main > div:last-child .flex, main div.flex.justify-between.items-center.mt-4',
                    popover: {
                        title: 'Paginación y Total',
                        description: 'Navega entre las páginas y revisa el total de registros encontrados en el sistema.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodyEventos') || window.location.search.includes('p=eventos')) {
            return [
                {
                    element: 'button[onclick*="abrirModalEvento"]',
                    popover: {
                        title: 'Registrar Evento',
                        description: 'Haz clic aquí para abrir el formulario y planificar una nueva competencia o evento, incluyendo sus tiempos de corte.',
                        side: 'left'
                    }
                },
                {
                    element: '#busquedaEvento',
                    popover: {
                        title: 'Buscador de Eventos',
                        description: 'Filtra la lista en tiempo real escribiendo el nombre del evento o la sede.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroTipo',
                    popover: {
                        title: 'Filtrar por Tipo',
                        description: 'Selecciona la categoría del evento (Regional, Nacional, Internacional, Selectivo o Control).',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroEstado',
                    popover: {
                        title: 'Filtrar por Estado',
                        description: 'Alterna entre los diferentes estados de la competencia (Planificado, Inscrito, En Progreso, Finalizado o Cancelado).',
                        side: 'bottom'
                    }
                },
                {
                    element: 'table',
                    popover: {
                        title: 'Calendario Competitivo',
                        description: 'Visualiza el listado detallado de eventos, sus fechas, sedes, niveles, conteo de inscritos y metas asignadas.',
                        side: 'top'
                    }
                },
                {
                    element: '#tbodyEventos tr:first-child button[onclick*="verDetalle"]',
                    popover: {
                        title: 'Ver Detalles',
                        description: 'Consulta la ficha completa del evento, tiempos de corte, metas grupales y lista de atletas inscritos.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyEventos tr:first-child button[onclick*="abrirModalMetas"]',
                    popover: {
                        title: 'Metas Competitivas',
                        description: 'Establece y evalúa las marcas objetivo y porcentajes de diferencia (PB) para los atletas en este evento.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyEventos tr:first-child button[onclick*="abrirModalInscripcion"]',
                    popover: {
                        title: 'Inscribir Atletas',
                        description: 'Gestiona la participación de los deportistas vinculándolos directamente al evento.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyEventos tr:first-child button[onclick*="abrirModalEvento("]',
                    popover: {
                        title: 'Editar Evento',
                        description: 'Modifica la información general de la competencia o ajusta sus parámetros y tiempos de corte.',
                        side: 'left'
                    }
                },
                {
                    element: '#tbodyEventos tr:first-child button[onclick*="accionEstado"]',
                    popover: {
                        title: 'Cambiar Estado',
                        description: 'Actualiza el ciclo de vida del evento de manera rápida (ej. pasar de Planificado a Inscrito o En Progreso).',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('selectSesion') || window.location.search.includes('p=asistencia')) {
            return [
                {
                    element: '#selectSesion',
                    popover: {
                        title: 'Selección de Sesión',
                        description: 'Elige el entrenamiento activo del día para habilitar la lista de convocados y el escáner de QR.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#visorCamara',
                    popover: {
                        title: 'Visor de Cámara',
                        description: 'Área donde se visualiza el flujo de video en tiempo real para apuntar al código QR del carnet del atleta.',
                        side: 'right'
                    }
                },
                {
                    element: '#btnActivarCamara',
                    popover: {
                        title: 'Activar / Detener Escáner',
                        description: 'Haz clic aquí para conceder permisos y encender la cámara trasera de tu dispositivo o detenerla al finalizar.',
                        side: 'right'
                    }
                },
                {
                    element: '#statPresentes',
                    popover: {
                        title: 'Contador de Presentes',
                        description: 'Mide en tiempo real la cantidad de atletas que ya han ingresado exitosamente a la sesión.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#statAusentes',
                    popover: {
                        title: 'Ausentes y Justificados',
                        description: 'Resumen numérico de las faltas registradas o de los atletas con justificación médica/personal.',
                        side: 'bottom'
                    }
                },
                {
                    element: '.dataTables_wrapper .dataTables_filter',
                    popover: {
                        title: 'Buscador Rápido',
                        description: 'Filtra el listado de convocados instantáneamente escribiendo nombres, apellidos o números de cédula.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#tablaAsistenciaDT',
                    popover: {
                        title: 'Listado de Convocados',
                        description: 'Visualiza la tabla detallada con la categoría de cada deportista, su estado actual de asistencia y el tipo de marcaje.',
                        side: 'top'
                    }
                },
                {
                    element: '#tablaAtletas tr:first-child td:last-child',
                    popover: {
                        title: 'Acciones Manuales',
                        description: 'Permite marcar manualmente al atleta como Presente, Ausente (Falta) o Justificado en caso de requerirlo, incluyendo validaciones de seguridad e inmutabilidad por QR.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodyObservaciones') || window.location.search.includes('p=observacionesTecnicas')) {
            return [
                {
                    element: 'button[onclick="abrirModalObservacion()"]',
                    popover: {
                        title: 'Registrar Observación',
                        description: 'Haz clic aquí para realizar una nueva evaluación cualitativa de la técnica de un nadador.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroAtleta',
                    popover: {
                        title: 'Filtrar por Atleta',
                        description: 'Selecciona un deportista específico para acotar el listado de evaluaciones técnicas registradas.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroAspecto',
                    popover: {
                        title: 'Filtrar por Aspecto',
                        description: 'Filtra las observaciones según el componente técnico específico evaluado.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#busquedaGeneral',
                    popover: {
                        title: 'Búsqueda Rápida',
                        description: 'Escribe cualquier palabra clave o nota para encontrar información detallada de manera instantánea.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroAtletaResumen',
                    popover: {
                        title: 'Resumen por Atleta',
                        description: 'Selecciona un nadador para generar un reporte analítico con sus promedios y evolución histórica por aspecto.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#tbodyObservaciones',
                    popover: {
                        title: 'Listado de Evaluaciones',
                        description: 'Visualiza la tabla detallada con las calificaciones y notas de cada registro.',
                        side: 'top'
                    }
                },
                {
                    element: '#tbodyObservaciones tr:first-child td:last-child',
                    popover: {
                        title: 'Acciones de Registro',
                        description: 'Permite ver el detalle completo con la evolución histórica del aspecto, editar la observación o eliminarla del sistema.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('btn-tab-perfil') || window.location.search.includes('p=mi_perfil')) {
            return [
                {
                    element: '.flex.flex-col.sm\\:flex-row.space-y-2',
                    popover: {
                        title: 'Navegación por Pestañas',
                        description: 'Cambia rápidamente entre las secciones de tu información personal, seguridad y preferencias del sistema.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#perfilFotoContenedor',
                    popover: {
                        title: 'Foto y Expediente',
                        description: 'Visualiza tu avatar oficial registrado en el sistema del club.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#contenedorQR',
                    popover: {
                        title: 'Pase de Acceso QR',
                        description: 'Presenta este código al entrenador o personal autorizado para registrar tu asistencia de forma automatizada.',
                        side: 'left'
                    }
                },
                {
                    element: '#btn-tab-preferencias',
                    popover: {
                        title: 'Preferencias y Ajustes',
                        description: 'Configura la apariencia visual del sistema y el modo del cronómetro inteligente según tus requerimientos.',
                        side: 'bottom'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tablaUsuarios') || window.location.search.includes('p=usuarios')) {
            return [
                {
                    element: 'button[onclick="abrirModalCrear()"]',
                    popover: {
                        title: 'Nuevo Usuario',
                        description: 'Haz clic aquí para registrar un nuevo integrante, asignar roles y definir sus permisos de acceso.',
                        side: 'left'
                    }
                },
                {
                    element: '#tablaUsuarios',
                    popover: {
                        title: 'Listado de Cuentas',
                        description: 'Visualiza todos los usuarios del sistema, verifica sus roles y gestiona su estado actual.',
                        side: 'top'
                    }
                },
                {
                    element: '.toggle-switch:first-child',
                    popover: {
                        title: 'Estado del Usuario',
                        description: 'Activa o desactiva el acceso de un usuario al sistema de forma inmediata sin eliminar su registro.',
                        side: 'left'
                    }
                },
                {
                    element: '#tablaUsuarios tr:first-child td:last-child',
                    popover: {
                        title: 'Acciones Administrativas',
                        description: 'Edita la información, restablece la clave de acceso o elimina permanentemente a un usuario si es necesario.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tablaRoles') || window.location.search.includes('p=roles')) {
            return [
                {
                    element: 'button[onclick="abrirModalCrear()"]',
                    popover: {
                        title: 'Nuevo Rol',
                        description: 'Haz clic aquí para registrar un nuevo rol de seguridad dentro del sistema.',
                        side: 'left'
                    }
                },
                {
                    element: '#tablaRoles',
                    popover: {
                        title: 'Listado de Roles',
                        description: 'Visualiza todos los roles creados, la cantidad de permisos asociados y su descripción general.',
                        side: 'top'
                    }
                },
                {
                    element: '.toggle-switch:first-child',
                    popover: {
                        title: 'Estado del Rol',
                        description: 'Habilita o deshabilita el rol completo de manera inmediata para restringir o permitir accesos.',
                        side: 'left'
                    }
                },
                {
                    element: '#tablaRoles tr:first-child td:last-child',
                    popover: {
                        title: 'Acciones de Seguridad',
                        description: 'Edita los datos del rol, asigna y configura sus permisos por módulo o elimina el registro si es necesario.',
                        side: 'left'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('formRestaurar') || window.location.search.includes('p=mantenimiento')) {
            return [
                {
                    element: '.border-t-emerald-500',
                    popover: {
                        title: 'Gestión de Respaldos',
                        description: 'Genera un paquete de seguridad con la información de todas las bases de datos. Se recomienda ejecutar esta acción semanalmente.',
                        side: 'top'
                    }
                },
                {
                    element: 'button[onclick="Mantenimiento.generarRespaldo()"]',
                    popover: {
                        title: 'Iniciar Respaldo',
                        description: 'Pulsa este botón para procesar la copia de seguridad. El sistema descargará automáticamente el archivo generado.',
                        side: 'bottom'
                    }
                },
                {
                    element: '.border-t-red-500',
                    popover: {
                        title: 'Restauración del Sistema',
                        description: 'Herramienta crítica para recuperar información desde un archivo de respaldo previo.',
                        side: 'top'
                    }
                },
                {
                    element: '#zonaDrop',
                    popover: {
                        title: 'Cargar Archivo',
                        description: 'Arrastra aquí tu archivo .sql o .zip de respaldo para preparar la restauración.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#btnRestaurar',
                    popover: {
                        title: 'Ejecutar',
                        description: 'Una vez cargado el archivo, este botón se activará para confirmar el proceso de sobrescritura de datos.',
                        side: 'bottom'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tbodyBitacora') || window.location.search.includes('p=bitacora')) {
            return [
                {
                    element: '.flex.flex-col.sm\\:flex-row.justify-between.items-start',
                    popover: {
                        title: 'Bitácora del Sistema',
                        description: 'Visualiza el registro detallado e inalterable de todas las actividades, transacciones y accesos realizados en el sistema.',
                        side: 'bottom'
                    }
                },
                {
                    element: 'button[onclick="exportarBitacoraPDF()"]',
                    popover: {
                        title: 'Exportar Reporte',
                        description: 'Genera y descarga un informe en formato PDF respetando de forma exacta los filtros que tengas aplicados en la tabla.',
                        side: 'left'
                    }
                },
                {
                    element: '#filtroUsuario',
                    popover: {
                        title: 'Filtrar por Usuario',
                        description: 'Selecciona a un responsable específico para rastrear únicamente las acciones ejecutadas por su cuenta.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroModulo',
                    popover: {
                        title: 'Filtrar por Área',
                        description: 'Filtra los registros según el módulo o sección del sistema donde se originó la actividad.',
                        side: 'bottom'
                    }
                },
                {
                    element: '#filtroFechaInicio',
                    popover: {
                        title: 'Rango de Fecha (Desde)',
                        description: 'Establece una fecha de inicio para acotar la búsqueda de eventos ocurridos a partir de este día.',
                        side: 'top'
                    }
                },
                {
                    element: '#filtroFechaFin',
                    popover: {
                        title: 'Rango de Fecha (Hasta)',
                        description: 'Define el límite final del período que deseas auditar en los registros del sistema.',
                        side: 'top'
                    }
                },
                {
                    element: '#tbodyBitacora',
                    popover: {
                        title: 'Tabla de Auditoría',
                        description: 'Consulta los datos clave de cada evento (fecha, usuario, módulo y tipo de operación). Haz clic en el ícono de ojo para ver los detalles completos, valores anteriores y nuevos.',
                        side: 'top'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        if (document.getElementById('tablaRepresentantes') || window.location.search.includes('p=representantes')) {
            return [
                {
                    element: '.flex.flex-col.sm\\:flex-row.justify-between.items-start',
                    popover: {
                        title: 'Directorio Familiar',
                        description: 'Bienvenido al módulo de gestión de representantes. Aquí puedes administrar la información de los padres, tutores y sus atletas vinculados.',
                        side: 'bottom',
                        align: 'start'
                    }
                },
                {
                    element: '#toggleEstadoBtn',
                    popover: {
                        title: 'Filtro por Estado',
                        description: 'Utiliza este botón para alternar rápidamente entre la visualización de los representantes <b>Activos</b> y los registros archivados (Inactivos).',
                        side: 'bottom',
                        align: 'center'
                    }
                },
                {
                    element: 'button[onclick="abrirModalRepresentante()"]',
                    popover: {
                        title: 'Nuevo Representante',
                        description: 'Haz clic aquí para registrar un nuevo representante en el sistema y asociarlo directamente con sus atletas a cargo.',
                        side: 'left',
                        align: 'center'
                    }
                },
                {
                    element: '.dataTables_filter input',
                    popover: {
                        title: 'Buscador Inteligente',
                        description: 'Puedes buscar de forma instantánea por número de cédula, nombre, apellido o incluso por el nombre del atleta vinculado.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#tablaRepresentantes',
                    popover: {
                        title: 'Tabla de Directorio',
                        description: 'Aquí se listan todos los datos clave. Puedes hacer clic en los botones de los atletas vinculados para ver su mini-perfil o utilizar las acciones de la derecha para editar o archivar.',
                        side: 'top',
                        align: 'center'
                    }
                }
            ].filter(paso => {
                const el = document.querySelector(paso.element);
                return el && el.offsetParent !== null;
            });
        }
        return [];
    }
});