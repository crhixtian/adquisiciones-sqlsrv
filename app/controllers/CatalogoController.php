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
        $stmtYears = $conn->query("SELECT DISTINCT Anio FROM adquisiciones.Requerimiento ORDER BY Anio DESC");
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
    public function editDocumentos()
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

    // procesa carga de nueva ficha técnica (guardar en carpeta uploads)
    public function uploadFichaTecnica()
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

            $terminosExistentes = TerminosReferencia::getByCatalogo($idCatalogo, $anio);
            if (!empty($terminosExistentes)) {
                die("Ya existe un término de referencia para este año. No se permiten más cargas de archivos.");
            }

            if (!isset($_FILES['Documento']) || $_FILES['Documento']['error'] !== 0) {
                die("Error al subir archivo.");
            }

            // Guardar archivo en carpeta uploads
            $rutaTemp = $_FILES['Documento']['tmp_name'];
            $nombreOriginal = basename($_FILES['Documento']['name']);
            
            // Generar nombre único para el archivo
            $timestamp = time();
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreArchivo = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($nombreOriginal, PATHINFO_FILENAME)) . '.' . $extension;
            $rutaDestino = __DIR__ . '/../../uploads/' . $nombreArchivo;
            
            if (!move_uploaded_file($rutaTemp, $rutaDestino)) {
                die("No se pudo guardar el archivo.");
            }
            
            // Guardar ruta relativa en la BD
            $rutaRelativa = 'uploads/' . $nombreArchivo;

            FichaTecnica::create([
                'IdCatalogoTecnologico' => $idCatalogo,
                'Marca' => $marca,
                'Modelo' => $modelo,
                'Anio' => $anio,
                'RutaDocumento' => $rutaRelativa
            ]);

            $this->redirect('index.php?controller=catalogo&action=editDocumentos&id=' . $idCatalogo . '&year=' . $anio);
        }
    }

    // procesa carga de nuevo término de referencia (guardar en carpeta uploads)
    public function uploadTerminosReferencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCatalogo = (int) $_POST['IdCatalogoTecnologico'];
            $codigoFT = trim($_POST['CodigoFT']);
            $anio = isset($_POST['Anio']) && $_POST['Anio'] !== '' ? (int) $_POST['Anio'] : null;

            if ($anio === null) {
                die("Debe seleccionar un año para el término de referencia.");
            }

            $years = CatalogoTecnologico::pedidosCompraYearsByCatalogo($idCatalogo);
            if (!in_array($anio, $years, true)) {
                die("El año seleccionado no es válido para este catálogo.");
            }

            $terminosExistentes = TerminosReferencia::getByCatalogo($idCatalogo, $anio);
            if (!empty($terminosExistentes)) {
                die("Ya existe un término de referencia para este año. No se permiten más cargas de archivos.");
            }

            if (!isset($_FILES['Documento']) || $_FILES['Documento']['error'] !== 0) {
                die("Error al subir archivo.");
            }

            // Guardar archivo en carpeta uploads
            $rutaTemp = $_FILES['Documento']['tmp_name'];
            $nombreOriginal = basename($_FILES['Documento']['name']);
            
            // Generar nombre único para el archivo
            $timestamp = time();
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreArchivo = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($nombreOriginal, PATHINFO_FILENAME)) . '.' . $extension;
            $rutaDestino = __DIR__ . '/../../uploads/' . $nombreArchivo;
            
            if (!move_uploaded_file($rutaTemp, $rutaDestino)) {
                die("No se pudo guardar el archivo.");
            }
            
            // Guardar ruta relativa en la BD
            $rutaRelativa = 'uploads/' . $nombreArchivo;

            TerminosReferencia::create([
                'IdCatalogoTecnologico' => $idCatalogo,
                'CodigoFT' => $codigoFT,
                'Anio' => $anio,
                'RutaDocumento' => $rutaRelativa
            ]);

            $this->redirect('index.php?controller=catalogo&action=editDocumentos&id=' . $idCatalogo . '&year=' . $anio);
        }
    }

    // elimina una ficha técnica
    public function deleteFichaTecnica()
    {
        if (!isset($_GET['eliminar']) || !isset($_GET['id'])) {
            die("Parámetros inválidos.");
        }
        $idEstudio = (int) $_GET['eliminar'];
        $idCatalogo = (int) $_GET['id'];
        $selectedYear = isset($_GET['year']) && $_GET['year'] !== '' && $_GET['year'] !== 'all'
            ? (int) $_GET['year']
            : null;

        $ficha = FichaTecnica::find($idEstudio);
        if ($ficha && isset($ficha['RutaDocumento'])) {
            $this->deleteUploadedDocument($ficha['RutaDocumento']);
        }

        FichaTecnica::delete($idEstudio);

        $url = 'index.php?controller=catalogo&action=editDocumentos&id=' . $idCatalogo;
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

        $termino = TerminosReferencia::find($idTermino);
        if ($termino && isset($termino['RutaDocumento'])) {
            $this->deleteUploadedDocument($termino['RutaDocumento']);
        }

        TerminosReferencia::delete($idTermino);

        $url = 'index.php?controller=catalogo&action=editDocumentos&id=' . $idCatalogo;
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

        // El campo RutaDocumento contiene la ruta relativa al archivo
        $rutaArchivo = __DIR__ . '/../../' . $documento['RutaDocumento'];
        
        if (!file_exists($rutaArchivo)) {
            die("Archivo no encontrado en el servidor.");
        }

        // Obtener el nombre del archivo
        $nombreArchivo = basename($rutaArchivo);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . urlencode($nombreArchivo) . '"');
        header('Content-Length: ' . filesize($rutaArchivo));
        readfile($rutaArchivo);
        exit;
    }

    // visualiza un documento PDF en línea (sin descarga forzada)
    public function viewDocumento()
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

        $rutaArchivo = __DIR__ . '/../../' . $documento['RutaDocumento'];
        if (!file_exists($rutaArchivo)) {
            die("Archivo no encontrado en el servidor.");
        }

        $nombreArchivo = basename($rutaArchivo);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . rawurlencode($nombreArchivo) . '"');
        header('Content-Length: ' . filesize($rutaArchivo));
        readfile($rutaArchivo);
        exit;
    }

    private function deleteUploadedDocument($rutaRelativa)
    {
        if (!$rutaRelativa) {
            return;
        }

        $uploadsDir = realpath(__DIR__ . '/../../uploads');
        if ($uploadsDir === false) {
            return;
        }

        $rutaNormalizada = str_replace('\\', '/', (string) $rutaRelativa);
        $prefijo = 'uploads/';
        if (strpos($rutaNormalizada, $prefijo) !== 0) {
            return;
        }

        $subRuta = ltrim(substr($rutaNormalizada, strlen($prefijo)), '/');
        if ($subRuta === '') {
            return;
        }

        $rutaArchivo = $uploadsDir . '/' . $subRuta;
        $rutaRealArchivo = realpath($rutaArchivo);
        if ($rutaRealArchivo === false) {
            return;
        }

        if (strpos($rutaRealArchivo, $uploadsDir . DIRECTORY_SEPARATOR) !== 0) {
            return;
        }

        if (is_file($rutaRealArchivo)) {
            @unlink($rutaRealArchivo);
        }
    }
}
