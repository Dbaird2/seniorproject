<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
if (empty($email) || empty($pw)) {
    echo json_encode(['status' => 'Failed to login']);
    exit;
}

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (password_verify($pw, $info['pw'])) {
        $select = 'SELECT distinct(profile_name) as profiles FROM user_asset_profile WHERE email = ?';
        $profiles = $query_repo->fetchAll($select, $email);
        echo json_encode(['status' => 'Ok', 'profiles' => $profiles]);
        exit;
    }
}
echo json_encode(['status' => 'fail']);
exit;
