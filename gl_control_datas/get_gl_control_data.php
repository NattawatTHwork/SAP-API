<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_control_datas.php'; // เปลี่ยนเป็นไฟล์ที่มี class ControlData

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['central_general_ledger_id'])) {
            $central_general_ledger_id = trim($_GET['central_general_ledger_id']);

            if (!empty($central_general_ledger_id)) {
                $controlData = new ControlData(); // สร้าง instance ของ ControlData
                $controlDataInfo = $controlData->getControlData($central_general_ledger_id);

                if ($controlDataInfo) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $controlDataInfo]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Control Data not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Central General Ledger ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Central General Ledger ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
