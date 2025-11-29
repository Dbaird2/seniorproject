<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$profile_name = trim($data['profile']);
if (empty($email) || empty($pw)) {
    echo json_encode(['status'=>'Failed to login']);
    exit;
}

try {
    $select_user = "SELECT email, pw FROM user_table WHERE (email = :email OR username = :email) limit 1";
    $stmt = $dbh->prepare($select_user);
    $stmt->execute([":email"=>$email]);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
    exit;
}
$info = $stmt->fetch(PDO::FETCH_ASSOC);
if ($info) {
    if (password_verify($pw, $info['pw'])) {
        $select = 'select p.asset_tag, a.asset_name, r.room_tag, b.bldg_name, a.date_added, a.serial_num, a.asset_price, a.asset_type, a.po, a.bus_unit, a.asset_notes, a.dept_id, a.asset_model, a.make, a.type2, b.bldg_id, r.room_loc from user_asset_profile p LEFT JOIN asset_info a ON a.asset_tag = p.asset_tag LEFT JOIN room_table r ON r.room_tag = a.room_tag LEFT JOIN bldg_table b on r.bldg_id = b.bldg_id WHERE email = :email AND profile_name = :name';
        $stmt = $dbh->prepare($select);
        $stmt->execute([':email'=>$email, ':name'=>$profile_name]);
        $profile_data = $stmt->fetchAll();
        echo json_encode(['status'=>'Ok','profiles'=>$profile_data]);
        exit;
    }
}
echo json_encode(['status'=>'fail']);
exit;
