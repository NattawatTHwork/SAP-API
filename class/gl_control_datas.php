<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class ControlData
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

    // ฟังก์ชันสำหรับดึงข้อมูล ControlData ด้วย ID
    public function getControlData($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'SELECT 
                      gl_control_data_id, 
                      central_general_ledger_id, 
                      account_currency, 
                      domestic_currency_balance, 
                      exchange_rate_difference_key, 
                      valuation_group, 
                      tax_category, 
                      post_without_tax, 
                      reconciliation_account_type, 
                      alternate_account_number, 
                      externally_managed_account, 
                      inflation_key, 
                      acceptance_range_group, 
                      open_item_management, 
                      display_line_items, 
                      sorting_key, 
                      authorization_group
                  FROM cm_sap.tb_gl_control_datas 
                  WHERE is_deleted = false AND central_general_ledger_id = $1';

        $result = pg_prepare($this->connection, "get_control_data_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting Control Data by ID.');
        }
        $result = pg_execute($this->connection, "get_control_data_by_id", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting Control Data by ID.');
        }
        $controlData = pg_fetch_assoc($result);
        if ($controlData === false) {
            return null;
        }
        $controlData['gl_control_data_id'] = $this->encryption->encrypt($controlData['gl_control_data_id']);
        $controlData['central_general_ledger_id'] = $this->encryption->encrypt($controlData['central_general_ledger_id']);
        return $controlData;
    }

    // ฟังก์ชันสำหรับสร้าง ControlData ใหม่
    public function createControlData($central_general_ledger_id) {
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $query = 'INSERT INTO cm_sap.tb_gl_control_datas (
                    central_general_ledger_id, 
                    account_currency, 
                    domestic_currency_balance, 
                    exchange_rate_difference_key, 
                    valuation_group, 
                    tax_category, 
                    post_without_tax, 
                    reconciliation_account_type, 
                    alternate_account_number, 
                    externally_managed_account, 
                    inflation_key, 
                    acceptance_range_group, 
                    open_item_management, 
                    display_line_items, 
                    sorting_key, 
                    authorization_group, 
                    created_at, 
                    updated_at, 
                    is_deleted
                ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, NOW(), NOW(), false
                ) RETURNING gl_control_data_id';

        $result = pg_prepare($this->connection, "create_control_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating Control Data.');
        }
        $result = pg_execute(
            $this->connection,
            "create_control_data",
            array(
                $centralGeneralLedgerId,
                '',
                'false',
                '',
                '',
                '',
                'false',
                '',
                '',
                'false',
                '',
                '',
                'false',
                'false',
                '',
                ''
            )
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating Control Data.');
        }
        $controlDataId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($controlDataId);
    }

    // ฟังก์ชันสำหรับอัปเดต ControlData
    public function updateControlData(
        $encryptedControlDataId,
        $account_currency,
        $domestic_currency_balance,
        $exchange_rate_difference_key,
        $valuation_group,
        $tax_category,
        $post_without_tax,
        $reconciliation_account_type,
        $alternate_account_number,
        $externally_managed_account,
        $inflation_key,
        $acceptance_range_group,
        $open_item_management,
        $display_line_items,
        $sorting_key,
        $authorization_group
    ) {
        $controlDataId = $this->encryption->decrypt($encryptedControlDataId);

        $query = 'UPDATE cm_sap.tb_gl_control_datas 
                  SET account_currency = $2, 
                      domestic_currency_balance = $3, 
                      exchange_rate_difference_key = $4, 
                      valuation_group = $5, 
                      tax_category = $6, 
                      post_without_tax = $7, 
                      reconciliation_account_type = $8, 
                      alternate_account_number = $9, 
                      externally_managed_account = $10, 
                      inflation_key = $11, 
                      acceptance_range_group = $12, 
                      open_item_management = $13, 
                      display_line_items = $14, 
                      sorting_key = $15, 
                      authorization_group = $16, 
                      updated_at = NOW() 
                  WHERE gl_control_data_id = $1 AND is_deleted = false';

        $result = pg_prepare($this->connection, "update_control_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating Control Data.');
        }
        $result = pg_execute(
            $this->connection,
            "update_control_data",
            array(
                $controlDataId,
                $account_currency,
                $domestic_currency_balance,
                $exchange_rate_difference_key,
                $valuation_group,
                $tax_category,
                $post_without_tax,
                $reconciliation_account_type,
                $alternate_account_number,
                $externally_managed_account,
                $inflation_key,
                $acceptance_range_group,
                $open_item_management,
                $display_line_items,
                $sorting_key,
                $authorization_group
            )
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating Control Data.');
        }
        return pg_affected_rows($result);
    }

    // ฟังก์ชันสำหรับลบ (Soft Delete) ControlData
    public function deleteControlData($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);

        $query = 'UPDATE cm_sap.tb_gl_control_datas SET is_deleted = true, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1';

        $result = pg_prepare($this->connection, "delete_control_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting Control Data.');
        }
        $result = pg_execute($this->connection, "delete_control_data", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting Control Data.');
        }
        return pg_affected_rows($result);
    }
}
?>
