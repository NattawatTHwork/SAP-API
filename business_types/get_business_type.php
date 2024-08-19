<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/business_types.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['business_type_id'])) {
            $business_type_id = trim($_GET['business_type_id']);
            if (!empty($business_type_id)) {
                $businessTypes = new BusinessTypes(); 
                $businessType = $businessTypes->getBusinessType($business_type_id);

                if ($businessType) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $businessType]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Business type not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Business type ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Business type ID is missing."]);
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
