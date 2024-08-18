<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/companies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['company_id'])) {
            $company_id = trim($_GET['company_id']);
            if (!empty($company_id)) {
                $companies = new Companies();
                $company = $companies->getCompany($company_id);

                if ($company) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $company]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Company not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Company ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Company ID is missing."]);
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
