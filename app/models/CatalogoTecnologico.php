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
            "SELECT Id, CategoriaTecnologica, NombreGenerico
             FROM CatalogoTecnologico
             WHERE Activo = 1
             ORDER BY
                CASE WHEN CategoriaTecnologica LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(CategoriaTecnologica, 2, LEN(CategoriaTecnologica)) AS INT),
                CategoriaTecnologica,
                NombreGenerico"
        );
        return $stmt->fetchAll();
    }

    // obtiene registros junto con conteo de fichas tecnicas para un año
    public static function withEstudiosCount($year = null)
    {
        $conn = Database::connect();
        $sql = "
            SELECT 
                ct.Id AS IdCatalogo,
                dr.CodigoSiga,
                ct.NombreGenerico,
                ct.CategoriaTecnologica,
                COUNT(ft.Id) AS TotalEstudios
            FROM DetalleRequerimiento dr
            INNER JOIN CatalogoTecnologico ct
                ON ct.Id = dr.IdCatalogoTecnologico
            INNER JOIN Requerimiento r
                ON dr.IdRequerimiento = r.Id
        ";

        if ($year) {
            $sql .= " LEFT JOIN FichaTecnica ft
                ON ft.IdCatalogoTecnologico = ct.Id
               AND ft.Anio = ? ";
        } else {
            $sql .= " LEFT JOIN FichaTecnica ft
                ON ft.IdCatalogoTecnologico = ct.Id ";
        }

        if ($year) {
            $sql .= " WHERE r.Anio = ? ";
        }

        $sql .= " GROUP BY 
                ct.Id,
                dr.CodigoSiga,
                ct.NombreGenerico,
                ct.CategoriaTecnologica
            ORDER BY
                CASE WHEN ct.CategoriaTecnologica LIKE 'T[0-9]%' THEN 0 ELSE 1 END,
                TRY_CAST(SUBSTRING(ct.CategoriaTecnologica, 2, LEN(ct.CategoriaTecnologica)) AS INT),
                ct.CategoriaTecnologica,
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
        $stmt = $conn->prepare("SELECT Id, CategoriaTecnologica, NombreGenerico FROM CatalogoTecnologico WHERE Id = ?");
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
             FROM DetalleRequerimiento dr
             INNER JOIN Requerimiento r
                ON dr.IdRequerimiento = r.Id
             INNER JOIN CentroCosto cc
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
             FROM DetalleRequerimiento dr
             INNER JOIN Requerimiento r
                ON dr.IdRequerimiento = r.Id
             WHERE dr.IdCatalogoTecnologico = ?
             ORDER BY r.Anio DESC"
        );
        $stmt->execute([$idCatalogo]);
        $rows = $stmt->fetchAll();
        return array_map('intval', array_column($rows, 'Anio'));
    }
}
