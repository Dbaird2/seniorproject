<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data, true);
$offset = $data['offset'];
$limit = $data['limit'];
$search = $data['search'];

if (isset($_POST)) {
    $select = "SELECT * FROM department WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $select .= " AND (dept_id = :dept OR dept_name = :dept) ";
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

