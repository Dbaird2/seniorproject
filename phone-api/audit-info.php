<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
if (empty($email) || empty($pw)) {
    echo json_encode(['status'=>'Failed to login']);
    exit;
}

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (!password_verify($pw, $info['pw'])) {
        echo json_encode(['status'=>'failed', 'reason'=>'invalid login']);
        exit;
    }
}
try {
    $select = 'SELECT * FROM audit_freq';
    $ids = $query_repo($select);
    $mgmt = $ids['curr_mgmt_id'];
    $self = $ids['curr_self_id'];

    $select = "select count(audit_status) as status from audit_history where audit_status = 'Completed' and audit_id = ?
        union
        select count(audit_status) as status from audit_history where audit_status = 'In Progress' AND audit_id = ?";

    $self_count = $query_repo->fetchAll($select, $self, $self);

    $mgmt_count = $query_repo->fetchAll($select, $mgmt, $mgmt);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status'=>'failed', 'reason'=>'database query failed']);
    exit;
}


echo json_encode(['status'=>'Ok', 'selfCompleted'=>$self_count[0]['status']?? 0, 'selfInProgress'=>$self_count[1]['status'] ?? 0, 'mgmtCompleted'=>$mgmt_count[0]['status'] ?? 0, 'mgmtInProgress'=>$mgmt_count[1]['status'] ?? 0]);
exit;
