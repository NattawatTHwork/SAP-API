<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class GLTypes
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

    public function getGLType($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'SELECT 
                      gl_type_id, 
                      central_general_ledger_id,
                      group_account_id, 
                      type_account, 
                      type_account_description, 
                      short_text, 
                      long_text, 
                      tradg_part
                  FROM cm_sap.tb_gl_types 
                  WHERE is_deleted = false AND central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "get_gl_type_by_central_ledger_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting GL type by Central Ledger ID.');
        }
        $result = pg_execute($this->connection, "get_gl_type_by_central_ledger_id", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting GL type by Central Ledger ID.');
        }
        $glType = pg_fetch_assoc($result);
        if ($glType === false) {
            return null;
        }
        $glType['gl_type_id'] = $this->encryption->encrypt($glType['gl_type_id']);
        $glType['central_general_ledger_id'] = $this->encryption->encrypt($glType['central_general_ledger_id']);
        $glType['group_account_id'] = $this->encryption->encrypt($glType['group_account_id']);
        return $glType;
    }

    public function createGLType($central_general_ledger_id) {
        $centralGeneralLedgerId = $this->encryption->decrypt($central_general_ledger_id);
        $query = 'INSERT INTO cm_sap.tb_gl_types (
                group_account_id, central_general_ledger_id, type_account, type_account_description, short_text, long_text, tradg_part, created_at, updated_at, is_deleted
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), NOW(), false) RETURNING gl_type_id';
        $result = pg_prepare($this->connection, "create_gl_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating GL type.');
        }
        $result = pg_execute(
            $this->connection,
            "create_gl_type",
            array(0, $centralGeneralLedgerId, '', '', '', '', '')
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating GL type.');
        }
        $glTypeId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($glTypeId);
    }

    public function updateGLType(
        $encryptedGLTypeId,
        $group_account_id,
        $type_account,
        $type_account_description,
        $short_text,
        $long_text,
        $tradg_part
    ) {
        $glTypeId = $this->encryption->decrypt($encryptedGLTypeId);
        $groupAccountId = $this->encryption->decrypt($group_account_id);
        
        $query = 'UPDATE cm_sap.tb_gl_types 
                  SET group_account_id = $2, type_account = $3, type_account_description = $4, short_text = $5, long_text = $6, tradg_part = $7, updated_at = NOW() 
                  WHERE gl_type_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_gl_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating GL type.');
        }
        $result = pg_execute(
            $this->connection,
            "update_gl_type",
            array($glTypeId, $groupAccountId, $type_account, $type_account_description, $short_text, $long_text, $tradg_part)
        );
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating GL type.');
        }
        return pg_affected_rows($result);
    }    

    public function deleteGLType($encryptedCentralLedgerId)
    {
        $centralGeneralLedgerId = $this->encryption->decrypt($encryptedCentralLedgerId);
        $query = 'UPDATE cm_sap.tb_gl_types SET is_deleted = true, updated_at = NOW() 
                  WHERE central_general_ledger_id = $1';
        $result = pg_prepare($this->connection, "delete_gl_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting GL type.');
        }
        $result = pg_execute($this->connection, "delete_gl_type", array($centralGeneralLedgerId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting GL type.');
        }
        return pg_affected_rows($result);
    }
}
?>
