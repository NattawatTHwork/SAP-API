<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GLCAData
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

    public function getGLCAData($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'SELECT 
                      gl_ca_data_id,
                      central_general_ledger_id,
                      account_assignment_info,
                      accounting_note,
                      account_assignment_info_9
                  FROM cm_sap.tb_gl_ca_datas
                  WHERE is_deleted = false AND central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "get_gl_ca_data_by_central_ledger_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting GL CA Data by Central Ledger ID.');
        }
        $result = pg_execute($this->connection, "get_gl_ca_data_by_central_ledger_id", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting GL CA Data by Central Ledger ID.');
        }
        $glCAData = pg_fetch_assoc($result);
        if ($glCAData === false) {
            return null;
        }
        // Encrypt IDs before returning
        $glCAData['gl_ca_data_id'] = $this->encryption->encrypt($glCAData['gl_ca_data_id']);
        $glCAData['central_general_ledger_id'] = $this->encryption->encrypt($glCAData['central_general_ledger_id']);
        return $glCAData;
    }

    // ฟังก์ชันนี้รับแค่ central_general_ledger_id
    public function createGLCAData($central_general_ledger_id)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $query = 'INSERT INTO cm_sap.tb_gl_ca_datas (
                central_general_ledger_id, account_assignment_info, accounting_note, 
                account_assignment_info_9, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, NOW(), NOW(), false) RETURNING gl_ca_data_id';
        $result = pg_prepare($this->connection, "create_gl_ca_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating GL CA Data.');
        }
        // ใส่ค่าเริ่มต้นให้กับฟิลด์ account_assignment_info, accounting_note, และ account_assignment_info_9
        $result = pg_execute(
            $this->connection,
            "create_gl_ca_data",
            array($centralGeneralLedgerId, '', '', '')
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating GL CA Data.');
        }
        $glCADataId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($glCADataId);
    }

    public function updateGLCAData(
        $encryptedGLCADataId,
        $account_assignment_info,
        $accounting_note,
        $account_assignment_info_9
    )
    {
        $glCADataId = $this->encryption->decrypt($encryptedGLCADataId);
        $query = 'UPDATE cm_sap.tb_gl_ca_datas 
                  SET account_assignment_info = $2, accounting_note = $3, 
                      account_assignment_info_9 = $4, updated_at = NOW()
                  WHERE gl_ca_data_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_gl_ca_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating GL CA Data.');
        }
        $result = pg_execute(
            $this->connection,
            "update_gl_ca_data",
            array($glCADataId, $account_assignment_info, $accounting_note, $account_assignment_info_9)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating GL CA Data.');
        }
        return pg_affected_rows($result);
    }

    public function deleteGLCAData($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'UPDATE cm_sap.tb_gl_ca_datas SET is_deleted = true, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "delete_gl_ca_data", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting GL CA Data.');
        }
        $result = pg_execute($this->connection, "delete_gl_ca_data", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting GL CA Data.');
        }
        return pg_affected_rows($result);
    }
}
?>
