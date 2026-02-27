<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Estudios de Mercado</h3>
            <div>
                <span class="badge bg-blue-lt"><?= htmlspecialchars($catalogo['Tecnologia']) ?></span>
                <?= htmlspecialchars($catalogo['NombreGenerico']) ?></div>
        </div>
        <div class="card-body">
            <h4>Estudios Registrados</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($estudios) > 0): ?>
                        <?php foreach ($estudios as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['Marca']) ?></td>
                                <td><?= htmlspecialchars($e['Modelo']) ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($e['RutaDocumento']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver PDF</a>
                                </td>
                                <?php
                                    $fecha = $e['FechaRegistro'] ?? null;
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
                                    <a href="index.php?controller=catalogo&action=deleteEstudio&eliminar=<?= $e['Id'] ?>&id=<?= $catalogo['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar estudio?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay estudios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <hr>
            <h4>Agregar Nuevo Estudio</h4>
            <form method="POST" action="index.php?controller=catalogo&action=uploadEstudio" enctype="multipart/form-data">
                <input type="hidden" name="IdCatalogoTec" value="<?= $catalogo['Id'] ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Marca</label>
                        <input type="text" name="Marca" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="Modelo" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PDF</label>
                        <input type="file" name="Documento" accept="application/pdf" class="form-control" required>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <a href="index.php?controller=catalogo&action=index" class="btn btn-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar Estudio</button>
                </div>
            </form>
        </div>
    </div>
</div>
