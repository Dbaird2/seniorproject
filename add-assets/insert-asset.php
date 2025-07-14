<?php
require_once("../config.php");
$data = json_decode(file_get_contents('php://input'), true);

$tag = $data['tag'];
$descr = $data['descr'];
$sn = $data['sn'];
$po = (int)$data['po'];
$model = $data['model'];
$acq_date = str_replace("-", "/", $data['acq_date']);
$profile = $data['profile'];
$dept_id = $data['dept_id'];
$room_name = $data['room_name'];
$bldg_name = $data['bldg_name'];
$notes = $data['notes'];
$type = $data['type'];

if (!is_null($tag) && !is_null($descr) && !is_null($po) && !is_null($dept_id) && 
    !is_null($bldg_name) && !is_null($room_name) && !is_null($acq_date)) {
    $column = $params = [];
    $question_marks = ['?','?','?','?','?','?'];
    $column[] = "asset_tag";$column[] = "asset_name"; $column[] = "date_added";
    $column[] = "dept_id"; $column[] = "po";
    $params[] = $tag;$params[] = $descr;$params[] = $acq_date;
    $params[] = $dept_id;$params[] = $po;

    if ($model !== '') {
        $column[] = "model";
        $params[] = $model;
        $question_marks[] = '?';
    } if ($sn !== '') {
    $column[] = "serial_num";
    $params[] = $sn;
    $question_marks[] = '?';
        } if ($type !== '') {
        $column[] = "asset_type";
        $params[] = $type;
        $question_marks[] = '?';
    } if ($notes !== '' ) {
    $column[] = "asset_notes";
    $params[] = $notes;
    $question_marks[] = '?';
        }
    try {
        $bldg_search[] = "bldg_name"; $bldg_search[] = "room_loc";
        $bldg_q = "SELECT room_tag FROM room_table r NATURAL JOIN bldg_table b WHERE r.room_loc = :room_loc AND b.bldg_name = :bldg_name";
        $b_stmt = $dbh->prepare($bldg_q);
        $b_stmt->execute([":room_loc"=>$room_name, ":bldg_name"=>$bldg_name]);
        $room_tag = $b_stmt->fetch(PDO::FETCH_ASSOC);
        if ($room_tag) {
            $column[] = "room_tag";
            $params[] = $room_tag["room_tag"];
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Room tag was not found"]);
            exit;
        }
        $tag_avail = "SELECT * FROM asset_info WHERE asset_tag = :tag";
        $tag_stmt = $dbh->prepare($tag_avail);
        $tag_stmt->execute([":tag"=>$tag]);
        $is_avail = $tag_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$is_avail) {
            $column = implode(", ", $column);
            $question_marks = implode(", ",$question_marks);
            $insert_q = "INSERT INTO asset_info ($column) VALUES ($question_marks)";
            $insert_stmt= $dbh->prepare($insert_q);
            $insert_stmt->execute($params);
            echo json_encode(["status" => "success"]);

        } else {
            echo json_encode(["error" => "Asset tag already exists"]);
        }
    } catch (PDOException e) {
        echo json_encode(["status" => "fail"]);
    } 
} else {
    echo json_encode(["error" => "Information missing"]);
}


