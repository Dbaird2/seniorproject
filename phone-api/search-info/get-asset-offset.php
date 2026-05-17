<?php
header("Access-Control-Allow-Oirigin: *");
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

$select_user = "SELECT pw, username FROM user_table WHERE (email = :email OR username = :email) limit 1";
$info = $query_repo->fetchOne($select_user, $email, $email);
if ($info) {
    if (!password_verify($pw, $info['pw'])) {
        echo json_encode(['status' => 'failed', 'reason' => 'invalid login']);
        exit;
    }
}
$offset = $data['offset'];
$limit = $data['limit'];
$search = $data['search'];

$echo = function ($array) {
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
};

if (isset($_POST)) {
    $dept_regex = "/^D\d+/i";
    $select = "SELECT a.asset_tag as tag, a.serial_num as serial, a.po, a.bus_unit,
            a.asset_name as name, a.asset_price as price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a
            LEFT JOIN room_table AS r ON a.room_tag = r.room_tag
            LEFT JOIN bldg_table AS b ON r.bldg_id = b.bldg_id WHERE asset_status = 'In Service' ";
    $params = [];
    if (!empty($search)) {
        if (preg_match($dept_regex, $search)) {
            $select .= " AND a.dept_id ILIKE ? ";
            $params[] = '%' . $search . '%';
        } else {
            $select .= " AND (asset_tag ILIKE ? OR asset_name ILIKE ? OR serial_num ILIKE ? OR CAST(a.room_tag AS TEXT) ILIKE ?) ";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
    }
    $params[] = $limit;
    $params[] = $offset;
    $select .= " ORDER BY asset_tag LIMIT ? OFFSET ?";
    $data = $query_repo->fetchAll($select, $params);
    echo json_encode(["data" => $data]);
    exit;
}
