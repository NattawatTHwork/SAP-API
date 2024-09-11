<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php'; // เปลี่ยนเป็น class ของคุณ

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['central_general_ledger_id', 'gl_account', 'company_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $encryptedLedgerId = trim($data['central_general_ledger_id']);
            $gl_account = trim($data['gl_account']);
            $encryptedCompanyId = trim($data['company_id']);

            // Create an instance of the CentralGeneralLedgers class
            $centralGeneralLedgers = new CentralGeneralLedgers();
            $result = $centralGeneralLedgers->updateCentralGeneralLedger(
                $encryptedLedgerId,
                $gl_account,
                $encryptedCompanyId
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Central General Ledger updated successfully"]);
            } else {
                throw new Exception("Error updating Central General Ledger.");
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
