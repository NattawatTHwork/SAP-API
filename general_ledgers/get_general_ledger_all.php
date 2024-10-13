<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/general_ledgers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // ใช้ POST เพื่อรับข้อมูลการค้นหา
        $data = json_decode(file_get_contents('php://input'), true);

        $generalLedgers = new GeneralLedgers();

        // รับค่าการค้นหาจาก POST
        $searchParams = [
            'company_id' => isset($data['company_id']) ? $data['company_id'] : null,
            'document_date' => isset($data['document_date']) ? $data['document_date'] : null,
            'posting_date' => isset($data['posting_date']) ? $data['posting_date'] : null,
            'document_type_id' => isset($data['document_type_id']) ? $data['document_type_id'] : null,
            'reference' => isset($data['reference']) ? $data['reference'] : null,
            'document_header_text' => isset($data['document_header_text']) ? $data['document_header_text'] : null,
            'currency_id' => isset($data['currency_id']) ? $data['currency_id'] : null,
            'branch_number_id' => isset($data['branch_number_id']) ? $data['branch_number_id'] : null
        ];

        // echo json_encode(["status" => "error", "message" => $data]);
        // exit;


        // ส่งค่าการค้นหาไปยังฟังก์ชัน getAllGeneralLedgers()
        $ledgerList = $generalLedgers->getAllGeneralLedgers($searchParams);

        if ($ledgerList) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $ledgerList]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No general ledgers found."]);
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
