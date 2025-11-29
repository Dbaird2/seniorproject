<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
ini_set('log_errors', 1);
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

if (isset($data['dept_name'])) {
    $select = "SELECT a.bus_unit,a.asset_notes,a.date_added, a.asset_tag, a.asset_name, a.serial_num, d.dept_name, a.po, CONCAT(b.bldg_name, ' ', r.room_loc) as Location FROM asset_info a LEFT JOIN department d ON a.dept_id = d.dept_id LEFT JOIN room_table r ON a.room_tag = r.room_tag LEFT JOIN bldg_table b on r.bldg_id = b.bldg_id WHERE dept_name = :name";
    $stmt = $dbh->prepare($select);
    $stmt->execute([':name'=>$data['dept_name']]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data'=>$data]);
    exit;
}
echo json_encode(['POST'=>$data]);
