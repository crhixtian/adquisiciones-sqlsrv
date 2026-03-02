<?php // vista de listado de hojas SIGA, incluye tabla y modales 
?>
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Pedidos de Compra</h3>
            <div class="card-actions">
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
                                <td><?= htmlspecialchars($row['NPedidoCompra']) ?></td>
                                <td><?= htmlspecialchars($row['NombreCentro']) ?></td>
                                <td><?= htmlspecialchars($row['AnioFiscal']) ?></td>
                                <td>
                                    <?php if ((int)($row['Estado'] ?? 0) === 1): ?>
                                        <span class="badge bg-green text-white">Completo</span>
                                    <?php else: ?>
                                        <span class="badge bg-yellow text-dark">Incompleto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?controller=hoja&action=show&id=<?= $row['Id'] ?>" class="btn btn-sm btn-outline-primary">
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
                                            <p>¿Estás seguro de que deseas eliminar la hoja <strong><?= htmlspecialchars($row['NPedidoCompra']) ?></strong>?</p>
                                            <p class="text-muted">Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <a href="index.php?controller=hoja&action=delete&id=<?= $row['Id'] ?>" class="btn btn-danger">Sí, eliminar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay hojas registradas.</td>
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
            <form method="post" action="index.php?controller=hoja&action=store">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dirección Solicitante</label>
                        <select name="IdCentroCosto" class="form-select" required>
                            <option value=""></option>
                            <?php foreach ($centros as $cc): ?>
                                <option value="<?= $cc['Id'] ?>"><?= htmlspecialchars($cc['NombreCentro']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Pedido</label>
                        <input type="text" name="NPedidoCompra" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta</label>
                        <input type="text" name="Meta" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Año</label>
                        <input type="number" name="AnioFiscal" class="form-control" required>
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