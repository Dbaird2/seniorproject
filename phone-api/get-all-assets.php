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
    echo json_encode(['status' => 'Failed to login']);
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
    $select_count = "SELECT COUNT(*) FROM asset_info WHERE asset_status = 'In Service'";
    $row_count = $query_repo->fetchColumn($select_count);
    $select = "SELECT a.asset_tag, a.serial_num, a.po, a.bus_unit,
            a.asset_name, a.asset_price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a
            LEFT JOIN room_table AS r ON a.room_tag = r.room_tag
            LEFT JOIN bldg_table AS b ON r.bldg_id = b.bldg_id WHERE asset_status = 'In Service' ORDER BY a.asset_tag";
    $data = $query_repo->fetchAll($select);
    echo json_encode(["data" => $data, 'count' => $row_count]);
    exit;
}
