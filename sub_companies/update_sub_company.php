<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/sub_companies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['sub_company_id', 'sub_company_code', 'sub_company_name', 'company_id', 'country_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $sub_company_id = trim($data['sub_company_id']);
            $company_id = trim($data['company_id']);
            $sub_company_code = trim($data['sub_company_code']);
            $sub_company_name = trim($data['sub_company_name']);
            $cnpj_bus_place = isset($data['cnpj_bus_place']) ? trim($data['cnpj_bus_place']) : '';
            $state_tax = isset($data['state_tax']) ? trim($data['state_tax']) : '';
            $munic_tax = isset($data['munic_tax']) ? trim($data['munic_tax']) : '';
            $bp_cfop_cat = isset($data['bp_cfop_cat']) ? trim($data['bp_cfop_cat']) : '';
            $representative_name = isset($data['representative_name']) ? trim($data['representative_name']) : '';
            $business_type = isset($data['business_type']) ? trim($data['business_type']) : '';
            $industry_type = isset($data['industry_type']) ? trim($data['industry_type']) : '';
            $tax_number1 = isset($data['tax_number1']) ? trim($data['tax_number1']) : '';
            $tax_number2 = isset($data['tax_number2']) ? trim($data['tax_number2']) : '';
            $tax_office = isset($data['tax_office']) ? trim($data['tax_office']) : '';
            $sub_name_th = isset($data['sub_name_th']) ? trim($data['sub_name_th']) : '';
            $sub_name_en = isset($data['sub_name_en']) ? trim($data['sub_name_en']) : '';
            $search_first = isset($data['search_first']) ? trim($data['search_first']) : '';
            $search_second = isset($data['search_second']) ? trim($data['search_second']) : '';
            $a_road = isset($data['a_road']) ? trim($data['a_road']) : '';
            $a_number = isset($data['a_number']) ? trim($data['a_number']) : '';
            $a_address = isset($data['a_address']) ? trim($data['a_address']) : '';
            $a_province = isset($data['a_province']) ? trim($data['a_province']) : '';
            $a_zip_code = isset($data['a_zip_code']) ? trim($data['a_zip_code']) : '';
            $zone = isset($data['zone']) ? trim($data['zone']) : '';
            $timezone = isset($data['timezone']) ? trim($data['timezone']) : '';
            $country_id = trim($data['country_id']);
            $postbox = isset($data['postbox']) ? trim($data['postbox']) : '';
            $zip_code = isset($data['zip_code']) ? trim($data['zip_code']) : '';
            $company_zip_code = isset($data['company_zip_code']) ? trim($data['company_zip_code']) : '';
            $language = isset($data['language']) ? trim($data['language']) : '';
            $phone = isset($data['phone']) ? trim($data['phone']) : '';
            $phone_ex = isset($data['phone_ex']) ? trim($data['phone_ex']) : '';
            $mobile_phone = isset($data['mobile_phone']) ? trim($data['mobile_phone']) : '';
            $fax = isset($data['fax']) ? trim($data['fax']) : '';
            $fax_ex = isset($data['fax_ex']) ? trim($data['fax_ex']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';
            $standard_communication = isset($data['standard_communication']) ? trim($data['standard_communication']) : '';

            $subCompany = new SubCompanies();
            $result = $subCompany->updateSubCompany(
                $sub_company_id, $company_id, $sub_company_code, $sub_company_name, $cnpj_bus_place, 
                $state_tax, $munic_tax, $bp_cfop_cat, $representative_name, $business_type, 
                $industry_type, $tax_number1, $tax_number2, $tax_office, $sub_name_th, 
                $sub_name_en, $search_first, $search_second, $a_road, $a_number, $a_address, 
                $a_province, $a_zip_code, $zone, $timezone, $country_id, $postbox, $zip_code, 
                $company_zip_code, $language, $phone, $phone_ex, $mobile_phone, $fax, $fax_ex, 
                $email, $standard_communication
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Sub-company updated successfully"]);
            } else {
                throw new Exception("Error updating sub-company.");
            }
        } else {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
