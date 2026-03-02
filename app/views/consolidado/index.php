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
