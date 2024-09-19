<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GLTransactions
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

    public function getGLTransactionAll()
    {
        $query = 'SELECT 
                      gl_transaction_id, 
                      tb_gl_transactions.central_general_ledger_id, 
                      general_ledger_id, 
                      calculate_tax, 
                      dc_type, 
                      amount, 
                      business_stablishment, 
                      tb_gl_transactions.business_type_id, 
                      determination, 
                      description,
                      gl_account
                  FROM cm_sap.tb_gl_transactions 
                  INNER JOIN cm_sap.tb_central_general_ledgers ON tb_central_general_ledgers.central_general_ledger_id = tb_gl_transactions.central_general_ledger_id
                  WHERE tb_gl_transactions.is_deleted = false
                  ORDER BY tb_gl_transactions.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_gl_transactions", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all GL transactions.');
        }
        $result = pg_execute($this->connection, "get_all_gl_transactions", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all GL transactions.');
        }
        $glTransactions = pg_fetch_all($result);
        if ($glTransactions === false) {
            return [];
        }
        foreach ($glTransactions as &$transaction) {
            $transaction['gl_transaction_id'] = $this->encryption->encrypt($transaction['gl_transaction_id']);
            $transaction['central_general_ledger_id'] = $this->encryption->encrypt($transaction['central_general_ledger_id']);
            $transaction['general_ledger_id'] = $this->encryption->encrypt($transaction['general_ledger_id']);
            $transaction['business_type_id'] = $this->encryption->encrypt($transaction['business_type_id']);
        }
        return $glTransactions;
    }

    public function getGLTransaction($encryptedGLTransactionId)
    {
        $glTransactionId = $this->encryption->decrypt($encryptedGLTransactionId);
        $query = 'SELECT 
                      gl_transaction_id, 
                      tb_gl_transactions.central_general_ledger_id, 
                      general_ledger_id, 
                      calculate_tax, 
                      dc_type, 
                      amount, 
                      business_stablishment, 
                      tb_gl_transactions.business_type_id, 
                      determination, 
                      description,
                      gl_account,
                      company_code, 
                      name_th
                  FROM cm_sap.tb_gl_transactions 
                  INNER JOIN cm_sap.tb_central_general_ledgers ON tb_central_general_ledgers.central_general_ledger_id = tb_gl_transactions.central_general_ledger_id
                  INNER JOIN cm_sap.tb_companies ON tb_central_general_ledgers.company_id = tb_companies.company_id
                  WHERE tb_gl_transactions.is_deleted = false AND tb_gl_transactions.gl_transaction_id = $1
                  ORDER BY tb_gl_transactions.created_at ASC';
        $result = pg_prepare($this->connection, "get_gl_transaction_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting GL transaction by ID.');
        }
        $result = pg_execute($this->connection, "get_gl_transaction_by_id", array($glTransactionId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting GL transaction by ID.');
        }
        $transaction = pg_fetch_assoc($result);
        if ($transaction === false) {
            return null;
        }
        $transaction['gl_transaction_id'] = $this->encryption->encrypt($transaction['gl_transaction_id']);
        $transaction['central_general_ledger_id'] = $this->encryption->encrypt($transaction['central_general_ledger_id']);
        $transaction['general_ledger_id'] = $this->encryption->encrypt($transaction['general_ledger_id']);
        $transaction['business_type_id'] = $this->encryption->encrypt($transaction['business_type_id']);
        return $transaction;
    }

    public function createGLTransaction(
        $central_general_ledger_id,
        $general_ledger_id,
        $calculate_tax,
        $dc_type,
        $amount,
        $business_stablishment,
        $business_type_id,
        $determination,
        $description
    ) {
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $generalLedgerId = $this->encryption->decrypt($general_ledger_id);
        $businessTypeId = $this->encryption->decrypt($business_type_id);

        $query = 'INSERT INTO cm_sap.tb_gl_transactions (
                central_general_ledger_id, general_ledger_id, calculate_tax, dc_type, amount, business_stablishment, business_type_id, determination, description, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW(), NOW(), false) RETURNING gl_transaction_id';
        $result = pg_prepare($this->connection, "create_gl_transaction", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating GL transaction.');
        }
        $result = pg_execute(
            $this->connection,
            "create_gl_transaction",
            array($centralGeneralLedgerId, $generalLedgerId, $calculate_tax, $dc_type, $amount, $business_stablishment, $businessTypeId, $determination, $description)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating GL transaction.');
        }
        $glTransactionId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($glTransactionId);
    }

    public function updateGLTransaction(
        $encryptedGLTransactionId,
        $central_general_ledger_id,
        $calculate_tax,
        $dc_type,
        $amount,
        $business_stablishment,
        $business_type_id,
        $determination,
        $description
    ) {
        $glTransactionId = $this->encryption->decrypt($encryptedGLTransactionId);
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $businessTypeId = $this->encryption->decrypt($business_type_id);

        $query = 'UPDATE cm_sap.tb_gl_transactions 
                  SET central_general_ledger_id = $2, calculate_tax = $3, dc_type = $4, amount = $5, business_stablishment = $6, business_type_id = $7, determination = $8, description = $9, updated_at = NOW() 
                  WHERE gl_transaction_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_gl_transaction", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating GL transaction.');
        }
        $result = pg_execute(
            $this->connection,
            "update_gl_transaction",
            array($glTransactionId, $centralGeneralLedgerId, $calculate_tax, $dc_type, $amount, $business_stablishment, $businessTypeId, $determination, $description)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating GL transaction.');
        }
        return pg_affected_rows($result);
    }

    public function deleteGLTransaction($encryptedGLTransactionId)
    {
        $glTransactionId = $this->encryption->decrypt($encryptedGLTransactionId);
        $query = 'UPDATE cm_sap.tb_gl_transactions SET is_deleted = true, updated_at = NOW() 
                  WHERE gl_transaction_id = $1';
        $result = pg_prepare($this->connection, "delete_gl_transaction", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting GL transaction.');
        }
        $result = pg_execute($this->connection, "delete_gl_transaction", array($glTransactionId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting GL transaction.');
        }
        return pg_affected_rows($result);
    }
}
