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

$select_user = "SELECT pw, username FROM user_table WHERE (email = ? OR username = ?) limit 1";
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

if (isset($_POST)) {
    $select = "SELECT * FROM department WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $select .= " AND (dept_id ILIKE ? OR dept_name ILIKE ?) ";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    $select .= " ORDER BY dept_id LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $data = $query_repo->fetchAll($select, $params);
    echo json_encode(["data" => $data]);
    exit;
}
