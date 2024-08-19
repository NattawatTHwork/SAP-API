<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class SubCompanies
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

    public function getSubCompanyAll($encryptedCompanyId)
    {
        $companyId = $this->encryption->decrypt($encryptedCompanyId);
        $query = 'SELECT sub_company_id, sub_company_code, sub_company_name 
                  FROM cm_sap.tb_sub_companies 
                  WHERE company_id = $1 AND is_deleted = false 
                  ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_sub_companies_by_company", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting sub-companies by company_id.');
        }
        $result = pg_execute($this->connection, "get_all_sub_companies_by_company", array($companyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting sub-companies by company_id.');
        }
        $subCompanies = pg_fetch_all($result);
        if ($subCompanies === false) {
            return [];
        }
        foreach ($subCompanies as &$subCompany) {
            $subCompany['sub_company_id'] = $this->encryption->encrypt($subCompany['sub_company_id']);
        }
        return $subCompanies;
    }    

    public function getSubCompany($encryptedSubCompanyId)
    {
        $subCompanyId = $this->encryption->decrypt($encryptedSubCompanyId);
        $query = 'SELECT 
                sub_company_id, sub_company_code, sub_company_name, cnpj_bus_place, state_tax, 
                munic_tax, bp_cfop_cat, representative_name, business_type, industry_type, 
                tax_number1, tax_number2, tax_office, sub_name_th, sub_name_en, 
                tb_sub_companies.search_first, tb_sub_companies.search_second, tb_sub_companies.a_road, 
                tb_sub_companies.a_number, tb_sub_companies.a_address, tb_sub_companies.a_province, 
                tb_sub_companies.a_zip_code, tb_sub_companies.zone, tb_sub_companies.timezone, tb_sub_companies.postbox, 
                tb_sub_companies.zip_code, tb_sub_companies.company_zip_code, tb_sub_companies.language, 
                tb_sub_companies.phone, tb_sub_companies.phone_ex, tb_sub_companies.mobile_phone, tb_sub_companies.fax, 
                tb_sub_companies.fax_ex, tb_sub_companies.email, tb_sub_companies.standard_communication, 
                tb_sub_companies.company_id AS company_id, company_code, name_th, name_en,
                tb_sub_companies.country_id AS country_id, country_code, country_name
            FROM 
                cm_sap.tb_sub_companies 
            INNER JOIN 
                cm_sap.tb_companies ON tb_sub_companies.company_id = tb_companies.company_id
            INNER JOIN 
                cm_sap.tb_countries ON tb_sub_companies.country_id = tb_countries.country_id
            WHERE 
                sub_company_id = $1 AND tb_sub_companies.is_deleted = false';
        $result = pg_prepare($this->connection, "get_sub_company_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting sub-company by ID.');
        }
        $result = pg_execute($this->connection, "get_sub_company_by_id", array($subCompanyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting sub-company by ID.');
        }
        $subCompany = pg_fetch_assoc($result);
        if ($subCompany === false) {
            return null;
        }
        $subCompany['sub_company_id'] = $this->encryption->encrypt($subCompany['sub_company_id']);
        $subCompany['company_id'] = $this->encryption->encrypt($subCompany['company_id']);
        $subCompany['country_id'] = $this->encryption->encrypt($subCompany['country_id']);
        return $subCompany;
    }    

public function createSubCompany(
    $company_id, $sub_company_code, $sub_company_name, $cnpj_bus_place, $state_tax, $munic_tax, $bp_cfop_cat, 
    $representative_name, $business_type, $industry_type, $tax_number1, $tax_number2, $tax_office, 
    $sub_name_th, $sub_name_en, $search_first, $search_second, $a_road, 
    $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $country_id, 
    $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, 
    $fax, $fax_ex, $email, $standard_communication
) {
    $decryptedCompanyId = $this->encryption->decrypt($company_id);
    $decryptedCountryId = $this->encryption->decrypt($country_id);
    $query = 'INSERT INTO cm_sap.tb_sub_companies (
        company_id, sub_company_code, sub_company_name, cnpj_bus_place, state_tax, munic_tax, 
        bp_cfop_cat, representative_name, business_type, industry_type, tax_number1, tax_number2, 
        tax_office, sub_name_th, sub_name_en, search_first, search_second, 
        a_road, a_number, a_address, a_province, a_zip_code, zone, timezone, country_id, postbox, 
        zip_code, company_zip_code, language, phone, phone_ex, mobile_phone, fax, fax_ex, email, 
        standard_communication, created_at, updated_at, is_deleted
    ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, 
        $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32, $33, $34, 
        $35, $36, NOW(), NOW(), false
    ) RETURNING sub_company_id';

    $result = pg_prepare($this->connection, "create_sub_company", $query);
    if (!$result) {
        throw new Exception('Failed to prepare SQL query for creating sub-company.');
    }

    $result = pg_execute($this->connection, "create_sub_company", array(
        $decryptedCompanyId, $sub_company_code, $sub_company_name, $cnpj_bus_place, $state_tax, $munic_tax,
        $bp_cfop_cat, $representative_name, $business_type, $industry_type, $tax_number1, $tax_number2, 
        $tax_office, $sub_name_th, $sub_name_en, $search_first, $search_second, 
        $a_road, $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $decryptedCountryId, 
        $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, 
        $fax_ex, $email, $standard_communication
    ));
    if (!$result) {
        throw new Exception('Failed to execute SQL query for creating sub-company.');
    }

    $subCompanyId = pg_fetch_result($result, 0, 0);
    return $this->encryption->encrypt($subCompanyId);
}

public function updateSubCompany(
    $encryptedSubCompanyId, $company_id, $sub_company_code, $sub_company_name, $cnpj_bus_place, 
    $state_tax, $munic_tax, $bp_cfop_cat, $representative_name, $business_type, 
    $industry_type, $tax_number1, $tax_number2, $tax_office, $sub_name_th, 
    $sub_name_en, $search_first, $search_second, 
    $a_road, $a_number, $a_address, $a_province, $a_zip_code, 
    $zone, $timezone, $country_id, $postbox, $zip_code, 
    $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, 
    $fax, $fax_ex, $email, $standard_communication
)
{
    $subCompanyId = $this->encryption->decrypt($encryptedSubCompanyId);
    $decryptedCompanyId = $this->encryption->decrypt($company_id);
    $decryptedCountryId = $this->encryption->decrypt($country_id);
    $query = 'UPDATE cm_sap.tb_sub_companies 
              SET company_id = $2, sub_company_code = $3, sub_company_name = $4, cnpj_bus_place = $5, 
                  state_tax = $6, munic_tax = $7, bp_cfop_cat = $8, representative_name = $9, 
                  business_type = $10, industry_type = $11, tax_number1 = $12, tax_number2 = $13, 
                  tax_office = $14, sub_name_th = $15, sub_name_en = $16, search_first = $17, 
                  search_second = $18, a_road = $19, a_number = $20, a_address = $21, 
                  a_province = $22, a_zip_code = $23, zone = $24, timezone = $25, 
                  country_id = $26, postbox = $27, zip_code = $28, company_zip_code = $29, 
                  language = $30, phone = $31, phone_ex = $32, mobile_phone = $33, 
                  fax = $34, fax_ex = $35, email = $36, standard_communication = $37, 
                  updated_at = NOW() 
              WHERE sub_company_id = $1 AND is_deleted = false';
    $result = pg_prepare($this->connection, "update_sub_company", $query);
    if (!$result) {
        throw new Exception('Failed to prepare SQL query for updating sub-company.');
    }
    $result = pg_execute($this->connection, "update_sub_company", array(
        $subCompanyId, $decryptedCompanyId, $sub_company_code, $sub_company_name, $cnpj_bus_place, $state_tax, 
        $munic_tax, $bp_cfop_cat, $representative_name, $business_type, $industry_type, $tax_number1, 
        $tax_number2, $tax_office, $sub_name_th, $sub_name_en, $search_first, $search_second, 
        $a_road, $a_number, $a_address, $a_province, $a_zip_code, $zone, $timezone, $decryptedCountryId, 
        $postbox, $zip_code, $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, 
        $fax_ex, $email, $standard_communication
    ));
    
    if (!$result) {
        throw new Exception('Failed to execute SQL query for updating sub-company.');
    }
    return true;
}


    public function deleteSubCompany($encryptedSubCompanyId)
    {
        $subCompanyId = $this->encryption->decrypt($encryptedSubCompanyId);
        $query = 'UPDATE cm_sap.tb_sub_companies SET is_deleted = true, updated_at = NOW() WHERE sub_company_id = $1';
        $result = pg_prepare($this->connection, "delete_sub_company", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting sub-company.');
        }
        $result = pg_execute($this->connection, "delete_sub_company", array($subCompanyId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting sub-company.');
        }
        return true;
    }
}
?>
