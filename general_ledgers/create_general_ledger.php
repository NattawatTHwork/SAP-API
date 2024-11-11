<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/general_ledgers.php'; // Import GeneralLedgers class
include_once '../class/gl_transactions.php'; // Import GLTransactions class
include_once '../class/document_types.php'; // Import DocumentTypes class

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['document_date', 'posting_date', 'company_id', 'document_type_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $created_by = trim($data['created_by']);
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
            $year = null;
            if (validateDate($data['posting_date'])) {
                $posting_date = $data['posting_date'];
                $year = date('Y', strtotime($posting_date)); // Extract the year from the posting date
            } else {
                $posting_date = null;
            }

            // Increment document type sequence if document_type_id is provided
            if (!empty($document_type_id)) {
                $documentTypes = new DocumentTypes();
                $newSequence = $documentTypes->incrementSequence($document_type_id);

                // Check if newSequence is valid
                if (empty($newSequence)) {
                    throw new Exception("Failed to increment sequence for document type.");
                }
            } else {
                throw new Exception("Document type ID is required.");
            }

            // Create General Ledger with the incremented sequence
            $generalLedgers = new GeneralLedgers();
            $generalLedgerId = $generalLedgers->createGeneralLedger(
                $created_by,
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
                $newSequence, // Pass the sequence to the createGeneralLedger function
                $year
            );

            if ($generalLedgerId) {
                if (isset($data['transactions']) && is_array($data['transactions']) && !empty($data['transactions'])) {
                    $glTransactions = new GLTransactions();
                    $glTransactionIds = $glTransactions->createGLTransactions(
                        $generalLedgerId,
                        $data['transactions'],
                        $newSequence // Pass the sequence to createGLTransactions
                    );

                    if (empty($glTransactionIds)) {
                        throw new Exception("Error creating GL Transactions.");
                    }

                    http_response_code(201);
                    echo json_encode([
                        "status" => "success",
                        "message" => "General Ledger and GL Transactions created successfully",
                        "general_ledger_id" => $generalLedgerId,
                        "gl_transaction_ids" => $glTransactionIds,
                        "new_sequence" => $newSequence
                    ]);
                } else {
                    http_response_code(201);
                    echo json_encode([
                        "status" => "success",
                        "message" => "General Ledger created successfully without GL Transactions",
                        "general_ledger_id" => $generalLedgerId,
                        "new_sequence" => $newSequence
                    ]);
                }
            } else {
                throw new Exception("Error creating General Ledger.");
            }
        } else {
            throw new Exception("Missing required field: " . implode(', ', $missing_fields));
        }
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
