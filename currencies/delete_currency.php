<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/currencies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่าได้ส่งค่า 'currency_id' มาหรือไม่
        $required_fields = ['currency_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // หากไม่มีฟิลด์ที่ขาดหาย จะทำการลบสกุลเงิน
        if (empty($missing_fields)) {
            $currencies = new Currencies();
            $result = $currencies->deleteCurrency(trim($data['currency_id']));

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Currency deleted successfully"]);
            } else {
                throw new Exception("Error deleting currency.");
            }
        } else {
            // หากฟิลด์ที่จำเป็นหายไป
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // หากใช้เมธอด HTTP ที่ไม่ถูกต้อง
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาด
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
