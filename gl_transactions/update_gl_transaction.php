<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_transactions.php';  // Updated class file name

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_transaction_id', 'central_general_ledger_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_transaction_id = trim($data['gl_transaction_id']);
            $central_general_ledger_id = trim($data['central_general_ledger_id']);
            $calculate_tax = isset($data['calculate_tax']) ? trim($data['calculate_tax']) : 'false';
            $dc_type = isset($data['dc_type']) ? trim($data['dc_type']) : '';
            $amount = isset($data['amount']) ? trim($data['amount']) : '';
            $business_stablishment = isset($data['business_stablishment']) ? trim($data['business_stablishment']) : '';
            $business_type_id = isset($data['business_type_id']) ? trim($data['business_type_id']) : '';
            $determination = isset($data['determination']) ? trim($data['determination']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';

            $glTransactions = new GLTransactions();
            $result = $glTransactions->updateGLTransaction(
                $gl_transaction_id,
                $central_general_ledger_id,
                $calculate_tax,
                $dc_type,
                $amount,
                $business_stablishment,
                $business_type_id,
                $determination,
                $description
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "GL transaction updated successfully"]);
            } else {
                throw new Exception("Error updating GL transaction.");
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
