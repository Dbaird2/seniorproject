<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$old_name = trim($data['old_name']);
$new_name = trim($data['new_name']);
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
        $update = 'UPDATE user_asset_profile SET profile_name = :new WHERE email = :email AND profile_name = :old';
        $stmt = $dbh->prepare($update);
        $stmt->execute([':email'=>$email, ':new'=>$new_name, ':old'=>$old_name]);
        echo json_encode(['status'=>'Ok']);
        exit;
    }
}
echo json_encode(['status'=>'fail']);
exit;
