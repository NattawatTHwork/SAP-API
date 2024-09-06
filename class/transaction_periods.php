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

    public function getTransactionPeriodAll($encryptedFiscalYearId)
    {
        $fiscalYearId = $this->encryption->decrypt($encryptedFiscalYearId);
        $query = 'SELECT 
                      tp.transaction_period_id, 
                      tp.fiscal_year_id, 
                      tp.transaction_period_type_id, 
                      tpt.transaction_period_type_code, 
                      tp.account_from, 
                      tp.account_to, 
                      tp.period_from_first, 
                      p1.period_code AS period_from_first_code, 
                      tp.period_from_first_year, 
                      tp.period_to_first, 
                      p2.period_code AS period_to_first_code, 
                      tp.period_to_first_year, 
                      tp.period_from_second, 
                      p3.period_code AS period_from_second_code, 
                      tp.period_from_second_year, 
                      tp.period_to_second, 
                      p4.period_code AS period_to_second_code, 
                      tp.period_to_second_year, 
                      tp.augr
                  FROM cm_sap.tb_transaction_periods tp
                  INNER JOIN cm_sap.tb_transaction_period_types tpt 
                      ON tp.transaction_period_type_id = tpt.transaction_period_type_id
                  LEFT JOIN cm_sap.tb_periods p1 
                      ON tp.period_from_first::integer = p1.period_id
                  LEFT JOIN cm_sap.tb_periods p2 
                      ON tp.period_to_first::integer = p2.period_id
                  LEFT JOIN cm_sap.tb_periods p3 
                      ON tp.period_from_second::integer = p3.period_id
                  LEFT JOIN cm_sap.tb_periods p4 
                      ON tp.period_to_second::integer = p4.period_id
                  WHERE tp.is_deleted = false 
                  AND tp.fiscal_year_id = $1
                  ORDER BY tp.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_transaction_periods", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all transaction periods.');
        }        
        $result = pg_execute($this->connection, "get_all_transaction_periods", [$fiscalYearId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all transaction periods.');
        }
        $transactionPeriods = pg_fetch_all($result);
        if ($transactionPeriods === false) {
            return [];
        }
        foreach ($transactionPeriods as &$transactionPeriod) {
            $transactionPeriod['transaction_period_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_id']);
            $transactionPeriod['fiscal_year_id'] = $this->encryption->encrypt($transactionPeriod['fiscal_year_id']);
            $transactionPeriod['transaction_period_type_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_type_id']);
        }
        return $transactionPeriods;
    }     
    
    public function getTransactionPeriod($encryptedTransactionPeriodId)
    {
        $transactionPeriodId = $this->encryption->decrypt($encryptedTransactionPeriodId);
        $query = 'SELECT 
                      tp.transaction_period_id, 
                      tp.fiscal_year_id, 
                      tp.transaction_period_type_id, 
                      tpt.transaction_period_type_code, 
                      tp.account_from, 
                      tp.account_to, 
                      tp.period_from_first, 
                      p1.period_code AS period_from_first_code, 
                      tp.period_from_first_year, 
                      tp.period_to_first, 
                      p2.period_code AS period_to_first_code, 
                      tp.period_to_first_year, 
                      tp.period_from_second, 
                      p3.period_code AS period_from_second_code, 
                      tp.period_from_second_year, 
                      tp.period_to_second, 
                      p4.period_code AS period_to_second_code, 
                      tp.period_to_second_year, 
                      tp.augr
                  FROM cm_sap.tb_transaction_periods tp
                  INNER JOIN cm_sap.tb_transaction_period_types tpt 
                      ON tp.transaction_period_type_id = tpt.transaction_period_type_id
                  LEFT JOIN cm_sap.tb_periods p1 
                      ON tp.period_from_first::integer = p1.period_id
                  LEFT JOIN cm_sap.tb_periods p2 
                      ON tp.period_to_first::integer = p2.period_id
                  LEFT JOIN cm_sap.tb_periods p3 
                      ON tp.period_from_second::integer = p3.period_id
                  LEFT JOIN cm_sap.tb_periods p4 
                      ON tp.period_to_second::integer = p4.period_id
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
        $transactionPeriod['fiscal_year_id'] = $this->encryption->encrypt($transactionPeriod['fiscal_year_id']);
        $transactionPeriod['transaction_period_type_id'] = $this->encryption->encrypt($transactionPeriod['transaction_period_type_id']);
        $transactionPeriod['period_from_first'] = $this->encryption->encrypt($transactionPeriod['period_from_first']);
        $transactionPeriod['period_to_first'] = $this->encryption->encrypt($transactionPeriod['period_to_first']);
        $transactionPeriod['period_from_second'] = $this->encryption->encrypt($transactionPeriod['period_from_second']);
        $transactionPeriod['period_to_second'] = $this->encryption->encrypt($transactionPeriod['period_to_second']);
        return $transactionPeriod;
    }    

    public function createTransactionPeriod(
        $fiscal_year_id,
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
        $fiscalYearId = $this->encryption->decrypt($fiscal_year_id);
        $transactionPeriodTypeId = $this->encryption->decrypt($transaction_period_type_id);
        $periodFromFirst = $this->encryption->decrypt($period_from_first);
        $periodToFirst = $this->encryption->decrypt($period_to_first);
        $periodFromSecond = $this->encryption->decrypt($period_from_second);
        $periodToSecond = $this->encryption->decrypt($period_to_second);
        
        $query = 'INSERT INTO cm_sap.tb_transaction_periods (
                fiscal_year_id, transaction_period_type_id, account_from, account_to, period_from_first, period_from_first_year,
                period_to_first, period_to_first_year, period_from_second, period_from_second_year, period_to_second,
                period_to_second_year, augr, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, NOW(), NOW(), false) RETURNING transaction_period_id';
        
        $result = pg_prepare($this->connection, "create_transaction_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating transaction period.');
        }
        
        $result = pg_execute(
            $this->connection, "create_transaction_period",
            array(
                $fiscalYearId,
                $transactionPeriodTypeId,
                $account_from,
                $account_to,
                $periodFromFirst,
                $period_from_first_year,
                $periodToFirst,
                $period_to_first_year,
                $periodFromSecond,
                $period_from_second_year,
                $periodToSecond,
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

    public function updateTransactionPeriod($encryptedTransactionPeriodId, $transaction_period_type_id, $account_from, $account_to, $period_from_first, $period_from_first_year, $period_to_first, $period_to_first_year, $period_from_second, $period_from_second_year, $period_to_second, $period_to_second_year, $augr)
    {
        $transactionPeriodId = $this->encryption->decrypt($encryptedTransactionPeriodId);
        $transactionPeriodTypeId = $this->encryption->decrypt($transaction_period_type_id);
        $periodFromFirst = $this->encryption->decrypt($period_from_first);
        $periodToFirst = $this->encryption->decrypt($period_to_first);
        $periodFromSecond = $this->encryption->decrypt($period_from_second);
        $periodToSecond = $this->encryption->decrypt($period_to_second);
        $query = 'UPDATE cm_sap.tb_transaction_periods 
                  SET transaction_period_type_id = $2, account_from = $3, account_to = $4, period_from_first = $5, period_from_first_year = $6, period_to_first = $7, 
                  period_to_first_year = $8, period_from_second = $9, period_from_second_year = $10, period_to_second = $11, period_to_second_year = $12, augr = $13, updated_at = NOW() 
                  WHERE transaction_period_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_transaction_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating transaction period.');
        }
        $result = pg_execute(
            $this->connection, "update_transaction_period", 
            array(
                $transactionPeriodId, 
                $transactionPeriodTypeId, 
                $account_from, 
                $account_to, 
                $periodFromFirst, 
                $period_from_first_year, 
                $periodToFirst, 
                $period_to_first_year, 
                $periodFromSecond, 
                $period_from_second_year, 
                $periodToSecond, 
                $period_to_second_year, 
                $augr));
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
