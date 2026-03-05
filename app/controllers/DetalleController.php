<?php
// controlador responsable de crear, editar y eliminar items dentro de un requerimiento

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/DetalleRequerimiento.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';

class DetalleController extends Controller
{
    // guarda un nuevo item dentro de un requerimiento
    public function store()
    {
        $data = $_POST;
        if (DetalleRequerimiento::existsCodigoEnRequerimiento($data['IdRequerimiento'], $data['CodigoSiga'])) {
            die("El Código SIGA '{$data['CodigoSiga']}' ya existe en este requerimiento.");
        }
        DetalleRequerimiento::create($data);
        $this->redirect('index.php?controller=requerimiento&action=show&id=' . $data['IdRequerimiento']);
    }

    // muestra el formulario de edición para un item existente
    public function edit()
    {
        if (!isset($_GET['id'])) die("Ítem no especificado.");
        $detalle = DetalleRequerimiento::find($_GET['id']);
        if (!$detalle) die("Ítem no encontrado.");
        $idRequerimiento = $detalle['IdRequerimiento'];
        $catalogos = CatalogoTecnologico::allActive();
        $this->render('detalleRequerimientos/edit', ['detalle' => $detalle, 'catalogos' => $catalogos]);
    }

    // actualiza un item ya existente
    public function update()
    {
        $id = $_POST['Id'];
        $data = $_POST;

        if (DetalleRequerimiento::existsCodigoEnRequerimiento($data['IdRequerimiento'], $data['CodigoSiga'], $id)) {
            die("El Código SIGA '{$data['CodigoSiga']}' ya existe en este requerimiento.");
        }
        DetalleRequerimiento::update($id, $data);
        $this->redirect('index.php?controller=requerimiento&action=show&id=' . $data['IdRequerimiento']);
    }

    // elimina un item de un requerimiento
    public function delete()
    {
        if (!isset($_GET['id'])) die("Ítem no especificado.");
        $detalle = DetalleRequerimiento::find($_GET['id']);
        if (!$detalle) die("Ítem no encontrado.");
        DetalleRequerimiento::delete($_GET['id']);
        $this->redirect('index.php?controller=requerimiento&action=show&id=' . $detalle['IdRequerimiento']);
    }
}
