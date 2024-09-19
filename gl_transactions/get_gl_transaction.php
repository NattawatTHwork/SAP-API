<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_transactions.php';  // Updated class file name

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['gl_transaction_id'])) {
            $gl_transaction_id = trim($_GET['gl_transaction_id']);
            if (!empty($gl_transaction_id)) {
                $glTransactions = new GLTransactions();
                $glTransaction = $glTransactions->getGLTransaction($gl_transaction_id);

                if ($glTransaction) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $glTransaction]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "GL transaction not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "GL transaction ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "GL transaction ID is missing."]);
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
