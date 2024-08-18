<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/companies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['company_id', 'company_code', 'name_th'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $company_id = trim($data['company_id']);
            $company_code = trim($data['company_code']);
            $name_th = trim($data['name_th']);
            $name_en = isset($data['name_en']) ? trim($data['name_en']) : '';
            $search_first = isset($data['search_first']) ? trim($data['search_first']) : '';
            $search_second = isset($data['search_second']) ? trim($data['search_second']) : '';
            $a_road = isset($data['a_road']) ? trim($data['a_road']) : '';
            $a_number = isset($data['a_number']) ? trim($data['a_number']) : '';
            $a_address = isset($data['a_address']) ? trim($data['a_address']) : '';
            $a_province = isset($data['a_province']) ? trim($data['a_province']) : '';
            $a_zip_code = isset($data['a_zip_code']) ? trim($data['a_zip_code']) : '';
            $zone = isset($data['zone']) ? trim($data['zone']) : '';
            $timezone = isset($data['timezone']) ? trim($data['timezone']) : '';
            $country_id = isset($data['country_id']) ? trim($data['country_id']) : '';
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
            $comment = isset($data['comment']) ? trim($data['comment']) : '';

            $company = new Companies();
            $result = $company->updateCompany(
                $company_id,
                $company_code,
                $name_th,
                $name_en,
                $search_first,
                $search_second,
                $a_road,
                $a_number,
                $a_address,
                $a_province,
                $a_zip_code,
                $zone,
                $timezone,
                $country_id,
                $postbox,
                $zip_code,
                $company_zip_code,
                $language,
                $phone,
                $phone_ex,
                $mobile_phone,
                $fax,
                $fax_ex,
                $email,
                $standard_communication,
                $comment
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Company updated successfully"]);
            } else {
                throw new Exception("Error updating company.");
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
