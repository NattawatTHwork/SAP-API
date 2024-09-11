<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class PeriodGroups
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

    public function getAllPeriodGroups()
    {
        $query = 'SELECT period_group_id, period_group_code, description FROM cm_sap.tb_period_groups WHERE is_deleted = false ORDER BY period_group_id ASC';
        $result = pg_prepare($this->connection, "get_all_period_groups", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all period groups.');
        }
        $result = pg_execute($this->connection, "get_all_period_groups", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all period groups.');
        }
        $periodGroups = pg_fetch_all($result);
        if ($periodGroups === false) {
            return [];
        }
        foreach ($periodGroups as &$periodGroup) {
            $periodGroup['period_group_id'] = $this->encryption->encrypt($periodGroup['period_group_id']);
        }
        return $periodGroups;
    }

    public function getPeriodGroup($encryptedPeriodGroupId)
    {
        $periodGroupId = $this->encryption->decrypt($encryptedPeriodGroupId);
        $query = 'SELECT period_group_id, period_group_code, description FROM cm_sap.tb_period_groups WHERE period_group_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_period_group_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting period group by ID.');
        }
        $result = pg_execute($this->connection, "get_period_group_by_id", array($periodGroupId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting period group by ID.');
        }
        $periodGroup = pg_fetch_assoc($result);
        if ($periodGroup === false) {
            return null;
        }
        $periodGroup['period_group_id'] = $this->encryption->encrypt($periodGroup['period_group_id']);

        return $periodGroup;
    }

    public function createPeriodGroup($period_group_code, $description)
    {
        $query = 'INSERT INTO cm_sap.tb_period_groups (period_group_code, description, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, NOW(), NOW(), false) RETURNING period_group_id';
        $result = pg_prepare($this->connection, "create_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating period group.');
        }
        $result = pg_execute($this->connection, "create_period_group", array($period_group_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating period group.');
        }
        $periodGroupId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($periodGroupId);
    }

    public function updatePeriodGroup($encryptedPeriodGroupId, $period_group_code, $description)
    {
        $periodGroupId = $this->encryption->decrypt($encryptedPeriodGroupId);
        $query = 'UPDATE cm_sap.tb_period_groups 
                  SET period_group_code = $2, description = $3, updated_at = NOW() 
                  WHERE period_group_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating period group.');
        }
        $result = pg_execute($this->connection, "update_period_group", array($periodGroupId, $period_group_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating period group.');
        }
        return pg_affected_rows($result);
    }

    public function deletePeriodGroup($encryptedPeriodGroupId)
    {
        $periodGroupId = $this->encryption->decrypt($encryptedPeriodGroupId);
        $query = 'UPDATE cm_sap.tb_period_groups SET is_deleted = true, updated_at = NOW() 
                  WHERE period_group_id = $1';
        $result = pg_prepare($this->connection, "delete_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting period group.');
        }
        $result = pg_execute($this->connection, "delete_period_group", array($periodGroupId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting period group.');
        }
        return pg_affected_rows($result);
    }
}
