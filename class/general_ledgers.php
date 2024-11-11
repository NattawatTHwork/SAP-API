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

    public function getAllGeneralLedgers($searchParams = [])
    {
        // Start building the main query
        $query = 'SELECT 
            tb_general_ledgers.general_ledger_id, 
            tb_general_ledgers.company_id, 
            document_date, 
            posting_date, 
            reference, 
            document_header_text, 
            tb_general_ledgers.document_type_id, 
            document_type_code,
            tb_general_ledgers.branch_number_id, 
            tb_branch_numbers.branch_number_code,
            tb_general_ledgers.currency_id, 
            tb_currencies.currency_code,
            company_code,
            name_th,
            exchange_rate, 
            translatn_date, 
            trading_part_ba, 
            calculate_tax,
            document_sequence,
            year
        FROM cm_sap.tb_general_ledgers 
        INNER JOIN cm_sap.tb_companies 
            ON tb_general_ledgers.company_id = tb_companies.company_id 
        INNER JOIN cm_sap.tb_document_types 
            ON tb_general_ledgers.document_type_id = tb_document_types.document_type_id
        LEFT JOIN cm_sap.tb_branch_numbers 
            ON tb_general_ledgers.branch_number_id = tb_branch_numbers.branch_number_id 
            AND tb_general_ledgers.branch_number_id != 0 
        LEFT JOIN cm_sap.tb_currencies 
            ON tb_general_ledgers.currency_id = tb_currencies.currency_id 
            AND tb_general_ledgers.currency_id != 0
        WHERE tb_general_ledgers.is_deleted = false';

        // Array to hold the parameters for the query
        $params = [];
        $paramIndex = 1;

        // Add conditions to the query only if parameters are not empty
        if (!empty($searchParams['company_id'])) {
            $query .= ' AND tb_general_ledgers.company_id = $' . $paramIndex;
            $params[] = $this->encryption->decrypt($searchParams['company_id']);
            $paramIndex++;
        }
        if (!empty($searchParams['document_date'])) {
            $query .= ' AND tb_general_ledgers.document_date = $' . $paramIndex;
            $params[] = $searchParams['document_date'];
            $paramIndex++;
        }
        if (!empty($searchParams['posting_date'])) {
            $query .= ' AND tb_general_ledgers.posting_date = $' . $paramIndex;
            $params[] = $searchParams['posting_date'];
            $paramIndex++;
        }
        if (!empty($searchParams['document_type_id'])) {
            $query .= ' AND tb_general_ledgers.document_type_id = $' . $paramIndex;
            $params[] = $this->encryption->decrypt($searchParams['document_type_id']);
            $paramIndex++;
        }
        if (!empty($searchParams['reference'])) {
            $query .= ' AND tb_general_ledgers.reference ILIKE $' . $paramIndex;
            $params[] = '%' . $searchParams['reference'] . '%';
            $paramIndex++;
        }
        if (!empty($searchParams['document_header_text'])) {
            $query .= ' AND tb_general_ledgers.document_header_text ILIKE $' . $paramIndex;
            $params[] = '%' . $searchParams['document_header_text'] . '%';
            $paramIndex++;
        }
        if (!empty($searchParams['currency_id'])) {
            $query .= ' AND tb_general_ledgers.currency_id = $' . $paramIndex;
            $params[] = $this->encryption->decrypt($searchParams['currency_id']);
            $paramIndex++;
        }
        if (!empty($searchParams['branch_number_id'])) {
            $query .= ' AND tb_general_ledgers.branch_number_id = $' . $paramIndex;
            $params[] = $this->encryption->decrypt($searchParams['branch_number_id']);
            $paramIndex++;
        }

        // Add the order by clause
        $query .= ' ORDER BY tb_general_ledgers.created_at ASC';

        // Prepare and execute the query
        $result = pg_prepare($this->connection, "get_all_general_ledgers", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all general ledgers.');
        }
        $result = pg_execute($this->connection, "get_all_general_ledgers", $params);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all general ledgers.');
        }

        // Fetch all results
        $generalLedgers = pg_fetch_all($result);
        if ($generalLedgers === false) {
            return [];
        }

        // Encrypt sensitive fields
        foreach ($generalLedgers as &$generalLedger) {
            $generalLedger['general_ledger_id'] = $this->encryption->encrypt($generalLedger['general_ledger_id']);
            $generalLedger['company_id'] = $this->encryption->encrypt($generalLedger['company_id']);
            $generalLedger['document_type_id'] = $this->encryption->encrypt($generalLedger['document_type_id']);
            $generalLedger['branch_number_id'] = ($this->encryption->encrypt($generalLedger['branch_number_id']) === 'Ny9TclFPb0NxNmtmOWlMUUlMS2c3dz09') ? '' : $this->encryption->encrypt($generalLedger['branch_number_id']);
            $generalLedger['currency_id'] = ($this->encryption->encrypt($generalLedger['currency_id']) === 'Ny9TclFPb0NxNmtmOWlMUUlMS2c3dz09') ? '' : $this->encryption->encrypt($generalLedger['currency_id']);
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
                      document_type_id, 
                      branch_number_id, 
                      currency_id,
                      company_code,
                      name_th, 
                      exchange_rate, 
                      translatn_date, 
                      trading_part_ba, 
                      calculate_tax,
                      document_sequence,
                      year
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
        $generalLedger['document_type_id'] = $this->encryption->encrypt($generalLedger['document_type_id']);
        $generalLedger['branch_number_id'] = ($this->encryption->encrypt($generalLedger['branch_number_id']) === 'Ny9TclFPb0NxNmtmOWlMUUlMS2c3dz09') ? '' : $this->encryption->encrypt($generalLedger['branch_number_id']);
        $generalLedger['currency_id'] = ($this->encryption->encrypt($generalLedger['currency_id']) === 'Ny9TclFPb0NxNmtmOWlMUUlMS2c3dz09') ? '' : $this->encryption->encrypt($generalLedger['currency_id']);
        return $generalLedger;
    }

    public function createGeneralLedger(
        $created_by,
        $company_id,
        $document_date,
        $posting_date,
        $reference,
        $document_header_text,
        $document_type_id,
        $branch_number_id,
        $currency_id,
        $exchange_rate,
        $translatn_date,
        $trading_part_ba,
        $calculate_tax,
        $document_sequence, // Add document_sequence as a parameter
        $year
    ) {
        $companyId = $this->encryption->decrypt($company_id);
        $DocumentTypeId = $this->encryption->decrypt($document_type_id);
        $BranchNumberId = empty($branch_number_id)
            ? 0
            : $this->encryption->decrypt($branch_number_id);
        $CurrencyId = empty($currency_id)
            ? 0
            : $this->encryption->decrypt($currency_id);

        // Update the query to include the document_sequence column
        $query = 'INSERT INTO cm_sap.tb_general_ledgers (
                    created_by, company_id, document_date, posting_date, reference, document_header_text, 
                    document_type_id, branch_number_id, currency_id, exchange_rate, translatn_date, 
                    trading_part_ba, calculate_tax, document_sequence, year, created_at, updated_at, is_deleted
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, NOW(), NOW(), false) 
                RETURNING general_ledger_id';

        // Prepare the query
        $result = pg_prepare($this->connection, "create_general_ledger", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating general ledger.');
        }

        // Execute the query and include document_sequence in the array of parameters
        $result = pg_execute($this->connection, "create_general_ledger", array(
            $created_by,
            $companyId,
            $document_date,
            $posting_date,
            $reference,
            $document_header_text,
            $DocumentTypeId,
            $BranchNumberId,
            $CurrencyId,
            $exchange_rate,
            $translatn_date,
            $trading_part_ba,
            $calculate_tax,
            $document_sequence, // Add document_sequence to the parameter array
            $year
        ));

        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating general ledger.');
        }

        // Fetch and return the general ledger ID
        $generalLedgerId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($generalLedgerId);
    }

    public function updateGeneralLedger(
        $encryptedGeneralLedgerId,
        $encryptedcompanyId,
        $document_date,
        $posting_date,
        $reference,
        $document_header_text,
        $document_type_id,
        $branch_number_id,
        $currency_id,
        $exchange_rate,
        $translatn_date,
        $trading_part_ba,
        $calculate_tax,
        $updated_by
    ) {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
        $companyId = $this->encryption->decrypt($encryptedcompanyId);
        $DocumentTypeId = $this->encryption->decrypt($document_type_id);
        $BranchNumberId = empty($branch_number_id)
            ? 0
            : $this->encryption->decrypt($branch_number_id);
        $CurrencyId = empty($currency_id)
            ? 0
            : $this->encryption->decrypt($currency_id);

        $query = 'UPDATE cm_sap.tb_general_ledgers 
                  SET company_id = $2, document_date = $3, posting_date = $4, reference = $5, 
                      document_header_text = $6, document_type_id = $7, 
                      branch_number_id = $8, currency_id = $9, exchange_rate = $10, 
                      translatn_date = $11, trading_part_ba = $12, calculate_tax = $13, updated_by = $14,
                      updated_at = NOW() 
                  WHERE general_ledger_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_general_ledger", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating general ledger.');
        }
        $result = pg_execute($this->connection, "update_general_ledger", array(
            $generalLedgerId,
            $companyId,
            $document_date,
            $posting_date,
            $reference,
            $document_header_text,
            $DocumentTypeId,
            $BranchNumberId,
            $CurrencyId,
            $exchange_rate,
            $translatn_date,
            $trading_part_ba,
            $calculate_tax,
            $updated_by
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating general ledger.');
        }
        return $encryptedGeneralLedgerId;
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
