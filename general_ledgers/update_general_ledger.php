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

        $required_fields = ['general_ledger_id', 'document_date', 'posting_date', 'company_id', 'document_type_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $general_ledger_id = trim($data['general_ledger_id']);
            $updated_by = trim($data['updated_by']);
            $company_id = trim($data['company_id']);
            $document_date = validateDate($data['document_date']) ? $data['document_date'] : date('Y-m-d');
            $posting_date = validateDate($data['posting_date']) ? $data['posting_date'] : null;
            $reference = isset($data['reference']) ? trim($data['reference']) : '';
            $document_header_text = isset($data['document_header_text']) ? trim($data['document_header_text']) : '';
            $document_type_id = isset($data['document_type_id']) ? trim($data['document_type_id']) : '';
            $branch_number_id = isset($data['branch_number_id']) ? trim($data['branch_number_id']) : '';
            $currency_id = isset($data['currency_id']) ? trim($data['currency_id']) : '';
            $exchange_rate = isset($data['exchange_rate']) ? trim($data['exchange_rate']) : '';
            $translatn_date = validateDate($data['translatn_date']) ? $data['translatn_date'] : null;
            $trading_part_ba = isset($data['trading_part_ba']) ? trim($data['trading_part_ba']) : '';
            $calculate_tax = isset($data['calculate_tax']) ? trim($data['calculate_tax']) : 'false';

            $generalLedgers = new GeneralLedgers();
            $generalLedgerId = $generalLedgers->updateGeneralLedger(
                $general_ledger_id,
                $company_id,
                $document_date,
                $posting_date,
                $reference,
                $document_header_text,
                $document_type_id,
                $branch_number_id,
                $currency_id,
                $exchange_rate,
                $translatn_date,
                $trading_part_ba,
                $calculate_tax,
                $updated_by
            );

            if ($generalLedgerId) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "General Ledger updated successfully",
                    "general_ledger_id" => $generalLedgerId
                ]);
                
            } else {
                throw new Exception("Error creating General Ledger.");
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
