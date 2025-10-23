<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data);

if (isset($_POST)) {
    $select_count = "SELECT COUNT(*) AS room_count FROM room_table WHERE bldg_id IS NOT NULL";
    $select_stmt = $dbh->query($select_count);
    $row_count = $select_stmt->fetchColumn();
    $select = "SELECT * FROM room_table WHERE bldg_id IS NOT NULL";
    $select_stmt = $dbh->query($select);
    $data = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data, 'count'=>$row_count]);
    exit;
}

