<div class="header">
    <h1>Gestionar Puntos de Venta</h1>
</div>

<div class="grid-container">
    <div class="card">
        <h3>Agregar Nuevo Punto de Venta</h3>
        <form id="addPuntoVentaForm">
            <div class="form-group">
                <label for="nombre_pv">Nombre</label>
                <input type="text" id="nombre_pv" required>
            </div>
            <div class="form-group">
                <label for="valor_minimo">Valor Mínimo de Compra</label>
                <input type="number" id="valor_minimo" required>
            </div>
            <button type="submit">Agregar</button>
        </form>
    </div>

    <div class="card">
        <h3>Puntos de Venta Existentes</h3>
        <div class="table-responsive">
            <table id="puntosVentaTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Valor Mínimo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>
</div>