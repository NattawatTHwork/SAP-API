<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_transactions.php';  // Updated class file name

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['general_ledger_id'])) {
            $general_ledger_id = trim($_GET['general_ledger_id']);
            if (!empty($general_ledger_id)) {
                $glTransactions = new GLTransactions();
                $allGLTransactions = $glTransactions->getGLTransactionAll($general_ledger_id);

                if ($allGLTransactions) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $allGLTransactions]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "No GL transactions found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "General Ledger ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "General Ledger ID is missing."]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
