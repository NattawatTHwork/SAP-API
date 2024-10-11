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

        // หากไม่มีฟิลด์ที่ขาดหาย จะทำการลบประเภทเอกสาร
        if (empty($missing_fields)) {
            $documentTypes = new DocumentTypes();
            $result = $documentTypes->deleteDocumentType(trim($data['document_type_id']));

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Document type deleted successfully"]);
            } else {
                throw new Exception("Error deleting document type.");
            }
        } else {
            // หากฟิลด์ที่จำเป็นหายไป
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // ส่งข้อความหากใช้เมธอดที่ไม่ถูกต้อง
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาด
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
