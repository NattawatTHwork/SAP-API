<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/fiscal_years.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['fiscal_year_id'])) {
            $fiscal_year_id = trim($_GET['fiscal_year_id']);
            if (!empty($fiscal_year_id)) {
                $fiscalYears = new FiscalYears();
                $fiscalYear = $fiscalYears->getFiscalYear($fiscal_year_id);

                if ($fiscalYear) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $fiscalYear]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Fiscal Year not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Fiscal Year ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Fiscal Year ID is missing."]);
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
