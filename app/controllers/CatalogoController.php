<?php
// controlador para gestionar tecnologias(T1, T2, T3, ...) y fichas tecnicas (PDF)

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/EstudioMercado.php';

class CatalogoController extends Controller
{
    // lista los registros de tecnologias con conteo de fichas tecnicas (PDF)
    public function index()
    {
        // obtener años desde HojaSiga
        $conn = Database::connect();
        $stmtYears = $conn->query("SELECT DISTINCT AnioFiscal FROM HojaSiga ORDER BY AnioFiscal DESC");
        $rows = $stmtYears->fetchAll();
        // obtener únicamente la primera columna (AnioFiscal) como arreglo simple
        $years = array_column($rows, 'AnioFiscal');

        $selectedYear = null;
        $hasRequest = isset($_GET['year']);
        if ($hasRequest) {
            if ($_GET['year'] === 'all') {
                $selectedYear = null;
            } else {
                $selectedYear = (int) $_GET['year'];
            }
            if ($selectedYear !== null && !in_array($selectedYear, $years, true)) {
                $selectedYear = (int) ($years[0] ?? $selectedYear);
            }
        } else {
            // si no se pidió año, tomar primero de la lista
            if (!empty($years)) {
                $selectedYear = (int) $years[0];
            }
        }

        $registros = CatalogoTecnologico::withEstudiosCount($selectedYear);
        $this->render('catalogo/index', ['registros' => $registros, 'years' => $years, 'selectedYear' => $selectedYear]);
    }

    // muestra formulario para editar fichas tecnicas (PDF) de un registro de tecnologia
    public function editEstudios()
    {
        if (!isset($_GET['id'])) die("ID no válido.");
        $id = (int) $_GET['id'];
        $catalogo = CatalogoTecnologico::find($id);
        if (!$catalogo) die("Catálogo no encontrado.");

        $years = CatalogoTecnologico::pedidosCompraYearsByCatalogo($id);

        $selectedYear = null;
        if (isset($_GET['year']) && $_GET['year'] !== 'all' && $_GET['year'] !== '') {
            $selectedYear = (int) $_GET['year'];
            if (!in_array($selectedYear, $years, true)) {
                $selectedYear = null;
            }
        } elseif (!empty($years)) {
            $selectedYear = (int) $years[0];
        }

        $estudios = EstudioMercado::getByCatalogo($id, $selectedYear);

        $pedidosCompra = CatalogoTecnologico::pedidosCompraByCatalogo($id, $selectedYear);
        $this->render('catalogo/edit_estudios', [
            'catalogo' => $catalogo,
            'estudios' => $estudios,
            'pedidosCompra' => $pedidosCompra,
            'years' => $years,
            'selectedYear' => $selectedYear
        ]);
    }

    // procesa carga de nuevo archivo PDF
    public function uploadEstudio()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCatalogo = (int) $_POST['IdCatalogoTec'];
            $marca = trim($_POST['Marca']);
            $modelo = trim($_POST['Modelo']);
            $anioFiscal = isset($_POST['AnioFiscal']) && $_POST['AnioFiscal'] !== '' ? (int) $_POST['AnioFiscal'] : null;

            if ($anioFiscal === null) {
                die("Debe seleccionar un año fiscal para la ficha técnica.");
            }

            $years = CatalogoTecnologico::pedidosCompraYearsByCatalogo($idCatalogo);
            if (!in_array($anioFiscal, $years, true)) {
                die("El año fiscal seleccionado no es válido para este catálogo.");
            }

            if (!isset($_FILES['Documento']) || $_FILES['Documento']['error'] !== 0) {
                die("Error al subir archivo.");
            }

            $directorio = __DIR__ . '/../../uploads/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $nombreArchivo = time() . "_" . basename($_FILES["Documento"]["name"]);
            $rutaFinal = "uploads/" . $nombreArchivo;

            if (!move_uploaded_file($_FILES["Documento"]["tmp_name"], __DIR__ . '/../../' . $rutaFinal)) {
                die("No se pudo guardar el archivo.");
            }

            EstudioMercado::create([
                'IdCatalogoTec' => $idCatalogo,
                'Marca' => $marca,
                'Modelo' => $modelo,
                'AnioFiscal' => $anioFiscal,
                'RutaDocumento' => $rutaFinal
            ]);

            $this->redirect('index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo . '&year=' . $anioFiscal);
        }
    }

    // elimina un item y su documento físico si existe (PDF)
    public function deleteEstudio()
    {
        if (!isset($_GET['eliminar']) || !isset($_GET['id'])) {
            die("Parámetros inválidos.");
        }
        $idEstudio = (int) $_GET['eliminar'];
        $idCatalogo = (int) $_GET['id'];
        $selectedYear = isset($_GET['year']) && $_GET['year'] !== '' && $_GET['year'] !== 'all'
            ? (int) $_GET['year']
            : null;

        $estudio = EstudioMercado::find($idEstudio);
        if ($estudio && file_exists(__DIR__ . '/../../' . $estudio['RutaDocumento'])) {
            unlink(__DIR__ . '/../../' . $estudio['RutaDocumento']);
        }
        EstudioMercado::delete($idEstudio);

        $url = 'index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo;
        if ($selectedYear !== null) {
            $url .= '&year=' . $selectedYear;
        }

        $this->redirect($url);
    }
}
