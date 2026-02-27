<?php // vista de formulario para editar un ítem de detalle 
?>
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Ítem</h3>
        </div>
        <form method="post" action="index.php?controller=detalle&action=update">
            <input type="hidden" name="Id" value="<?= htmlspecialchars($detalle['Id']) ?>">
            <input type="hidden" name="IdHojaSiga" value="<?= htmlspecialchars($detalle['IdHojaSiga']) ?>">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Código SIGA</label>
                    <input type="text" name="CodigoSiga" class="form-control" value="<?= htmlspecialchars($detalle['CodigoSiga']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción Detallada</label>
                    <textarea name="DescripcionDetallada" class="form-control" rows="3" required><?= htmlspecialchars($detalle['DescripcionDetallada']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Clasificador</label>
                    <input type="text" name="Clasificador" class="form-control" value="<?= htmlspecialchars($detalle['Clasificador']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" step="0.01" name="Cantidad" class="form-control" value="<?= htmlspecialchars($detalle['Cantidad']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Unidad de Medida</label>
                    <input type="text" name="UnidadMedida" class="form-control" value="<?= htmlspecialchars($detalle['UnidadMedida']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Homologar a Catálogo</label>
                    <select name="IdCatalogoTec" class="form-select">
                        <option value=""></option>
                        <?php foreach ($catalogos as $cat): ?>
                            <option value="<?= $cat['Id'] ?>" <?= $detalle['IdCatalogoTec'] == $cat['Id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['Tecnologia'] . ' - ' . $cat['NombreGenerico']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="index.php?controller=hoja&action=show&id=<?= $detalle['IdHojaSiga'] ?>" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Ítem</button>
            </div>
        </form>
    </div>
</div>