<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class Countries
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

    public function getCountryAll()
    {
        $query = 'SELECT country_id, country_code, name FROM cm_sap.tb_countries WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_countries", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all countries.');
        }
        $result = pg_execute($this->connection, "get_all_countries", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all countries.');
        }

        $countries = pg_fetch_all($result);

        if ($countries === false) {
            return [];
        }

        foreach ($countries as &$country) {
            $country['country_id'] = $this->encryption->encrypt($country['country_id']);
        }

        return $countries;
    }

    public function getCountry($encryptedCountryId)
    {
        $countryId = $this->encryption->decrypt($encryptedCountryId);
    
        $query = 'SELECT * FROM cm_sap.tb_countries WHERE country_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_country_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting country by ID.');
        }
        $result = pg_execute($this->connection, "get_country_by_id", array($countryId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting country by ID.');
        }
    
        $country = pg_fetch_assoc($result);
    
        if ($country === false) {
            return null;
        }
    
        $country['country_id'] = $this->encryption->encrypt($country['country_id']);
    
        return $country;
    }
    

    public function createCountry($country_code, $name, $full_name, $nationality, $full_nationality, $country_vehicle_key, $language_key, $major_currency_index, $major_currency, $member_euro, $trade_statistic_abbreviation, $step, $capital_goods_indicator, $iso_key, $three_iso_key, $intrastat_key, $address_outline_key, $standard_name_format, $type_country_name, $date_format, $decimal_format)
    {
        $query = 'INSERT INTO cm_sap.tb_countries (country_code, name, full_name, nationality, full_nationality, country_vehicle_key, language_key, major_currency_index, major_currency, member_euro, trade_statistic_abbreviation, step, capital_goods_indicator, iso_key, three_iso_key, intrastat_key, address_outline_key, standard_name_format, type_country_name, date_format, decimal_format, created_at, update_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, NOW(), NOW(), false) RETURNING country_id';
        $result = pg_prepare($this->connection, "create_country", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating country.');
        }
        $result = pg_execute($this->connection, "create_country", array($country_code, $name, $full_name, $nationality, $full_nationality, $country_vehicle_key, $language_key, $major_currency_index, $major_currency, $member_euro, $trade_statistic_abbreviation, $step, $capital_goods_indicator, $iso_key, $three_iso_key, $intrastat_key, $address_outline_key, $standard_name_format, $type_country_name, $date_format, $decimal_format));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating country.');
        }
        $countryId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($countryId);
    }

    public function updateCountry($encryptedCountryId, $country_code, $name, $full_name, $nationality, $full_nationality, $country_vehicle_key, $language_key, $major_currency_index, $major_currency, $member_euro, $trade_statistic_abbreviation, $step, $capital_goods_indicator, $iso_key, $three_iso_key, $intrastat_key, $address_outline_key, $standard_name_format, $type_country_name, $date_format, $decimal_format)
    {
        $countryId = $this->encryption->decrypt($encryptedCountryId);

        $query = 'UPDATE cm_sap.tb_countries 
                  SET country_code = $2, name = $3, full_name = $4, nationality = $5, full_nationality = $6, country_vehicle_key = $7, language_key = $8, major_currency_index = $9, major_currency = $10, member_euro = $11, trade_statistic_abbreviation = $12, step = $13, capital_goods_indicator = $14, iso_key = $15, three_iso_key = $16, intrastat_key = $17, address_outline_key = $18, standard_name_format = $19, type_country_name = $20, date_format = $21, decimal_format = $22, update_at = NOW() 
                  WHERE country_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_country", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating country.');
        }
        $result = pg_execute($this->connection, "update_country", array($countryId, $country_code, $name, $full_name, $nationality, $full_nationality, $country_vehicle_key, $language_key, $major_currency_index, $major_currency, $member_euro, $trade_statistic_abbreviation, $step, $capital_goods_indicator, $iso_key, $three_iso_key, $intrastat_key, $address_outline_key, $standard_name_format, $type_country_name, $date_format, $decimal_format));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating country.');
        }
        return pg_affected_rows($result);
    }

    public function deleteCountry($encryptedCountryId)
    {
        $countryId = $this->encryption->decrypt($encryptedCountryId);

        $query = 'UPDATE cm_sap.tb_countries SET is_deleted = true, update_at = NOW() 
                  WHERE country_id = $1';
        $result = pg_prepare($this->connection, "delete_country", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting country.');
        }
        $result = pg_execute($this->connection, "delete_country", array($countryId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting country.');
        }
        return pg_affected_rows($result);
    }
}
