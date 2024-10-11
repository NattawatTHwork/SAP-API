<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/branch_numbers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['branch_number_id', 'branch_number_code'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $branch_number_id = trim($data['branch_number_id']);
            $branch_number_code = trim($data['branch_number_code']);

            $branchNumbers = new BranchNumbers();
            $result = $branchNumbers->updateBranchNumber($branch_number_id, $branch_number_code);

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Branch number updated successfully"]);
            } else {
                throw new Exception("Error updating branch number.");
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
