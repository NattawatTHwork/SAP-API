<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class TransactionPeriodGroups
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

    public function getAllTransactionPeriodGroups()
    {
        $query = 'SELECT transaction_period_group_id, transaction_period_group_code, description 
                  FROM cm_sap.tb_transaction_period_groups 
                  WHERE is_deleted = false 
                  ORDER BY transaction_period_group_id ASC';
        $result = pg_prepare($this->connection, "get_all_transaction_period_groups", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all transaction period groups.');
        }
        $result = pg_execute($this->connection, "get_all_transaction_period_groups", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all transaction period groups.');
        }
        $transactionPeriodGroups = pg_fetch_all($result);
        if ($transactionPeriodGroups === false) {
            return [];
        }
        foreach ($transactionPeriodGroups as &$transactionPeriodGroup) {
            $transactionPeriodGroup['transaction_period_group_id'] = $this->encryption->encrypt($transactionPeriodGroup['transaction_period_group_id']);
        }
        return $transactionPeriodGroups;
    }

    public function getTransactionPeriodGroup($encryptedTransactionPeriodGroupId)
    {
        $transactionPeriodGroupId = $this->encryption->decrypt($encryptedTransactionPeriodGroupId);
        $query = 'SELECT transaction_period_group_id, transaction_period_group_code, description 
                  FROM cm_sap.tb_transaction_period_groups 
                  WHERE transaction_period_group_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_transaction_period_group_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting transaction period group by ID.');
        }
        $result = pg_execute($this->connection, "get_transaction_period_group_by_id", array($transactionPeriodGroupId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting transaction period group by ID.');
        }
        $transactionPeriodGroup = pg_fetch_assoc($result);
        if ($transactionPeriodGroup === false) {
            return null;
        }
        $transactionPeriodGroup['transaction_period_group_id'] = $this->encryption->encrypt($transactionPeriodGroup['transaction_period_group_id']);

        return $transactionPeriodGroup;
    }

    public function createTransactionPeriodGroup($transaction_period_group_code, $description)
    {
        $query = 'INSERT INTO cm_sap.tb_transaction_period_groups (transaction_period_group_code, description, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, NOW(), NOW(), false) RETURNING transaction_period_group_id';
        $result = pg_prepare($this->connection, "create_transaction_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating transaction period group.');
        }
        $result = pg_execute($this->connection, "create_transaction_period_group", array($transaction_period_group_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating transaction period group.');
        }
        $transactionPeriodGroupId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($transactionPeriodGroupId);
    }

    public function updateTransactionPeriodGroup($encryptedTransactionPeriodGroupId, $transaction_period_group_code, $description)
    {
        $transactionPeriodGroupId = $this->encryption->decrypt($encryptedTransactionPeriodGroupId);
        $query = 'UPDATE cm_sap.tb_transaction_period_groups 
                  SET transaction_period_group_code = $2, description = $3, updated_at = NOW() 
                  WHERE transaction_period_group_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_transaction_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating transaction period group.');
        }
        $result = pg_execute($this->connection, "update_transaction_period_group", array($transactionPeriodGroupId, $transaction_period_group_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating transaction period group.');
        }
        return pg_affected_rows($result);
    }

    public function deleteTransactionPeriodGroup($encryptedTransactionPeriodGroupId)
    {
        $transactionPeriodGroupId = $this->encryption->decrypt($encryptedTransactionPeriodGroupId);
        $query = 'UPDATE cm_sap.tb_transaction_period_groups SET is_deleted = true, updated_at = NOW() 
                  WHERE transaction_period_group_id = $1';
        $result = pg_prepare($this->connection, "delete_transaction_period_group", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting transaction period group.');
        }
        $result = pg_execute($this->connection, "delete_transaction_period_group", array($transactionPeriodGroupId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting transaction period group.');
        }
        return pg_affected_rows($result);
    }
}
