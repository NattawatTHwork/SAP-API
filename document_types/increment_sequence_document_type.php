<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/document_types.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่ามีการส่งค่า document_type_id มาหรือไม่
        $required_fields = ['document_type_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // หากไม่มีฟิลด์ที่ขาดหาย จะทำการเพิ่มค่า sequence
        if (empty($missing_fields)) {
            $document_type_id = trim($data['document_type_id']);

            // เรียกใช้งาน DocumentTypes class และ incrementSequence
            $documentTypes = new DocumentTypes();
            $newSequence = $documentTypes->incrementSequence($document_type_id);

            if ($newSequence) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Sequence incremented successfully", "new_sequence" => $newSequence]);
            } else {
                throw new Exception("Error incrementing sequence.");
            }
        } else {
            // หากฟิลด์ที่จำเป็นขาดหาย
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // หากใช้เมธอดที่ไม่ถูกต้อง
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาด
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
