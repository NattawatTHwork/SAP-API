<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GroupAccounts
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

    public function getGroupAccountAll()
    {
        $query = 'SELECT 
                      group_account_id, 
                      group_account_code, 
                      tb_group_accounts.chart_account_id, 
                      chart_account_code,
                      name_account, 
                      account_from, 
                      account_to
                  FROM cm_sap.tb_group_accounts 
                  INNER JOIN cm_sap.tb_chart_accounts
                  ON tb_group_accounts.chart_account_id = tb_chart_accounts.chart_account_id
                  WHERE tb_group_accounts.is_deleted = false
                  ORDER BY tb_group_accounts.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_group_accounts", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all group accounts.');
        }
        $result = pg_execute($this->connection, "get_all_group_accounts", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all group accounts.');
        }
        $groupAccounts = pg_fetch_all($result);
        if ($groupAccounts === false) {
            return [];
        }
        foreach ($groupAccounts as &$groupAccount) {
            $groupAccount['group_account_id'] = $this->encryption->encrypt($groupAccount['group_account_id']);
            $groupAccount['chart_account_id'] = $this->encryption->encrypt($groupAccount['chart_account_id']);
        }
        return $groupAccounts;
    }

    public function getGroupAccount($encryptedGroupAccountId)
    {
        $groupAccountId = $this->encryption->decrypt($encryptedGroupAccountId);
        $query = 'SELECT 
                      group_account_id, 
                      group_account_code, 
                      tb_group_accounts.chart_account_id, 
                      chart_account_code, 
                      name_account, 
                      account_from, 
                      account_to
                  FROM cm_sap.tb_group_accounts 
                  INNER JOIN cm_sap.tb_chart_accounts
                  ON tb_group_accounts.chart_account_id = tb_chart_accounts.chart_account_id
                  WHERE tb_group_accounts.is_deleted = false AND tb_group_accounts.group_account_id = $1
                  ORDER BY tb_group_accounts.created_at ASC';
        $result = pg_prepare($this->connection, "get_group_account_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting group account by ID.');
        }
        $result = pg_execute($this->connection, "get_group_account_by_id", array($groupAccountId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting group account by ID.');
        }
        $groupAccount = pg_fetch_assoc($result);
        if ($groupAccount === false) {
            return null;
        }
        $groupAccount['group_account_id'] = $this->encryption->encrypt($groupAccount['group_account_id']);
        $groupAccount['chart_account_id'] = $this->encryption->encrypt($groupAccount['chart_account_id']);
        return $groupAccount;
    }

    public function createGroupAccount(
        $group_account_code,
        $chart_account_id,
        $name_account,
        $account_from,
        $account_to
    ) {
        $chartAccountId = $this->encryption->decrypt($chart_account_id);
        $query = 'INSERT INTO cm_sap.tb_group_accounts (
                group_account_code, chart_account_id, name_account, account_from, account_to, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, NOW(), NOW(), false) RETURNING group_account_id';
        $result = pg_prepare($this->connection, "create_group_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating group account.');
        }
        $result = pg_execute(
            $this->connection,
            "create_group_account",
            array($group_account_code, $chartAccountId, $name_account, $account_from, $account_to)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating group account.');
        }
        $groupAccountId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($groupAccountId);
    }

    public function updateGroupAccount(
        $encryptedGroupAccountId,
        $group_account_code,
        $chart_account_id,
        $name_account,
        $account_from,
        $account_to
    ) {
        $groupAccountId = $this->encryption->decrypt($encryptedGroupAccountId);
        $chartAccountId = $this->encryption->decrypt($chart_account_id);
        $query = 'UPDATE cm_sap.tb_group_accounts 
                  SET group_account_code = $2, chart_account_id = $3, name_account = $4, account_from = $5, account_to = $6, updated_at = NOW() 
                  WHERE group_account_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_group_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating group account.');
        }
        $result = pg_execute(
            $this->connection,
            "update_group_account",
            array($groupAccountId, $group_account_code, $chartAccountId, $name_account, $account_from, $account_to)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating group account.');
        }
        return pg_affected_rows($result);
    }

    public function deleteGroupAccount($encryptedGroupAccountId)
    {
        $groupAccountId = $this->encryption->decrypt($encryptedGroupAccountId);
        $query = 'UPDATE cm_sap.tb_group_accounts SET is_deleted = true, updated_at = NOW() 
                  WHERE group_account_id = $1';
        $result = pg_prepare($this->connection, "delete_group_account", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting group account.');
        }
        $result = pg_execute($this->connection, "delete_group_account", array($groupAccountId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting group account.');
        }
        return pg_affected_rows($result);
    }
}
