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
  
  document.addEventListener('DOMContentLoaded', async () => {
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
        });