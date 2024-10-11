<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/document_types.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // เรียกใช้งาน DocumentTypes class และดึงข้อมูลประเภทเอกสารทั้งหมด
        $documentTypes = new DocumentTypes();
        $documentTypeList = $documentTypes->getDocumentTypeAll();

        if ($documentTypeList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $documentTypeList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No document types found."]);
        }
    } else {
        // ส่งข้อความหากใช้เมธอดที่ไม่ถูกต้อง
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาดกรณีเกิดข้อผิดพลาดในกระบวนการ
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
