<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_period_groups.php'; // ใช้ class ที่จัดการ transaction period groups

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['transaction_period_group_id'])) { // เปลี่ยนเป็น transaction_period_group_id
            $transaction_period_group_id = trim($_GET['transaction_period_group_id']); // เปลี่ยนเป็น transaction_period_group_id
            if (!empty($transaction_period_group_id)) {
                $transactionPeriodGroups = new TransactionPeriodGroups(); // สร้างอินสแตนซ์ของ TransactionPeriodGroups
                $transactionPeriodGroup = $transactionPeriodGroups->getTransactionPeriodGroup($transaction_period_group_id); // เรียกใช้ getTransactionPeriodGroup

                if ($transactionPeriodGroup) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $transactionPeriodGroup]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Transaction Period Group not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Transaction Period Group ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Transaction Period Group ID is missing."]);
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
