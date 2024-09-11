<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_period_groups.php'; // Assuming you have a class for handling transaction period groups

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $transactionPeriodGroups = new TransactionPeriodGroups(); // Create instance of TransactionPeriodGroups class
        $transactionPeriodGroupList = $transactionPeriodGroups->getAllTransactionPeriodGroups();

        if ($transactionPeriodGroupList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $transactionPeriodGroupList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No transaction period groups found."]);
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