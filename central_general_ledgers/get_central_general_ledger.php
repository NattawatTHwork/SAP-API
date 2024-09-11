<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php'; // เปลี่ยนเป็น class ของคุณ

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['central_general_ledger_id'])) {
            $encryptedLedgerId = trim($_GET['central_general_ledger_id']);
            if (!empty($encryptedLedgerId)) {
                $centralGeneralLedgers = new CentralGeneralLedgers();
                $ledger = $centralGeneralLedgers->getCentralGeneralLedger($encryptedLedgerId);

                if ($ledger) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $ledger]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Central general ledger not found."]);
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
?>
