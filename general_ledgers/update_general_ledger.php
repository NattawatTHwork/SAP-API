<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/general_ledgers.php';

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['general_ledger_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $general_ledger_id = trim($data['general_ledger_id']);
            $document_date = validateDate($data['document_date']) ? $data['document_date'] : date('Y-m-d');
            $posting_date = validateDate($data['posting_date']) ? $data['posting_date'] : null;
            $reference = isset($data['reference']) ? trim($data['reference']) : '';
            $document_header_text = isset($data['document_header_text']) ? trim($data['document_header_text']) : '';
            $document_type = isset($data['document_type']) ? trim($data['document_type']) : '';
            $intercompany_number = isset($data['intercompany_number']) ? trim($data['intercompany_number']) : '';
            $branch_number = isset($data['branch_number']) ? trim($data['branch_number']) : '';
            $currency = isset($data['currency']) ? trim($data['currency']) : '';

            $generalLedgers = new GeneralLedgers();
            $affectedRows = $generalLedgers->updateGeneralLedger(
                $general_ledger_id,
                $document_date,
                $posting_date,
                $reference,
                $document_header_text,
                $document_type,
                $intercompany_number,
                $branch_number,
                $currency
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "General Ledger updated successfully"]);
            } else {
                throw new Exception("Error updating General Ledger.");
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
