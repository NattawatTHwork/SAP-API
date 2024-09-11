<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/group_accounts.php'; // Use GroupAccounts class

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = [
            'group_account_id', 'group_account_code', 'chart_account_id', 'name_account', 'account_from', 'account_to'
        ];

        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $group_account_id = trim($data['group_account_id']);
            $group_account_code = trim($data['group_account_code']);
            $chart_account_id = trim($data['chart_account_id']);
            $name_account = trim($data['name_account']);
            $account_from = trim($data['account_from']);
            $account_to = trim($data['account_to']);

            $groupAccounts = new GroupAccounts();
            
            $result = $groupAccounts->updateGroupAccount(
                $group_account_id,
                $group_account_code,
                $chart_account_id,
                $name_account,
                $account_from,
                $account_to
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Group account updated successfully."]);
            } else {
                throw new Exception("Error updating group account.");
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
