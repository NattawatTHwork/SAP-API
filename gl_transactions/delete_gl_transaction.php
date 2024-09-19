<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_transactions.php';  // Updated class file name

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_transaction_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_transaction_id = trim($data['gl_transaction_id']);
            $glTransactions = new GLTransactions();
            $result = $glTransactions->deleteGLTransaction($gl_transaction_id);

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "GL transaction deleted successfully"]);
            } else {
                throw new Exception("Error deleting GL transaction.");
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
