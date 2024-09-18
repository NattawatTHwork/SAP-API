<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/general_ledgers.php'; // นำเข้าคลาส GeneralLedgers

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['general_ledger_id'])) {
            $general_ledger_id = trim($_GET['general_ledger_id']);

            if (!empty($general_ledger_id)) {
                $generalLedgers = new GeneralLedgers();
                $generalLedger = $generalLedgers->getGeneralLedger($general_ledger_id);

                if ($generalLedger) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $generalLedger]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "General Ledger not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Central General Ledger ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Central General Ledger ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
