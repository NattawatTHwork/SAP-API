<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_periods.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['transaction_period_id'])) {
            $transaction_period_id = trim($_GET['transaction_period_id']);
            
            if (!empty($transaction_period_id)) {
                $transactionPeriods = new TransactionPeriods();
                $transactionPeriod = $transactionPeriods->getTransactionPeriod($transaction_period_id);

                if ($transactionPeriod) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $transactionPeriod]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Transaction period not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Transaction Period ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Transaction Period ID is missing."]);
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
