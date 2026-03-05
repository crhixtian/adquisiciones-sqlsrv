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
                "SELECT Id, CodigoTDR, Anio, RutaDocumento, FechaRegistro
                 FROM TerminosReferencia
                 WHERE IdCatalogoTecnologico = ?
                 ORDER BY Anio DESC, FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo]);
        } else {
            $stmt = $conn->prepare(
                "SELECT Id, CodigoTDR, Anio, RutaDocumento, FechaRegistro
                 FROM TerminosReferencia
                 WHERE IdCatalogoTecnologico = ? AND Anio = ?
                 ORDER BY FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo, $year]);
        }

        return $stmt->fetchAll();
    }

    // crea un nuevo registro de términos de referencia
    public static function create($data)
    {
        $conn = Database::connect();
        $sql = "INSERT INTO TerminosReferencia
                (IdCatalogoTecnologico, CodigoTDR, Anio, RutaDocumento)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Guardar solo la ruta del archivo
        $params = array(
            $data['IdCatalogoTecnologico'],
            $data['CodigoTDR'],
            $data['Anio'],
            $data['RutaDocumento']
        );
        
        $stmt->execute($params);
    }

    // borra un término de referencia por id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM TerminosReferencia WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // encuentra un término de referencia específico
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
            "SELECT Id, IdCatalogoTecnologico, CodigoTDR, Anio, RutaDocumento, FechaRegistro
             FROM TerminosReferencia 
             WHERE Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene la ruta del documento de un término de referencia para descarga
    public static function getDocumento($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT RutaDocumento FROM TerminosReferencia WHERE Id = ?");
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
