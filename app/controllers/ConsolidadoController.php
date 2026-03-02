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

        // obtener todos los equipos y sus cantidades agrupadas por centro de costo
        $sql = "
            SELECT 
                ct.Id,
                ct.NombreGenerico,
                cc.Id AS IdCentroCosto,
                cc.NombreCentro,
                cc.Siglas,
                SUM(CAST(dr.Cantidad AS INT)) AS TotalEquipo
            FROM DetalleRequerimiento dr
            INNER JOIN HojaSiga hs ON dr.IdHojaSiga = hs.Id
            INNER JOIN CentroCosto cc ON hs.IdCentroCosto = cc.Id
            INNER JOIN CatalogoTecnologico ct ON dr.IdCatalogoTec = ct.Id
            GROUP BY ct.Id, ct.NombreGenerico, cc.Id, cc.NombreCentro, cc.Siglas
            ORDER BY ct.NombreGenerico, cc.NombreCentro
        ";

        $stmt = $conn->query($sql);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // procesar datos para crear matriz pivote
        $equipos = [];
        $centrosCosto = [];
        $centrosSiglas = [];

        foreach ($datos as $row) {
            $equipo = $row['NombreGenerico'];
            $centro = $row['NombreCentro'];
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

        sort($centrosCosto);

        $this->render('consolidado/index', [
            'equipos' => $equipos,
            'centrosCosto' => $centrosCosto,
            'centrosSiglas' => $centrosSiglas,
            'datos' => $datos
        ]);
    }
}
