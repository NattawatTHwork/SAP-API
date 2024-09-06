<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class ChartAccounts
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

    public function getChartAccountsAll()
    {
        $query = 'SELECT chart_account_id, chart_account_code, language, account_length, collection_control, chart_account_group, suspend
                  FROM cm_sap.tb_chart_accounts 
                  WHERE is_deleted = false
                  ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_chart_accounts", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all chart accounts.');
        }
        $result = pg_execute($this->connection, "get_all_chart_accounts", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all chart accounts.');
        }
        $chartAccounts = pg_fetch_all($result);
        if ($chartAccounts === false) {
            return [];
        }
        foreach ($chartAccounts as &$chartAccount) {
            $chartAccount['chart_account_id'] = $this->encryption->encrypt($chartAccount['chart_account_id']);
        }
        return $chartAccounts;
    }

    public function getChartAccount($encryptedChartAccountId)
    {
        $chartAccountId = $this->encryption->decrypt($encryptedChartAccountId);
        $query = 'SELECT chart_account_id, chart_account_code, language, account_length, collection_control, chart_account_group, suspend
                  FROM cm_sap.tb_chart_accounts 
                  WHERE chart_account_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_chart_account_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting chart account by ID.');
        }
        $result = pg_execute($this->connection, "get_chart_account_by_id", array($chartAccountId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting chart account by ID.');
        }
        $chartAccount = pg_fetch_assoc($result);
        if ($chartAccount === false) {
            return null;
        }
        $chartAccount['chart_account_id'] = $this->encryption->encrypt($chartAccount['chart_account_id']);        
        return $chartAccount;
    }

    public function createChartAccount($chart_account_code, $language, $account_length, $collection_control, $chart_account_group, $suspend)
    {
        $query = 'INSERT INTO cm_sap.tb_chart_accounts (chart_account_code, language, account_length, collection_control, chart_account_group, suspend, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW(), false) RETURNING chart_account_id';
        $result = pg_prepare($this->connection, "create_chart_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating chart account.');
        }
        $result = pg_execute($this->connection, "create_chart_account", array(
            $chart_account_code, $language, $account_length, $collection_control, $chart_account_group, $suspend
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating chart account.');
        }
        $chartAccountId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($chartAccountId);
    }

    public function updateChartAccount($encryptedChartAccountId, $chart_account_code, $language, $account_length, $collection_control, $chart_account_group, $suspend)
    {
        $chartAccountId = $this->encryption->decrypt($encryptedChartAccountId);
        $query = 'UPDATE cm_sap.tb_chart_accounts 
                  SET language = $2, account_length = $3, collection_control = $4, chart_account_group = $5, suspend = $6, chart_account_code = $7, updated_at = NOW() 
                  WHERE chart_account_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_chart_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating chart account.');
        }
        $result = pg_execute($this->connection, "update_chart_account", array(
            $chartAccountId, $language, $account_length, $collection_control, $chart_account_group, $suspend, $chart_account_code
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating chart account.');
        }
        return pg_affected_rows($result);
    }

    public function deleteChartAccount($encryptedChartAccountId)
    {
        $chartAccountId = $this->encryption->decrypt($encryptedChartAccountId);
        $query = 'UPDATE cm_sap.tb_chart_accounts SET is_deleted = true, updated_at = NOW() 
                  WHERE chart_account_id = $1';
        $result = pg_prepare($this->connection, "delete_chart_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting chart account.');
        }
        $result = pg_execute($this->connection, "delete_chart_account", array($chartAccountId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting chart account.');
        }
        return pg_affected_rows($result);
    }
}
