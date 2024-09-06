<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_periods.php'; // Updated to TransactionPeriods

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Ensure fiscal_year_id is provided in the GET request
        if (!isset($_GET['fiscal_year_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Fiscal Year ID is required."]);
            exit;
        }

        $encryptedFiscalYearId = $_GET['fiscal_year_id'];

        // Instantiate the TransactionPeriods class
        $transactionPeriods = new TransactionPeriods();
        // Call the getTransactionPeriodAll method
        $periodList = $transactionPeriods->getTransactionPeriodAll($encryptedFiscalYearId);

        // Return results based on the response
        if ($periodList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $periodList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No periods found."]);
        }
    } else {
        // Return method not allowed error if request method isn't GET
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    // Return a 500 error if there's any exception
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
