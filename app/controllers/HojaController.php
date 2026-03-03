<?php
// controlador que maneja las acciones sobre las hojas SIGA (listar, crear, mostrar, eliminar)

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/HojaSiga.php';
require_once __DIR__ . '/../models/CentroCosto.php';
require_once __DIR__ . '/../models/Detalle.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';

class HojaController extends Controller
{
    // muestra todas las hojas junto con los centros de costo disponibles
    public function index()
    {
        $years = HojaSiga::years();

        $selectedYear = null;
        if (isset($_GET['year'])) {
            if ($_GET['year'] !== 'all' && $_GET['year'] !== '') {
                $selectedYear = (int) $_GET['year'];
                if (!in_array($selectedYear, $years, true)) {
                    $selectedYear = !empty($years) ? (int) $years[0] : null;
                }
            }
        } elseif (!empty($years)) {
            $selectedYear = (int) $years[0];
        }

        $hojas = HojaSiga::all($selectedYear);
        $centros = CentroCosto::all();
        $this->render('hojas/index', [
            'hojas' => $hojas,
            'centros' => $centros,
            'years' => $years,
            'selectedYear' => $selectedYear,
        ]);
    }

    // crea una nueva hoja a partir de los datos POST
    public function store()
    {
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

    // visualiza una hoja específica con sus detalles
    public function show()
    {
        if (!isset($_GET['id'])) {
            die("Hoja no especificada.");
        }
        $id = $_GET['id'];
        
        // Manejar cambio de estado si es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
            $nuevoEstado = isset($_POST['estado']) && (int)$_POST['estado'] === 1 ? 1 : 0;
            HojaSiga::updateEstado($id, $nuevoEstado);
            $this->redirect('index.php?controller=hoja&action=show&id=' . urlencode($id));
        }
        
        $hoja = HojaSiga::find($id);
        if (!$hoja) {
            die("Hoja no encontrada.");
        }
        $detalles = HojaSiga::detalles($id);
        $catalogos = CatalogoTecnologico::allActive();
        $this->render('hojas/show', ['hoja' => $hoja, 'detalles' => $detalles, 'catalogos' => $catalogos]);
    }

    // elimina una hoja identificada por id
    public function delete()
    {
        if (!isset($_GET['id'])) {
            die("Hoja no especificada.");
        }
        $selectedYear = isset($_GET['year']) && $_GET['year'] !== '' && $_GET['year'] !== 'all'
            ? (int) $_GET['year']
            : null;

        HojaSiga::delete($_GET['id']);

        $url = 'index.php?controller=hoja&action=index';
        if ($selectedYear !== null) {
            $url .= '&year=' . $selectedYear;
        }
        $this->redirect($url);
    }
}
