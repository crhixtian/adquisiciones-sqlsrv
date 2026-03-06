<?php // vista para editar y agregar fichas técnicas de una tecnología
?>
<div class="col-12">
    <div class="mb-4">
        <h3 class="mb-1">Fichas Técnicas y Términos de Referencia</h3>
        <div>
            <span class="badge bg-blue-lt">
                <?= htmlspecialchars($catalogo['CategoriaTecnologica']) ?>
            </span>
            <?= htmlspecialchars($catalogo['NombreGenerico']) ?>
        </div>
    </div>

    <?php $bloquearCargaDocumentos = ($selectedYear !== null && count($terminosReferencia) > 0); ?>

    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Pedidos de Compra donde aparece</h4>
                </div>
                <div class="card-body">
                    <form method="get" action="index.php" class="row gy-2 gx-2 align-items-center mb-3">
                        <input type="hidden" name="controller" value="catalogo">
                        <input type="hidden" name="action" value="editDocumentos">
                        <input type="hidden" name="id" value="<?= (int)$catalogo['Id'] ?>">
                        <div class="col-auto">
                            <label class="col-form-label col-form-label-sm">Año:</label>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select form-select-sm" style="min-width:120px;" onchange="this.form.submit()">
                                <option value="all" <?= $selectedYear === null ? 'selected' : '' ?>>Todos</option>
                                <?php if (!empty($years)): ?>
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= (int)$y ?>" <?= ($selectedYear === (int)$y) ? 'selected' : '' ?>><?= (int)$y ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <noscript>
                            <div class="col-auto"><button class="btn btn-sm btn-primary">Filtrar</button></div>
                        </noscript>
                    </form>

                    <?php if (!empty($pedidosCompra)): ?>
                        <div class="table-responsive mb-0">
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
                        <p class="text-muted mb-0">Este código no aparece en ningún pedido de compra.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="card-title mb-0">Fichas Técnicas de Referencia</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
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
                                                <button type="button" class="btn btn-sm btn-outline-primary js-ver-pdf" data-pdf-url="index.php?controller=catalogo&action=viewDocumento&tipo=ficha&id=<?= $f['Id'] ?>">Ver PDF</button>
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
                    </div>

                    <?php if ($bloquearCargaDocumentos): ?>
                        <div class="alert alert-info mb-0">
                            Ya existe un término de referencia para el año seleccionado. La carga de archivos está deshabilitada.
                        </div>
                    <?php else: ?>
                        <hr class="my-4">
                        <h5 class="mb-3">Agregar Nueva Ficha Técnica</h5>
                        <form method="POST" action="index.php?controller=catalogo&action=uploadFichaTecnica" enctype="multipart/form-data">
                            <input type="hidden" name="IdCatalogoTecnologico" value="<?= $catalogo['Id'] ?>">
                            <input type="hidden" name="Anio" value="<?= $selectedYear !== null ? (int)$selectedYear : '' ?>">
                            <div class="row g-3">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="card-title mb-0">Términos de Referencia Registrados</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
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
                                                <button type="button" class="btn btn-sm btn-outline-primary js-ver-pdf" data-pdf-url="index.php?controller=catalogo&action=viewDocumento&tipo=termino&id=<?= $t['Id'] ?>">Ver PDF</button>
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
                    </div>

                    <?php if ($bloquearCargaDocumentos): ?>
                        <div class="alert alert-info mb-0">
                            Ya existe un término de referencia para el año seleccionado. La carga de archivos está deshabilitada.
                        </div>
                    <?php else: ?>
                        <hr class="my-4">
                        <h5 class="mb-3">Agregar Nuevo Término de Referencia</h5>
                        <?php
                        $codigoTdrSugerido = '';
                        if ($selectedYear !== null) {
                            $categoriaTecnologica = strtoupper(trim((string)($catalogo['CategoriaTecnologica'] ?? '')));
                            $codigoCategoria = preg_match('/^T\d+$/', $categoriaTecnologica)
                                ? $categoriaTecnologica
                                : 'T1';
                            $codigoTdrSugerido = 'FT-' . $codigoCategoria . '-' . (int)$selectedYear;
                        }
                        ?>
                        <form method="POST" action="index.php?controller=catalogo&action=uploadTerminosReferencia" enctype="multipart/form-data">
                            <input type="hidden" name="IdCatalogoTecnologico" value="<?= $catalogo['Id'] ?>">
                            <input type="hidden" name="Anio" value="<?= $selectedYear !== null ? (int)$selectedYear : '' ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código TDR</label>
                                    <input type="text" name="CodigoTDR" class="form-control" value="<?= htmlspecialchars($codigoTdrSugerido) ?>" placeholder="TDR-<?= htmlspecialchars(strtoupper((string)($catalogo['CategoriaTecnologica'] ?? 'T1'))) ?>-<?= $selectedYear !== null ? (int)$selectedYear : date('Y') ?>" required>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-backdrop.custom-blur-backdrop {
            backdrop-filter: blur(10px);
            background-color: rgba(15, 23, 42, 0.65);
        }

        .visor-pdf-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(10px);
            z-index: 1040;
            display: none;
        }

        .visor-pdf-overlay.active {
            display: block;
        }
    </style>

    <div class="modal fade" id="visorPdfModal" tabindex="-1" aria-labelledby="visorPdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="visorPdfModalLabel">Vista previa de documento PDF</h5>
                </div>
                <div class="modal-body p-0">
                    <iframe id="visorPdfFrame" src="" style="width: 100%; height: 75vh; border: 0;" title="Vista previa PDF"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cerrarVisorPdf" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div id="visorPdfOverlay" class="visor-pdf-overlay" aria-hidden="true"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var botonesVerPdf = document.querySelectorAll('.js-ver-pdf');
        var visorModalElement = document.getElementById('visorPdfModal');
        var visorFrame = document.getElementById('visorPdfFrame');
        var cerrarVisor = document.getElementById('cerrarVisorPdf');
        var visorOverlay = document.getElementById('visorPdfOverlay');
        var modalInstance = null;

        if (visorModalElement && window.bootstrap && typeof bootstrap.Modal === 'function') {
            modalInstance = bootstrap.Modal.getOrCreateInstance(visorModalElement);
        }

        var clearFrameSource = function () {
            if (visorFrame) {
                visorFrame.src = '';
            }
        };

        // Abre el visor PDF dentro de un modal para mantener la vista principal limpia.
        botonesVerPdf.forEach(function (boton) {
            boton.addEventListener('click', function () {
                var pdfUrl = boton.getAttribute('data-pdf-url');
                if (!pdfUrl || !visorModalElement || !visorFrame) {
                    return;
                }

                visorFrame.src = pdfUrl;

                if (modalInstance) {
                    modalInstance.show();
                } else {
                    visorModalElement.classList.add('show', 'd-block');
                    visorModalElement.style.display = 'block';
                    visorModalElement.removeAttribute('aria-hidden');
                    if (visorOverlay) {
                        visorOverlay.classList.add('active');
                    }
                }
            });
        });

        if (visorModalElement) {
            visorModalElement.addEventListener('shown.bs.modal', function () {
                var backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.classList.add('custom-blur-backdrop');
                }
            });

            visorModalElement.addEventListener('hidden.bs.modal', function () {
                clearFrameSource();
                if (visorOverlay) {
                    visorOverlay.classList.remove('active');
                }
            });
        }

        if (cerrarVisor) {
            cerrarVisor.addEventListener('click', function () {
                if (!modalInstance && visorModalElement) {
                    visorModalElement.classList.remove('show', 'd-block');
                    visorModalElement.style.display = 'none';
                    visorModalElement.setAttribute('aria-hidden', 'true');
                    if (visorOverlay) {
                        visorOverlay.classList.remove('active');
                    }
                }
                clearFrameSource();
            });
        }
    });
</script>
