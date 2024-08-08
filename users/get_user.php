<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/users.php';

try {
    $users = new Users();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['user_id'])) {
            $user_id = trim($_GET['user_id']);  // Check the value passed

            if (!empty($user_id)) {
                $user = $users->getUserById($user_id);

                if ($user) {
                    http_response_code(200);
                    echo json_encode(array('status' => 'success', 'data' => $user));
                } else {
                    http_response_code(404);
                    echo json_encode(array('status' => 'error', 'message' => 'User not found.'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('status' => 'error', 'message' => 'User ID cannot be empty.'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('status' => 'error', 'message' => 'User ID is missing.'));
        }
    } else {
        http_response_code(405);
        echo json_encode(array('status' => 'error', 'message' => 'Method not allowed.'));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
?>
