<?php // vista para editar y agregar estudios de mercado de un catálogo 
?>
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex flex-column align-items-start">
            <h3 class="card-title">Fichas Técnicas</h3>
            <div class="mt-2">
                <span class="badge bg-blue-lt">
                    <?= htmlspecialchars($catalogo['Tecnologia']) ?>
                </span>
                <?= htmlspecialchars($catalogo['NombreGenerico']) ?>
            </div>
        </div>
        <div class="card-body">
            <h4>Pedidos de Compra donde aparece</h4>
            <form method="get" action="index.php" class="d-flex align-items-center mb-3">
                <input type="hidden" name="controller" value="catalogo">
                <input type="hidden" name="action" value="editEstudios">
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
                                    <td><?= htmlspecialchars($pedido['NPedidoCompra']) ?></td>
                                    <td><?= htmlspecialchars($pedido['NombreCentro']) ?></td>
                                    <td><?= htmlspecialchars($pedido['AnioFiscal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-3">Este código no aparece en ningún pedido de compra.</p>
            <?php endif; ?>

            <h4>Fichas Tecnicas Registradas</h4>
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
                                    <button type="button" class="btn btn-sm btn-outline-primary view-pdf-btn" data-url="<?= htmlspecialchars($e['RutaDocumento']) ?>">Ver PDF</button>
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
            <div id="pdf-viewer-container" class="mt-3" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="m-0">Visor de PDF</h5>
                    <button type="button" id="close-pdf-viewer" class="btn btn-sm btn-outline-secondary">Cerrar</button>
                </div>
                <div style="width:100%;height:600px;">
                    <iframe id="pdf-viewer" src="" style="width:100%;height:100%;border:1px solid #ddd;" frameborder="0"></iframe>
                </div>
            </div>
            <hr>
            <h4>Agregar Nuevo Ficha Técnica</h4>
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
                    <button type="submit" class="btn btn-primary">Guardar Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>
        <script>
        document.addEventListener('click', function(e){
            var btn = e.target.closest && e.target.closest('.view-pdf-btn');
            if(btn){
                var url = btn.getAttribute('data-url');
                if(!url) return;
                var container = document.getElementById('pdf-viewer-container');
                var iframe = document.getElementById('pdf-viewer');
                iframe.src = url;
                container.style.display = 'block';
                iframe.scrollIntoView({behavior:'smooth'});
            }
            if(e.target && e.target.id === 'close-pdf-viewer'){
                var container = document.getElementById('pdf-viewer-container');
                var iframe = document.getElementById('pdf-viewer');
                iframe.src = '';
                container.style.display = 'none';
            }
        });
        </script>