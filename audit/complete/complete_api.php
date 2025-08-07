<?php
ini_set('display_errors', 0);      
ini_set('log_errors', 1);          
error_reporting(E_ALL);
header("Content-Type: application/json");
try {
    header("Access-Control-Allow-Headers: Content-Type");
    require_once "../../config.php";

    check_auth('high');

    $result = json_decode(file_get_contents("php://input"), true);
    $content_type = $_SERVER["CONTENT_TYPE"] ?? '';
    $audit_data = $_SESSION['data'];
    $audit_type = $_SESSION['info'][3];
    $dept = $_SESSION['data'][0]["Dept"];

    $audit_id = match ($audit_type) {
        'cust' => 1,
        'mgmt' => 3,
        'SPA'  => 5,
        'FDN'  => 7
    };

    $audited_asset_json = json_encode($audit_data, true);
    $auditor = $_SESSION['email'];
    try {
        $check_recent_audits = "SELECT dept_id, audit_id FROM audit_history WHERE extract(YEAR from finished_at) = extract(YEAR FROM CURRENT_TIMESTAMP) AND dept_id = :dept_id AND audit_id = :audit_id";
        $check_stmt = $dbh->prepare($check_recent_audits);
        $check_stmt->execute([":dept_id" => $dept, ":audit_id" => $audit_id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $found_current_year = false;
        if ($result) {
            $found_current_year = true;
            $update_q = "UPDATE audit_history SET finished_at = CURRENT_TIMESTAMP, auditor = :auditor, audit_data = :audit_data WHERE audit_id = :audit_id AND dept_id = :dept_id";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":audit_id" => $audit_id, ":dept_id" => $result['dept_id'], ":auditor" => $auditor, ":audit_data" => $audited_asset_json]);

            echo json_encode(['status' => 'success', 'Message' => 'Updated audit id ' . $audit_id]);
            exit;
        } else {
            $start = $audit_id;
            $end = $audit_id + 1;
            $get_audit_ids = "SELECT audit_id FROM audit_history WHERE dept_id = :dept_id AND audit_id BETWEEN :start AND :end ORDER BY finished_at";
            $check_stmt = $dbh->prepare($get_audit_ids);
            $check_stmt->execute([':dept_id' => $dept, ":start" => $start, ":end" => $end]);
            $id_results = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($id_results) > 1) {
                $audit_id = (int)$id_results[0]['audit_id'];
            } else if (count($id_results) > 0) {
                $audit_id = $id_results[0]['audit_id'] == $audit_id ? $audit_id + 1 : $audit_id;
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'failed', 'Error on select' => $e->getMessage()]);
        exit;
    }
    try {
        $insert_q = "INSERT INTO audit_history (dept_id, audit_id, auditor, audit_data) VALUES (?, ?, ?, ?)";
        $insert_stmt = $dbh->prepare($insert_q);
        $insert_stmt->execute([$dept, $audit_id, $auditor, $audited_asset_json]);

        echo json_encode(['status' => 'success', 'message' => 'Insert audit id' . $audit_id]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'failed', 'Error on Insert' => $e->getMessage(),]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'failed', 'Error' => $e->getMessage()]);
    exit;
}

echo json_encode(['status' => 'success']);
exit;

