<?php
ini_set('display_errors', 0);      
ini_set('log_errors', 1);          
error_reporting(0);
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
        'mgmt' => 4,
        'SPA'  => 7
    };

    $audited_asset_json = json_encode($audit_data);
    $auditor = $_SESSION['email'];
    try {
        $get_curr_audit_q = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
        $get_id_stmt = $dbh->query($get_curr_audit_q);
        $id_results = $get_id_stmt->fetch(PDO::FETCH_ASSOC);

        $check_recent_audits = "SELECT dept_id, audit_id FROM audit_history WHERE audit_id = :audit_id AND dept_id = :dept_id";
        $check_stmt = $dbh->prepare($check_recent_audits);

        if (isset($_SESSION['info'][5])) {
            $id = $_SESSION['info'][5];
        } else {
            if ($audit_id === 1) {
                $id = $id_results['curr_self_id'];
            } else if ($audit_id === 4) {
                $id = $id_results['curr_mgmt_id'];
            } else {
                $id = $id_results['curr_spa_id'];
            }
        }

        $check_stmt->execute([":dept_id" => $dept, ":audit_id" => $id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            try {
                $update_q = "UPDATE audit_history SET finished_at = CURRENT_TIMESTAMP, auditor = :auditor, audit_data = :audit_data WHERE audit_id = :audit_id AND dept_id = :dept_id";
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([":audit_id" => $id, ":dept_id" => $result['dept_id'], ":auditor" => $auditor, ":audit_data" => $audited_asset_json]);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'failure', "Message" => 'Fail on update ' . $e->getMessage()]);
                exit;
            }
            echo json_encode(['status' => 'success', 'Message' => 'Updated audit id ' . $audit_id]);
            exit;
        } else {
            try {
                $insert_q = "INSERT INTO audit_history (dept_id, audit_id, auditor, audit_data) VALUES (?, ?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$dept, $audit_id, $auditor, $audited_asset_json]);

                echo json_encode(['status' => 'success', 'message' => 'Insert audit id' . $audit_id]);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'failed', 'Error on Insert' => $e->getMessage(),]);
                exit;
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'failed', 'Error on select' => $e->getMessage()]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'failed', 'Error' => $e->getMessage()]);
    exit;
}

