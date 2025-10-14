<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$profile_name = trim($data['profile_name']);
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
        $delete = 'DELETE FROM user_asset_profile WHERE email = :email AND profile_name = :name';
        $stmt = $dbh->prepare($update);
        $stmt->execute([':email'=>$email, ':name'=>$profile_name]);
        echo json_encode(['status'=>'Ok']);
        exit;
    }
}
echo json_encode(['status'=>'fail']);
exit;
