<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_interest_bank_creations.php'; // เปลี่ยนเป็นไฟล์ที่มี class GLInterestBankCreations

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_interest_bank_creation_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_interest_bank_creation_id = isset($data['gl_interest_bank_creation_id']) ? trim($data['gl_interest_bank_creation_id']) : '';
            $field_status_group = isset($data['field_status_group']) ? trim($data['field_status_group']) : '';
            $automatic_posting_only = isset($data['automatic_posting_only']) ? trim($data['automatic_posting_only']) : 'false';
            $automatic_incremental_posting = isset($data['automatic_incremental_posting']) ? trim($data['automatic_incremental_posting']) : 'false';
            $reconciliation_account_input = isset($data['reconciliation_account_input']) ? trim($data['reconciliation_account_input']) : 'false';
            $planning_level = isset($data['planning_level']) ? trim($data['planning_level']) : '';
            $cash_flow_related = isset($data['cash_flow_related']) ? trim($data['cash_flow_related']) : 'false';
            $commitment_item = isset($data['commitment_item']) ? trim($data['commitment_item']) : '';
            $correspondent_bank = isset($data['correspondent_bank']) ? trim($data['correspondent_bank']) : '';
            $account_number = isset($data['account_number']) ? trim($data['account_number']) : '';
            $interest_indicator = isset($data['interest_indicator']) ? trim($data['interest_indicator']) : '';
            $interest_calculation_frequency = isset($data['interest_calculation_frequency']) ? trim($data['interest_calculation_frequency']) : '';
            $last_interest_calculation_date_key = isset($data['last_interest_calculation_date_key']) ? trim($data['last_interest_calculation_date_key']) : '';
            $last_interest_calculation_date = isset($data['last_interest_calculation_date']) ? trim($data['last_interest_calculation_date']) : '';

            $glInterestBankCreations = new GLInterestBankCreations();
            $affectedRows = $glInterestBankCreations->updateGLInterestBankCreation(
                $gl_interest_bank_creation_id,
                $field_status_group,
                $automatic_posting_only,
                $automatic_incremental_posting,
                $reconciliation_account_input,
                $planning_level,
                $cash_flow_related,
                $commitment_item,
                $correspondent_bank,
                $account_number,
                $interest_indicator,
                $interest_calculation_frequency,
                $last_interest_calculation_date_key,
                $last_interest_calculation_date
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "GL Interest Bank Creation updated successfully"]);
            } else {
                throw new Exception("Error updating GL Interest Bank Creation.");
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
