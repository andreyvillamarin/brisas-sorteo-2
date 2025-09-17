<div class="header">
    <h1>Dashboard</h1>
    <p>Registros del día: <?php echo date('d/m/Y'); ?></p>
</div>

<div class="card">
    <div class="card-header filter-controls">
        <div class="input-group-icon">
            <i class="fas fa-search icon"></i>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Buscar por nombre, cédula, punto de venta...">
        </div>
        <div class="input-group-icon">
            <i class="fas fa-calendar-alt icon"></i>
            <input type="date" id="datePicker" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
    <div class="table-responsive">
        <table id="registrosTable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Valor Compra</th>
                    <th>Punto de Venta</th>
                    <th>Factura</th>
                    <th>Oportunidades</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
        </table>
    </div>
</div>