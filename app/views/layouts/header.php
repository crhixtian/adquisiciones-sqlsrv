<?php // cabecera HTML común a todas las vistas, carga assets y menú 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adquisiciones</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet" />
</head>

<body>
    <div class="page">
        <div class="page-body">
            <div class="container-xl">
                <!-- navigation menu -->
                <nav class="navbar navbar-expand-md navbar-light bg-white mb-4">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="index.php">PECH</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php?controller=catalogo&action=index">Codigos</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>