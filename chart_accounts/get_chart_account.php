<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/chart_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['chart_account_id'])) {
            $chart_account_id = trim($_GET['chart_account_id']);
            if (!empty($chart_account_id)) {
                $chartAccounts = new ChartAccounts();
                $chartAccount = $chartAccounts->getChartAccount($chart_account_id);

                if ($chartAccount) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $chartAccount]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Chart Account not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Chart Account ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Chart Account ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
