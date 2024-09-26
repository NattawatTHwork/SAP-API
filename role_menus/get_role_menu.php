<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/role_menus.php'; // Include RoleMenus class

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if role_id is provided in the GET request
        if (!isset($_GET['role_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Role ID is required."]);
            exit;
        }

        $role_id = $_GET['role_id']; // Get the role_id from the request

        // Instantiate the RoleMenus class
        $roleMenus = new RoleMenus();
        
        // Call the getRoleMenu method with the provided role_id
        $roleMenuList = $roleMenus->getRoleMenuEncrypte($role_id);

        // Check if any role-menu mappings are found
        if ($roleMenuList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $roleMenuList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No role menus found."]);
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
