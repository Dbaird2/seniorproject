<?php
include_once "../config.php";
header('Content-Type: application/json');
check_auth();
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);

$bldg = trim($data['bldg']);
$loc = trim($data['room_loc']);

$select = 'SELECT room_tag FROM room_table r LEFT JOIN bldg_table b ON r.bldg_id = b.bldg_id WHERE bldg_name = :name AND room_loc = :loc';
$stmt = $dbh->prepare($select);
$stmt->execute([':name'=>$bldg, ':loc'=>$loc]);
$room_tag = $stmt->fetchColumn();
if ($room_tag) {
    echo json_encode(['status'=>'Ok','room_tag'=>$room_tag]);
} else {
    echo json_encode(['status'=>'failed']);
}

exit;



