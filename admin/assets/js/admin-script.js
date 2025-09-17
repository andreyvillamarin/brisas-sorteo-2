// Este código es largo pero contiene toda la funcionalidad del admin.
// ¡Cópialo completo!
document.addEventListener('DOMContentLoaded', () => {

    // Lógica para Sidebar dinámico
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mainContent = document.querySelector('.main-content-wrapper');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }

    // --- Lógica Específica de cada página ---
    const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';

    switch (page) {
        case 'dashboard':
            initDashboard();
            break;
        case 'puntos_venta':
            initPuntosVenta();
            break;
        case 'reportes':
            initReportes();
            break;
        case 'sorteo':
            initSorteo();
            break;
        case 'ganadores':
            initGanadores();
            break;
        case 'analitica':
            initAnalitica();
            break;
        case 'configuracion':
            initConfiguracion();
            break;
        case 'usuarios':
            initUsuarios();
            break;
    }
});

// --- FUNCIONES DE INICIALIZACIÓN POR PÁGINA ---

function initDashboard() {
    const datePicker = document.getElementById('datePicker');
    const tableBody = document.querySelector('#registrosTable tbody');

    const loadRegistros = (date) => {
        fetch(`ajax.php?action=get_registros&date=${date}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    tableBody.innerHTML = response.data.map(reg => `
                        <tr>
                            <td>${reg.nombre_completo}</td>
                            <td>${reg.cedula}</td>
                            <td>$${parseInt(reg.valor_compra).toLocaleString('es-CO')}</td>
                            <td>${reg.punto_venta}</td>
                            <td><a href="../uploads/${reg.archivo_adjunto}" target="_blank" class="btn-secondary">Ver Factura</a></td>
                            <td>${reg.oportunidades}</td>
                            <td>${new Date(reg.fecha_registro).toLocaleString()}</td>
                            <td><a href="?page=editar_registro&id=${reg.id}" class="btn-primary">Editar</a></td>
                        </tr>
                    `).join('');
                }
            });
    };

    datePicker.addEventListener('change', () => loadRegistros(datePicker.value));
    loadRegistros(datePicker.value);
}

function initPuntosVenta() {
    const form = document.getElementById('addPuntoVentaForm');
    const tableBody = document.querySelector('#puntosVentaTable tbody');

    const loadPuntosVenta = () => {
        fetch('ajax.php?action=get_puntos_venta')
            .then(res => res.json())
            .then(response => {
                 if (response.success) {
                    tableBody.innerHTML = response.data.map(pv => `
                        <tr>
                            <td>${pv.nombre}</td>
                            <td>$${parseInt(pv.valor_minimo_compra).toLocaleString('es-CO')}</td>
                            <td class="actions-cell">
                                <a href="?page=editar_punto_venta&id=${pv.id}" class="btn-primary" style="margin-right: 5px;">Editar</a>
                                <button class="btn btn-danger" onclick="deletePuntoVenta(${pv.id})">Eliminar</button>
                            </td>
                        </tr>
                    `).join('');
                }
            });
    };

    form.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'add_punto_venta');
        formData.append('nombre', document.getElementById('nombre_pv').value);
        formData.append('valor_minimo', document.getElementById('valor_minimo').value);
        
        fetch('ajax.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if(response.success){
                    form.reset();
                    loadPuntosVenta();
                } else {
                    alert('Error: ' + response.message);
                }
            });
    });

    loadPuntosVenta();
}

function deletePuntoVenta(id) {
    if(!confirm('¿Estás seguro de que quieres eliminar este punto de venta?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_punto_venta');
    formData.append('id', id);
    fetch('ajax.php', { method: 'POST', body: formData })
        .then(() => loadPuntosVenta()); // Recargar lista
}


function initReportes() {
    const generarBtn = document.getElementById('generarReporteBtn');
    const downloadBtn = document.getElementById('downloadCsvBtn');
    const tableBody = document.querySelector('#reporteTable tbody');
    let reportData = [];

    generarBtn.addEventListener('click', () => {
        const pv_id = document.getElementById('reportePuntoVentaSelect').value;
        const inicio = document.getElementById('reporteFechaInicio').value;
        const fin = document.getElementById('reporteFechaFin').value;

        fetch(`ajax.php?action=get_reporte&pv_id=${pv_id}&inicio=${inicio}&fin=${fin}`)
            .then(res => res.json())
            .then(response => {
                if(response.success) {
                    reportData = response.data;
                    tableBody.innerHTML = reportData.map(row => `
                        <tr>
                            <td>${row.nombre_completo}</td>
                            <td>${row.cedula}</td>
                            <td>${row.email}</td>
                            <td>$${parseInt(row.total_compras).toLocaleString('es-CO')}</td>
                            <td>${row.oportunidades}</td>
                        </tr>
                    `).join('');
                    downloadBtn.style.display = reportData.length > 0 ? 'inline-block' : 'none';
                }
            });
    });

    downloadBtn.addEventListener('click', () => {
        // Preparar los datos para SheetJS
        const headers = ["Nombre", "Cédula", "Email", "Total Compras", "Oportunidades"];
        const data = reportData.map(row => ({
            "Nombre": row.nombre_completo,
            "Cédula": row.cedula,
            "Email": row.email,
            "Total Compras": row.total_compras,
            "Oportunidades": row.oportunidades
        }));

        // Crear el worksheet y el workbook
        const worksheet = XLSX.utils.json_to_sheet(data, { header: headers });

        // Ajustar el ancho de las columnas
        worksheet['!cols'] = [
            { wch: 30 }, // Nombre
            { wch: 15 }, // Cédula
            { wch: 30 }, // Email
            { wch: 20 }, // Total Compras
            { wch: 15 }  // Oportunidades
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Reporte");

        // Construir el nombre del archivo dinámicamente
        const puntoVentaSelect = document.getElementById('reportePuntoVentaSelect');
        const puntoVentaNombre = puntoVentaSelect.options[puntoVentaSelect.selectedIndex].text;
        const fechaInicio = document.getElementById('reporteFechaInicio').value;
        const fechaFin = document.getElementById('reporteFechaFin').value;
        const fileName = `Reporte - ${puntoVentaNombre} - ${fechaInicio} a ${fechaFin}.xlsx`;

        // Generar el archivo XLSX y descargarlo
        XLSX.writeFile(workbook, fileName);
    });
}

function initSorteo() {
    let participantes = [];
    let premios = [];
    let currentWinnerIndex = 0;
    let isSorteoRunning = false;

    const cargarBtn = document.getElementById('cargarParticipantesBtn');
    const mezclarBtn = document.getElementById('mezclarBtn');
    const numGanadoresInput = document.getElementById('numGanadores');
    const premiosContainer = document.getElementById('premiosContainer');
    const iniciarBtn = document.getElementById('iniciarSorteoBtn');
    const configPremiosDiv = document.getElementById('configPremios');
    const participantesList = document.getElementById('participantesList');

    const renderParticipantes = () => {
        participantesList.innerHTML = participantes.map(p => `<span class="tag">${p.nombre}</span>`).join(' ');
    };

    numGanadoresInput.addEventListener('input', () => {
        const num = parseInt(numGanadoresInput.value) || 1;
        premiosContainer.innerHTML = '';
        for (let i = 1; i <= num; i++) {
            premiosContainer.innerHTML += `
            <div class="form-group">
                <label for="premio_${i}">Nombre del Premio ${i}</label>
                <input type="text" id="premio_${i}" class="premio-input" placeholder="Ej: Un Viaje">
            </div>`;
        }
    });
    numGanadoresInput.dispatchEvent(new Event('input'));

    cargarBtn.addEventListener('click', () => {
        const pv_id = document.getElementById('sorteoPuntoVenta').value;
        const inicio = document.getElementById('sorteoFechaInicio').value;
        const fin = document.getElementById('sorteoFechaFin').value;

        fetch(`ajax.php?action=get_participantes&pv_id=${pv_id}&inicio=${inicio}&fin=${fin}`)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    participantes = response.data;
                    document.getElementById('participantesCount').textContent = participantes.length;
                    renderParticipantes();
                    
                    const hasParticipants = participantes.length > 0;
                    configPremiosDiv.style.display = hasParticipants ? 'block' : 'none';
                    mezclarBtn.style.display = hasParticipants ? 'block' : 'none';
                    
                    // Resetear el sorteo si se recargan los participantes
                    currentWinnerIndex = 0;
                    isSorteoRunning = false;
                    iniciarBtn.textContent = '¡Iniciar Sorteo!';
                    iniciarBtn.disabled = false;
                    document.querySelector('#ganadoresSorteoTable tbody').innerHTML = '';
                }
            });
    });

    mezclarBtn.addEventListener('click', () => {
        // Algoritmo Fisher-Yates para mezclar el array
        for (let i = participantes.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [participantes[i], participantes[j]] = [participantes[j], participantes[i]];
        }
        renderParticipantes();
    });

    iniciarBtn.addEventListener('click', () => {
        if (!isSorteoRunning) {
            premios = Array.from(document.querySelectorAll('.premio-input')).map(input => input.value.trim());
            if (premios.some(p => p === '')) {
                alert('Por favor, nombra todos los premios.');
                return;
            }
            if (participantes.length < premios.length) {
                alert('No hay suficientes participantes para la cantidad de premios.');
                return;
            }
            isSorteoRunning = true;
            document.querySelector('#ganadoresSorteoTable tbody').innerHTML = '';
        }

        if (participantes.length < (premios.length - currentWinnerIndex)) {
            alert('No quedan suficientes participantes para los premios restantes.');
            return;
        }

        runSingleDraw();
    });

    function runSingleDraw() {
        if (currentWinnerIndex >= premios.length) {
            iniciarBtn.textContent = 'Sorteo Finalizado';
            iniciarBtn.disabled = true;
            return;
        }
        
        iniciarBtn.disabled = true; // Deshabilitar el botón para evitar clics múltiples

        const popup = document.getElementById('sorteo-popup');
        const animationContainer = document.getElementById('winner-animation');
        const resultContainer = document.getElementById('winner-result');
        const popupContent = popup.querySelector('.popup-content');

        popup.classList.add('show');
        animationContainer.style.display = 'block';
        resultContainer.style.display = 'none';
        popupContent.classList.remove('celebrate'); // Reset celebration

        // --- Nueva Lógica de Animación de Tragamonedas ---
        animationContainer.innerHTML = ''; // Limpiar contenedor
        const reel = document.createElement('div');
        reel.className = 'reel';

        // 1. Determinar el ganador primero
        const winnerIndex = Math.floor(Math.random() * participantes.length);
        const winner = participantes[winnerIndex];

        // 2. Construir el reel
        // Para un efecto de giro más largo, creamos una lista más grande de nombres.
        let reelItems = [];
        const otherParticipants = participantes.filter(p => p.cedula !== winner.cedula);

        if (otherParticipants.length > 0) {
            // Añadimos múltiples copias desordenadas de los otros participantes para alargar el reel.
            for (let i = 0; i < 6; i++) { 
                reelItems.push(...[...otherParticipants].sort(() => 0.5 - Math.random()));
            }
        }
        
        // Si hay muy pocos participantes, o solo uno, llenamos el espacio con el ganador.
        if (reelItems.length === 0 && winner) {
            reelItems = Array(50).fill(winner);
        }

        // Insertar el ganador real en una posición predecible, cerca del final, para que el reel se detenga en él.
        const winnerPosition = Math.max(20, reelItems.length - 5);
        reelItems.splice(winnerPosition, 0, winner);

        // Llenar el reel con los nombres
        reelItems.forEach(p => {
            const reelItem = document.createElement('div');
            reelItem.className = 'reel-item';
            reelItem.textContent = p.nombre;
            reel.appendChild(reelItem);
        });
        animationContainer.appendChild(reel);

        // 3. Iniciar la animación
        // Forzar un reflow para que la transición se aplique
        reel.style.transform = 'translateY(0)'; 
        
        setTimeout(() => {
            // Mover el reel a la posición del ganador.
            // La larga lista de `reelItems` y la duración de la transición en CSS se encargan del efecto.
            const reelItemHeight = 150; // Debe coincidir con el CSS
            const targetY = - (winnerPosition * reelItemHeight);
            reel.style.transform = `translateY(${targetY}px)`;
        }, 100);


        // Revelar el ganador después de que la animación termine
        setTimeout(() => {
            // El `winner` ya fue determinado al inicio.
            const formData = new FormData();
            formData.append('action', 'guardar_ganador');
            formData.append('nombre', winner.nombre);
            formData.append('cedula', winner.cedula);
            formData.append('premio', premios[currentWinnerIndex]);
            fetch('ajax.php', { method: 'POST', body: formData });

            animationContainer.style.display = 'none';
            resultContainer.style.display = 'block';
            resultContainer.querySelector('.winner-name').textContent = winner.nombre;
            resultContainer.querySelector('.winner-prize').textContent = `Ganador de: ${premios[currentWinnerIndex]}`;
            
            popupContent.classList.add('celebrate');
            launchConfetti();

            const ganadoresTableBody = document.querySelector('#ganadoresSorteoTable tbody');
            ganadoresTableBody.innerHTML += `<tr><td>${winner.nombre}</td><td>${premios[currentWinnerIndex]}</td></tr>`;
            
            const cedulaGanadora = winner.cedula;
            participantes = participantes.filter(p => p.cedula !== cedulaGanadora);
            
            document.getElementById('participantesCount').textContent = participantes.length;
            document.getElementById('participantesList').innerHTML = participantes.map(p => `<span class="tag">${p.nombre}</span>`).join(' ');

            currentWinnerIndex++;
        }, 7500); // Sincronizado con la animación de 7.5s en CSS.
    }

    function launchConfetti() {
        const confettiContainer = document.createElement('div');
        confettiContainer.className = 'confetti-container';
        document.querySelector('.popup-content.celebrate').appendChild(confettiContainer);

        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = `${Math.random() * 100}%`;
            confetti.style.animationDelay = `${Math.random() * 2}s`;
            
            // Asignar colores variados
            const colors = ['#aa182c', '#f9a825', '#fdd835', '#ffffff', '#e0e0e0'];
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            
            confettiContainer.appendChild(confetti);
        }

        setTimeout(() => {
            confettiContainer.remove();
        }, 4000);
    }

    document.getElementById('closeWinnerPopup').addEventListener('click', () => {
        document.getElementById('sorteo-popup').classList.remove('show');
        
        if (currentWinnerIndex < premios.length) {
            iniciarBtn.textContent = `Iniciar Sorteo ${currentWinnerIndex + 1}`;
            iniciarBtn.disabled = false;
        } else {
            iniciarBtn.textContent = 'Sorteo Finalizado';
            iniciarBtn.disabled = true;
        }
    });
}


function initGanadores() {
    const tableBody = document.querySelector('#historicoGanadoresTable tbody');
    const searchInput = document.getElementById('searchGanadoresInput');
    let allGanadores = [];

    const renderTable = (data) => {
         tableBody.innerHTML = data.map(g => `
            <tr>
                <td>${g.nombre_completo}</td>
                <td>${g.cedula}</td>
                <td>${g.email}</td>
                <td>${g.premio}</td>
                <td>${new Date(g.fecha_sorteo).toLocaleString()}</td>
            </tr>
        `).join('');
    };
    
    fetch('ajax.php?action=get_ganadores')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                allGanadores = response.data;
                renderTable(allGanadores);
            }
        });
    
    searchInput.addEventListener('keyup', () => {
        const term = searchInput.value.toLowerCase();
        const filtered = allGanadores.filter(g => 
            g.nombre_completo.toLowerCase().includes(term) ||
            g.cedula.toLowerCase().includes(term) ||
            g.premio.toLowerCase().includes(term)
        );
        renderTable(filtered);
    });
}

function initAnalitica() {
    fetch('ajax.php?action=get_analiticas')
        .then(res => res.json())
        .then(response => {
            if(response.success){
                // Top Clientes
                const clientesBody = document.querySelector('#topClientesTable tbody');
                clientesBody.innerHTML = response.top_clientes.map((c, i) => `
                    <tr>
                        <td>${i+1}</td>
                        <td>${c.nombre_completo}</td>
                        <td>${c.cedula}</td>
                        <td>$${parseInt(c.total).toLocaleString('es-CO')}</td>
                    </tr>
                `).join('');
                
                // Top PDV
                const pdvBody = document.querySelector('#topPdvTable tbody');
                 pdvBody.innerHTML = response.top_pdv.map(p => `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>$${parseInt(p.total).toLocaleString('es-CO')}</td>
                    </tr>
                `).join('');
            }
        });
}

function initConfiguracion() {
    const form = document.getElementById('configForm');
    const messageDiv = document.getElementById('configMessage');

    // Inicializar Quill
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    form.addEventListener('submit', e => {
        e.preventDefault();

        // Poner el contenido de Quill en el input hidden antes de enviar
        document.getElementById('texto_introduccion_hidden').value = quill.root.innerHTML;

        const formData = new FormData(form);
        formData.append('action', 'save_config');

        fetch('ajax.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if(response.success){
                    messageDiv.textContent = '¡Configuración guardada exitosamente!';
                    messageDiv.className = 'success-msg';
                } else {
                     messageDiv.textContent = 'Error al guardar: ' + response.message;
                     messageDiv.className = 'error-msg';
                }
                setTimeout(() => messageDiv.textContent = '', 3000);
            });
    });
}

function initUsuarios() {
    const form = document.getElementById('addAdminForm');
    const tableBody = document.querySelector('#adminUsersTable tbody');

    const loadAdmins = () => {
        fetch('ajax.php?action=get_admins')
            .then(res => res.json())
            .then(response => {
                tableBody.innerHTML = response.data.map(admin => `
                    <tr>
                        <td>${admin.usuario}</td>
                        <td><button class="btn-danger" onclick="deleteAdmin(${admin.id})">Eliminar</button></td>
                    </tr>
                `).join('');
            });
    };

    form.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'add_admin');
        formData.append('usuario', document.getElementById('nuevo_usuario').value);
        formData.append('password', document.getElementById('nueva_password').value);
        fetch('ajax.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    form.reset();
                    loadAdmins();
                } else {
                    alert('Error: ' + response.message);
                }
            });
    });

    loadAdmins();
}

function deleteAdmin(id) {
    if(!confirm('¿Seguro que quieres eliminar a este administrador?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_admin');
    formData.append('id', id);
    fetch('ajax.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(response => {
            if(!response.success) alert('Error: ' + response.message);
            loadAdmins();
        });
}


// --- FUNCIONES GLOBALES ---

function filterTable() {
    // Función de búsqueda genérica para tablas del dashboard
    const input = document.getElementById("searchInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("registrosTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // Empezar en 1 para saltar el header
        let td, txtValue, visible = false;
        let tds = tr[i].getElementsByTagName("td");
        for (let j = 0; j < tds.length; j++) {
            td = tds[j];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    visible = true;
                    break;
                }
            }
        }
        tr[i].style.display = visible ? "" : "none";
    }
}
