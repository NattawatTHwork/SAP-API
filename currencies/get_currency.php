<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/currencies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // ตรวจสอบว่ามีการส่งค่า currency_id มาหรือไม่
        if (isset($_GET['currency_id'])) {
            $currency_id = trim($_GET['currency_id']);
            if (!empty($currency_id)) {
                // เรียกใช้งาน Currencies class และฟังก์ชัน getCurrency
                $currencies = new Currencies(); 
                $currency = $currencies->getCurrency($currency_id);

                if ($currency) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $currency]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Currency not found."]);
                }
            } else {
                // กรณีที่ currency_id เป็นค่าว่าง
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Currency ID is empty."]);
            }
        } else {
            // กรณีที่ไม่มีการส่งค่า currency_id มาในคำขอ
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Currency ID is missing."]);
        }
    } else {
        // กรณีที่ไม่ใช่เมธอด GET
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาดกรณีเกิดข้อผิดพลาดในกระบวนการ
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
