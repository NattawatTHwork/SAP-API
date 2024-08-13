<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/menus.php';
include_once '../class/role_menus.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['role_id', 'menu_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $role_id = trim($data['role_id']);
            $menu_id = $data['menu_id'];

            $menus = new Menus();
            $menuIds = $menus->getMenuAll();
            $menuList = array_column($menuIds, 'menu_id');
            $menuList = array_map('strval', $menuList);

            $roleMenus = new RoleMenus();

            foreach ($menuList as $menu) {
                if (in_array($menu, $menu_id)) {
                    if ($roleMenus->checkRoleMenu($role_id, $menu)) {
                        $roleMenus->updateRoleMenu($role_id, $menu, 'false'); // Update the record to ensure it's not deleted
                    } else {
                        $roleMenus->createRoleMenu($role_id, $menu); // Create a new record
                    }
                } else {
                    if ($roleMenus->checkRoleMenu($role_id, $menu)) {
                        $roleMenus->updateRoleMenu($role_id, $menu, 'true'); // Mark the record as deleted
                    }
                }
            }

            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Role menus updated successfully"]);
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
