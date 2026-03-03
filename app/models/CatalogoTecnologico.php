<?php
// modelo que representa el catálogo tecnológico y consultas relacionadas

require_once __DIR__ . '/../core/Database.php';

class CatalogoTecnologico
{
    // devuelve todos los catálogos activos ordenados
    public static function allActive()
    {
        $conn = Database::connect();
        $stmt = $conn->query(
            "SELECT Id, Tecnologia, NombreGenerico
             FROM CatalogoTecnologico
             WHERE Activo = 1
             ORDER BY Tecnologia, NombreGenerico"
        );
        return $stmt->fetchAll();
    }

    // obtiene registros junto con conteo de estudios para un año opcional
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
            LEFT JOIN EstudioMercado em
                ON em.IdCatalogoTec = ct.Id
        ";

        if ($year) {
            $sql .= " WHERE hs.AnioFiscal = ? ";
        }

        $sql .= " GROUP BY 
                ct.Id,
                dr.CodigoSiga,
                ct.NombreGenerico,
                ct.Tecnologia
            ORDER BY ct.Tecnologia, dr.CodigoSiga";

        $stmt = $conn->prepare($sql);
        if ($year) {
            $stmt->execute([$year]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // busca un catálogo por su id
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT Id, Tecnologia, NombreGenerico FROM CatalogoTecnologico WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
