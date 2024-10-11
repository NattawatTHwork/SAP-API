<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/branch_numbers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบฟิลด์ที่จำเป็น
        $required_fields = ['branch_number_code'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // หากไม่มีฟิลด์ที่ขาดหาย ทำการสร้าง branch number ใหม่
        if (empty($missing_fields)) {
            $branch_number_code = trim($data['branch_number_code']);

            // เรียกใช้งาน BranchNumbers class เพื่อสร้างสาขาใหม่
            $branchNumbers = new BranchNumbers();
            $branchNumberId = $branchNumbers->createBranchNumber($branch_number_code);

            if ($branchNumberId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Branch number created successfully", "branch_number_id" => $branchNumberId]);
            } else {
                throw new Exception("Error creating branch number.");
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
