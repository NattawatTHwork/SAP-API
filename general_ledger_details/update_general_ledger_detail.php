<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/general_ledger_details.php'; // Change to the file with GeneralLedgerDetails class

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['general_ledger_detail_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $general_ledger_detail_id = trim($data['general_ledger_detail_id']);
            $exchange_rate = isset($data['exchange_rate']) ? trim($data['exchange_rate']) : '';
            $translatn_date = validateDate($data['translatn_date']) ? $data['translatn_date'] : null;
            $trading_part_ba = isset($data['trading_part_ba']) ? trim($data['trading_part_ba']) : '';
            $calculate_tax = isset($data['calculate_tax']) ? trim($data['calculate_tax']) : 'false';

            $generalLedgerDetails = new GeneralLedgerDetails();
            $affectedRows = $generalLedgerDetails->updateGeneralLedgerDetail(
                $general_ledger_detail_id,
                $exchange_rate,
                $translatn_date,
                $trading_part_ba,
                $calculate_tax
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "General Ledger Detail updated successfully"]);
            } else {
                throw new Exception("Error updating General Ledger Detail.");
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
