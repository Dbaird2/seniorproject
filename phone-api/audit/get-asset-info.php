<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";


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

if (!empty($data['tag'])) {
    $select = "SELECT * FROM asset_info WHERE asset_tag = :tag";
    $stmt = $dbh->prepare($select);
    $stmt->execute([':tag'=>$data['tag']]);
    $tag_info = $stmt->fetch();
    if ($tag_info) {
        $role = $info['u_role'];
        if (in_array($role, ['admin', 'management'])) {
            $select = 'SELECT curr_mgmt_id FROM audit_freq';
        } else {
            $select = 'SELECT curr_self_id FROM audit_freq';
        }
        $stmt = $dbh->query($select);
        $audit_id = $stmt->fetch();
    }
    echo json_encode(['status'=>'success', 'data'=>$tag_info]);
    exit;
}
echo json_encode(['POST'=>$data, 'status'=>'failure']);
exit;

