<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/period_groups.php'; // Assuming you have a class for handling period groups

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['period_group_code', 'description']; // Adjust the fields for period groups
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $period_group_code = trim($data['period_group_code']);
            $description = trim($data['description']);

            $periodGroup = new PeriodGroups(); // Create instance of PeriodGroups class
            $periodGroupId = $periodGroup->createPeriodGroup(
                $period_group_code,
                $description
            );

            if ($periodGroupId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Period group created successfully", "period_group_id" => $periodGroupId]);
            } else {
                throw new Exception("Error creating period group.");
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
