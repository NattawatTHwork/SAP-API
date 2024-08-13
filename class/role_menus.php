<?php
include_once '../connect/db_connect.php';
include_once '../class/utility.php';

class RoleMenus
{
    private $db;
    private $connection;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
    }

    public function getRoleMenu($role_id)
    {
        $query = 'SELECT menu_id FROM cm_sap.tb_role_menus
                  INNER JOIN cm_sap.tb_roles ON tb_role_menus.role_id = tb_roles.role_id 
                  WHERE tb_role_menus.role_id = $1 AND tb_role_menus.is_deleted = false AND tb_roles.is_deleted = false';
        $stmt_name = "get_role_menus_" . Utility::generateRandomString();
        $result = pg_prepare($this->connection, $stmt_name, $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for retrieving role menus.');
        }
        $result = pg_execute($this->connection, $stmt_name, [$role_id]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for retrieving role menus.');
        }
        return pg_fetch_all($result);
    }

    public function checkRoleMenu($role_id, $menu_id)
    {
        $query = 'SELECT COUNT(*) FROM cm_sap.tb_role_menus
                  WHERE role_id = $1 AND menu_id = $2';
        $stmt_name = "check_role_menu_" . Utility::generateRandomString();
        $result = pg_prepare($this->connection, $stmt_name, $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for checking role menu.');
        }
        $result = pg_execute($this->connection, $stmt_name, [$role_id, $menu_id]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for checking role menu.');
        }
        $count = pg_fetch_result($result, 0, 0);
        return $count > 0;
    }

    public function createRoleMenu($role_id, $menu_id)
    {
        $query = 'INSERT INTO cm_sap.tb_role_menus (role_id, menu_id, updated_at, is_deleted) 
                  VALUES ($1, $2, NOW(), false)';
        $stmt_name = "create_role_menu_" . Utility::generateRandomString();
        $result = pg_prepare($this->connection, $stmt_name, $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating role menu.');
        }
        $result = pg_execute($this->connection, $stmt_name, [$role_id, $menu_id]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating role menu.');
        }
        return pg_affected_rows($result);
    }

    public function updateRoleMenu($role_id, $menu_id, $is_deleted)
    {
        $query = 'UPDATE cm_sap.tb_role_menus 
                  SET updated_at = NOW(), is_deleted = $1 
                  WHERE role_id = $2 AND menu_id = $3';
        $stmt_name = "update_role_menu_" . Utility::generateRandomString();
        $result = pg_prepare($this->connection, $stmt_name, $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating role menu.');
        }
        $result = pg_execute($this->connection, $stmt_name, [$is_deleted, $role_id, $menu_id]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating role menu.');
        }
        return pg_affected_rows($result);
    }
}
