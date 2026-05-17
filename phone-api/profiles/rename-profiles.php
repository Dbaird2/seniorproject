<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
$old_name = trim($data['old_name']);
$new_name = trim($data['new_name']);
if (empty($email) || empty($pw)) {
    echo json_encode(['status' => 'Failed to login']);
    exit;
}

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (password_verify($pw, $info['pw'])) {
        $update = 'UPDATE user_asset_profile SET profile_name = ? WHERE email = ? AND profile_name = ?';
        $query_repo->execute($update, $email, $new_name, $old_name);
        echo json_encode(['status' => 'Ok']);
        exit;
    }
}
echo json_encode(['status' => 'fail']);
exit;
