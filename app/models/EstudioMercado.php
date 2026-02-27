<?php

require_once __DIR__ . '/../core/Database.php';

class EstudioMercado {
    public static function getByCatalogo($idCatalogo) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT Id, Marca, Modelo, RutaDocumento, FechaRegistro
             FROM EstudioMercado
             WHERE IdCatalogoTec = ?
             ORDER BY FechaRegistro DESC"
        );
        $stmt->execute([$idCatalogo]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO EstudioMercado
             (IdCatalogoTec, Marca, Modelo, RutaDocumento)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdCatalogoTec'],
            $data['Marca'],
            $data['Modelo'],
            $data['RutaDocumento'],
        ]);
    }

    public static function delete($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM EstudioMercado WHERE Id = ?");
        $stmt->execute([$id]);
    }

    public static function find($id) {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM EstudioMercado WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
