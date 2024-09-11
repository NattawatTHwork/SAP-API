<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/group_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and decode the input data
        $data = json_decode(file_get_contents('php://input'), true);

        // Define required fields for creating a group account
        $required_fields = ['group_account_code', 'chart_account_id', 'name_account', 'account_from', 'account_to'];
        $missing_fields = [];

        // Check for missing fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // Retrieve and sanitize input data
            $group_account_code = trim($data['group_account_code']);
            $chart_account_id = trim($data['chart_account_id']); // This should be encrypted
            $name_account = trim($data['name_account']);
            $account_from = trim($data['account_from']);
            $account_to = trim($data['account_to']);

            // Create instance of GroupAccounts class
            $groupAccounts = new GroupAccounts();
            
            // Call the method to create a group account
            $groupAccountId = $groupAccounts->createGroupAccount(
                $group_account_code,
                $chart_account_id,
                $name_account,
                $account_from,
                $account_to
            );

            // Return success response
            if ($groupAccountId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Group account created successfully", "group_account_id" => $groupAccountId]);
            } else {
                throw new Exception("Error creating group account.");
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
