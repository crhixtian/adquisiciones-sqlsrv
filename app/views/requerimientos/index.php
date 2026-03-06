<?php // vista de listado de hojas SIGA, incluye tabla y modales 
?>
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Lista de Pedidos de Compra</h3>
            <div class="card-actions d-flex align-items-center gap-2">
                <form method="get" action="index.php" class="d-flex align-items-center">
                    <input type="hidden" name="controller" value="hoja">
                    <input type="hidden" name="action" value="index">
                    <label class="me-2 mb-0">Año:</label>
                    <select name="year" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                        <option value="all" <?= $selectedYear === null ? 'selected' : '' ?>>Todos</option>
                        <?php if (!empty($years)): ?>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= (int)$y ?>" <?= ($selectedYear === (int)$y) ? 'selected' : '' ?>><?= (int)$y ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <noscript><button class="btn btn-sm btn-primary ms-2">Filtrar</button></noscript>
                </form>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHojaModal">+ Nuevo</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>Nro. de Pedido</th>
                        <th>Dirección Solicitante</th>
                        <th>Año</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($hojas) > 0): ?>
                        <?php foreach ($hojas as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['NroPedidoCompra']) ?></td>
                                <td><?= htmlspecialchars($row['NombreCentroCosto']) ?></td>
                                <td><?= htmlspecialchars($row['Anio']) ?></td>
                                <td>
                                    <?php if ((int)($row['Estado'] ?? 0) === 1): ?>
                                        <span class="badge bg-green text-white">Completo</span>
                                    <?php else: ?>
                                        <span class="badge bg-yellow text-white">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?controller=requerimiento&action=show&id=<?= $row['Id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['Id'] ?>">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal de confirmación -->
                            <div class="modal modal-blur fade" id="deleteModal<?= $row['Id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmar Eliminación</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar el requerimiento <strong><?= htmlspecialchars($row['NroPedidoCompra']) ?></strong>?</p>
                                            <p class="text-muted">Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <a href="index.php?controller=requerimiento&action=delete&id=<?= $row['Id'] ?><?= $selectedYear !== null ? '&year=' . (int)$selectedYear : '' ?>" class="btn btn-danger">Sí, eliminar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay requerimientos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear nueva hoja -->
<div class="modal modal-blur fade" id="createHojaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Pedido de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="index.php?controller=requerimiento&action=store">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dirección Solicitante</label>
                        <select name="IdCentroCosto" class="form-select" required>
                            <option value=""></option>
                            <?php foreach ($centros as $cc): ?>
                                <option value="<?= $cc['Id'] ?>"><?= htmlspecialchars($cc['NombreCentroCosto']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Pedido</label>
                        <input type="text" name="NroPedidoCompra" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Año</label>
                        <input type="number" name="Anio" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>