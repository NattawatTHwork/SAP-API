<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php'; // เปลี่ยนเป็นไฟล์ที่มี class CentralGeneralLedgers

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['central_general_ledger_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $centralGeneralLedgers = new CentralGeneralLedgers();
            $result = $centralGeneralLedgers->deleteCentralGeneralLedger(trim($data['central_general_ledger_id']));

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Central General Ledger deleted successfully"]);
            } else {
                throw new Exception("Error deleting Central General Ledger.");
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
