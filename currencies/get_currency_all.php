<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/currencies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // เรียกใช้งาน Currencies class และดึงข้อมูลสกุลเงินทั้งหมด
        $currencies = new Currencies();
        $currencyList = $currencies->getCurrencyAll();

        if ($currencyList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $currencyList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No currencies found."]);
        }
    } else {
        // หากเมธอดที่เรียกไม่ใช่ GET
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    // กรณีเกิดข้อผิดพลาดในกระบวนการ
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
