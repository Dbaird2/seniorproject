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
    echo json_encode(['status'=>'Failed to login']);
    exit;
}

try {
    $select_user = "SELECT u_role, email, pw FROM user_table WHERE (email = :email OR username = :email) limit 1";
    $stmt = $dbh->prepare($select_user);
    $stmt->execute([":email"=>$email]);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
    exit;
}
$info = $stmt->fetch();
if ($info) {
    if (!password_verify($pw, $info['pw'])) {
        echo json_encode(['status'=>'failed', 'reason'=>'invalid login']);
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
        $select .= " AND (dept_id ILIKE :dept OR dept_name ILIKE :dept) ";
        $params[':dept'] = '%' . $search . '%';
    }
    $select .= " ORDER BY dept_id LIMIT :limit OFFSET :offset";
    $params[":limit"] = $limit;
    $params[':offset'] = $offset;
    $stmt = $dbh->prepare($select);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data]);
    exit;
}

