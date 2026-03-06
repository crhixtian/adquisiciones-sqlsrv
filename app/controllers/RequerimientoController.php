<?php
// controlador que maneja las acciones sobre los requerimientos (listar, crear, mostrar, eliminar)

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Requerimiento.php';
require_once __DIR__ . '/../models/CentroCosto.php';
require_once __DIR__ . '/../models/DetalleRequerimiento.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';

class RequerimientoController extends Controller
{
    // muestra todos los requerimientos junto con los centros de costo disponibles
    public function index()
    {
        $years = Requerimiento::years();

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

        $requerimientos = Requerimiento::all($selectedYear);
        $centros = CentroCosto::all();
        $this->render('requerimientos/index', [
            'requerimientos' => $requerimientos,
            'centros' => $centros,
            'years' => $years,
            'selectedYear' => $selectedYear,
        ]);
    }

    // crea un nuevo requerimiento a partir de los datos POST
    public function store()
    {
        try {
            Requerimiento::create($_POST);
            $this->redirect('index.php?controller=requerimiento&action=index');
        } catch (Exception $e) {
            // SQLSRV uses SQLSTATE codes; 23000 indica violación de clave única
            if ((string)$e->getCode() === '23000') {
                die("Ya existe un requerimiento con ese número de pedido para ese año.");
            }
            die("Error al guardar: " . $e->getMessage());
        }
    }

    // visualiza un requerimiento específico con sus detalles
    public function show()
    {
        if (!isset($_GET['id'])) {
            die("Requerimiento no especificado.");
        }
        $id = $_GET['id'];
        
        // Manejar cambio de estado si es POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
            $nuevoEstado = isset($_POST['estado']) && (int)$_POST['estado'] === 1 ? 1 : 0;
            Requerimiento::updateEstado($id, $nuevoEstado);
            $this->redirect('index.php?controller=requerimiento&action=show&id=' . urlencode($id));
        }
        
        $requerimiento = Requerimiento::find($id);
        if (!$requerimiento) {
            die("Requerimiento no encontrado.");
        }
        $detalles = Requerimiento::detalles($id);
        $catalogos = CatalogoTecnologico::allActive();
        $this->render('requerimientos/show', ['hoja' => $requerimiento, 'detalles' => $detalles, 'catalogos' => $catalogos]);
    }

    // elimina un requerimiento identificado por id
    public function delete()
    {
        if (!isset($_GET['id'])) {
            die("Requerimiento no especificado.");
        }
        $selectedYear = isset($_GET['year']) && $_GET['year'] !== '' && $_GET['year'] !== 'all'
            ? (int) $_GET['year']
            : null;

        Requerimiento::delete($_GET['id']);

        $url = 'index.php?controller=requerimiento&action=index';
        if ($selectedYear !== null) {
            $url .= '&year=' . $selectedYear;
        }
        $this->redirect($url);
    }
}
