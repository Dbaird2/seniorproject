<?php
header('Content-Type: application/json');
include_once('../../config.php');
$data = json_decode(file_get_contents('php://input'), true);
$f_name = $data['f_name'] ?? '';
$l_name = $data['l_name'] ?? '';
$key = $data['key'] ?? '';
$old_f_name = $data['old_f_name'] ?? '';
$old_l_name = $data['old_l_name'] ?? '';
$old_key = $data['old_key'] ?? '';
$params = [];
$set_q = [];
if ($f_name !== $old_f_name) {
    $params[] = $f_name;
    $set_q[] = "f_name = ?";
}
if ($l_name !== $old_l_name) {
    $params[] = $l_name;
    $set_q[] = "l_name = ?";
}
if ($key !== $old_key) {
    $params[] = $key;
    $set_q[] = "kuali_key = ?";
}
if (!empty($set_q)) {
    $query_to_add = implode(", ", $set_q);
    $update_q = "UPDATE user_table SET " . $query_to_add . " WHERE email = :email";
} else {
    echo json_encode(['status' => 'failed', 'msg' => 'Nothing to change']);
    exit;
}
$params[] = $_SESSION["email"];
try {
    $query_repo->execute($update_q, $params);
} catch (PDOException $e) {
    echo json_encode(['status' => 'failed', 'error' => $e->getMessage()]);
    exit;
}

echo json_encode(['status' => 'success']);
exit;
