<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/countries.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['country_id', 'country_code', 'name'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $country_id = trim($data['country_id']);
            $country_code = trim($data['country_code']);
            $other_country_key = isset($data['other_country_key']) ? trim($data['other_country_key']) : '';
            $name = trim($data['name']);
            $full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
            $nationality = isset($data['nationality']) ? trim($data['nationality']) : '';
            $full_nationality = isset($data['full_nationality']) ? trim($data['full_nationality']) : '';
            $country_vehicle_key = isset($data['country_vehicle_key']) ? trim($data['country_vehicle_key']) : '';
            $language_key = isset($data['language_key']) ? trim($data['language_key']) : '';
            $major_currency_index = isset($data['major_currency_index']) ? trim($data['major_currency_index']) : '';
            $major_currency = isset($data['major_currency']) ? trim($data['major_currency']) : '';
            $member_euro = isset($data['member_euro']) ? trim($data['member_euro']) : 'false';
            $trade_statistic_abbreviation = isset($data['trade_statistic_abbreviation']) ? trim($data['trade_statistic_abbreviation']) : '';
            $step = isset($data['step']) ? trim($data['step']) : '';
            $capital_goods_indicator = isset($data['capital_goods_indicator']) ? trim($data['capital_goods_indicator']) : 'false';
            $iso_key = isset($data['iso_key']) ? trim($data['iso_key']) : '';
            $three_iso_key = isset($data['three_iso_key']) ? trim($data['three_iso_key']) : '';
            $intrastat_key = isset($data['intrastat_key']) ? trim($data['intrastat_key']) : '';
            $address_outline_key = isset($data['address_outline_key']) ? trim($data['address_outline_key']) : '';
            $standard_name_format = isset($data['standard_name_format']) ? trim($data['standard_name_format']) : '';
            $type_country_name = isset($data['type_country_name']) ? trim($data['type_country_name']) : 'false';
            $date_format = isset($data['date_format']) ? trim($data['date_format']) : '';
            $decimal_format = isset($data['decimal_format']) ? trim($data['decimal_format']) : '';

            $countries = new Countries();
            $result = $countries->updateCountry(
                $country_id,
                $country_code,
                $other_country_key,
                $name,
                $full_name,
                $nationality,
                $full_nationality,
                $country_vehicle_key,
                $language_key,
                $major_currency_index,
                $major_currency,
                $member_euro,
                $trade_statistic_abbreviation,
                $step,
                $capital_goods_indicator,
                $iso_key,
                $three_iso_key,
                $intrastat_key,
                $address_outline_key,
                $standard_name_format,
                $type_country_name,
                $date_format,
                $decimal_format
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Country updated successfully"]);
            } else {
                throw new Exception("Error updating country.");
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
