<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/transaction_period_groups.php'; // ใช้ class ที่จัดการ transaction period groups

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // เปลี่ยนชื่อ field ตาม transaction period group
        $required_fields = ['transaction_period_group_id', 'transaction_period_group_code', 'description'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // เปลี่ยนชื่อตัวแปรให้เป็น transaction_period_group
            $transaction_period_group_id = trim($data['transaction_period_group_id']);
            $transaction_period_group_code = trim($data['transaction_period_group_code']);
            $description = trim($data['description']);

            // เรียกใช้ class TransactionPeriodGroups แทน
            $transactionPeriodGroups = new TransactionPeriodGroups();
            $result = $transactionPeriodGroups->updateTransactionPeriodGroup(
                $transaction_period_group_id,
                $transaction_period_group_code,
                $description,
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Transaction Period Group updated successfully"]);
            } else {
                throw new Exception("Error updating Transaction Period Group.");
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
