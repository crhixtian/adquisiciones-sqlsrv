<?php
// modelo para gestionar términos de referencia (PDF como VARBINARY) vinculados a una tecnología

require_once __DIR__ . '/../core/Database.php';

class TerminosReferencia
{
    // obtiene los términos de referencia asociados a una tecnología
    public static function getByCatalogo($idCatalogo, $year = null)
    {
        $conn = Database::connect();

        if ($year === null) {
            $stmt = $conn->prepare(
                "SELECT Id, CodigoTDR, Anio, NombreDocumento, TipoMime, FechaRegistro
                 FROM TerminosReferencia
                 WHERE IdCatalogoTecnologico = ?
                 ORDER BY Anio DESC, FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo]);
        } else {
            $stmt = $conn->prepare(
                "SELECT Id, CodigoTDR, Anio, NombreDocumento, TipoMime, FechaRegistro
                 FROM TerminosReferencia
                 WHERE IdCatalogoTecnologico = ? AND Anio = ?
                 ORDER BY FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo, $year]);
        }

        return $stmt->fetchAll();
    }

    // crea un nuevo registro de términos de referencia (PDF como VARBINARY)
    public static function create($data)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "INSERT INTO TerminosReferencia
             (IdCatalogoTecnologico, CodigoTDR, Anio, NombreDocumento, TipoMime, Documento)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['IdCatalogoTecnologico'],
            $data['CodigoTDR'],
            $data['Anio'],
            $data['NombreDocumento'],
            $data['TipoMime'],
            $data['Documento'], // VARBINARY como string en PHP
        ]);
    }

    // borra un término de referencia por id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM TerminosReferencia WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // encuentra un término de referencia específico incluyendo el documento binario
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT Id, IdCatalogoTecnologico, CodigoTDR, Anio, NombreDocumento, TipoMime, Documento, FechaRegistro
             FROM TerminosReferencia 
             WHERE Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene solo el documento binario de un término de referencia para descarga
    public static function getDocumento($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT Documento, NombreDocumento, TipoMime FROM TerminosReferencia WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // busca un TDR por código
    public static function findByCodigo($codigo)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT * FROM TerminosReferencia WHERE CodigoTDR = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }
}
