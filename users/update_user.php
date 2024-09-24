<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/users.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['user_id', 'firstname', 'lastname', 'sysid', 'statusflag'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $user_id = trim($data['user_id']);
            $firstname = trim($data['firstname']);
            $lastname = trim($data['lastname']);
            $statusflag = trim($data['statusflag']);
            $sysid = trim($data['sysid']);

            $users = new Users();
            $result = $users->updateUser($user_id, $firstname, $lastname, $statusflag, $sysid);

            if ($result) {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "User updated successfully"));
            } else {
                throw new Exception("Error updating user.");
            }
        } else {
            throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
        }
    } else {
        throw new Exception("Method not allowed.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}
?>
