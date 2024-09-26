<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class Roles
{
    private $db;
    private $connection;
    private $encryption;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
        $this->encryption = new Encryption();
    }

    public function getRoleAll()
    {
        $query = 'SELECT role_id, role FROM cm_sap.tb_roles WHERE is_deleted = false ORDER BY role_id ASC';
        $result = pg_prepare($this->connection, "get_all_roles", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for retrieving all roles.');
        }
        $result = pg_execute($this->connection, "get_all_roles", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for retrieving all roles.');
        }
        $roles = pg_fetch_all($result);
        if ($roles === false) {
            return [];
        }
        foreach ($roles as &$role) {
            $role['role_id'] = $this->encryption->encrypt($role['role_id']);
        }
        return $roles;
    }

    public function getRole($encryptedRoleId)
    {
        $roleId = $this->encryption->decrypt($encryptedRoleId);
        $query = 'SELECT role_id, role FROM cm_sap.tb_roles WHERE role_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_role_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for retrieving role by ID.');
        }
        $result = pg_execute($this->connection, "get_role_by_id", array($roleId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for retrieving role by ID.');
        }
        $role = pg_fetch_assoc($result);
        if ($role === false) {
            return null;
        }
        $role['role_id'] = $this->encryption->encrypt($role['role_id']);
        return $role;
    }

    public function createRole($role)
    {
        $query = 'INSERT INTO cm_sap.tb_roles (role, created_at, updated_at, is_deleted) 
                  VALUES ($1, NOW(), NOW(), false) RETURNING role_id';
        $result = pg_prepare($this->connection, "create_role", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating role.');
        }
        $result = pg_execute($this->connection, "create_role", array($role));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating role.');
        }
        $roleId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($roleId);
    }

    public function updateRole($encryptedRoleId, $role)
    {
        $roleId = $this->encryption->decrypt($encryptedRoleId);
        $query = 'UPDATE cm_sap.tb_roles 
                  SET role = $2, updated_at = NOW() 
                  WHERE role_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_role", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating role.');
        }
        $result = pg_execute($this->connection, "update_role", array($roleId, $role));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating role.');
        }
        return pg_affected_rows($result);
    }

    public function deleteRole($encryptedRoleId)
    {
        $roleId = $this->encryption->decrypt($encryptedRoleId);
        $query = 'UPDATE cm_sap.tb_roles SET is_deleted = true, updated_at = NOW() 
                  WHERE role_id = $1';
        $result = pg_prepare($this->connection, "delete_role", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting role.');
        }
        $result = pg_execute($this->connection, "delete_role", array($roleId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting role.');
        }
        return pg_affected_rows($result);
    }
}
