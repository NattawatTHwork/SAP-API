<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_control_datas.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_control_data_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_control_data_id = isset($data['gl_control_data_id']) ? trim($data['gl_control_data_id']) : '';
            $account_currency = isset($data['account_currency']) ? trim($data['account_currency']) : '';
            $domestic_currency_balance = isset($data['domestic_currency_balance']) ? trim($data['domestic_currency_balance']) : 'false';
            $exchange_rate_difference_key = isset($data['exchange_rate_difference_key']) ? trim($data['exchange_rate_difference_key']) : '';
            $valuation_group = isset($data['valuation_group']) ? trim($data['valuation_group']) : '';
            $tax_category = isset($data['tax_category']) ? trim($data['tax_category']) : '';
            $post_without_tax = isset($data['post_without_tax']) ? trim($data['post_without_tax']) : 'false';
            $reconciliation_account_type = isset($data['reconciliation_account_type']) ? trim($data['reconciliation_account_type']) : '';
            $alternate_account_number = isset($data['alternate_account_number']) ? trim($data['alternate_account_number']) : '';
            $externally_managed_account = isset($data['externally_managed_account']) ? trim($data['externally_managed_account']) : 'false';
            $inflation_key = isset($data['inflation_key']) ? trim($data['inflation_key']) : '';
            $acceptance_range_group = isset($data['acceptance_range_group']) ? trim($data['acceptance_range_group']) : '';
            $open_item_management = isset($data['open_item_management']) ? trim($data['open_item_management']) : 'false';
            $display_line_items = isset($data['display_line_items']) ? trim($data['display_line_items']) : 'false';
            $sorting_key = isset($data['sorting_key']) ? trim($data['sorting_key']) : '';
            $authorization_group = isset($data['authorization_group']) ? trim($data['authorization_group']) : '';

            $controlData = new ControlData();
            $affectedRows = $controlData->updateControlData(
                $gl_control_data_id,
                $account_currency,
                $domestic_currency_balance,
                $exchange_rate_difference_key,
                $valuation_group,
                $tax_category,
                $post_without_tax,
                $reconciliation_account_type,
                $alternate_account_number,
                $externally_managed_account,
                $inflation_key,
                $acceptance_range_group,
                $open_item_management,
                $display_line_items,
                $sorting_key,
                $authorization_group
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Control Data updated successfully"]);
            } else {
                throw new Exception("Error updating Control Data.");
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
