<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class FiscalYears
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

    public function getAllFiscalYears()
    {
        $query = 'SELECT fiscal_year_id, fiscal_year_code, description, fiscal_year_check, calendar_year_check FROM cm_sap.tb_fiscal_years WHERE is_deleted = false ORDER BY fiscal_year_id ASC';
        $result = pg_prepare($this->connection, "get_all_fiscal_years", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all fiscal years.');
        }
        $result = pg_execute($this->connection, "get_all_fiscal_years", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all fiscal years.');
        }
        $fiscalYears = pg_fetch_all($result);
        if ($fiscalYears === false) {
            return [];
        }
        foreach ($fiscalYears as &$fiscalYear) {
            $fiscalYear['fiscal_year_id'] = $this->encryption->encrypt($fiscalYear['fiscal_year_id']);
        }
        return $fiscalYears;
    }

    public function getFiscalYear($encryptedFiscalYearId)
    {
        $fiscalYearId = $this->encryption->decrypt($encryptedFiscalYearId);
        $query = 'SELECT fiscal_year_id, fiscal_year_code, description, fiscal_year_check, calendar_year_check FROM cm_sap.tb_fiscal_years WHERE fiscal_year_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_fiscal_year_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting fiscal year by ID.');
        }
        $result = pg_execute($this->connection, "get_fiscal_year_by_id", array($fiscalYearId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting fiscal year by ID.');
        }
        $fiscalYear = pg_fetch_assoc($result);
        if ($fiscalYear === false) {
            return null;
        }
        $fiscalYear['fiscal_year_id'] = $this->encryption->encrypt($fiscalYear['fiscal_year_id']);

        return $fiscalYear;
    }

    public function createFiscalYear($fiscal_year_code, $description, $fiscal_year_check, $calendar_year_check)
    {
        $query = 'INSERT INTO cm_sap.tb_fiscal_years (fiscal_year_code, description, fiscal_year_check, calendar_year_check, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, NOW(), NOW(), false) RETURNING fiscal_year_id';
        $result = pg_prepare($this->connection, "create_fiscal_year", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating fiscal year.');
        }
        $result = pg_execute($this->connection, "create_fiscal_year", array($fiscal_year_code, $description, $fiscal_year_check, $calendar_year_check));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating fiscal year.');
        }
        $fiscalYearId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($fiscalYearId);
    }

    public function updateFiscalYear($encryptedFiscalYearId, $fiscal_year_code, $description, $fiscal_year_check, $calendar_year_check)
    {
        $fiscalYearId = $this->encryption->decrypt($encryptedFiscalYearId);
        $query = 'UPDATE cm_sap.tb_fiscal_years 
                  SET fiscal_year_code = $2, description = $3, fiscal_year_check = $4, calendar_year_check = $5, updated_at = NOW() 
                  WHERE fiscal_year_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_fiscal_year", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating fiscal year.');
        }
        $result = pg_execute($this->connection, "update_fiscal_year", array($fiscalYearId, $fiscal_year_code, $description, $fiscal_year_check, $calendar_year_check));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating fiscal year.');
        }
        return pg_affected_rows($result);
    }

    public function deleteFiscalYear($encryptedFiscalYearId)
    {
        $fiscalYearId = $this->encryption->decrypt($encryptedFiscalYearId);
        $query = 'UPDATE cm_sap.tb_fiscal_years SET is_deleted = true, updated_at = NOW() 
                  WHERE fiscal_year_id = $1';
        $result = pg_prepare($this->connection, "delete_fiscal_year", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting fiscal year.');
        }
        $result = pg_execute($this->connection, "delete_fiscal_year", array($fiscalYearId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting fiscal year.');
        }
        return pg_affected_rows($result);
    }
}
