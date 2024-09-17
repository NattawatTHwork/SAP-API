<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GLInterestBankCreations
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

    public function getGLInterestBankCreation($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'SELECT 
                      gl_interest_bank_creation_id,
                      central_general_ledger_id,
                      field_status_group,
                      automatic_posting_only,
                      automatic_incremental_posting,
                      reconciliation_account_input,
                      planning_level,
                      cash_flow_related,
                      commitment_item,
                      correspondent_bank,
                      account_number,
                      interest_indicator,
                      interest_calculation_frequency,
                      last_interest_calculation_date_key,
                      last_interest_calculation_date
                  FROM cm_sap.tb_gl_interest_bank_creations
                  WHERE is_deleted = false AND central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "get_gl_interest_bank_creation_by_central_ledger_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting GL Interest Bank Creation by Central Ledger ID.');
        }
        $result = pg_execute($this->connection, "get_gl_interest_bank_creation_by_central_ledger_id", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting GL Interest Bank Creation by Central Ledger ID.');
        }
        $glInterestBankCreation = pg_fetch_assoc($result);
        if ($glInterestBankCreation === false) {
            return null;
        }
        // Encrypt IDs before returning
        $glInterestBankCreation['gl_interest_bank_creation_id'] = $this->encryption->encrypt($glInterestBankCreation['gl_interest_bank_creation_id']);
        $glInterestBankCreation['central_general_ledger_id'] = $this->encryption->encrypt($glInterestBankCreation['central_general_ledger_id']);
        return $glInterestBankCreation;
    }

    public function createGLInterestBankCreation($central_general_ledger_id) {
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $query = 'INSERT INTO cm_sap.tb_gl_interest_bank_creations (
                central_general_ledger_id, field_status_group, automatic_posting_only, 
                automatic_incremental_posting, reconciliation_account_input, planning_level,
                cash_flow_related, commitment_item, correspondent_bank, account_number,
                interest_indicator, interest_calculation_frequency, last_interest_calculation_date_key, last_interest_calculation_date,
                created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, NOW(), NOW(), false) RETURNING gl_interest_bank_creation_id';
        $result = pg_prepare($this->connection, "create_gl_interest_bank_creation", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating GL Interest Bank Creation.');
        }
        $result = pg_execute(
            $this->connection,
            "create_gl_interest_bank_creation",
            array($centralGeneralLedgerId, '', 'false', 'false', 'false', '', 'false', '', '', '', '', '', '', '')
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating GL Interest Bank Creation.');
        }
        $glInterestBankCreationId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($glInterestBankCreationId);
    }

    public function updateGLInterestBankCreation(
        $encryptedGLInterestBankCreationId,
        $field_status_group,
        $automatic_posting_only,
        $automatic_incremental_posting,
        $reconciliation_account_input,
        $planning_level,
        $cash_flow_related,
        $commitment_item,
        $correspondent_bank,
        $account_number,
        $interest_indicator,
        $interest_calculation_frequency,
        $last_interest_calculation_date_key,
        $last_interest_calculation_date
    ) {
        $glInterestBankCreationId = $this->encryption->decrypt($encryptedGLInterestBankCreationId);

        $query = 'UPDATE cm_sap.tb_gl_interest_bank_creations 
                  SET field_status_group = $2, automatic_posting_only = $3, automatic_incremental_posting = $4, 
                      reconciliation_account_input = $5, planning_level = $6, cash_flow_related = $7, 
                      commitment_item = $8, correspondent_bank = $9, account_number = $10, 
                      interest_indicator = $11, interest_calculation_frequency = $12, last_interest_calculation_date_key = $13, last_interest_calculation_date = $14, updated_at = NOW()
                  WHERE gl_interest_bank_creation_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_gl_interest_bank_creation", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating GL Interest Bank Creation.');
        }
        $result = pg_execute(
            $this->connection,
            "update_gl_interest_bank_creation",
            array($glInterestBankCreationId, $field_status_group, $automatic_posting_only, 
                  $automatic_incremental_posting, $reconciliation_account_input, $planning_level, $cash_flow_related,
                  $commitment_item, $correspondent_bank, $account_number, $interest_indicator, 
                  $interest_calculation_frequency, $last_interest_calculation_date_key, $last_interest_calculation_date)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating GL Interest Bank Creation.');
        }
        return pg_affected_rows($result);
    }    

    public function deleteGLInterestBankCreation($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'UPDATE cm_sap.tb_gl_interest_bank_creations SET is_deleted = true, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "delete_gl_interest_bank_creation", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting GL Interest Bank Creation.');
        }
        $result = pg_execute($this->connection, "delete_gl_interest_bank_creation", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting GL Interest Bank Creation.');
        }
        return pg_affected_rows($result);
    }
}
?>
