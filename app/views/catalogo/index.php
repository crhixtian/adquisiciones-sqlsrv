<?php // vista que muestra códigos SIGA consolidados y filtro por año ?>
<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Códigos SIGA Consolidados</h3>
            <form method="get" action="index.php" class="d-flex align-items-center">
                <input type="hidden" name="controller" value="catalogo">
                <input type="hidden" name="action" value="index">
                <label class="me-2 mb-0">Año:</label>
                <select name="year" class="form-select form-select-sm me-2" style="width:120px;" onchange="this.form.submit()">
                    <option value="all" <?= $selectedYear === null ? 'selected' : '' ?>>Todos</option>
                    <?php if (!empty($years)): ?>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= ($selectedYear === (int)$y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <noscript><button class="btn btn-sm btn-primary">Filtrar</button></noscript>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Código SIGA</th>
                        <th>Tecnología</th>
                        <th>Nombre Genérico</th>
                        <th>Estudios</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $row): ?>
                        <tr>
                            <td><span class="badge bg-blue-lt"><?= htmlspecialchars($row['CodigoSiga']) ?></span></td>
                            <td><?= htmlspecialchars($row['Tecnologia']) ?></td>
                            <td><?= htmlspecialchars($row['NombreGenerico']) ?></td>
                            <td>
                                <?php if ($row['TotalEstudios'] >= 4): ?>
                                    <span class="badge bg-success text-white"><?= $row['TotalEstudios'] ?> Estudios</span>
                                <?php else: ?>
                                    <span class="badge bg-red text-white"><?= $row['TotalEstudios'] ?> Estudios</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?controller=catalogo&action=editEstudios&id=<?= $row['IdCatalogo'] ?>" class="btn btn-sm btn-primary">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
