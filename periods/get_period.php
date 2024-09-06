<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/periods.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['period_id'])) {
            $period_id = trim($_GET['period_id']);
            if (!empty($period_id)) {
                $periods = new Periods();
                $period = $periods->getPeriod($period_id);

                if ($period) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $period]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Period not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Period ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Period ID is missing."]);
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
