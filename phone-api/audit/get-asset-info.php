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

if (!empty($data['tag'])) {
    $select = "SELECT * FROM asset_info WHERE asset_tag = ?";
    $tag_info = $query_repo->fetchOne($select, $data['tag']);

    if ($tag_info) {
        $role = $info['u_role'];
        if (in_array($role, ['admin', 'management'])) {
            $select = 'SELECT curr_mgmt_id FROM audit_freq';
        } else {
            $select = 'SELECT curr_self_id FROM audit_freq';
        }
        $audit_id = $query_repo->fetchOne($select);
    }
    echo json_encode(['status' => 'success', 'data' => $tag_info]);
    exit;
}
echo json_encode(['POST' => $data, 'status' => 'failure']);
exit;
