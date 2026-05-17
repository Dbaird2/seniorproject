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

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (!password_verify($pw, $info['pw'])) {
        echo json_encode(['status' => 'failed', 'reason' => 'invalid login']);
        exit;
    }
}


if (isset($_POST)) {
    $select_count = "SELECT COUNT(*) AS room_count FROM room_table WHERE bldg_id IS NOT NULL";
    $row_count = $query_repo->fetchColumn($select_count);

    $select = "SELECT * FROM room_table WHERE bldg_id IS NOT NULL";
    $data = $query_repo->fetchAll($select);

    echo json_encode(["data" =>$data, 'count'=>$row_count]);
    exit;
}

