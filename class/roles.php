<?php
include_once '../connect/db_connect.php';

class Roles
{
    private $db;
    private $connection;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
    }

    public function getRoleAll()
    {
        $query = 'SELECT role_id, role FROM cm_sap.tb_roles WHERE is_deleted = false';
        $result = pg_prepare($this->connection, "get_all_roles", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for retrieving all roles.');
        }
        $result = pg_execute($this->connection, "get_all_roles", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for retrieving all roles.');
        }
        return pg_fetch_all($result);
    }
}
