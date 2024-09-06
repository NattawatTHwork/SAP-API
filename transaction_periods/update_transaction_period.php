<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_periods.php'; // Use TransactionPeriods class

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the input data as an associative array
        $data = json_decode(file_get_contents('php://input'), true);

        // Define required fields
        $required_fields = [
            'transaction_period_id', 'transaction_period_type_id', 'account_from', 'account_to',
            'period_from_first', 'period_from_first_year', 'period_to_first', 'period_to_first_year',
            'period_from_second', 'period_from_second_year', 'period_to_second', 'period_to_second_year', 'augr'
        ];

        $missing_fields = [];

        // Check for missing required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // If no fields are missing, proceed with the update
        if (empty($missing_fields)) {
            $transaction_period_id = trim($data['transaction_period_id']);
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

            // Instantiate the TransactionPeriods class
            $transactionPeriods = new TransactionPeriods();
            
            // Call the updateTransactionPeriod method
            $result = $transactionPeriods->updateTransactionPeriod(
                $transaction_period_id,
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

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Transaction period updated successfully."]);
            } else {
                throw new Exception("Error updating transaction period.");
            }
        } else {
            // If there are missing fields, return an error
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // Handle invalid request method
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // Handle any exceptions and return an error response
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
