<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data, true);
$offset = $data['offset'];
$limit = $data['limit'];
$search = $data['search'];

if (isset($_POST)) {
    $select = "SELECT * FROM department WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $select .= " AND (bldg_name = :bldg OR bldg_name = :bldg ";
        $params[':bldg'] = $search;
    }
    $select .= " ORDER BY bldg_name LIMIT :limit OFFSET :offset";
    $params[":limit"] = $limit;
    $params[':offset'] = $offset;
    $stmt = $dbh->query($select);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data]);
    exit;
}

