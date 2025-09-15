<?php
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data);

if (isset($_POST)) {
    $select = "SELECT a.asset_tag, a.serial_num, a.po, a.bus_unit,
            a.asset_name, a.asset_price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a
            JOIN room_table AS r ON a.room_tag = r.room_tag
            JOIN bldg_table AS b ON r.bldg_id = b.bldg_id";
    $select_stmt = $dbh->query($select);
    $data = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data]);
    exit;
}

