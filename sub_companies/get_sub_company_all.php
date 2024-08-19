<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/sub_companies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['company_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Company ID is required."]);
            exit;
        }
        $encryptedCompanyId = $_GET['company_id'];

        $subCompanies = new SubCompanies();
        $subCompanyList = $subCompanies->getSubCompanyAll($encryptedCompanyId);

        if ($subCompanyList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $subCompanyList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No sub-companies found for the given company ID."]);
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
