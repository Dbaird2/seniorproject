<?php

require_once "../../../config.php";
$encoded_data = file_get_contents("php://input");
$data = json_decode($encoded_data, true);

if (!empty($data)) {
    try {
        $select = 'SELECT dept_id, dept_id FROM department WHERE dept_id = :dept';
        $stmt = $dbh->prepare($select);
        $stmt->execute([':dept'=$data['dept_id']]);
        $dept_data = $stmt->fetch();
        if ($dept_data) {
            $update = "UPDATE audit_history SET forms_submitted = true WHERE audit_id = :id AND dept_id = :dept";
            $update_stmt = $dbh->prepare($update);
            $update_stmt->execute([":id"=>$data['audit_id'], ":dept"=>$data['dept_id']]);
            echo json_encode(['status'=>'Ok']);
            exit;
        } else {
            echo json_encode(['status'=>'failed', 'reason'=>'department_not_exist']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['status'=>'fail to update database']);
        exit;
    }
} else {
    echo json_encode(['status'=>$data . ' Empty']);
    exit;
}

