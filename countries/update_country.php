<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/countries.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['country_id', 'country_code', 'name', 'full_name', 'nationality', 'full_nationality', 'country_carrier_key', 'language_key', 'major_currency_index', 'major_currency', 'member_euro', 'trade_statistic_abbreviation', 'step', 'capital_goods_indicator', 'iso_key', 'three_iso_key', 'intrastat_key', 'address_outline_key', 'standard_name_format', 'type_country_name', 'date_format', 'decimal_format'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $countries = new Countries();
            $result = $countries->updateCountry(
                trim($data['country_id']),
                trim($data['country_code']),
                trim($data['name']),
                trim($data['full_name']),
                trim($data['nationality']),
                trim($data['full_nationality']),
                trim($data['country_carrier_key']),
                trim($data['language_key']),
                trim($data['major_currency_index']),
                trim($data['major_currency']),
                trim($data['member_euro']),
                trim($data['trade_statistic_abbreviation']),
                trim($data['step']),
                trim($data['capital_goods_indicator']),
                trim($data['iso_key']),
                trim($data['three_iso_key']),
                trim($data['intrastat_key']),
                trim($data['address_outline_key']),
                trim($data['standard_name_format']),
                trim($data['type_country_name']),
                trim($data['date_format']),
                trim($data['decimal_format'])
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
