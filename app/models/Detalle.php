<?php

require_once __DIR__ . '/../core/Database.php';

class Detalle {
    public static function getByHoja($idHoja) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT dr.*, ct.NombreGenerico
             FROM DetalleRequerimiento dr
             LEFT JOIN CatalogoTecnologico ct ON dr.IdCatalogoTec = ct.Id
             WHERE dr.IdHojaSiga = ?
             ORDER BY dr.Id DESC"
        );
        $stmt->execute([$idHoja]);
        return $stmt->fetchAll();
    }

    public static function find($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM DetalleRequerimiento WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO DetalleRequerimiento
             (IdHojaSiga, CodigoSiga, DescripcionDetallada, Clasificador, Cantidad, UnidadMedida, IdCatalogoTec)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdHojaSiga'],
            $data['CodigoSiga'],
            $data['DescripcionDetallada'],
            $data['Clasificador'],
            $data['Cantidad'],
            $data['UnidadMedida'] ?: 'UND',
            $data['IdCatalogoTec'] ?: null,
        ]);
    }

    public static function update($id, $data) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "UPDATE DetalleRequerimiento
             SET CodigoSiga = ?,
                 DescripcionDetallada = ?,
                 Clasificador = ?,
                 Cantidad = ?,
                 UnidadMedida = ?,
                 IdCatalogoTec = ?
             WHERE Id = ?"
        );
        $stmt->execute([
            $data['CodigoSiga'],
            $data['DescripcionDetallada'],
            $data['Clasificador'],
            $data['Cantidad'],
            $data['UnidadMedida'] ?: 'UND',
            $data['IdCatalogoTec'] ?: null,
            $id
        ]);
    }

    public static function delete($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM DetalleRequerimiento WHERE Id = ?");
        $stmt->execute([$id]);
    }

    public static function existsCodigoEnHoja($idHoja, $codigo, $excludeId = null) {
        $conn = Database::connect();
        if ($excludeId) {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as total FROM DetalleRequerimiento
                 WHERE IdHojaSiga = ? AND CodigoSiga = ? AND Id <> ?"
            );
            $stmt->execute([$idHoja, $codigo, $excludeId]);
        } else {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) as total FROM DetalleRequerimiento
                 WHERE IdHojaSiga = ? AND CodigoSiga = ?"
            );
            $stmt->execute([$idHoja, $codigo]);
        }
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }
}
