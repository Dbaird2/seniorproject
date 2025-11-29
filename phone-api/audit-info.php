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

try {
    $select_user = "SELECT u_role, email, pw FROM user_table WHERE (email = :email OR username = :email) limit 1";
    $stmt = $dbh->prepare($select_user);
    $stmt->execute([":email"=>$email]);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
    exit;
}
$info = $stmt->fetch();
if ($info) {
    if (!password_verify($pw, $info['pw'])) {
        echo json_encode(['status'=>'failed', 'reason'=>'invalid login']);
        exit;
    }
}
try {
    $select = 'SELECT * FROM audit_freq';
    $stmt = $dbh->query($select);
    $ids = $stmt->fetch();
    $mgmt = $ids['curr_mgmt_id'];
    $self = $ids['curr_self_id'];

    $select = "select count(audit_status) as status from audit_history where audit_status = 'Completed' and audit_id = :id
        union
        select count(audit_status) as status from audit_history where audit_status = 'In Progress' AND audit_id = :id";
    $stmt = $dbh->prepare($select);
    $stmt->execute([':id'=>$self]);
    $self_count = $stmt->fetchAll();

    $stmt = $dbh->prepare($select);
    $stmt->execute([':id'=>$mgmt]);
    $mgmt_count = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status'=>'failed', 'reason'=>'database query failed']);
    exit;
}


echo json_encode(['status'=>'Ok', 'selfCompleted'=>$self_count[0]['status']?? 0, 'selfInProgress'=>$self_count[1]['status'] ?? 0, 'mgmtCompleted'=>$mgmt_count[0]['status'] ?? 0, 'mgmtInProgress'=>$mgmt_count[1]['status'] ?? 0]);
exit;
