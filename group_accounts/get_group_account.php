<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/group_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['group_account_id'])) {
            $group_account_id = trim($_GET['group_account_id']);
            
            if (!empty($group_account_id)) {
                $groupAccounts = new GroupAccounts();
                $groupAccount = $groupAccounts->getGroupAccount($group_account_id);

                if ($groupAccount) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $groupAccount]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Group account not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Group Account ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Group Account ID is missing."]);
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
