<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/document_types.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบฟิลด์ที่จำเป็น
        $required_fields = ['document_type_id', 'document_type_code', 'dt_year', 'dt_from', 'dt_to'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // หากไม่มีฟิลด์ที่ขาดหาย ทำการอัปเดตข้อมูลประเภทเอกสาร
        if (empty($missing_fields)) {
            $document_type_id = trim($data['document_type_id']);
            $document_type_code = trim($data['document_type_code']);
            $dt_year = trim($data['dt_year']);
            $dt_from = trim($data['dt_from']);
            $dt_to = trim($data['dt_to']);

            // เรียกใช้งาน DocumentTypes class เพื่ออัปเดตประเภทเอกสาร
            $documentTypes = new DocumentTypes();
            $result = $documentTypes->updateDocumentType($document_type_id, $document_type_code, $dt_year, $dt_from, $dt_to);

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Document type updated successfully"]);
            } else {
                throw new Exception("Error updating document type.");
            }
        } else {
            // ส่งข้อความเมื่อมีฟิลด์ที่จำเป็นขาดหาย
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // ส่งข้อความหากใช้เมธอดที่ไม่ถูกต้อง
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // ส่งกลับข้อความแสดงข้อผิดพลาดกรณีเกิดข้อผิดพลาดในกระบวนการ
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
