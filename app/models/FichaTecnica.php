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
                 "SELECT Id, Marca, Modelo, Anio, Estado, RutaDocumento, FechaRegistro
                  FROM adquisiciones.FichaTecnicaReferencia
                 WHERE IdCatalogoTecnologico = ?
                 ORDER BY Anio DESC, FechaRegistro DESC"
            );
            $stmt->execute([$idCatalogo]);
        } else {
            $stmt = $conn->prepare(
                 "SELECT Id, Marca, Modelo, Anio, Estado, RutaDocumento, FechaRegistro
                  FROM adquisiciones.FichaTecnicaReferencia
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
        $sql = "INSERT INTO adquisiciones.FichaTecnicaReferencia
            (IdCatalogoTecnologico, Marca, Modelo, Anio, RutaDocumento)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Guardar solo la ruta del archivo
        $params = array(
            $data['IdCatalogoTecnologico'],
            $data['Marca'],
            $data['Modelo'],
            $data['Anio'],
            $data['RutaDocumento']
        );
        
        $stmt->execute($params);
    }

    // borra una ficha técnica por id
    public static function delete($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("DELETE FROM adquisiciones.FichaTecnicaReferencia WHERE Id = ?");
        $stmt->execute([$id]);
    }

    // encuentra una ficha técnica específica
    public static function find($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare(
              "SELECT Id, IdCatalogoTecnologico, Marca, Modelo, Anio, Estado, RutaDocumento, FechaRegistro
               FROM adquisiciones.FichaTecnicaReferencia 
             WHERE Id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // obtiene la ruta del documento de una ficha técnica para descarga
    public static function getDocumento($id)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare("SELECT RutaDocumento FROM adquisiciones.FichaTecnicaReferencia WHERE Id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
