<?php 
header("Access-Control-Allow-Origin: *");
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
    $select_user = "SELECT pw, username FROM user_table WHERE (email = :email OR username = :email) limit 1";
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
        echo json_encode(['status'=>'success']);
        exit;
    }
}
echo json_encode(['status'=>'Failed to login']);
exit;
