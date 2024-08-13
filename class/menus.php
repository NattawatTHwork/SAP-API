<?php
include_once '../connect/db_connect.php';

class Menus
{
    private $db;
    private $connection;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
    }

    public function getMenuAll()
    {
        $query = 'SELECT menu_id FROM cm_sap.tb_menus WHERE is_deleted = false';
        $result = pg_prepare($this->connection, "get_all_menus", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all menus.');
        }
        $result = pg_execute($this->connection, "get_all_menus", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all menus.');
        }
        return pg_fetch_all($result);
    }
}
?>
