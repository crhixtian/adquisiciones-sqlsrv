<?php
// controlador para gestionar tecnologias(T1, T2, T3, ...) y fichas tecnicas (PDF)

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/CatalogoTecnologico.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/FichaTecnica.php';
require_once __DIR__ . '/../models/TerminosReferencia.php';

class CatalogoController extends Controller
{
    // lista los registros de tecnologias con conteo de fichas tecnicas
    public function index()
    {
        // obtener años desde Requerimiento
        $conn = Database::connect();
        $stmtYears = $conn->query("SELECT DISTINCT Anio FROM Requerimiento ORDER BY Anio DESC");
        $rows = $stmtYears->fetchAll();
        // obtener únicamente la primera columna (Anio) como arreglo simple
        $years = array_column($rows, 'Anio');

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

    // muestra formulario para editar fichas tecnicas de un registro de tecnologia
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

        $fichasTecnicas = FichaTecnica::getByCatalogo($id, $selectedYear);
        $terminosReferencia = TerminosReferencia::getByCatalogo($id, $selectedYear);

        $pedidosCompra = CatalogoTecnologico::pedidosCompraByCatalogo($id, $selectedYear);
        $this->render('catalogo/edit_documentos', [
            'catalogo' => $catalogo,
            'fichasTecnicas' => $fichasTecnicas,
            'terminosReferencia' => $terminosReferencia,
            'pedidosCompra' => $pedidosCompra,
            'years' => $years,
            'selectedYear' => $selectedYear
        ]);
    }

    // procesa carga de nueva ficha técnica (PDF como VARBINARY)
    public function uploadEstudio()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCatalogo = (int) $_POST['IdCatalogoTecnologico'];
            $marca = trim($_POST['Marca']);
            $modelo = trim($_POST['Modelo']);
            $anio = isset($_POST['Anio']) && $_POST['Anio'] !== '' ? (int) $_POST['Anio'] : null;

            if ($anio === null) {
                die("Debe seleccionar un año para la ficha técnica.");
            }

            $years = CatalogoTecnologico::pedidosCompraYearsByCatalogo($idCatalogo);
            if (!in_array($anio, $years, true)) {
                die("El año seleccionado no es válido para este catálogo.");
            }

            if (!isset($_FILES['Documento']) || $_FILES['Documento']['error'] !== 0) {
                die("Error al subir archivo.");
            }

            // Leer el archivo en VARBINARY
            $rutaTemp = $_FILES['Documento']['tmp_name'];
            $nombreArchivo = basename($_FILES['Documento']['name']);
            $tipoMime = $_FILES['Documento']['type'] ?: 'application/octet-stream';
            
            $contenidoArchivo = file_get_contents($rutaTemp);
            if ($contenidoArchivo === false) {
                die("No se pudo leer el archivo.");
            }

            FichaTecnica::create([
                'IdCatalogoTecnologico' => $idCatalogo,
                'Marca' => $marca,
                'Modelo' => $modelo,
                'Anio' => $anio,
                'NombreDocumento' => $nombreArchivo,
                'TipoMime' => $tipoMime,
                'Documento' => $contenidoArchivo
            ]);

            $this->redirect('index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo . '&year=' . $anio);
        }
    }

    // procesa carga de nuevo término de referencia (PDF como VARBINARY)
    public function uploadTerminosReferencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCatalogo = (int) $_POST['IdCatalogoTecnologico'];
            $codigoTDR = trim($_POST['CodigoTDR']);
            $anio = isset($_POST['Anio']) && $_POST['Anio'] !== '' ? (int) $_POST['Anio'] : null;

            if ($anio === null) {
                die("Debe seleccionar un año para el término de referencia.");
            }

            $years = CatalogoTecnologico::pedidosCompraYearsByCatalogo($idCatalogo);
            if (!in_array($anio, $years, true)) {
                die("El año seleccionado no es válido para este catálogo.");
            }

            if (!isset($_FILES['Documento']) || $_FILES['Documento']['error'] !== 0) {
                die("Error al subir archivo.");
            }

            // Leer el archivo en VARBINARY
            $rutaTemp = $_FILES['Documento']['tmp_name'];
            $nombreArchivo = basename($_FILES['Documento']['name']);
            $tipoMime = $_FILES['Documento']['type'] ?: 'application/octet-stream';
            
            $contenidoArchivo = file_get_contents($rutaTemp);
            if ($contenidoArchivo === false) {
                die("No se pudo leer el archivo.");
            }

            TerminosReferencia::create([
                'IdCatalogoTecnologico' => $idCatalogo,
                'CodigoTDR' => $codigoTDR,
                'Anio' => $anio,
                'NombreDocumento' => $nombreArchivo,
                'TipoMime' => $tipoMime,
                'Documento' => $contenidoArchivo
            ]);

            $this->redirect('index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo . '&year=' . $anio);
        }
    }

    // elimina una ficha técnica
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

        FichaTecnica::delete($idEstudio);

        $url = 'index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo;
        if ($selectedYear !== null) {
            $url .= '&year=' . $selectedYear;
        }

        $this->redirect($url);
    }

    // elimina un término de referencia
    public function deleteTerminosReferencia()
    {
        if (!isset($_GET['eliminar']) || !isset($_GET['id'])) {
            die("Parámetros inválidos.");
        }
        $idTermino = (int) $_GET['eliminar'];
        $idCatalogo = (int) $_GET['id'];
        $selectedYear = isset($_GET['year']) && $_GET['year'] !== '' && $_GET['year'] !== 'all'
            ? (int) $_GET['year']
            : null;

        TerminosReferencia::delete($idTermino);

        $url = 'index.php?controller=catalogo&action=editEstudios&id=' . $idCatalogo;
        if ($selectedYear !== null) {
            $url .= '&year=' . $selectedYear;
        }

        $this->redirect($url);
    }

    // descarga un documento (ficha técnica o término de referencia)
    public function downloadDocumento()
    {
        if (!isset($_GET['tipo']) || !isset($_GET['id'])) {
            die("Parámetros inválidos.");
        }
        
        $tipo = $_GET['tipo']; // 'ficha' o 'termino'
        $id = (int) $_GET['id'];

        if ($tipo === 'ficha') {
            $documento = FichaTecnica::getDocumento($id);
        } elseif ($tipo === 'termino') {
            $documento = TerminosReferencia::getDocumento($id);
        } else {
            die("Tipo de documento inválido.");
        }

        if (!$documento) {
            die("Documento no encontrado.");
        }

        header('Content-Type: ' . $documento['TipoMime']);
        header('Content-Disposition: attachment; filename="' . urlencode($documento['NombreDocumento']) . '"');
        header('Content-Length: ' . strlen($documento['Documento']));
        echo $documento['Documento'];
        exit;
    }
}
