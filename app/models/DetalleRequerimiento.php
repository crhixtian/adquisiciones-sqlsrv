<?php
// modelo que maneja los detalles de requerimiento asociados a un requerimiento

require_once __DIR__ . '/../core/Database.php';

class DetalleRequerimiento
{
    // obtiene todos los items asociados a un requerimiento
    public static function getByRequerimiento($idRequerimiento)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT dr.*, ct.NombreGenerico
             FROM DetalleRequerimiento dr
             LEFT JOIN CatalogoTecnologico ct ON dr.IdCatalogoTecnologico = ct.Id
             WHERE dr.IdRequerimiento = ?
             ORDER BY dr.Id DESC"
        );
        $stmt->execute([$idRequerimiento]);
        return $stmt->fetchAll();
    }

    // busca un item específico por id
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM DetalleRequerimiento WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // inserta un nuevo item en la base de datos
    public static function create($data)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO DetalleRequerimiento
             (IdRequerimiento, IdCatalogoTecnologico, CodigoSiga, DescripcionDetallada, Cantidad, UnidadMedida)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdRequerimiento'],
            $data['IdCatalogoTecnologico'] ?: null,
            $data['CodigoSiga'],
            $data['DescripcionDetallada'],
            $data['Cantidad'],
            $data['UnidadMedida'] ?: 'UND',
        ]);
    }

    // actualiza un item existente con nuevos datos
    public static function update($id, $data)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "UPDATE DetalleRequerimiento
             SET CodigoSiga = ?,
                 DescripcionDetallada = ?,
                 Cantidad = ?,
                 UnidadMedida = ?,
                 IdCatalogoTecnologico = ?
             WHERE Id = ?"
        );
        $stmt->execute([
            $data['CodigoSiga'],
            $data['DescripcionDetallada'],
            $data['Cantidad'],
            $data['UnidadMedida'] ?: 'UND',
            $data['IdCatalogoTecnologico'] ?: null,
            $id
        ]);
    }

    // elimina un item por su id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM DetalleRequerimiento WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // verifica la existencia de un código SIGA en un requerimiento, opcionalmente excluyendo un id
    public static function existsCodigoEnRequerimiento($idRequerimiento, $codigo, $excludeId = null)
    {
        $conn = Database::connect();
        if ($excludeId) {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as total FROM DetalleRequerimiento
                 WHERE IdRequerimiento = ? AND CodigoSiga = ? AND Id <> ?"
            );
            $stmt->execute([$idRequerimiento, $codigo, $excludeId]);
        } else {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as total FROM DetalleRequerimiento
                 WHERE IdRequerimiento = ? AND CodigoSiga = ?"
            );
            $stmt->execute([$idRequerimiento, $codigo]);
        }
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }
}
