<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/period_groups.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['period_group_id', 'period_group_code', 'description'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $period_group_id = trim($data['period_group_id']);
            $period_group_code = trim($data['period_group_code']);
            $description = trim($data['description']);

            $periodGroups = new PeriodGroups();
            $result = $periodGroups->updatePeriodGroup(
                $period_group_id,
                $period_group_code,
                $description,
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Period Group updated successfully"]);
            } else {
                throw new Exception("Error updating Period Group.");
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
