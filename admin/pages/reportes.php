<div class="header">
    <h1>Reporte por Punto de Venta</h1>
</div>
<div class="card">
    <div class="card-header">
        <div class="filters">
            <select id="reportePuntoVentaSelect">
                <option value="">Seleccionar Punto de Venta</option>
                 <?php
                    $stmt = $pdo->query("SELECT id, nombre FROM puntos_venta ORDER BY nombre");
                    while($row = $stmt->fetch()){
                        echo "<option value='{$row['id']}'>".htmlspecialchars($row['nombre'])."</option>";
                    }
                 ?>
            </select>
            <input type="date" id="reporteFechaInicio">
            <input type="date" id="reporteFechaFin">
            <button id="generarReporteBtn">Generar Reporte</button>
        </div>
        <div>
            <button id="downloadCsvBtn" style="display:none;">Descargar Excel (XLSX)</button>
        </div>
    </div>
    <div class="table-responsive">
        <table id="reporteTable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Email</th>
                    <th>Valor Total Compras</th>
                    <th>Oportunidades Totales</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>