<?php
// modelo de requerimiento con operaciones CRUD

require_once __DIR__ . '/../core/Database.php';

class Requerimiento
{
    // retorna todos los requerimientos junto con información de centro de costo
    public static function all($year = null)
    {
        $conn = Database::connect();
        $sql = "SELECT r.Id, r.NroPedidoCompra, r.Anio, r.FechaRegistro, r.Estado, cc.NombreCentroCosto
                FROM Requerimiento r
                INNER JOIN CentroCosto cc ON r.IdCentroCosto = cc.Id";

        if ($year !== null) {
            $sql .= " WHERE r.Anio = ?";
        }

        $sql .= " ORDER BY r.NroPedidoCompra, cc.NombreCentroCosto ASC";

        $stmt = $conn->prepare($sql);
        if ($year !== null) {
            $stmt->execute([$year]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    // devuelve años disponibles en requerimientos
    public static function years()
    {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT DISTINCT Anio FROM Requerimiento ORDER BY Anio DESC");
        $rows = $stmt->fetchAll();
        return array_map('intval', array_column($rows, 'Anio'));
    }

    // busca un requerimiento específico con información de centro de costo
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT r.Id,
                    r.NroPedidoCompra,
                    r.Anio,
                    r.FechaRegistro,
                    r.Estado,
                    cc.NombreCentroCosto,
                    r.IdCentroCosto
             FROM Requerimiento r
             INNER JOIN CentroCosto cc ON r.IdCentroCosto = cc.Id
             WHERE r.Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // inserta un nuevo requerimiento en la base de datos
    public static function create($data)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO Requerimiento
             (IdCentroCosto, NroPedidoCompra, Anio)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['IdCentroCosto'],
            $data['NroPedidoCompra'],
            $data['Anio'],
        ]);

        return $conn->lastInsertId();
    }

    // elimina requerimiento y detalles vinculados en transacción
    public static function delete($id)
    {
        $conn = Database::connect();
        // elimina items asociados antes de eliminar el requerimiento para evitar errores de FK
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("DELETE FROM DetalleRequerimiento WHERE IdRequerimiento = ?");
            $stmt->execute([$id]);
            $stmt = $conn->prepare("DELETE FROM Requerimiento WHERE Id = ?");
            $stmt->execute([$id]);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    // obtiene los items asociados a un requerimiento
    public static function detalles($idRequerimiento)
    {
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
                ON dr.IdCatalogoTecnologico = ct.Id
             WHERE dr.IdRequerimiento = ?
             ORDER BY dr.Id DESC"
        );
        $stmt->execute([$idRequerimiento]);
        return $stmt->fetchAll();
    }

    // actualiza el estado del requerimiento (0=incompleto, 1=completo)
    public static function updateEstado($id, $estado)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("UPDATE Requerimiento SET Estado = ? WHERE Id = ?");
        $stmt->execute([(int)$estado, $id]);
    }
}
