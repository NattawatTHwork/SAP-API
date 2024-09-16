<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/gl_types.php';
include_once '../class/central_general_ledgers.php';
include_once '../class/group_accounts.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required_fields = ['gl_type_id', 'group_account_id', 'central_general_ledger_id', 'type_account'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            $gl_type_id = trim($data['gl_type_id']);
            $group_account_id = trim($data['group_account_id']);
            $central_general_ledger_id = trim($data['central_general_ledger_id']);
            $type_account = trim($data['type_account']);
            $short_text = trim($data['short_text']);
            $long_text = trim($data['long_text']);
            $tradg_part = trim($data['tradg_part']);

            if ($type_account === 'BS') {
                $type_account_description = '';
            } else {
                $type_account_description = trim($data['type_account_description']);
            }

            $glTypes = new GLTypes();
            $centralLedgers = new CentralGeneralLedgers();
            $groupAccounts = new GroupAccounts();

            $ledger = $centralLedgers->getCentralGeneralLedger($central_general_ledger_id);
            if ($ledger === null) {
                throw new Exception("Central General Ledger not found.");
            }
            $gl_account = $ledger['gl_account'];

            $groupAccount = $groupAccounts->getGroupAccount($group_account_id);
            if ($groupAccount === null) {
                throw new Exception("Group Account not found.");
            }
            $account_from = $groupAccount['account_from'];
            $account_to = $groupAccount['account_to'];

            if ($gl_account < $account_from || $gl_account > $account_to) {
                http_response_code(400);
                echo json_encode(["status" => "invalid_range", "message" => "GL Account is out of the allowed range."]);
                exit();
            }

            $affectedRows = $glTypes->updateGLType(
                $gl_type_id,
                $group_account_id,
                $type_account,
                $type_account_description,
                $short_text,
                $long_text,
                $tradg_part
            );

            if ($affectedRows > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "GL Type updated successfully"]);
            } else {
                throw new Exception("Error updating GL Type.");
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
