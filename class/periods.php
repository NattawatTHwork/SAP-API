<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class Periods
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

    public function getPeriodAll($encrypteFiscalYearId)
    {
        $fiscalYearId = $this->encryption->decrypt($encrypteFiscalYearId);
        $query = 'SELECT period_id, period_code, fiscal_year_id, number_month, number_day, change_year, text_period_en, text_period_th FROM cm_sap.tb_periods WHERE is_deleted = false AND fiscal_year_id = $1 ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_periods", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all periods.');
        }
        $result = pg_execute($this->connection, "get_all_periods", [$fiscalYearId]);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all periods.');
        }
        $periods = pg_fetch_all($result);
        if ($periods === false) {
            return [];
        }
        foreach ($periods as &$period) {
            $period['period_id'] = $this->encryption->encrypt($period['period_id']);
        }
        return $periods;
    }

    public function getPeriod($encryptedPeriodId)
    {
        $periodId = $this->encryption->decrypt($encryptedPeriodId);
        $query = 'SELECT * FROM cm_sap.tb_periods WHERE period_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_period_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting period by ID.');
        }
        $result = pg_execute($this->connection, "get_period_by_id", array($periodId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting period by ID.');
        }
        $period = pg_fetch_assoc($result);
        if ($period === false) {
            return null;
        }
        $period['period_id'] = $this->encryption->encrypt($period['period_id']);
        return $period;
    }

    public function createPeriod($period_code, $fiscal_year_id, $number_month, $number_day, $change_year, $text_period_en, $text_period_th)
    {
        $fiscalYearId = $this->encryption->decrypt($fiscal_year_id);
        $query = 'INSERT INTO cm_sap.tb_periods (period_code, fiscal_year_id, number_month, number_day, change_year, text_period_en, text_period_th, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), NOW(), false) RETURNING period_id';
        $result = pg_prepare($this->connection, "create_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating period.');
        }
        $result = pg_execute($this->connection, "create_period", array($period_code, $fiscalYearId, $number_month, $number_day, $change_year, $text_period_en, $text_period_th));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating period.');
        }
        $periodId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($periodId);
    }

    public function updatePeriod($encryptedPeriodId, $period_code, $number_month, $number_day, $change_year, $text_period_en, $text_period_th)
    {
        $periodId = $this->encryption->decrypt($encryptedPeriodId);
        $query = 'UPDATE cm_sap.tb_periods 
                  SET period_code = $2, number_month = $3, number_day = $4, change_year = $5, text_period_en = $6, text_period_th = $7, updated_at = NOW() 
                  WHERE period_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating period.');
        }
        $result = pg_execute($this->connection, "update_period", array($periodId, $period_code, $number_month, $number_day, $change_year, $text_period_en, $text_period_th));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating period.');
        }
        return pg_affected_rows($result);
    }

    public function deletePeriod($encryptedPeriodId)
    {
        $periodId = $this->encryption->decrypt($encryptedPeriodId);
        $query = 'UPDATE cm_sap.tb_periods SET is_deleted = true, updated_at = NOW() 
                  WHERE period_id = $1';
        $result = pg_prepare($this->connection, "delete_period", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting period.');
        }
        $result = pg_execute($this->connection, "delete_period", array($periodId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting period.');
        }
        return pg_affected_rows($result);
    }
}
