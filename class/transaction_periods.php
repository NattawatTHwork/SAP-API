<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class TransactionPeriods
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

    public function getTransactionPeriodAll()
    {
        $query = 'SELECT 
                      tp.transaction_period_id, 
                      tp.transaction_period_group_id, 
                      tpg.transaction_period_group_code,
                      tp.transaction_period_type_id, 
                      tpt.transaction_period_type_code, 
                      tp.account_from, 
                      tp.account_to, 
                      tp.period_from_first, 
                      tp.period_from_first_year, 
                      tp.period_to_first, 
                      tp.period_to_first_year, 
                      tp.period_from_second, 
                      tp.period_from_second_year, 
                      tp.period_to_second, 
                      tp.period_to_second_year, 
                      tp.augr
                  FROM cm_sap.tb_transaction_periods tp
                  INNER JOIN cm_sap.tb_transaction_period_types tpt 
                  ON tp.transaction_period_type_id = tpt.transaction_period_type_id
                  INNER JOIN cm_sap.tb_transaction_period_groups tpg 
                  ON tp.transaction_period_group_id = tpg.transaction_period_group_id
                  WHERE tp.is_deleted = false 
                  ORDER BY tp.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_transaction_periods", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all transaction periods.');
        }
        $result = pg_execute($this->connection, "get_all_transaction_periods", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all transaction periods.');
        }
        $transactionPeriods = pg_fetch_all($result);
        if ($transactionPeriods === false) {
            return [];
        }
        foreach ($transactionPeriods as &$transactionPeriod) {
            $transactionPeriod['transaction_period_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_id']);
            $transactionPeriod['transaction_period_group_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_group_id']);
            $transactionPeriod['transaction_period_type_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_type_id']);
        }
        return $transactionPeriods;
    }

    public function getTransactionPeriod($encryptedTransactionPeriodId)
    {
        $transactionPeriodId = $this->encryption->decrypt($encryptedTransactionPeriodId);
        $query = 'SELECT 
                      tp.transaction_period_id, 
                      tp.transaction_period_group_id, 
                      tpg.transaction_period_group_code,
                      tp.transaction_period_type_id, 
                      tpt.transaction_period_type_code, 
                      tp.account_from, 
                      tp.account_to, 
                      tp.period_from_first, 
                      tp.period_from_first_year, 
                      tp.period_to_first, 
                      tp.period_to_first_year, 
                      tp.period_from_second, 
                      tp.period_from_second_year, 
                      tp.period_to_second, 
                      tp.period_to_second_year, 
                      tp.augr
                  FROM cm_sap.tb_transaction_periods tp
                  INNER JOIN cm_sap.tb_transaction_period_types tpt 
                  ON tp.transaction_period_type_id = tpt.transaction_period_type_id
                  INNER JOIN cm_sap.tb_transaction_period_groups tpg 
                  ON tp.transaction_period_group_id = tpg.transaction_period_group_id
                  WHERE tp.is_deleted = false 
                  AND tp.transaction_period_id = $1
                  ORDER BY tp.created_at ASC';
        $result = pg_prepare($this->connection, "get_transaction_period_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting transaction period by ID.');
        }
        $result = pg_execute($this->connection, "get_transaction_period_by_id", array($transactionPeriodId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting transaction period by ID.');
        }
        $transactionPeriod = pg_fetch_assoc($result);
        if ($transactionPeriod === false) {
            return null;
        }
        $transactionPeriod['transaction_period_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_id']);
        $transactionPeriod['transaction_period_group_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_group_id']);
        $transactionPeriod['transaction_period_type_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_type_id']);
        return $transactionPeriod;
    }

    public function createTransactionPeriod(
        $transaction_period_group_id,
        $transaction_period_type_id,
        $account_from,
        $account_to,
        $period_from_first,
        $period_from_first_year,
        $period_to_first,
        $period_to_first_year,
        $period_from_second,
        $period_from_second_year,
        $period_to_second,
        $period_to_second_year,
        $augr
    ) {
        $TransactionPeriodGroupId = $this->encryption->decrypt($transaction_period_group_id);
        $transactionPeriodTypeId = $this->encryption->decrypt($transaction_period_type_id);
        $query = 'INSERT INTO cm_sap.tb_transaction_periods (
                transaction_period_group_id, transaction_period_type_id, account_from, account_to, period_from_first, period_from_first_year,
                period_to_first, period_to_first_year, period_from_second, period_from_second_year, period_to_second,
                period_to_second_year, augr, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, NOW(), NOW(), false) RETURNING transaction_period_id';
        $result = pg_prepare($this->connection, "create_transaction_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating transaction period.');
        }
        $result = pg_execute(
            $this->connection,
            "create_transaction_period",
            array(
                $TransactionPeriodGroupId,
                $transactionPeriodTypeId,
                $account_from,
                $account_to,
                $period_from_first,
                $period_from_first_year,
                $period_to_first,
                $period_to_first_year,
                $period_from_second,
                $period_from_second_year,
                $period_to_second,
                $period_to_second_year,
                $augr
            )
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating transaction period.');
        }
        $transactionPeriodId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($transactionPeriodId);
    }

    public function updateTransactionPeriod(
        $encryptedTransactionPeriodId,
        $transaction_period_group_id,
        $transaction_period_type_id,
        $account_from,
        $account_to,
        $period_from_first,
        $period_from_first_year,
        $period_to_first,
        $period_to_first_year,
        $period_from_second,
        $period_from_second_year,
        $period_to_second,
        $period_to_second_year,
        $augr
    ) {
        $transactionPeriodId = $this->encryption->decrypt($encryptedTransactionPeriodId);
        $TransactionPeriodGroupId = $this->encryption->decrypt($transaction_period_group_id);
        $transactionPeriodTypeId = $this->encryption->decrypt($transaction_period_type_id);
        $query = 'UPDATE cm_sap.tb_transaction_periods 
                  SET transaction_period_group_id = $2, transaction_period_type_id = $3, account_from = $4, account_to = $5, period_from_first = $6, period_from_first_year = $7, period_to_first = $8, 
                  period_to_first_year = $9, period_from_second = $10, period_from_second_year = $11, period_to_second = $12, period_to_second_year = $13, augr = $14, updated_at = NOW() 
                  WHERE transaction_period_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_transaction_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating transaction period.');
        }
        $result = pg_execute(
            $this->connection,
            "update_transaction_period",
            array(
                $transactionPeriodId,
                $TransactionPeriodGroupId,
                $transactionPeriodTypeId,
                $account_from,
                $account_to,
                $period_from_first,
                $period_from_first_year,
                $period_to_first,
                $period_to_first_year,
                $period_from_second,
                $period_from_second_year,
                $period_to_second,
                $period_to_second_year,
                $augr
            )
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating transaction period.');
        }
        return pg_affected_rows($result);
    }

    public function deleteTransactionPeriod($encryptedTransactionPeriodId)
    {
        $transactionPeriodId = $this->encryption->decrypt($encryptedTransactionPeriodId);
        $query = 'UPDATE cm_sap.tb_transaction_periods SET is_deleted = true, updated_at = NOW() 
                  WHERE transaction_period_id = $1';
        $result = pg_prepare($this->connection, "delete_transaction_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting transaction period.');
        }
        $result = pg_execute($this->connection, "delete_transaction_period", array($transactionPeriodId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting transaction period.');
        }
        return pg_affected_rows($result);
    }
}
