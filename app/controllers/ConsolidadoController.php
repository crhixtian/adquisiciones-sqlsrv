<?php
// controlador para mostrar el consolidado de equipos por centro de costo

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Database.php';

class ConsolidadoController extends Controller
{
    // muestra la tabla consolidada con equipos por centro de costo
    public function index()
    {
        $conn = Database::connect();

        // obtener años disponibles y seleccionar el más reciente por defecto
        $stmtYears = $conn->query("SELECT DISTINCT Anio FROM Requerimiento ORDER BY Anio DESC");
        $rowsYears = $stmtYears->fetchAll();
        $years = array_map('intval', array_column($rowsYears, 'Anio'));

        $selectedYear = null;
        if (!empty($years)) {
            $selectedYear = (int) $years[0];
        }

        if (isset($_GET['year']) && $_GET['year'] !== '') {
            $requestedYear = (int) $_GET['year'];
            if (in_array($requestedYear, $years, true)) {
                $selectedYear = $requestedYear;
            }
        }

        // obtener todos los equipos y sus cantidades agrupadas por centro de costo
        $sql = "
            SELECT 
                ct.Id,
                ct.NombreGenerico,
                cc.Id AS IdCentroCosto,
                cc.NombreCentroCosto,
                cc.Siglas,
                SUM(CAST(dr.Cantidad AS INT)) AS TotalEquipo
            FROM DetalleRequerimiento dr
            INNER JOIN Requerimiento r ON dr.IdRequerimiento = r.Id
            INNER JOIN CentroCosto cc ON r.IdCentroCosto = cc.Id
            INNER JOIN CatalogoTecnologico ct ON dr.IdCatalogoTecnologico = ct.Id
        ";

        if ($selectedYear !== null) {
            $sql .= " WHERE r.Anio = ? ";
        }

        $sql .= "
            GROUP BY ct.Id, ct.NombreGenerico, cc.Id, cc.NombreCentroCosto, cc.Siglas
            ORDER BY ct.NombreGenerico, cc.NombreCentroCosto
        ";

        $stmt = $conn->prepare($sql);
        if ($selectedYear !== null) {
            $stmt->execute([$selectedYear]);
        } else {
            $stmt->execute();
        }
        $datos = $stmt->fetchAll();

        // procesar datos para crear matriz
        $equipos = [];
        $centrosCosto = [];
        $centrosSiglas = [];

        foreach ($datos as $row) {
            $equipo = $row['NombreGenerico'];
            $centro = $row['NombreCentroCosto'];
            $siglas = $row['Siglas'];
            $idCentro = $row['IdCentroCosto'];
            $cantidad = $row['TotalEquipo'];

            if (!isset($equipos[$equipo])) {
                $equipos[$equipo] = [];
            }
            $equipos[$equipo][$idCentro] = $cantidad;

            if (!in_array($idCentro, array_keys($centrosSiglas))) {
                $centrosCosto[] = $idCentro;
                $centrosSiglas[$idCentro] = $siglas;
            }
        }

        sort($centrosCosto);

        $this->render('consolidado/index', [
            'equipos' => $equipos,
            'centrosCosto' => $centrosCosto,
            'centrosSiglas' => $centrosSiglas,
            'datos' => $datos,
            'years' => $years,
            'selectedYear' => $selectedYear
        ]);
    }
}
