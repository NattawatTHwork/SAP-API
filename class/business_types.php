<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class BusinessTypes
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

    public function getBusinessTypeAll()
    {
        $query = 'SELECT business_type_id, business_type_code, description 
                  FROM cm_sap.tb_business_types WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_business_types", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all business types.');
        }
        $result = pg_execute($this->connection, "get_all_business_types", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all business types.');
        }
        $businessTypes = pg_fetch_all($result);
        if ($businessTypes === false) {
            return [];
        }
        foreach ($businessTypes as &$type) {
            $type['business_type_id'] = $this->encryption->encrypt($type['business_type_id']);
        }
        return $businessTypes;
    }

    public function getBusinessType($encryptedBusinessTypeId)
    {
        $businessTypeId = $this->encryption->decrypt($encryptedBusinessTypeId);
        $query = 'SELECT business_type_id, business_type_code, description 
                  FROM cm_sap.tb_business_types WHERE business_type_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_business_type_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting business type by ID.');
        }
        $result = pg_execute($this->connection, "get_business_type_by_id", array($businessTypeId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting business type by ID.');
        }
        $type = pg_fetch_assoc($result);
        if ($type === false) {
            return null;
        }
        $type['business_type_id'] = $this->encryption->encrypt($type['business_type_id']);
        return $type;
    }

    public function createBusinessType($business_type_code, $description)
    {
        $query = 'INSERT INTO cm_sap.tb_business_types (business_type_code, description, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, NOW(), NOW(), false) RETURNING business_type_id';
        $result = pg_prepare($this->connection, "create_business_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating business type.');
        }
        $result = pg_execute($this->connection, "create_business_type", array($business_type_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating business type.');
        }
        $businessTypeId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($businessTypeId);
    }

    public function updateBusinessType($encryptedBusinessTypeId, $business_type_code, $description)
    {
        $businessTypeId = $this->encryption->decrypt($encryptedBusinessTypeId);
        $query = 'UPDATE cm_sap.tb_business_types 
                  SET business_type_code = $2, description = $3, updated_at = NOW() 
                  WHERE business_type_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_business_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating business type.');
        }
        $result = pg_execute($this->connection, "update_business_type", array($businessTypeId, $business_type_code, $description));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating business type.');
        }
        return pg_affected_rows($result);
    }

    public function deleteBusinessType($encryptedBusinessTypeId)
    {
        $businessTypeId = $this->encryption->decrypt($encryptedBusinessTypeId);
        $query = 'UPDATE cm_sap.tb_business_types SET is_deleted = true, updated_at = NOW() 
                  WHERE business_type_id = $1';
        $result = pg_prepare($this->connection, "delete_business_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting business type.');
        }
        $result = pg_execute($this->connection, "delete_business_type", array($businessTypeId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting business type.');
        }
        return pg_affected_rows($result);
    }
}
