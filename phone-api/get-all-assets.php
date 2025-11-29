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

if (isset($_POST)) {
    $select_count = "SELECT COUNT(*) FROM asset_info WHERE asset_status = 'In Service'";
    $select_stmt = $dbh->query($select_count);
    $row_count = $select_stmt->fetchColumn();
    $select = "SELECT a.asset_tag, a.serial_num, a.po, a.bus_unit,
            a.asset_name, a.asset_price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a
            LEFT JOIN room_table AS r ON a.room_tag = r.room_tag
            LEFT JOIN bldg_table AS b ON r.bldg_id = b.bldg_id WHERE asset_status = 'In Service' ORDER BY a.asset_tag";
    $select_stmt = $dbh->query($select);
    $data = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data, 'count'=>$row_count]);
    exit;
}

