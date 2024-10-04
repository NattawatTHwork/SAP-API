<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GeneralLedgers
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

    public function getAllGeneralLedgers()
    {
        $query = 'SELECT 
                      tb_general_ledgers.general_ledger_id, 
                      tb_general_ledgers.company_id, 
                      document_date, 
                      posting_date, 
                      reference, 
                      document_header_text, 
                      document_type, 
                      intercompany_number, 
                      branch_number, 
                      currency,
                      company_code,
                      name_th
                  FROM cm_sap.tb_general_ledgers 
                  INNER JOIN cm_sap.tb_companies ON tb_general_ledgers.company_id = tb_companies.company_id 
                  INNER JOIN cm_sap.tb_general_ledger_details ON tb_general_ledger_details.general_ledger_id = tb_general_ledgers.general_ledger_id 
                  WHERE tb_general_ledgers.is_deleted = false 
                  ORDER BY tb_general_ledgers.created_at ASC';
        $result = pg_prepare($this->connection, "get_all_general_ledgers", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all general ledgers.');
        }
        $result = pg_execute($this->connection, "get_all_general_ledgers", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all general ledgers.');
        }
        $generalLedgers = pg_fetch_all($result);
        if ($generalLedgers === false) {
            return [];
        }
        foreach ($generalLedgers as &$generalLedger) {
            $generalLedger['general_ledger_id'] = $this->encryption->encrypt($generalLedger['general_ledger_id']);
            $generalLedger['company_id'] = $this->encryption->encrypt($generalLedger['company_id']);
        }
        return $generalLedgers;
    }

    public function getGeneralLedger($encryptedGeneralLedgerId)
    {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
        $query = 'SELECT 
                      general_ledger_id, 
                      tb_general_ledgers.company_id, 
                      document_date, 
                      posting_date, 
                      reference, 
                      document_header_text, 
                      document_type, 
                      intercompany_number, 
                      branch_number, 
                      currency,
                      company_code,
                      name_th 
                  FROM cm_sap.tb_general_ledgers 
                  INNER JOIN cm_sap.tb_companies ON tb_general_ledgers.company_id = tb_companies.company_id
                  WHERE tb_general_ledgers.is_deleted = false AND general_ledger_id = $1 
                  ORDER BY tb_general_ledgers.created_at ASC';
        $result = pg_prepare($this->connection, "get_general_ledger_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting general ledger by ID.');
        }
        $result = pg_execute($this->connection, "get_general_ledger_by_id", array($generalLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting general ledger by ID.');
        }
        $generalLedger = pg_fetch_assoc($result);
        if ($generalLedger === false) {
            return null;
        }
        $generalLedger['general_ledger_id'] = $this->encryption->encrypt($generalLedger['general_ledger_id']);
        $generalLedger['company_id'] = $this->encryption->encrypt($generalLedger['company_id']);
        return $generalLedger;
    }

    public function createGeneralLedger(
        $company_id,
        $document_date,
        $posting_date,
        $reference,
        $document_header_text,
        $document_type,
        $intercompany_number,
        $branch_number,
        $currency
    ) {
        $companyId = $this->encryption->decrypt($company_id);
        $query = 'INSERT INTO cm_sap.tb_general_ledgers (
                    company_id, document_date, posting_date, reference, document_header_text, 
                    document_type, intercompany_number, branch_number, currency, created_at, updated_at, is_deleted
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW(), NOW(), false) 
                RETURNING general_ledger_id';
        $result = pg_prepare($this->connection, "create_general_ledger", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating general ledger.');
        }
        $result = pg_execute($this->connection, "create_general_ledger", array(
            $companyId, $document_date, $posting_date, $reference, $document_header_text, 
            $document_type, $intercompany_number, $branch_number, $currency
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating general ledger.');
        }
        $generalLedgerId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($generalLedgerId);
    }

    public function updateGeneralLedger(
        $encryptedGeneralLedgerId,
        $document_date,
        $posting_date,
        $reference,
        $document_header_text,
        $document_type,
        $intercompany_number,
        $branch_number,
        $currency
    ) {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
        $query = 'UPDATE cm_sap.tb_general_ledgers 
                  SET document_date = $2, posting_date = $3, reference = $4, 
                      document_header_text = $5, document_type = $6, intercompany_number = $7, 
                      branch_number = $8, currency = $9, updated_at = NOW() 
                  WHERE general_ledger_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_general_ledger", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating general ledger.');
        }
        $result = pg_execute($this->connection, "update_general_ledger", array(
            $generalLedgerId, $document_date, $posting_date, $reference, 
            $document_header_text, $document_type, $intercompany_number, $branch_number, $currency
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating general ledger.');
        }
        return pg_affected_rows($result);
    }

    public function deleteGeneralLedger($encryptedGeneralLedgerId)
    {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
        $query = 'UPDATE cm_sap.tb_general_ledgers 
                  SET is_deleted = true, updated_at = NOW() 
                  WHERE general_ledger_id = $1';
        $result = pg_prepare($this->connection, "delete_general_ledger", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting general ledger.');
        }
        $result = pg_execute($this->connection, "delete_general_ledger", array($generalLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting general ledger.');
        }
        return pg_affected_rows($result);
    }
}
?>
