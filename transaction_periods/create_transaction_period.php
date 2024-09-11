<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_periods.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and decode the input data
        $data = json_decode(file_get_contents('php://input'), true);

        // Define required fields
        $required_fields = ['transaction_period_group_id', 'transaction_period_type_id', 'account_from', 'account_to', 'period_from_first', 'period_from_first_year', 'period_to_first', 'period_to_first_year', 'period_from_second', 'period_from_second_year', 'period_to_second', 'period_to_second_year', 'augr'];
        $missing_fields = [];

        // Check for missing fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // Retrieve and sanitize input data
            $transaction_period_group_id = trim($data['transaction_period_group_id']);
            $transaction_period_type_id = trim($data['transaction_period_type_id']);
            $account_from = trim($data['account_from']);
            $account_to = trim($data['account_to']);
            $period_from_first = trim($data['period_from_first']);
            $period_from_first_year = trim($data['period_from_first_year']);
            $period_to_first = trim($data['period_to_first']);
            $period_to_first_year = trim($data['period_to_first_year']);
            $period_from_second = trim($data['period_from_second']);
            $period_from_second_year = trim($data['period_from_second_year']);
            $period_to_second = trim($data['period_to_second']);
            $period_to_second_year = trim($data['period_to_second_year']);
            $augr = trim($data['augr']);

            // Create instance of TransactionPeriods class
            $transactionPeriods = new TransactionPeriods();
            
            // Call the method to create a transaction period
            $transactionPeriodId = $transactionPeriods->createTransactionPeriod(
                $transaction_period_group_id,
                $transaction_period_type_id,
                $account_from,
                $account_to,
                $period_from_first,
                $period_from_first_year,
                $period_to_first,
                $period_to_first_year,
                $period_from_second,
                $period_from_second_year,
                $period_to_second,
                $period_to_second_year,
                $augr
            );

            // Return success response
            if ($transactionPeriodId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Transaction period created successfully", "transaction_period_id" => $transactionPeriodId]);
            } else {
                throw new Exception("Error creating transaction period.");
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
