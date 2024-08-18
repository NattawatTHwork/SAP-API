<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class Companies
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

    public function getCompanyAll()
    {
        $query = 'SELECT company_id, company_code, name_th FROM cm_sap.tb_companies WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_companies", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all companies.');
        }
        $result = pg_execute($this->connection, "get_all_companies", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all companies.');
        }
        $companies = pg_fetch_all($result);
        if ($companies === false) {
            return [];
        }
        foreach ($companies as &$company) {
            $company['company_id'] = $this->encryption->encrypt($company['company_id']);
        }
        return $companies;
    }

    public function getCompany($encryptedCompanyId)
    {
        $companyId = $this->encryption->decrypt($encryptedCompanyId);
        $query = 'SELECT company_id, company_code, name_th, name_en, search_first, search_second, a_road, a_number, a_address, a_province, a_zip_code, zone, timezone, tb_companies.country_id AS country_id, country_code, name AS country_name, postbox, zip_code, company_zip_code, language, phone, phone_ex, mobile_phone, fax, fax_ex, email, standard_communication, comment FROM cm_sap.tb_companies INNER JOIN cm_sap.tb_countries ON tb_companies.country_id = tb_countries.country_id WHERE tb_companies.company_id = $1 AND tb_companies.is_deleted = false';
        $result = pg_prepare($this->connection, "get_company_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting company by ID.');
        }
        $result = pg_execute($this->connection, "get_company_by_id", array($companyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting company by ID.');
        }
        $company = pg_fetch_assoc($result);
        if ($company === false) {
            return null;
        }
        $company['company_id'] = $this->encryption->encrypt($company['company_id']);
        $company['country_id'] = $this->encryption->encrypt($company['country_id']);

        return $company;
    }

    public function createCompany($company_code, $name_th, $name_en, $search_first, $search_second, $a_road, $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $country_id, $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, $fax_ex, $email, $standard_communication, $comment)
    {
        $decryptedCountryId = $this->encryption->decrypt($country_id);
        $query = 'INSERT INTO cm_sap.tb_companies (company_code, name_th, name_en, search_first, search_second, a_road, a_number, a_address, a_province, a_zip_code, zone, timezone, country_id, postbox, zip_code, company_zip_code, language, phone, phone_ex, mobile_phone, fax, fax_ex, email, standard_communication, comment, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24, $25, NOW(), NOW(), false) RETURNING company_id';
        $result = pg_prepare($this->connection, "create_company", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating company.');
        }
        $result = pg_execute($this->connection, "create_company", array(
            $company_code, $name_th, $name_en, $search_first, $search_second, $a_road, $a_number, $a_address, $a_province, $a_zip_code,
            $zone, $timezone, $decryptedCountryId, $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone,
            $fax, $fax_ex, $email, $standard_communication, $comment
        ));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating company.');
        }
        $companyId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($companyId);
    }

    public function updateCompany($encryptedCompanyId, $company_code, $name_th, $name_en, $search_first, $search_second, $a_road, $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $country_id, $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, $fax_ex, $email, $standard_communication, $comment)
    {
        $companyId = $this->encryption->decrypt($encryptedCompanyId);
        $decryptedCountryId = $this->encryption->decrypt($country_id);
        $query = 'UPDATE cm_sap.tb_companies 
                  SET company_code = $2, name_th = $3, name_en = $4, search_first = $5, search_second = $6, a_road = $7, a_number = $8, a_address = $9, a_province = $10, a_zip_code = $11, zone = $12, timezone = $13, country_id = $14, postbox = $15, zip_code = $16, company_zip_code = $17, language = $18, phone = $19, phone_ex = $20, mobile_phone = $21, fax = $22, fax_ex = $23, email = $24, standard_communication = $25, comment = $26, updated_at = NOW() 
                  WHERE company_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_company", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating company.');
        }
        $result = pg_execute($this->connection, "update_company", array($companyId, $company_code, $name_th, $name_en, $search_first, $search_second, $a_road, $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $decryptedCountryId, $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, $fax_ex, $email, $standard_communication, $comment));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating company.');
        }
        return pg_affected_rows($result);
    }

    public function deleteCompany($encryptedCompanyId)
    {
        $companyId = $this->encryption->decrypt($encryptedCompanyId);
        $query = 'UPDATE cm_sap.tb_companies SET is_deleted = true, updated_at = NOW() 
                  WHERE company_id = $1';
        $result = pg_prepare($this->connection, "delete_company", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting company.');
        }
        $result = pg_execute($this->connection, "delete_company", array($companyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting company.');
        }
        return pg_affected_rows($result);
    }
}
