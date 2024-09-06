<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/chart_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['chart_account_code', 'language', 'account_length', 'collection_control', 'chart_account_group', 'suspend'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $chart_account_code = isset($data['chart_account_code']) ? trim($data['chart_account_code']) : '';
            $language = isset($data['language']) ? trim($data['language']) : '';
            $account_length = isset($data['account_length']) ? trim($data['account_length']) : '';
            $collection_control = isset($data['collection_control']) ? trim($data['collection_control']) : '';
            $chart_account_group = isset($data['chart_account_group']) ? trim($data['chart_account_group']) : '';
            $suspend = isset($data['suspend']) ? trim($data['suspend']) : 'false';

            $chartAccounts = new ChartAccounts();
            $chartAccountId = $chartAccounts->createChartAccount(
                $chart_account_code,
                $language,
                $account_length,
                $collection_control,
                $chart_account_group,
                $suspend
            );

            if ($chartAccountId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Chart Account created successfully", "chart_account_id" => $chartAccountId]);
            } else {
                throw new Exception("Error creating chart account.");
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
