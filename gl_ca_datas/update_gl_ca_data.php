<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_ca_datas.php'; // เปลี่ยนเป็นไฟล์ที่มี class GLCAData

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_ca_data_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_ca_data_id = isset($data['gl_ca_data_id']) ? trim($data['gl_ca_data_id']) : '';
            $account_assignment_info = isset($data['account_assignment_info']) ? trim($data['account_assignment_info']) : '';
            $accounting_note = isset($data['accounting_note']) ? trim($data['accounting_note']) : '';
            $account_assignment_info_9 = isset($data['account_assignment_info_9']) ? trim($data['account_assignment_info_9']) : '';

            $glCAData = new GLCAData();
            $affectedRows = $glCAData->updateGLCAData(
                $gl_ca_data_id,
                $account_assignment_info,
                $accounting_note,
                $account_assignment_info_9
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "GL CA Data updated successfully"]);
            } else {
                throw new Exception("Error updating GL CA Data.");
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
