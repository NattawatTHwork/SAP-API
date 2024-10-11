<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/branch_numbers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['branch_number_id'])) {
            $branch_number_id = trim($_GET['branch_number_id']);
            if (!empty($branch_number_id)) {
                $branchNumbers = new BranchNumbers(); 
                $branchNumber = $branchNumbers->getBranchNumber($branch_number_id);

                if ($branchNumber) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "data" => $branchNumber]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Branch number not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Branch number ID is empty."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Branch number ID is missing."]);
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
