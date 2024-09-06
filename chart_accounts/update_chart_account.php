<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/chart_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['chart_account_id', 'chart_account_code', 'language', 'account_length', 'collection_control', 'chart_account_group'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $chart_account_id = trim($data['chart_account_id']);
            $chart_account_code = isset($data['chart_account_code']) ? trim($data['chart_account_code']) : '';
            $language = isset($data['language']) ? trim($data['language']) : '';
            $account_length = isset($data['account_length']) ? trim($data['account_length']) : '';
            $collection_control = isset($data['collection_control']) ? trim($data['collection_control']) : '';
            $chart_account_group = isset($data['chart_account_group']) ? trim($data['chart_account_group']) : '';
            $suspend = isset($data['suspend']) ? trim($data['suspend']) : 'false';

            $chartAccounts = new ChartAccounts();
            $result = $chartAccounts->updateChartAccount(
                $chart_account_id,
                $chart_account_code,
                $language,
                $account_length,
                $collection_control,
                $chart_account_group,
                $suspend
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Chart Account updated successfully"]);
            } else {
                throw new Exception("Error updating Chart Account.");
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
