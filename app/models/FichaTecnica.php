<?php
// modelo para gestionar fichas tecnicas (PDF como VARBINARY) vinculadas a una tecnologia

require_once __DIR__ . '/../core/Database.php';

class FichaTecnica
{
    // obtiene las fichas técnicas asociadas a una tecnología
    public static function getByCatalogo($idCatalogo, $year = null)
    {
        $conn = Database::connect();

        if ($year === null) {
            $stmt = $conn->prepare(
                "SELECT Id, Marca, Modelo, Anio, NombreDocumento, TipoMime, FechaRegistro
                 FROM FichaTecnica
                 WHERE IdCatalogoTecnologico = ?
                 ORDER BY Anio DESC, FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo]);
        } else {
            $stmt = $conn->prepare(
                "SELECT Id, Marca, Modelo, Anio, NombreDocumento, TipoMime, FechaRegistro
                 FROM FichaTecnica
                 WHERE IdCatalogoTecnologico = ? AND Anio = ?
                 ORDER BY FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo, $year]);
        }

        return $stmt->fetchAll();
    }

    // crea un nuevo registro de ficha técnica (PDF como VARBINARY)
    public static function create($data)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO FichaTecnica
             (IdCatalogoTecnologico, Marca, Modelo, Anio, NombreDocumento, TipoMime, Documento)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdCatalogoTecnologico'],
            $data['Marca'],
            $data['Modelo'],
            $data['Anio'],
            $data['NombreDocumento'],
            $data['TipoMime'],
            $data['Documento'], // VARBINARY como string en PHP
        ]);
    }

    // borra una ficha técnica por id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM FichaTecnica WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // encuentra una ficha técnica específica incluyendo el documento binario
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT Id, IdCatalogoTecnologico, Marca, Modelo, Anio, NombreDocumento, TipoMime, Documento, FechaRegistro
             FROM FichaTecnica 
             WHERE Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene solo el documento binario de una ficha técnica para descarga
    public static function getDocumento($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT Documento, NombreDocumento, TipoMime FROM FichaTecnica WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
