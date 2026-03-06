<?php
// modelo que representa el catálogo tecnológico y consultas relacionadas

require_once __DIR__ . '/../core/Database.php';

class CatalogoTecnologico
{
    // devuelve todas las tecnologías activas ordenadas
    public static function allActive()
    {
        $conn = Database::connect();
        $stmt = $conn->query(
            "SELECT Id, Codigo, NombreGenerico
             FROM adquisiciones.CatalogoTecnologico
             WHERE Activo = 1
             ORDER BY
                CASE WHEN Codigo LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(Codigo, 2, LEN(Codigo)) AS INT),
                Codigo,
                NombreGenerico"
        );
        return $stmt->fetchAll();
    }

    // obtiene registros junto con conteo de fichas tecnicas para un año
    public static function withEstudiosCount($year = null)
    {
        $conn = Database::connect();
        $sql = "
            WITH CodigosCTE AS (
                SELECT DISTINCT 
                    dr.IdCatalogoTecnologico,
                    dr.CodigoSiga
                FROM adquisiciones.DetalleRequerimiento dr
            ),
            CodigosAgrupados AS (
                SELECT 
                    IdCatalogoTecnologico,
                    STRING_AGG(CodigoSiga, ', ') AS CodigoSiga
                FROM CodigosCTE
                GROUP BY IdCatalogoTecnologico
            )
            SELECT 
                ct.Id AS IdCatalogo,
                ISNULL(ca.CodigoSiga, '') AS CodigoSiga,
                ct.NombreGenerico,
                ct.Codigo,
                (SELECT COUNT(*) FROM adquisiciones.FichaTecnicaReferencia ft2
                 WHERE ft2.IdCatalogoTecnologico = ct.Id";

        if ($year) {
            $sql .= " AND ft2.Anio = ?";
        }

        $sql .= ") AS TotalEstudios,
            (SELECT COUNT(*) FROM adquisiciones.FichaTecnica tr2
             WHERE tr2.IdCatalogoTecnologico = ct.Id";

        if ($year) {
            $sql .= " AND tr2.Anio = ?";
        }

        $sql .= ") AS TotalTDR,
            CASE 
                WHEN (SELECT COUNT(*) FROM adquisiciones.FichaTecnica tr3
                  WHERE tr3.IdCatalogoTecnologico = ct.Id";

        if ($year) {
            $sql .= " AND tr3.Anio = ?";
        }

        $sql .= ") > 0 THEN 'Completo'
                    ELSE 'Incompleto'
                END AS Estado
            FROM adquisiciones.CatalogoTecnologico ct
            LEFT JOIN CodigosAgrupados ca ON ct.Id = ca.IdCatalogoTecnologico
            WHERE EXISTS (
                SELECT 1 FROM adquisiciones.DetalleRequerimiento dr
                INNER JOIN adquisiciones.Requerimiento r ON dr.IdRequerimiento = r.Id
                WHERE dr.IdCatalogoTecnologico = ct.Id";

        if ($year) {
            $sql .= " AND r.Anio = ?";
        }

        $sql .= "
            )
            ORDER BY
                CASE WHEN ct.Codigo LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(ct.Codigo, 2, LEN(ct.Codigo)) AS INT),
                ct.Codigo,
                ct.NombreGenerico";

        $stmt = $conn->prepare($sql);
        if ($year) {
            $stmt->execute([$year, $year, $year, $year]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // busca una tecnologia por su id
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT Id, Codigo, NombreGenerico FROM adquisiciones.CatalogoTecnologico WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene los pedidos de compra en los que aparece una tecnologia
    public static function pedidosCompraByCatalogo($idCatalogo, $year = null)
    {
        $conn = Database::connect();
          $sql = "SELECT DISTINCT
                    r.Id,
                    r.NroPedidoCompra,
                    r.Anio,
                    cc.NombreCentroCosto
                 FROM adquisiciones.DetalleRequerimiento dr
                 INNER JOIN adquisiciones.Requerimiento r
                ON dr.IdRequerimiento = r.Id
                 INNER JOIN adquisiciones.CentroCosto cc
                ON r.IdCentroCosto = cc.Id
             WHERE dr.IdCatalogoTecnologico = ?";

        $params = [$idCatalogo];
        if ($year !== null) {
            $sql .= " AND r.Anio = ?";
            $params[] = $year;
        }

        $sql .= " ORDER BY r.Anio DESC, r.NroPedidoCompra ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // obtiene años disponibles para pedidos de una tecnologia 
    public static function pedidosCompraYearsByCatalogo($idCatalogo)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT DISTINCT r.Anio
             FROM adquisiciones.DetalleRequerimiento dr
             INNER JOIN adquisiciones.Requerimiento r
                ON dr.IdRequerimiento = r.Id
             WHERE dr.IdCatalogoTecnologico = ?
             ORDER BY r.Anio DESC"
        );
        $stmt->execute([$idCatalogo]);
        $rows = $stmt->fetchAll();
        return array_map('intval', array_column($rows, 'Anio'));
    }
}
