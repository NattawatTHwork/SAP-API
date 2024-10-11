<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class Currencies
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

    public function getCurrencyAll()
    {
        $query = 'SELECT currency_id, currency_code 
                  FROM cm_sap.tb_currencies WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_currencies", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all currencies.');
        }
        $result = pg_execute($this->connection, "get_all_currencies", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all currencies.');
        }
        $currencies = pg_fetch_all($result);
        if ($currencies === false) {
            return [];
        }
        foreach ($currencies as &$currency) {
            $currency['currency_id'] = $this->encryption->encrypt($currency['currency_id']);
        }
        return $currencies;
    }

    public function getCurrency($encryptedCurrencyId)
    {
        $currencyId = $this->encryption->decrypt($encryptedCurrencyId);
        $query = 'SELECT currency_id, currency_code 
                  FROM cm_sap.tb_currencies WHERE currency_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_currency_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting currency by ID.');
        }
        $result = pg_execute($this->connection, "get_currency_by_id", array($currencyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting currency by ID.');
        }
        $currency = pg_fetch_assoc($result);
        if ($currency === false) {
            return null;
        }
        $currency['currency_id'] = $this->encryption->encrypt($currency['currency_id']);
        return $currency;
    }

    public function createCurrency($currency_code)
    {
        $query = 'INSERT INTO cm_sap.tb_currencies (currency_code, created_at, updated_at, is_deleted) 
                  VALUES ($1, NOW(), NOW(), false) RETURNING currency_id';
        $result = pg_prepare($this->connection, "create_currency", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating currency.');
        }
        $result = pg_execute($this->connection, "create_currency", array($currency_code));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating currency.');
        }
        $currencyId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($currencyId);
    }

    public function updateCurrency($encryptedCurrencyId, $currency_code)
    {
        $currencyId = $this->encryption->decrypt($encryptedCurrencyId);
        $query = 'UPDATE cm_sap.tb_currencies 
                  SET currency_code = $2, updated_at = NOW() 
                  WHERE currency_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_currency", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating currency.');
        }
        $result = pg_execute($this->connection, "update_currency", array($currencyId, $currency_code));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating currency.');
        }
        return pg_affected_rows($result);
    }

    public function deleteCurrency($encryptedCurrencyId)
    {
        $currencyId = $this->encryption->decrypt($encryptedCurrencyId);
        $query = 'UPDATE cm_sap.tb_currencies SET is_deleted = true, updated_at = NOW() 
                  WHERE currency_id = $1';
        $result = pg_prepare($this->connection, "delete_currency", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting currency.');
        }
        $result = pg_execute($this->connection, "delete_currency", array($currencyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting currency.');
        }
        return pg_affected_rows($result);
    }
}
