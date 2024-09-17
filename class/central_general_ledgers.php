<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class CentralGeneralLedgers
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

    public function getAllCentralGeneralLedgers()
    {
        $query = 'SELECT central_general_ledger_id, gl_account, tb_central_general_ledgers.company_id, company_code, tb_central_general_ledgers.created_at, username
                  FROM cm_sap.tb_central_general_ledgers 
                  INNER JOIN cm_sap.tb_companies ON tb_central_general_ledgers.company_id = tb_companies.company_id
                  INNER JOIN cm_sap.tb_users ON tb_central_general_ledgers.user_create = tb_users.user_id
                  WHERE tb_central_general_ledgers.is_deleted = false 
                  ORDER BY tb_central_general_ledgers.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_cgl", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all central general ledgers.');
        }
        $result = pg_execute($this->connection, "get_all_cgl", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all central general ledgers.');
        }
        $ledgers = pg_fetch_all($result);
        if ($ledgers === false) {
            return [];
        }
        foreach ($ledgers as &$ledger) {
            $ledger['central_general_ledger_id'] = $this->encryption->encrypt($ledger['central_general_ledger_id']);
            $ledger['company_id'] = $this->encryption->encrypt($ledger['company_id']);
        }
        return $ledgers;
    }

    public function getCentralGeneralLedger($encryptedLedgerId)
    {
        $ledgerId = $this->encryption->decrypt($encryptedLedgerId);
        $query = 'SELECT central_general_ledger_id, gl_account, tb_central_general_ledgers.company_id, company_code, tb_central_general_ledgers.created_at, username
                  FROM cm_sap.tb_central_general_ledgers 
                  INNER JOIN cm_sap.tb_companies ON tb_central_general_ledgers.company_id = tb_companies.company_id
                  INNER JOIN cm_sap.tb_users ON tb_central_general_ledgers.user_create = tb_users.user_id
                  WHERE central_general_ledger_id = $1 AND tb_central_general_ledgers.is_deleted = false';
        $result = pg_prepare($this->connection, "get_cgl_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting central general ledger by ID.');
        }
        $result = pg_execute($this->connection, "get_cgl_by_id", [$ledgerId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting central general ledger by ID.');
        }
        $ledger = pg_fetch_assoc($result);
        if ($ledger === false) {
            return null;
        }
        $ledger['central_general_ledger_id'] = $this->encryption->encrypt($ledger['central_general_ledger_id']);
        $ledger['company_id'] = $this->encryption->encrypt($ledger['company_id']);
        return $ledger;
    }

    public function createCentralGeneralLedger($gl_account, $encryptedCompanyId, $encryptedUserId)
    {
        $CompanyId = $this->encryption->decrypt($encryptedCompanyId);
        $UserId = $this->encryption->decrypt($encryptedUserId);
        $query = 'INSERT INTO cm_sap.tb_central_general_ledgers (gl_account, company_id, user_create, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, NOW(), NOW(), false) 
                  RETURNING central_general_ledger_id';
        $result = pg_prepare($this->connection, "create_cgl", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating central general ledger.');
        }
        $result = pg_execute($this->connection, "create_cgl", [$gl_account, $CompanyId, $UserId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating central general ledger.');
        }
        $ledgerId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($ledgerId);
    }

    public function updateCentralGeneralLedger($encryptedLedgerId, $gl_account, $encryptedCompanyId)
    {
        $CompanyId = $this->encryption->decrypt($encryptedCompanyId);
        $ledgerId = $this->encryption->decrypt($encryptedLedgerId);
        $query = 'UPDATE cm_sap.tb_central_general_ledgers 
                  SET gl_account = $2, company_id = $3, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_cgl", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating central general ledger.');
        }
        $result = pg_execute($this->connection, "update_cgl", [$ledgerId, $gl_account, $CompanyId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating central general ledger.');
        }
        return pg_affected_rows($result);
    }

    public function deleteCentralGeneralLedger($encryptedLedgerId)
    {
        $ledgerId = $this->encryption->decrypt($encryptedLedgerId);
        $query = 'UPDATE cm_sap.tb_central_general_ledgers 
                  SET is_deleted = true, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "delete_cgl", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting central general ledger.');
        }
        $result = pg_execute($this->connection, "delete_cgl", [$ledgerId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting central general ledger.');
        }
        return pg_affected_rows($result);
    }
}
