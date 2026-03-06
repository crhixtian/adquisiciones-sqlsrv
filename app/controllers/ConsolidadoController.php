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
        $stmtYears = $conn->query("SELECT DISTINCT Anio FROM adquisiciones.Requerimiento ORDER BY Anio DESC");
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
            FROM adquisiciones.DetalleRequerimiento dr
            INNER JOIN adquisiciones.Requerimiento r ON dr.IdRequerimiento = r.Id
            INNER JOIN adquisiciones.CentroCosto cc ON r.IdCentroCosto = cc.Id
            INNER JOIN adquisiciones.CatalogoTecnologico ct ON dr.IdCatalogoTecnologico = ct.Id
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

    // exporta el consolidado a Excel
    public function exportarExcel()
    {
        $conn = Database::connect();

        // obtener años disponibles
        $stmtYears = $conn->query("SELECT DISTINCT Anio FROM adquisiciones.Requerimiento ORDER BY Anio DESC");
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
            FROM adquisiciones.DetalleRequerimiento dr
            INNER JOIN adquisiciones.Requerimiento r ON dr.IdRequerimiento = r.Id
            INNER JOIN adquisiciones.CentroCosto cc ON r.IdCentroCosto = cc.Id
            INNER JOIN adquisiciones.CatalogoTecnologico ct ON dr.IdCatalogoTecnologico = ct.Id
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
            $idCentro = $row['IdCentroCosto'];
            $siglas = $row['Siglas'];
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

        // generar archivo Excel
        $filename = 'Consolidado_Equipos_' . $selectedYear . '_' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // usar el formato HTML table que Excel puede importar
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        echo '<x:Name>Consolidado ' . htmlspecialchars($selectedYear) . '</x:Name>';
        echo '<x:WorksheetOptions><x:Print><x:ValidPrinterInfo/></x:Print></x:WorksheetOptions>';
        echo '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml>';
        echo '</head>';
        echo '<body>';
        echo '<table border="1">';
        
        // encabezado
        echo '<thead>';
        echo '<tr>';
        echo '<th style="background-color: #4CAF50; color: white; font-weight: bold;">EQUIPO</th>';
        foreach ($centrosCosto as $idCentro) {
            echo '<th style="background-color: #4CAF50; color: white; font-weight: bold; text-align: center;">' . htmlspecialchars($centrosSiglas[$idCentro]) . '</th>';
        }
        echo '<th style="background-color: #4CAF50; color: white; font-weight: bold; text-align: center;">TOTAL</th>';
        echo '</tr>';
        echo '</thead>';
        
        // datos
        echo '<tbody>';
        $totalesPorCentro = [];
        foreach ($centrosCosto as $idCentro) {
            $totalesPorCentro[$idCentro] = 0;
        }
        
        foreach ($equipos as $equipo => $cantidades) {
            $totalEquipo = 0;
            echo '<tr>';
            echo '<td style="font-weight: bold;">' . htmlspecialchars($equipo) . '</td>';
            
            foreach ($centrosCosto as $idCentro) {
                $cantidad = isset($cantidades[$idCentro]) ? $cantidades[$idCentro] : '';
                echo '<td style="text-align: center;">' . ($cantidad ?: '') . '</td>';
                if ($cantidad) {
                    $totalEquipo += $cantidad;
                    $totalesPorCentro[$idCentro] += $cantidad;
                }
            }
            
            echo '<td style="text-align: center; font-weight: bold;">' . ($totalEquipo > 0 ? $totalEquipo : '') . '</td>';
            echo '</tr>';
        }
        
        // fila de totales
        echo '<tr style="background-color: #f0f0f0;">';
        echo '<td style="font-weight: bold;">TOTAL</td>';
        $totalGeneral = 0;
        foreach ($centrosCosto as $idCentro) {
            $total = $totalesPorCentro[$idCentro];
            $totalGeneral += $total;
            echo '<td style="text-align: center; font-weight: bold;">' . ($total > 0 ? $total : '') . '</td>';
        }
        echo '<td style="text-align: center; font-weight: bold;">' . ($totalGeneral > 0 ? $totalGeneral : '') . '</td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        echo '</body>';
        echo '</html>';
        
        exit;
    }
}
