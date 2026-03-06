<?php // vista detallada de un requerimiento con sus ítems y formulario de agregar detalle 

$estadoActual = (int)($hoja['Estado'] ?? 0);
$estadoTexto = $estadoActual === 1 ? 'Completo' : 'Incompleto';
$estadoClase = $estadoActual === 1 ? 'bg-green text-white' : 'bg-yellow text-dark';

?>
<div class="col-12">
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Pedido de Compra: <?= htmlspecialchars($hoja['NroPedidoCompra']) ?></h3>
            <div class="card-actions d-flex align-items-center gap-2">
                <span class="badge <?= $estadoClase ?>"><?= $estadoTexto ?></span>
                <form method="post" action="index.php?controller=requerimiento&action=show&id=<?= urlencode($hoja['Id']) ?>" class="m-0">
                    <input type="hidden" name="estado" value="<?= $estadoActual === 1 ? 0 : 1 ?>">
                    <button type="submit" name="cambiar_estado" value="1" class="btn btn-sm btn-outline-primary">
                        Marcar como <?= $estadoActual === 1 ? 'Incompleto' : 'Completo' ?>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <strong>Centro:</strong> <?= htmlspecialchars($hoja['NombreCentroCosto']) ?><br>
            <strong>Año:</strong> <?= htmlspecialchars($hoja['Anio']) ?>
        </div>
    </div>

    <!-- Modal Nuevo Ítem -->
    <div class="modal modal-blur fade" id="createItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Ítem - Pedido de Compra <?= htmlspecialchars($hoja['NroPedidoCompra']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="index.php?controller=detalle&action=store">
                    <input type="hidden" name="IdRequerimiento" value="<?= htmlspecialchars($hoja['Id']) ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Código SIGA</label>
                            <input type="text" name="CodigoSiga" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción Detallada</label>
                            <textarea name="DescripcionDetallada" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="1" min="1" name="Cantidad" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidad de Medida</label>
                            <input type="text" name="UnidadMedida" class="form-control" value="UND" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Homologar a Catálogo</label>
                            <select name="IdCatalogoTecnologico" class="form-select">
                                <option value=""></option>
                                <?php foreach ($catalogos as $cat): ?>
                                    <option value="<?= $cat['Id'] ?>"><?= htmlspecialchars($cat['Codigo'] . ' - ' . $cat['NombreGenerico']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Ítem</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ítems Registrados</h3>
            <div class="card-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createItemModal">
                    + Agregar Ítem
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-hover card-table">
                <thead>
                    <tr>
                        <th>Código SIGA</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Homologación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($detalles) > 0): ?>
                        <?php foreach ($detalles as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['CodigoSiga']) ?></td>
                                <td><?= htmlspecialchars($row['DescripcionDetallada']) ?></td>
                                <td><?= htmlspecialchars($row['Cantidad']) ?></td>
                                <td><?= htmlspecialchars($row['UnidadMedida']) ?></td>
                                <td>
                                    <?php if ($row['NombreGenerico'] && $row['NombreGenerico'] !== 'SIN HOMOLOGAR'): ?>
                                        <span class="badge bg-green text-white"><?= htmlspecialchars($row['NombreGenerico']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-red text-white">SIN HOMOLOGAR</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?controller=detalle&action=edit&id=<?= $row['Id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                    <a href="index.php?controller=detalle&action=delete&id=<?= $row['Id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este ítem?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay ítems registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="index.php?controller=requerimiento&action=index" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>