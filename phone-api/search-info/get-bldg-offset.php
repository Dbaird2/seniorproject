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
    $select = "SELECT b.bldg_id, b.bldg_name, r.room_tag, r.room_loc FROM bldg_table b left join room_table r on r.bldg_id = b.bldg_id WHERE 1=1 ";
    $params = [];
    if (!empty($search)) {
        $select .= " AND (b.bldg_name = :bldg OR b.bldg_id = :bldg OR r.room_tag = :bldg) ";
        $params[':bldg'] = $search;
    }
    $select .= " ORDER BY bldg_name LIMIT :limit OFFSET :offset";
    $params[":limit"] = $limit;
    $params[':offset'] = $offset;
    $stmt = $dbh->prepare($select);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data]);
    exit;
}

