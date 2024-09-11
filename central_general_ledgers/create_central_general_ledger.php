<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_account', 'company_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_account = trim($data['gl_account']);
            $company_id = trim($data['company_id']);

            $centralGeneralLedgers = new CentralGeneralLedgers();
            $centralGeneralLedgerId = $centralGeneralLedgers->createCentralGeneralLedger($gl_account, $company_id);

            if ($centralGeneralLedgerId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Central General Ledger created successfully", "central_general_ledger_id" => $centralGeneralLedgerId]);
            } else {
                throw new Exception("Error creating Central General Ledger.");
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
