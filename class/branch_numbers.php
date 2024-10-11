<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class BranchNumbers
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

    public function getBranchNumberAll()
    {
        $query = 'SELECT branch_number_id, branch_number_code 
                  FROM cm_sap.tb_branch_numbers WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_branch_numbers", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all branch numbers.');
        }
        $result = pg_execute($this->connection, "get_all_branch_numbers", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all branch numbers.');
        }
        $branchNumbers = pg_fetch_all($result);
        if ($branchNumbers === false) {
            return [];
        }
        foreach ($branchNumbers as &$branch) {
            $branch['branch_number_id'] = $this->encryption->encrypt($branch['branch_number_id']);
        }
        return $branchNumbers;
    }

    public function getBranchNumber($encryptedBranchNumberId)
    {
        $branchNumberId = $this->encryption->decrypt($encryptedBranchNumberId);
        $query = 'SELECT branch_number_id, branch_number_code 
                  FROM cm_sap.tb_branch_numbers WHERE branch_number_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_branch_number_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting branch number by ID.');
        }
        $result = pg_execute($this->connection, "get_branch_number_by_id", array($branchNumberId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting branch number by ID.');
        }
        $branch = pg_fetch_assoc($result);
        if ($branch === false) {
            return null;
        }
        $branch['branch_number_id'] = $this->encryption->encrypt($branch['branch_number_id']);
        return $branch;
    }

    public function createBranchNumber($branch_number_code)
    {
        $query = 'INSERT INTO cm_sap.tb_branch_numbers (branch_number_code, created_at, updated_at, is_deleted) 
                  VALUES ($1, NOW(), NOW(), false) RETURNING branch_number_id';
        $result = pg_prepare($this->connection, "create_branch_number", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating branch number.');
        }
        $result = pg_execute($this->connection, "create_branch_number", array($branch_number_code));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating branch number.');
        }
        $branchNumberId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($branchNumberId);
    }

    public function updateBranchNumber($encryptedBranchNumberId, $branch_number_code)
    {
        $branchNumberId = $this->encryption->decrypt($encryptedBranchNumberId);
        $query = 'UPDATE cm_sap.tb_branch_numbers 
                  SET branch_number_code = $2, updated_at = NOW() 
                  WHERE branch_number_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_branch_number", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating branch number.');
        }
        $result = pg_execute($this->connection, "update_branch_number", array($branchNumberId, $branch_number_code));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating branch number.');
        }
        return pg_affected_rows($result);
    }

    public function deleteBranchNumber($encryptedBranchNumberId)
    {
        $branchNumberId = $this->encryption->decrypt($encryptedBranchNumberId);
        $query = 'UPDATE cm_sap.tb_branch_numbers SET is_deleted = true, updated_at = NOW() 
                  WHERE branch_number_id = $1';
        $result = pg_prepare($this->connection, "delete_branch_number", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting branch number.');
        }
        $result = pg_execute($this->connection, "delete_branch_number", array($branchNumberId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting branch number.');
        }
        return pg_affected_rows($result);
    }
}
