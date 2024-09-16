<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/central_general_ledgers.php';
include_once '../class/gl_types.php'; // เพิ่มการนำเข้าของ GLTypes
include_once '../class/gl_control_datas.php';
include_once '../class/gl_interest_bank_creations.php'; // เพิ่มการนำเข้าของ GLInterestBankCreations

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_account', 'company_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_account = trim($data['gl_account']);
            $company_id = trim($data['company_id']);

            // สร้าง Central General Ledger
            $centralGeneralLedgers = new CentralGeneralLedgers();
            $centralGeneralLedgerId = $centralGeneralLedgers->createCentralGeneralLedger($gl_account, $company_id);

            if ($centralGeneralLedgerId) {
                // ใช้ ID ที่ได้รับเพื่อสร้าง GL Type
                $glTypes = new GLTypes(); // สร้าง instance ของ GLTypes
                $glTypeId = $glTypes->createGLType($centralGeneralLedgerId);

                if ($glTypeId) {
                    // สร้าง Control Data
                    $controlData = new ControlData(); // สร้าง instance ของ ControlData
                    $controlDataId = $controlData->createControlData($centralGeneralLedgerId);

                    if ($controlDataId) {
                        // สร้าง GL Interest Bank Creation
                        $glInterestBankCreations = new GLInterestBankCreations(); // สร้าง instance ของ GLInterestBankCreations
                        $glInterestBankCreationId = $glInterestBankCreations->createGLInterestBankCreation($centralGeneralLedgerId);

                        if ($glInterestBankCreationId) {
                            http_response_code(201);
                            echo json_encode([
                                "status" => "success",
                                "message" => "Central General Ledger, GL Type, Control Data, and GL Interest Bank Creation created successfully",
                                "central_general_ledger_id" => $centralGeneralLedgerId,
                                "gl_type_id" => $glTypeId,
                                "control_data_id" => $controlDataId,
                                "gl_interest_bank_creation_id" => $glInterestBankCreationId
                            ]);
                        } else {
                            throw new Exception("Error creating GL Interest Bank Creation.");
                        }
                    } else {
                        throw new Exception("Error creating Control Data.");
                    }
                } else {
                    throw new Exception("Error creating GL Type.");
                }
            } else {
                throw new Exception("Error creating Central General Ledger.");
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
