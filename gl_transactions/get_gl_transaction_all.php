<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_transactions.php';  // Updated class file name

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $glTransactions = new GLTransactions();
        $glTransactionList = $glTransactions->getGLTransactionAll();

        if ($glTransactionList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $glTransactionList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No GL transactions found."]);
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
