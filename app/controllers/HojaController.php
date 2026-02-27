<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/HojaSiga.php';
require_once __DIR__ . '/../models/CentroCosto.php';
require_once __DIR__ . '/../models/Detalle.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';

class HojaController extends Controller {
    public function index() {
        $hojas = HojaSiga::all();
        $centros = CentroCosto::all();
        $this->render('hojas/index', ['hojas' => $hojas, 'centros' => $centros]);
    }

    public function store() {
        try {
            HojaSiga::create($_POST);
            $this->redirect('index.php?controller=hoja&action=index');
        } catch (Exception $e) {
            // SQLSRV uses SQLSTATE codes; 23000 indica violación de clave única
            if ((string)$e->getCode() === '23000') {
                die("Ya existe una hoja con ese número de pedido para ese año.");
            }
            die("Error al guardar: " . $e->getMessage());
        }
    }

    public function show() {
        if (!isset($_GET['id'])) {
            die("Hoja no especificada.");
        }
        $id = $_GET['id'];
        $hoja = HojaSiga::find($id);
        if (!$hoja) {
            die("Hoja no encontrada.");
        }
        $detalles = HojaSiga::detalles($id);
        $catalogos = CatalogoTecnologico::allActive();
        $this->render('hojas/show', ['hoja' => $hoja, 'detalles' => $detalles, 'catalogos' => $catalogos]);
    }

    public function delete() {
        if (!isset($_GET['id'])) {
            die("Hoja no especificada.");
        }
        HojaSiga::delete($_GET['id']);
        $this->redirect('index.php?controller=hoja&action=index');
    }
}
