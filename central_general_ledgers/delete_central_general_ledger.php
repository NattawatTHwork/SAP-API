<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php'; // เปลี่ยนเป็นไฟล์ที่มี class CentralGeneralLedgers
include_once '../class/gl_types.php'; // เปลี่ยนเป็นไฟล์ที่มี class GLTypes
include_once '../class/gl_control_datas.php'; // เพิ่มการนำเข้าของ ControlData
include_once '../class/gl_interest_bank_creations.php'; // เพิ่มการนำเข้าของ GLInterestBankCreations
include_once '../class/gl_ca_datas.php'; // เพิ่มการนำเข้าของ GLCAData

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['central_general_ledger_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $centralGeneralLedgerId = trim($data['central_general_ledger_id']);
            $centralGeneralLedgers = new CentralGeneralLedgers();
            $glTypes = new GLTypes();
            $controlData = new ControlData(); // สร้าง instance ของ ControlData
            $glInterestBankCreations = new GLInterestBankCreations(); // สร้าง instance ของ GLInterestBankCreations
            $glCAData = new GLCAData(); // สร้าง instance ของ GLCAData

            // Delete Central General Ledger
            $result = $centralGeneralLedgers->deleteCentralGeneralLedger($centralGeneralLedgerId);

            if ($result) {
                // Delete GL Types associated with the Central General Ledger
                $resultGLType = $glTypes->deleteGLType($centralGeneralLedgerId);

                if ($resultGLType > 0) {
                    // Delete Control Data associated with the Central General Ledger
                    $resultControlData = $controlData->deleteControlData($centralGeneralLedgerId);

                    if ($resultControlData > 0) {
                        // Delete GL Interest Bank Creation associated with the Central General Ledger
                        $resultGLInterestBankCreation = $glInterestBankCreations->deleteGLInterestBankCreation($centralGeneralLedgerId);

                        if ($resultGLInterestBankCreation > 0) {
                            // Delete GL CA Data associated with the Central General Ledger
                            $resultGLCAData = $glCAData->deleteGLCAData($centralGeneralLedgerId);

                            if ($resultGLCAData > 0) {
                                http_response_code(200);
                                echo json_encode([
                                    "status" => "success",
                                    "message" => "Central General Ledger, associated GL Types, Control Data, GL Interest Bank Creation, and GL CA Data deleted successfully"
                                ]);
                            } else {
                                throw new Exception("Error deleting GL CA Data associated with Central General Ledger.");
                            }
                        } else {
                            throw new Exception("Error deleting GL Interest Bank Creation associated with Central General Ledger.");
                        }
                    } else {
                        throw new Exception("Error deleting Control Data associated with Central General Ledger.");
                    }
                } else {
                    throw new Exception("Error deleting GL Types associated with Central General Ledger.");
                }
            } else {
                throw new Exception("Error deleting Central General Ledger.");
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
