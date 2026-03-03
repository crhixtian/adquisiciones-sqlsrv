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
            "SELECT Id, Tecnologia, NombreGenerico
             FROM CatalogoTecnologico
             WHERE Activo = 1
             ORDER BY
                CASE WHEN Tecnologia LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(Tecnologia, 2, LEN(Tecnologia)) AS INT),
                Tecnologia,
                NombreGenerico"
        );
        return $stmt->fetchAll();
    }

    // obtiene registros junto con conteo de fichas tecnicas (PDF) para un año
    public static function withEstudiosCount($year = null)
    {
        $conn = Database::connect();
        $sql = "
            SELECT 
                ct.Id AS IdCatalogo,
                dr.CodigoSiga,
                ct.NombreGenerico,
                ct.Tecnologia,
                COUNT(em.Id) AS TotalEstudios
            FROM DetalleRequerimiento dr
            INNER JOIN CatalogoTecnologico ct
                ON ct.Id = dr.IdCatalogoTec
            INNER JOIN HojaSiga hs
                ON dr.IdHojaSiga = hs.Id
        ";

        if ($year) {
            $sql .= " LEFT JOIN EstudioMercado em
                ON em.IdCatalogoTec = ct.Id
               AND em.AnioFiscal = ? ";
        } else {
            $sql .= " LEFT JOIN EstudioMercado em
                ON em.IdCatalogoTec = ct.Id ";
        }

        if ($year) {
            $sql .= " WHERE hs.AnioFiscal = ? ";
        }

        $sql .= " GROUP BY 
                ct.Id,
                dr.CodigoSiga,
                ct.NombreGenerico,
                ct.Tecnologia
            ORDER BY
                CASE WHEN ct.Tecnologia LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(ct.Tecnologia, 2, LEN(ct.Tecnologia)) AS INT),
                ct.Tecnologia,
                dr.CodigoSiga";

        $stmt = $conn->prepare($sql);
        if ($year) {
            $stmt->execute([$year, $year]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // busca una tecnologia por su id
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT Id, Tecnologia, NombreGenerico FROM CatalogoTecnologico WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene los pedidos de compra en los que aparece una tecnologia
    public static function pedidosCompraByCatalogo($idCatalogo, $year = null)
    {
        $conn = Database::connect();
        $sql = "SELECT DISTINCT
                    hs.Id,
                    hs.NPedidoCompra,
                    hs.AnioFiscal,
                    cc.NombreCentro
             FROM DetalleRequerimiento dr
             INNER JOIN HojaSiga hs
                ON dr.IdHojaSiga = hs.Id
             INNER JOIN CentroCosto cc
                ON hs.IdCentroCosto = cc.Id
             WHERE dr.IdCatalogoTec = ?";

        $params = [$idCatalogo];
        if ($year !== null) {
            $sql .= " AND hs.AnioFiscal = ?";
            $params[] = $year;
        }

        $sql .= " ORDER BY hs.AnioFiscal DESC, hs.NPedidoCompra ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // obtiene años disponibles para pedidos de una tecnologia 
    public static function pedidosCompraYearsByCatalogo($idCatalogo)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT DISTINCT hs.AnioFiscal
             FROM DetalleRequerimiento dr
             INNER JOIN HojaSiga hs
                ON dr.IdHojaSiga = hs.Id
             WHERE dr.IdCatalogoTec = ?
             ORDER BY hs.AnioFiscal DESC"
        );
        $stmt->execute([$idCatalogo]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map('intval', array_column($rows, 'AnioFiscal'));
    }
}
