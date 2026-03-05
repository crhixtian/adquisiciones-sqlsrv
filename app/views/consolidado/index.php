<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Consolidado de Equipos por Centro de Costo</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <form method="GET" class="d-flex align-items-center">
                                <input type="hidden" name="controller" value="consolidado">
                                <input type="hidden" name="action" value="index">
                                <label for="year" class="me-2 mb-0">Año:</label>
                                <select id="year" name="year" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= (int)$y ?>" <?= ((int)$selectedYear === (int)$y) ? 'selected' : '' ?>><?= (int)$y ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <a href="index.php?controller=consolidado&action=exportarExcel&year=<?= (int)$selectedYear ?>" class="btn btn-success btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-spreadsheet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                                Exportar a Excel
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>EQUIPO</th>
                                        <?php foreach ($centrosCosto as $idCentro): ?>
                                            <th class="text-center" style="width: 60px;">
                                                <small><?php echo htmlspecialchars($centrosSiglas[$idCentro]); ?></small>
                                            </th>
                                        <?php endforeach; ?>
                                        <th class="text-center" style="width: 60px;"><strong>TOTAL</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalesPorCentro = [];
                                    foreach ($centrosCosto as $idCentro) {
                                        $totalesPorCentro[$idCentro] = 0;
                                    }
                                    
                                    foreach ($equipos as $equipo => $cantidades):
                                        $totalEquipo = 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($equipo); ?></strong>
                                            </td>
                                            <?php foreach ($centrosCosto as $idCentro): ?>
                                                <td class="text-center">
                                                    <?php
                                                    $cantidad = isset($cantidades[$idCentro]) ? $cantidades[$idCentro] : '';
                                                    echo $cantidad ?: '';
                                                    if ($cantidad) {
                                                        $totalEquipo += $cantidad;
                                                        $totalesPorCentro[$idCentro] += $cantidad;
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center">
                                                <strong><?php echo $totalEquipo > 0 ? $totalEquipo : ''; ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-active">
                                        <td><strong>TOTAL</strong></td>
                                        <?php
                                        $totalGeneral = 0;
                                        foreach ($centrosCosto as $idCentro):
                                            $total = $totalesPorCentro[$idCentro];
                                            $totalGeneral += $total;
                                        ?>
                                            <td class="text-center">
                                                <strong><?php echo $total > 0 ? $total : ''; ?></strong>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-center">
                                            <strong><?php echo $totalGeneral > 0 ? $totalGeneral : ''; ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    @media print {
        .table {
            font-size: 11px;
        }
        .table thead th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .table-active {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
