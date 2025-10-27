<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data);
$offset = $data['offset'];
$limit = $data['limit'];
$search = $data['search'];

if (isset($_POST)) {
    $dept_regex = "/^D\d+/i";
    $select = "SELECT a.asset_tag, a.serial_num, a.po, a.bus_unit,
            a.asset_name, a.asset_price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a
            LEFT JOIN room_table AS r ON a.room_tag = r.room_tag
            LEFT JOIN bldg_table AS b ON r.bldg_id = b.bldg_id WHERE asset_status = 'In Service' ";
    $params = [];
    if (!empty($search)) {
        if (preg_match($dept_regex, $search)) {
            $select .= " AND dept_id = :dept ";
            $params[':dept'] = $search;
        } else {
            $select .= " AND (asset_tag ILIKE :tag OR asset_name ILIKE :tag) ";
            $params[':tag'] = $search;
        } 
    }
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    $select .= " ORDER BY asset_tag LIMIT :limit OFFSET :offset";
    $stmt = $dbh->prepare($select);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data, 'count'=>$row_count]);
    exit;
}

