<?php
class DBConnect {
    private $connection;
    private $host = '203.151.66.18';
    private $dbname = 'SAP';
    private $user = 'postgres';
    private $password = 'dbpgvTA@2023';

    public function __construct() {
        $this->connection = pg_connect("host={$this->host} dbname={$this->dbname} user={$this->user} password={$this->password}");

        if (!$this->connection) {
            die("Error: Unable to connect to the database.");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function __destruct() {
        pg_close($this->connection);
    }
}
?>
