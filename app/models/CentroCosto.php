<?php
// modelo para acceder a la tabla de centros de costo

require_once __DIR__ . '/../core/Database.php';

class CentroCosto
{
    // devuelve lista de centros de costo ordenada por nombre
    public static function all()
    {
        $conn = Database::connect();
        $stmt = $conn->query("SELECT Id, NombreCentro FROM CentroCosto ORDER BY NombreCentro");
        return $stmt->fetchAll();
    }
}
