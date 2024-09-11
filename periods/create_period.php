<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/periods.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['period_code', 'period_group_id', 'number_month', 'number_day', 'change_year'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $period_code = trim($data['period_code']);
            $period_group_id = trim($data['period_group_id']);
            $number_month = trim($data['number_month']);
            $number_day = trim($data['number_day']);
            $change_year = trim($data['change_year']);
            $text_period_en = isset($data['text_period_en']) ? trim($data['text_period_en']) : '';
            $text_period_th = isset($data['text_period_th']) ? trim($data['text_period_th']) : '';

            $periods = new Periods();
            $periodId = $periods->createPeriod(
                $period_code,
                $period_group_id,
                $number_month,
                $number_day,
                $change_year,
                $text_period_en,
                $text_period_th
            );

            if ($periodId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Period created successfully", "period_id" => $periodId]);
            } else {
                throw new Exception("Error creating period.");
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
