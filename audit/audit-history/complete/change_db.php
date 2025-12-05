<?php

require_once "../../../config.php";
$encoded_data = file_get_contents("php://input");
$data = json_decode($encoded_data, true);

if (!empty($data)) {
    try {
        $select = 'SELECT dept_id, dept_name, custodian[1] as cust, dept_manager FROM department WHERE dept_id = :dept';
        $stmt = $dbh->prepare($select);
        $stmt->execute([':dept' => $data['dept_id']]);
        $dept_data = $stmt->fetch();
        if (!isset($dept_data['dept_id'])) {
            echo json_encode(['status'=>'failed', 'reason'=>'Department does not exist']);
            exit;
        }
        if (empty($dept_data['dept_manager'])) {
            echo json_encode(['status'=>'failed', 'reason'=>'Manager not set']);
            exit;
        }
        if (empty($dept_data['cust'])) {
            echo json_encode(['status'=>'failed', 'reason'=>'Custodian not set']);
            exit;
        }
        $update = "UPDATE audit_history SET forms_submitted = true WHERE audit_id = :id AND dept_id = :dept";
        $update_stmt = $dbh->prepare($update);
        $update_stmt->execute([":id"=>$data['audit_id'], ":dept"=>$data['dept_id']]);
        echo json_encode(['status'=>'Ok']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status'=>'failed', 'reason'=>'database failed']);
        exit;
    }
} else {
    echo json_encode(['status'=>$data . ' Empty']);
    exit;
}

