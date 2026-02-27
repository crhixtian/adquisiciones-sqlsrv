<?php
// envoltorio ligero para manejar conexiones SQLSRV y simplificar consultas
class SqlsrvException extends \Exception {}

class SqlsrvStatement {
    private $conn;
    private $sql;
    private $result;

    public function __construct($conn, $sql) {
        $this->conn = $conn;
        $this->sql  = $sql;
    }

    public function execute($params = []) {
        $this->result = sqlsrv_query($this->conn, $this->sql, $params);
        if ($this->result === false) {
            $this->throwError();
        }
        return true;
    }

    public function fetchAll() {
        $rows = [];
        while ($row = sqlsrv_fetch_array($this->result, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function fetch() {
        return sqlsrv_fetch_array($this->result, SQLSRV_FETCH_ASSOC);
    }

    private function throwError() {
        $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        $message = isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown SQLSRV error';
        $code    = isset($errors[0]['SQLSTATE']) ? $errors[0]['SQLSTATE'] : 0;
        throw new SqlsrvException($message, $code);
    }
}

class SqlsrvConnection {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function query($sql) {
        $stmt = new SqlsrvStatement($this->conn, $sql);
        $stmt->execute();
        return $stmt;
    }

    public function prepare($sql) {
        return new SqlsrvStatement($this->conn, $sql);
    }

    public function beginTransaction() {
        if (!sqlsrv_begin_transaction($this->conn)) {
            $this->throwError();
        }
        return true;
    }

    public function commit() {
        if (!sqlsrv_commit($this->conn)) {
            $this->throwError();
        }
        return true;
    }

    public function rollBack() {
        if (!sqlsrv_rollback($this->conn)) {
            $this->throwError();
        }
        return true;
    }

    public function lastInsertId() {
        $rs = sqlsrv_query($this->conn, 'SELECT SCOPE_IDENTITY() AS id');
        if ($rs === false) {
            $this->throwError();
        }
        $row = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

    private function throwError() {
        $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        $message = isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown SQLSRV error';
        $code    = isset($errors[0]['SQLSTATE']) ? $errors[0]['SQLSTATE'] : 0;
        throw new SqlsrvException($message, $code);
    }
}

class Database {
    private static $instance = null;

    public static function connect() {
        if (self::$instance === null) {
            $server   = "localhost";
            $database = "AdquisicionesPech";
            $username = "";
            $password = "";

            $params = [
                "Database" => $database,
                "TrustServerCertificate" => true,
            ];

            $conn = sqlsrv_connect($server, $params + [
                'UID' => $username,
                'PWD' => $password,
            ]);

            if ($conn === false) {
                $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
                error_log(print_r($errors, true));
                die("Error de conexión al sistema.");
            }

            self::$instance = new SqlsrvConnection($conn);
        }

        return self::$instance;
    }
}
