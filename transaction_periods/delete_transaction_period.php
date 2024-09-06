<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_periods.php'; // Assuming the class is in this file

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        $required_fields = ['transaction_period_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // Initialize TransactionPeriods class
            $transactionPeriods = new TransactionPeriods();
            
            // Call deleteTransactionPeriod method
            $result = $transactionPeriods->deleteTransactionPeriod(trim($data['transaction_period_id']));

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Transaction period deleted successfully"]);
            } else {
                throw new Exception("Error deleting transaction period.");
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
