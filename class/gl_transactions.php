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

    public function getGLTransactionAll($encryptedGeneralLedgerId)
    {
        $GeneralLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
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
                      tb_gl_transactions.description AS gl_description,
                      gl_account,
                      business_type_code, 
                      tb_business_types.description AS bus_description
                  FROM cm_sap.tb_gl_transactions 
                  INNER JOIN cm_sap.tb_central_general_ledgers ON tb_central_general_ledgers.central_general_ledger_id = tb_gl_transactions.central_general_ledger_id
                  INNER JOIN cm_sap.tb_business_types ON tb_gl_transactions.business_type_id = tb_business_types.business_type_id
                  WHERE tb_gl_transactions.is_deleted = false AND general_ledger_id = $1
                  ORDER BY tb_gl_transactions.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_gl_transactions", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all GL transactions.');
        }
        $result = pg_execute($this->connection, "get_all_gl_transactions", [$GeneralLedgerId]);
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
            $transaction['business_type_id'] = ($this->encryption->encrypt($transaction['business_type_id']) === 'Ny9TclFPb0NxNmtmOWlMUUlMS2c3dz09') ? '' : $this->encryption->encrypt($transaction['business_type_id']);
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
                      tb_gl_transactions.description AS gl_description,
                      gl_account,
                      company_code, 
                      name_th,
                      business_type_code, 
                      tb_business_types.description AS bus_description
                  FROM cm_sap.tb_gl_transactions 
                  INNER JOIN cm_sap.tb_central_general_ledgers ON tb_central_general_ledgers.central_general_ledger_id = tb_gl_transactions.central_general_ledger_id
                  INNER JOIN cm_sap.tb_companies ON tb_central_general_ledgers.company_id = tb_companies.company_id
                  INNER JOIN cm_sap.tb_business_types ON tb_gl_transactions.business_type_id = tb_business_types.business_type_id
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

    public function createGLTransactions(
        $general_ledger_id,
        $transactions
    ) {
        $generalLedgerId = $this->encryption->decrypt($general_ledger_id);

        $placeholders = [];
        $values = [];

        foreach ($transactions as $index => $transaction) {
            $centralGeneralLedgerId = $this->encryption->decrypt($transaction['central_general_ledger_id']);
            $calculate_tax = $transaction['calculate_tax'];
            $dc_type = $transaction['dc_type'];
            $amount = $transaction['amount'];
            $business_stablishment = $transaction['business_stablishment'];
            $businessTypeId = empty($transaction['business_type_id'])
                ? 0
                : $this->encryption->decrypt($transaction['business_type_id']);
            $determination = $transaction['determination'];
            $description = $transaction['description'];
            $baseIndex = $index * 9 + 1;
            $placeholders[] = "(\$" . $baseIndex . ", \$" . ($baseIndex + 1) . ", \$" . ($baseIndex + 2) . ", \$" . ($baseIndex + 3) . ", \$" . ($baseIndex + 4) . ", \$" . ($baseIndex + 5) . ", \$" . ($baseIndex + 6) . ", \$" . ($baseIndex + 7) . ", \$" . ($baseIndex + 8) . ", NOW(), NOW(), false)";
            $values = array_merge($values, [
                $centralGeneralLedgerId,
                $generalLedgerId,
                $calculate_tax,
                $dc_type,
                $amount,
                $business_stablishment,
                $businessTypeId,
                $determination,
                $description
            ]);
        }
        $placeholderString = implode(', ', $placeholders);
        $query = "INSERT INTO cm_sap.tb_gl_transactions (
                central_general_ledger_id, general_ledger_id, calculate_tax, dc_type, amount, business_stablishment, business_type_id, determination, description, created_at, updated_at, is_deleted
            ) VALUES $placeholderString RETURNING gl_transaction_id";
        $result = pg_prepare($this->connection, 'gl_transactions', $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating GL transactions.');
        }
        $result = pg_execute($this->connection, 'gl_transactions', $values);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating GL transactions.');
        }
        $glTransactionIds = [];
        while ($row = pg_fetch_assoc($result)) {
            $glTransactionIds[] = $this->encryption->encrypt($row['gl_transaction_id']);
        }
        return $glTransactionIds;
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
