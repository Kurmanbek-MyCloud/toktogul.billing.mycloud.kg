<?php

function Calc_debt($ws_entity) {
    global $adb;

    // WS id
    $ws_id = $ws_entity->getId();
    $module = $ws_entity->getModuleName();
    // echo '<pre>';
    // var_dump($module);
    // var_dump($ws_entity);
    // echo '</pre>';
    // exit();
    if (empty($ws_id) || empty($module)) {
        return;
    }

    $crmid = vtws_getCRMEntityId($ws_id);
    if ($crmid <= 0) {
        return;
    }

    if ($module == 'SPPayments') {
        if ($ws_entity->get("cf_1416") != null) {
            $flat_id = explode("x", $ws_entity->get("cf_1416"))[1];
        } else {
            return;
        }
        updateDebt($flat_id, $adb);
    }
    if ($module == 'Invoice') {
        if ($ws_entity->get("cf_1265") != null) {
            $flat_id = explode("x", $ws_entity->get("cf_1265"))[1];
        } else {
            return;
        }
        updateDebt($flat_id, $adb);
    }
    if ($module == 'Penalty') {
        if ($ws_entity->get("cf_to_ivoice") != null) {
            $invoice_id = explode("x", $ws_entity->get("cf_to_ivoice"))[1];
            $flat_id = $adb->run_query_field("SELECT cf_1265 FROM vtiger_invoicecf 
                                    WHERE invoiceid = $invoice_id");
            updateDebt($flat_id, $adb);
        } else {
            return;
        }
        updateDebt($flat_id, $adb);
    }
    if ($module == 'Flats') {
        $flat_id = $crmid;
        updateDebt($flat_id, $adb);
    }
}

function updateDebt($flat_id, $adb) {
    $sql = "SELECT sum(a.total) FROM vtiger_invoice as a 
                INNER JOIN vtiger_crmentity as b ON b.crmid=a.invoiceid 
                INNER JOIN vtiger_invoicecf as c ON b.crmid=c.invoiceid 
                WHERE b.deleted=0 and c.cf_1265=$flat_id";
    $invsum = $adb->run_query_field($sql);

    $sql2 = "SELECT sum(a.amount) FROM sp_payments as a 
                INNER JOIN vtiger_crmentity as b ON b.crmid=a.payid 
                INNER JOIN sp_paymentscf as c ON b.crmid=c.payid 
                WHERE b.deleted=0 and c.cf_1416=$flat_id";
    $paysum = $adb->run_query_field($sql2);

    $sql3 = "SELECT SUM(vp.penalty_amount) FROM vtiger_penalty vp 
                INNER JOIN vtiger_invoicecf vi ON vp.cf_to_ivoice = vi.invoiceid 
                INNER JOIN vtiger_crmentity vc ON vc.crmid = vp.penaltyid 
                WHERE vc.deleted = 0
                AND vi.cf_1265 = $flat_id";
    $penaltysum = $adb->run_query_field($sql3);
    $res = round($penaltysum + $invsum - $paysum, 2);

    $adb->run_query_field("UPDATE vtiger_flatscf set cf_1289='$res' WHERE flatsid='$flat_id'");
}
?>