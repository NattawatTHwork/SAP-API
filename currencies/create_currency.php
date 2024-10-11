<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/currencies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Define the required field
        $required_fields = ['currency_code'];
        $missing_fields = [];

        // Check if the required field is present and not empty
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // If no missing fields, proceed to create currency
        if (empty($missing_fields)) {
            $currency_code = trim($data['currency_code']);

            // Instantiate Currencies class and create the currency
            $currencies = new Currencies();
            $currencyId = $currencies->createCurrency($currency_code);

            if ($currencyId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Currency created successfully", "currency_id" => $currencyId]);
            } else {
                throw new Exception("Error creating currency.");
            }
        } else {
            // Throw error if any required fields are missing
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        // Throw error for invalid HTTP method
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
