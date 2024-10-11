<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/document_types.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // ตรวจสอบว่ามีการส่งค่า document_type_id มาหรือไม่
        if (isset($_GET['document_type_id'])) {
            $document_type_id = trim($_GET['document_type_id']);
            if (!empty($document_type_id)) {
                // เรียกใช้งาน DocumentTypes class และฟังก์ชัน getDocumentType
                $documentTypes = new DocumentTypes(); 
                $documentType = $documentTypes->getDocumentType($document_type_id);

                if ($documentType) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $documentType]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Document type not found."]);
                }
            } else {
                // กรณีที่ document_type_id เป็นค่าว่าง
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Document type ID is empty."]);
            }
        } else {
            // กรณีที่ไม่มีการส่งค่า document_type_id มาในคำขอ
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Document type ID is missing."]);
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
