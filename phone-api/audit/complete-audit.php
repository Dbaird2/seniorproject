<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$data = trim($data['data']);
$dept = trim($data['dept_name']);
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
    if (password_verify($pw, $info['pw'])) {
        try {
            $select = "SELECT dept_id FROM department WHERE dept_name = :dept OR dept_id = :dept";
            $stmt = $dbh->prepare($select);
            $stmt->execute([':dept'=>$dept]);
            $dept_id = $stmt->fetchColumn();
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
            exit;
        }
        if (empty($dept_id)) {
            echo json_encode(['status'=>'Failed','reason'=>'Deptartment ID Not found']);
            exit;
        }

        try {
            $select = 'SELECT curr_self_id, curr_mgmt_id FROM audit_freq';
            $stmt = $dbh->query($select);
            $audit_ids = $stmt->fetch();
            $audit_id = ($info['u_role'] === 'admin' || $info['u_role'] === 'management') ?  $audit_ids['curr_mgmt_id'] : $audit_ids ['curr_self_ids'];
            $json_data = json_encode($data);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
            exit;
        }

        try {
            $insert = "INSERT INTO audit_history (auditor, audit_id, audit_data, dept_id) VALUE (?, ?, ?, ?)";
            $stmt = $dbh->prepare($select);
            $stmt->execute([$email, $audit_id, $json_data, $dept_id]);
        } catch (PDOException $e) { 
            echo json_encode(['status'=>'Failed', 'reason'=>$e->getMessage()]);
            exit;
        }

        echo json_encode(['status'=>'Ok']);
        exit;
    }
}
echo json_encode(['status'=>'Failed']);
exit;
