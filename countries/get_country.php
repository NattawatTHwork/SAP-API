<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/countries.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['country_id'])) {
            $country_id = trim($_GET['country_id']);
            if (!empty($country_id)) {
                $countries = new Countries();
                $country = $countries->getCountry($country_id);

                if ($country) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $country]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Country not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Country ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Country ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
