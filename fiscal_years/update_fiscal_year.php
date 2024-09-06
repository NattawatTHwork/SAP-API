<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/fiscal_years.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['fiscal_year_id', 'fiscal_year_code', 'description'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $fiscal_year_id = trim($data['fiscal_year_id']);
            $fiscal_year_code = trim($data['fiscal_year_code']);
            $description = trim($data['description']);
            $fiscal_year_check = isset($data['fiscal_year_check']) ? trim($data['fiscal_year_check']) : false;
            $calendar_year_check = isset($data['calendar_year_check']) ? trim($data['calendar_year_check']) : false;

            $fiscalYears = new FiscalYears();
            $result = $fiscalYears->updateFiscalYear(
                $fiscal_year_id,
                $fiscal_year_code,
                $description,
                $fiscal_year_check,
                $calendar_year_check
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Fiscal Year updated successfully"]);
            } else {
                throw new Exception("Error updating Fiscal Year.");
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
