<?php
// modelo para gestionar fichas tecnicas (PDF) vinculadas a una tecnologia (T1, T2, T3, etc.)

require_once __DIR__ . '/../core/Database.php';

class EstudioMercado
{
    // guarda las fichas técnicas (PDF) asociadas a una tecnología
    public static function getByCatalogo($idCatalogo)
    {
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

    // crea un nuevo registro de fichas técnicas (PDF) para una tecnología
    public static function create($data)
    {
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

    // borra una ficha técnica (PDF) por id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM EstudioMercado WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // encuentra una ficha técnica (PDF) específica
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM EstudioMercado WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
