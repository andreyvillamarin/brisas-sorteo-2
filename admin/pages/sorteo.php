<div class="header">
    <h1>Realizar Sorteo</h1>
</div>

<div class="sorteo-container">
    <div class="sorteo-setup card">
        <h3>Configuración del Sorteo</h3>
        <div class="form-group">
            <label for="sorteoPuntoVenta">Filtrar por Punto de Venta (Opcional)</label>
            <select id="sorteoPuntoVenta">
                <option value="">Todos los Puntos de Venta</option>
                 <?php
                    $stmt = $pdo->query("SELECT id, nombre FROM puntos_venta ORDER BY nombre");
                    while($row = $stmt->fetch()){
                        echo "<option value='{$row['id']}'>".htmlspecialchars($row['nombre'])."</option>";
                    }
                 ?>
            </select>
        </div>
        <div class="form-group">
            <label>Rango de Fechas (Opcional)</label>
            <div style="display: flex; gap: 10px;">
                <input type="date" id="sorteoFechaInicio" style="width: 50%;">
                <input type="date" id="sorteoFechaFin" style="width: 50%;">
            </div>
        </div>
        <button id="cargarParticipantesBtn">Cargar Participantes</button>
        <hr>
        <div id="configPremios" style="display:none;">
            <div class="form-group">
                <label for="numGanadores">Número de Ganadores</label>
                <input type="number" id="numGanadores" min="1" value="1">
            </div>
            <div id="premiosContainer">
                </div>
            <button id="iniciarSorteoBtn" class="cta-button">¡Iniciar Sorteo!</button>
        </div>
    </div>

    <div class="sorteo-results">
        <div class="card">
            <h3>Participantes (<span id="participantesCount">0</span>)</h3>
            <div class="participantes-list" id="participantesList">
                Carga los participantes para ver la lista.
            </div>
        </div>
        <div class="card">
            <h3>Ganadores de esta Sesión</h3>
            <table id="ganadoresSorteoTable">
                <thead><tr><th>Nombre</th><th>Premio</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="sorteo-popup" class="popup-overlay">
    <div class="popup-content sorteo-animation-content">
        <div id="winner-animation" class="winner-animation-container"></div>
        <div id="winner-result" style="display:none;">
            <h2 class="winner-title">¡GANADOR!</h2>
            <p class="winner-name"></p>
            <p class="winner-prize"></p>
            <button id="closeWinnerPopup">Cerrar</button>
        </div>
    </div>
</div>