<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$profile_name = trim($data['profile_name']);
if (empty($email) || empty($pw)) {
    echo json_encode(['status' => 'Failed to login']);
    exit;
}

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (password_verify($pw, $info['pw'])) {
        $delete = 'DELETE FROM user_asset_profile WHERE email = ? AND profile_name = ?';
        $query_repo->execute($delete, $email, $profile_name);
        echo json_encode(['status' => 'Ok']);
        exit;
    }
}
echo json_encode(['status' => 'fail']);
exit;
