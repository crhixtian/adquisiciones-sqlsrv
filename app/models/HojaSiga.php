<?php

require_once __DIR__ . '/../core/Database.php';

class HojaSiga {

    public static function all() {
        $conn = Database::connect();
        $stmt = $conn->query(
            "SELECT hs.Id, hs.NPedidoCompra, hs.Meta, hs.AnioFiscal, hs.FechaRegistro, cc.NombreCentro
             FROM HojaSiga hs
             INNER JOIN CentroCosto cc ON hs.IdCentroCosto = cc.Id
             ORDER BY cc.NombreCentro, hs.NPedidoCompra ASC"
        );
        return $stmt->fetchAll();
    }

    public static function find($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT hs.Id,
                    hs.NPedidoCompra,
                    hs.Meta,
                    hs.AnioFiscal,
                    hs.FechaRegistro,
                    cc.NombreCentro,
                    hs.IdCentroCosto
             FROM HojaSiga hs
             INNER JOIN CentroCosto cc ON hs.IdCentroCosto = cc.Id
             WHERE hs.Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO HojaSiga
             (IdCentroCosto, NPedidoCompra, Meta, AnioFiscal)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdCentroCosto'],
            $data['NPedidoCompra'],
            $data['Meta'],
            $data['AnioFiscal'],
        ]);

        return $conn->lastInsertId();
    }

    public static function delete($id) {
        $conn = Database::connect();
        // remove detalles asociados antes de eliminar la hoja para evitar errores de FK
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("DELETE FROM DetalleRequerimiento WHERE IdHojaSiga = ?");
            $stmt->execute([$id]);
            $stmt = $conn->prepare("DELETE FROM HojaSiga WHERE Id = ?");
            $stmt->execute([$id]);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public static function detalles($idHoja) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT dr.Id,
                    dr.CodigoSiga,
                    dr.DescripcionDetallada,
                    dr.Cantidad,
                    dr.UnidadMedida,
                    ct.NombreGenerico
             FROM DetalleRequerimiento dr
             LEFT JOIN CatalogoTecnologico ct
                ON dr.IdCatalogoTec = ct.Id
             WHERE dr.IdHojaSiga = ?
             ORDER BY dr.Id DESC"
        );
        $stmt->execute([$idHoja]);
        return $stmt->fetchAll();
    }
}
