<?php
// controlador responsable de crear, editar y eliminar items dentro de una hoja SIGA

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Detalle.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';

class DetalleController extends Controller
{
    // guarda un nuevo item dentro de una hoja SIGA
    public function store()
    {
        $data = $_POST;
        if (Detalle::existsCodigoEnHoja($data['IdHojaSiga'], $data['CodigoSiga'])) {
            die("El Código SIGA '{$data['CodigoSiga']}' ya existe en esta hoja.");
        }
        Detalle::create($data);
        $this->redirect('index.php?controller=hoja&action=show&id=' . $data['IdHojaSiga']);
    }

    // muestra el formulario de edición para un item existente
    public function edit()
    {
        if (!isset($_GET['id'])) die("Ítem no especificado.");
        $detalle = Detalle::find($_GET['id']);
        if (!$detalle) die("Ítem no encontrado.");
        $idHoja = $detalle['IdHojaSiga'];
        $catalogos = CatalogoTecnologico::allActive();
        $this->render('detalles/edit', ['detalle' => $detalle, 'catalogos' => $catalogos]);
    }

    // actualiza un item ya existente
    public function update()
    {
        $id = $_POST['Id'];
        $data = $_POST;

        if (Detalle::existsCodigoEnHoja($data['IdHojaSiga'], $data['CodigoSiga'], $id)) {
            die("El Código SIGA '{$data['CodigoSiga']}' ya existe en esta hoja.");
        }
        Detalle::update($id, $data);
        $this->redirect('index.php?controller=hoja&action=show&id=' . $data['IdHojaSiga']);
    }

    // elimina un item de una hoja SIGA
    public function delete()
    {
        if (!isset($_GET['id'])) die("Ítem no especificado.");
        $detalle = Detalle::find($_GET['id']);
        if (!$detalle) die("Ítem no encontrado.");
        Detalle::delete($_GET['id']);
        $this->redirect('index.php?controller=hoja&action=show&id=' . $detalle['IdHojaSiga']);
    }
}
