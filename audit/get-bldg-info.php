<?php
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data);

$select = "SELECT b.bldg_id, b.bldg_name, r.room_tag, r.room_loc from room_table r left join bldg_table b on r.bldg_id = b.bldg_id where room_tag = :tag";
try {
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":tag"=>$data['room_tag']]);
    $bldg_data = $select_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode('bldg_name'=>'', 'status'=>'failed'.$e->getMessage());
    exit;
}
echo json_encode("bldg_name"=>$bldg_data['bldg_name'], "room_number"=>$bldg_data['room_loc'],'bldg_id'=>$bldg_data['bldg_id']);
exit;

