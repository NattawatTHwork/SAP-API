<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/menus.php'; // Include the Menus class

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Create an instance of the Menus class
        $menus = new Menus();
        
        // Call the getMenuAll function
        $menuList = $menus->getMenuAll();

        if ($menuList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $menuList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No menus found."]);
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
