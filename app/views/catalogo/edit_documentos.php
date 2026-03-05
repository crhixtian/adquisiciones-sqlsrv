<?php // vista para editar y agregar fichas técnicas de una tecnología
?>
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex flex-column align-items-start">
            <h3 class="card-title">Fichas Técnicas y Términos de Referencia</h3>
            <div class="mt-2">
                <span class="badge bg-blue-lt">
                    <?= htmlspecialchars($catalogo['CategoriaTecnologica']) ?>
                </span>
                <?= htmlspecialchars($catalogo['NombreGenerico']) ?>
            </div>
        </div>
        <div class="card-body">
            <h4>Pedidos de Compra donde aparece</h4>
            <form method="get" action="index.php" class="d-flex align-items-center mb-3">
                <input type="hidden" name="controller" value="catalogo">
                <input type="hidden" name="action" value="editDocumentos">
                <input type="hidden" name="id" value="<?= (int)$catalogo['Id'] ?>">
                <label class="me-2 mb-0">Año:</label>
                <select name="year" class="form-select form-select-sm me-2" style="width:120px;" onchange="this.form.submit()">
                    <option value="all" <?= $selectedYear === null ? 'selected' : '' ?>>Todos</option>
                    <?php if (!empty($years)): ?>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int)$y ?>" <?= ($selectedYear === (int)$y) ? 'selected' : '' ?>><?= (int)$y ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <noscript><button class="btn btn-sm btn-primary">Filtrar</button></noscript>
            </form>

            <?php if (!empty($pedidosCompra)): ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-vcenter">
                        <thead>
                            <tr>
                                <th>Nro. de Pedido</th>
                                <th>Dirección Solicitante</th>
                                <th>Año</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosCompra as $pedido): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pedido['NroPedidoCompra']) ?></td>
                                    <td><?= htmlspecialchars($pedido['NombreCentroCosto']) ?></td>
                                    <td><?= htmlspecialchars($pedido['Anio']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-3">Este código no aparece en ningún pedido de compra.</p>
            <?php endif; ?>

            <h4>Fichas Técnicas Registradas</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fichasTecnicas) > 0): ?>
                        <?php foreach ($fichasTecnicas as $f): ?>
                            <tr>
                                <td><?= isset($f['Anio']) ? (int)$f['Anio'] : '-' ?></td>
                                <td><?= htmlspecialchars($f['Marca']) ?></td>
                                <td><?= htmlspecialchars($f['Modelo']) ?></td>
                                <td>
                                    <a href="index.php?controller=catalogo&action=downloadDocumento&tipo=ficha&id=<?= $f['Id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <?= htmlspecialchars(basename($f['RutaDocumento'])) ?>
                                    </a>
                                </td>
                                <?php
                                $fecha = $f['FechaRegistro'] ?? null;
                                if ($fecha instanceof DateTime) {
                                    $fechaText = date('d/m/Y', $fecha->getTimestamp());
                                } elseif ($fecha) {
                                    $fechaText = date('d/m/Y', strtotime($fecha));
                                } else {
                                    $fechaText = '';
                                }
                                ?>
                                <td><?= $fechaText ?></td>
                                <td class="text-end">
                                    <a href="index.php?controller=catalogo&action=deleteFichaTecnica&eliminar=<?= $f['Id'] ?>&id=<?= $catalogo['Id'] ?><?= $selectedYear !== null ? '&year=' . (int)$selectedYear : '' ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar ficha técnica?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay fichas técnicas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <hr>

            <h4>Términos de Referencia Registrados</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Código TDR</th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($terminosReferencia) > 0): ?>
                        <?php foreach ($terminosReferencia as $t): ?>
                            <tr>
                                <td><?= isset($t['Anio']) ? (int)$t['Anio'] : '-' ?></td>
                                <td><?= htmlspecialchars($t['CodigoTDR']) ?></td>
                                <td>
                                    <a href="index.php?controller=catalogo&action=downloadDocumento&tipo=termino&id=<?= $t['Id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <?= htmlspecialchars(basename($t['RutaDocumento'])) ?>
                                    </a>
                                </td>
                                <?php
                                $fecha = $t['FechaRegistro'] ?? null;
                                if ($fecha instanceof DateTime) {
                                    $fechaText = date('d/m/Y', $fecha->getTimestamp());
                                } elseif ($fecha) {
                                    $fechaText = date('d/m/Y', strtotime($fecha));
                                } else {
                                    $fechaText = '';
                                }
                                ?>
                                <td><?= $fechaText ?></td>
                                <td class="text-end">
                                    <a href="index.php?controller=catalogo&action=deleteTerminosReferencia&eliminar=<?= $t['Id'] ?>&id=<?= $catalogo['Id'] ?><?= $selectedYear !== null ? '&year=' . (int)$selectedYear : '' ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar término de referencia?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay términos de referencia registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <hr>

            <h4>Agregar Nueva Ficha Técnica</h4>
            <form method="POST" action="index.php?controller=catalogo&action=uploadFichaTecnica" enctype="multipart/form-data">
                <input type="hidden" name="IdCatalogoTecnologico" value="<?= $catalogo['Id'] ?>">
                <input type="hidden" name="Anio" value="<?= $selectedYear !== null ? (int)$selectedYear : '' ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Marca</label>
                        <input type="text" name="Marca" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="Modelo" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Documento PDF</label>
                        <input type="file" name="Documento" accept="application/pdf" class="form-control" required>
                    </div>
                </div>
                <?php if ($selectedYear === null): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        Para registrar una ficha técnica, seleccione un año específico en el filtro superior.
                    </div>
                <?php endif; ?>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary" <?= $selectedYear === null ? 'disabled' : '' ?>>Guardar Ficha Técnica</button>
                </div>
            </form>

            <hr>

            <h4>Agregar Nuevo Término de Referencia</h4>
            <form method="POST" action="index.php?controller=catalogo&action=uploadTerminosReferencia" enctype="multipart/form-data">
                <input type="hidden" name="IdCatalogoTecnologico" value="<?= $catalogo['Id'] ?>">
                <input type="hidden" name="Anio" value="<?= $selectedYear !== null ? (int)$selectedYear : '' ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Código TDR</label>
                        <input type="text" name="CodigoTDR" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Documento PDF</label>
                        <input type="file" name="Documento" accept="application/pdf" class="form-control" required>
                    </div>
                </div>
                <?php if ($selectedYear === null): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        Para registrar un término de referencia, seleccione un año específico en el filtro superior.
                    </div>
                <?php endif; ?>
                <div class="mt-3 text-end">
                    <a href="index.php?controller=catalogo&action=index<?= $selectedYear !== null ? '&year=' . (int)$selectedYear : '' ?>" class="btn btn-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary" <?= $selectedYear === null ? 'disabled' : '' ?>>Guardar Término de Referencia</button>
                </div>
            </form>
        </div>
    </div>
</div>
