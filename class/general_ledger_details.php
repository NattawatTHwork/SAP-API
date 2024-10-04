<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GeneralLedgerDetails
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

    public function getGeneralLedgerDetail($encryptedGeneralLedgerId)
    {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);
        $query = 'SELECT 
                      general_ledger_detail_id, 
                      general_ledger_id, 
                      tb_general_ledger_details.company_id, 
                      exchange_rate, 
                      translatn_date, 
                      trading_part_ba, 
                      calculate_tax,
                      company_code  
                  FROM cm_sap.tb_general_ledger_details 
                  INNER JOIN cm_sap.tb_companies ON tb_general_ledger_details.company_id = tb_companies.company_id 
                  WHERE tb_general_ledger_details.is_deleted = false AND general_ledger_id = $1';

        $result = pg_prepare($this->connection, "get_general_ledger_detail_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting General Ledger Detail by ID.');
        }
        $result = pg_execute($this->connection, "get_general_ledger_detail_by_id", array($generalLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting General Ledger Detail by ID.');
        }
        $detailData = pg_fetch_assoc($result);
        if ($detailData === false) {
            return null;
        }
        $detailData['general_ledger_detail_id'] = $this->encryption->encrypt($detailData['general_ledger_detail_id']);
        $detailData['general_ledger_id'] = $this->encryption->encrypt($detailData['general_ledger_id']);
        $detailData['company_id'] = $this->encryption->encrypt($detailData['company_id']);
        return $detailData;
    }

    // Create new GeneralLedgerDetail
    public function createGeneralLedgerDetail($general_ledger_id, $company_id)
    {
        $generalLedgerId = $this->encryption->decrypt($general_ledger_id);
        $CompanyId = $this->encryption->decrypt($company_id);
        $query = 'INSERT INTO cm_sap.tb_general_ledger_details (
                    general_ledger_id, 
                    company_id, 
                    exchange_rate, 
                    translatn_date, 
                    trading_part_ba, 
                    calculate_tax, 
                    created_at, 
                    updated_at, 
                    is_deleted
                ) VALUES (
                    $1, $2, $3, $4, $5, $6, NOW(), NOW(), false
                ) RETURNING general_ledger_detail_id';

        $result = pg_prepare($this->connection, "create_general_ledger_detail", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating General Ledger Detail.');
        }
        $result = pg_execute($this->connection, "create_general_ledger_detail", array(
            $generalLedgerId,
            $CompanyId,
            '',
            null,
            '',
            'false'
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating General Ledger Detail.');
        }
        $detailId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($detailId);
    }

    // Update GeneralLedgerDetail
    public function updateGeneralLedgerDetail(
        $encryptedGeneralLedgerDetailId,
        $exchange_rate,
        $translatn_date,
        $trading_part_ba,
        $calculate_tax
    )
    {
        $generalLedgerDetailId = $this->encryption->decrypt($encryptedGeneralLedgerDetailId);

        $query = 'UPDATE cm_sap.tb_general_ledger_details 
                  SET exchange_rate = $2, 
                      translatn_date = $3, 
                      trading_part_ba = $4, 
                      calculate_tax = $5, 
                      updated_at = NOW() 
                  WHERE general_ledger_detail_id = $1 AND is_deleted = false';

        $result = pg_prepare($this->connection, "update_general_ledger_detail", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating General Ledger Detail.');
        }
        $result = pg_execute($this->connection, "update_general_ledger_detail", array(
            $generalLedgerDetailId,
            $exchange_rate,
            $translatn_date,
            $trading_part_ba,
            $calculate_tax
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating General Ledger Detail.');
        }
        return pg_affected_rows($result);
    }

    // Soft delete GeneralLedgerDetail
    public function deleteGeneralLedgerDetail($encryptedGeneralLedgerId)
    {
        $generalLedgerId = $this->encryption->decrypt($encryptedGeneralLedgerId);

        $query = 'UPDATE cm_sap.tb_general_ledger_details 
                  SET is_deleted = true, updated_at = NOW() 
                  WHERE general_ledger_id = $1';

        $result = pg_prepare($this->connection, "delete_general_ledger_detail", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting General Ledger Detail.');
        }
        $result = pg_execute($this->connection, "delete_general_ledger_detail", array($generalLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting General Ledger Detail.');
        }
        return pg_affected_rows($result);
    }
}
?>
