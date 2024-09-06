<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../auth/authorization.php';
include_once '../class/companies.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // กำหนดฟิลด์ที่จำเป็นต้องมี
        $required_fields = ['company_id'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        if (empty($missing_fields)) {
            // รับค่าที่ส่งมาจาก POST request
            $company_id = trim($data['company_id']);
            $chart_account_id = isset($data['chart_account_id']) ? trim($data['chart_account_id']) : null;
            $chart_account_country = isset($data['chart_account_country']) ? trim($data['chart_account_country']) : '';
            $company_second = isset($data['company_second']) ? trim($data['company_second']) : '';
            $fm_zone = isset($data['fm_zone']) ? trim($data['fm_zone']) : '';
            $credit_control_zone = isset($data['credit_control_zone']) ? trim($data['credit_control_zone']) : '';
            $fiscal_year_id = isset($data['fiscal_year_id']) ? trim($data['fiscal_year_id']) : null;
            $non_system_company_code = isset($data['non_system_company_code']) ? $data['non_system_company_code'] : 'false';
            $company_all_code = isset($data['company_all_code']) ? trim($data['company_all_code']) : '';
            $actual_company_code = isset($data['actual_company_code']) ? $data['actual_company_code'] : 'false';
            $registration_number_vat = isset($data['registration_number_vat']) ? trim($data['registration_number_vat']) : '';
            $doc_record_view = isset($data['doc_record_view']) ? trim($data['doc_record_view']) : '';
            $field_status_set = isset($data['field_status_set']) ? trim($data['field_status_set']) : '';
            $entry_period_set = isset($data['entry_period_set']) ? trim($data['entry_period_set']) : '';
            $max_ex_rate_diff = isset($data['max_ex_rate_diff']) ? trim($data['max_ex_rate_diff']) : '';
            $sample_acc_rule_set = isset($data['sample_acc_rule_set']) ? trim($data['sample_acc_rule_set']) : '';
            $workflow_select = isset($data['workflow_select']) ? trim($data['workflow_select']) : '';
            $inflation_method = isset($data['inflation_method']) ? trim($data['inflation_method']) : '';
            $tax_currency_conv = isset($data['tax_currency_conv']) ? trim($data['tax_currency_conv']) : '';
            $co_area = isset($data['co_area']) ? trim($data['co_area']) : '';
            $current_cogs = isset($data['current_cogs']) ? trim($data['current_cogs']) : '';
            $biz_fin_stmt = isset($data['biz_fin_stmt']) ? $data['biz_fin_stmt'] : 'false';
            $fiscal_year_prop = isset($data['fiscal_year_prop']) ? $data['fiscal_year_prop'] : 'false';
            $init_val_date = isset($data['init_val_date']) ? $data['init_val_date'] : 'false';
            $no_forex_diff_lc_clear = isset($data['no_forex_diff_lc_clear']) ? $data['no_forex_diff_lc_clear'] : 'false';
            $tax_base_net_val = isset($data['tax_base_net_val']) ? $data['tax_base_net_val'] : 'false';
            $fin_asset_mgmt = isset($data['fin_asset_mgmt']) ? $data['fin_asset_mgmt'] : 'false';
            $proc_acc_proc = isset($data['proc_acc_proc']) ? $data['proc_acc_proc'] : 'false';
            $allow_neg_entry = isset($data['allow_neg_entry']) ? $data['allow_neg_entry'] : 'false';
            $cash_mgmt_enabled = isset($data['cash_mgmt_enabled']) ? $data['cash_mgmt_enabled'] : 'false';
            $net_discount_base = isset($data['net_discount_base']) ? $data['net_discount_base'] : 'false';
            $split_quantity = isset($data['split_quantity']) ? $data['split_quantity'] : 'false';

            $company = new Companies();
            $result = $company->updateCompanyAdditional(
                $company_id,
                $chart_account_id,
                $chart_account_country,
                $company_second,
                $fm_zone,
                $credit_control_zone,
                $fiscal_year_id,
                $non_system_company_code,
                $company_all_code,
                $actual_company_code,
                $registration_number_vat,
                $doc_record_view,
                $field_status_set,
                $entry_period_set,
                $max_ex_rate_diff,
                $sample_acc_rule_set,
                $workflow_select,
                $inflation_method,
                $tax_currency_conv,
                $co_area,
                $current_cogs,
                $biz_fin_stmt,
                $fiscal_year_prop,
                $init_val_date,
                $no_forex_diff_lc_clear,
                $tax_base_net_val,
                $fin_asset_mgmt,
                $proc_acc_proc,
                $allow_neg_entry,
                $cash_mgmt_enabled,
                $net_discount_base,
                $split_quantity
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Additional company information updated successfully"]);
            } else {
                throw new Exception("Error updating additional company information.");
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
