<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class TransactionPeriodTypes
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

    public function getTransactionPeriodTypeAll()
    {
        $query = 'SELECT transaction_period_type_id, transaction_period_type_code
                  FROM cm_sap.tb_transaction_period_types 
                  WHERE is_deleted = false ORDER BY transaction_period_type_id ASC';
        $result = pg_prepare($this->connection, "get_all_transaction_period_types", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all transaction period types.');
        }
        $result = pg_execute($this->connection, "get_all_transaction_period_types", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all transaction period types.');
        }
        $transactionPeriodTypes = pg_fetch_all($result);
        if ($transactionPeriodTypes === false) {
            return [];
        }
        foreach ($transactionPeriodTypes as &$transactionPeriodType) {
            $transactionPeriodType['transaction_period_type_id'] = $this->encryption->encrypt($transactionPeriodType['transaction_period_type_id']);
        }
        return $transactionPeriodTypes;
    }
}
