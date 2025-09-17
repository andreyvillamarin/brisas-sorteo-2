<div class="header">
    <h1>Gestionar Administradores</h1>
</div>
<div class="grid-container">
    <div class="card">
        <h3>Agregar Nuevo Administrador</h3>
        <form id="addAdminForm">
            <div class="form-group">
                <label for="nuevo_usuario">Usuario</label>
                <input type="text" id="nuevo_usuario" required>
            </div>
            <div class="form-group">
                <label for="nueva_password">Contraseña</label>
                <input type="password" id="nueva_password" required>
            </div>
            <button type="submit">Crear Administrador</button>
        </form>
    </div>
    <div class="card">
        <h3>Administradores Actuales</h3>
        <table id="adminUsersTable">
            <thead><tr><th>Usuario</th><th>Acciones</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>