<?php

require_once "../../../config.php";
$encoded_data = file_get_contents("php://input");
$data = json_decode($encoded_data, true);

if (!empty($data)) {
    try {
        $update = "UPDATE audit_history SET forms_submitted = true WHERE audit_id = :id AND dept_id = :dept";
        $update_stmt = $dbh->prepare($update);
        $update_stmt->execute([":id"=>$data['audit_id'], ":dept"=>$data['dept_id']]);
        echo json_encode(['status'=>'updated database']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status'=>'fail to update database']);
        exit;
    }
} else {
    echo json_encode(['status'=>$data . ' Empty']);
    exit;
}

