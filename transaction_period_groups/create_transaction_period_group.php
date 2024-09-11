<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_period_groups.php'; // Assuming you have a class for handling transaction period groups

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['transaction_period_group_code', 'description']; // Adjust the fields for transaction period groups
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $transaction_period_group_code = trim($data['transaction_period_group_code']);
            $description = trim($data['description']);

            $transactionPeriodGroup = new TransactionPeriodGroups(); // Create instance of TransactionPeriodGroups class
            $transactionPeriodGroupId = $transactionPeriodGroup->createTransactionPeriodGroup(
                $transaction_period_group_code,
                $description
            );

            if ($transactionPeriodGroupId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Transaction period group created successfully", "transaction_period_group_id" => $transactionPeriodGroupId]);
            } else {
                throw new Exception("Error creating transaction period group.");
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
