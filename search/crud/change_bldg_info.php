<?php
include_once "../../config.php";
check_auth("high");

if (isset($_POST['bldg'])) {
    $old_id =(int)trim($_POST['old_bldg_id']);
    $old_name =trim($_POST['old_name']);
    $old_loc =trim($_POST['old_room_loc']);
    $old_tag =trim($_POST['old_room_tag']);

    $new_id = (int)trim($_POST['bldg_id']);
    $new_name =trim($_POST['name']);
    $new_loc =trim($_POST['room_loc']);
    $new_tag =trim($_POST['room_tag']);

    try {
        $set_array = [];
        $bldg_params = [":old_name"=>$old_name];
        $bldg_params[":old_id"]= $old_id;
        $bldg_id_change = false;
        if ($new_id !== '' && $new_id !== $old_id) {
            $bldg_id_change = true;
            $set_array[] = "bldg_id = :bldg_id";
            $bldg_params[":bldg_id"] = $new_id;
        }
        if ($new_name !== '' && $new_name !== $old_name) {
            $set_array[] = "bldg_name = :bldg_name";
            $bldg_params[":bldg_name"] = $new_name;
        }
        if (!empty($set_array)) {
            $set_bldg = implode(', ', $set_array);
            $select_q = "SELECT bldg_id, bldg_name  FROM bldg_table WHERE bldg_id = :bldg_id OR bldg_name = :bldg_name";
            $select_stmt = $dbh->prepare($select_q);
            $select_stmt->execute([":bldg_id"=>$old_id, ":bldg_name"=>$new_name]);
            if ($select_stmt->rowCount() <= 0) {
                $update_q = "UPDATE bldg_table SET " . $set_bldg . ' WHERE bldg_id = :old_id OR bldg_name = :old_name';
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute($params);
            }
        }
    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
        exit;
    } 
    try {
        $set_array = [];
        $room_params = [":old_tag"=>$old_tag];
        if ($new_loc !== '' && $new_loc !== $old_loc) {
            $set_array[] = "room_loc = :new_loc";
            $room_params[":new_loc"] = $new_loc;
        }
        if ($new_tag !== '' && $new_tag !== $old_tag) {
            $set_array[] = "new_tag = :new_tag";
            $room_params[":new_tag"] = $new_tag;
        }
        if (!empty($set_array)) {
            $set_room = implode(', ', $set_array);
            $select_q = "SELECT room_loc, room_tag  FROM bldg_table WHERE room_tag = :new_tag";
            $select_stmt = $dbh->prepare($select_q);
            $select_stmt->execute([":new_tag"=>$new_tag]);
            if ($select_stmt->rowCount() <= 0) {
                $update_q = "UPDATE room_table SET " . $set_room . ' WHERE room_tag = :old_tag';
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute($params);
            }
        }
        if ($bldg_id_change === true) {
            $update_rooms = "UPDATE room_table set bldg_id = :bldg_id WHERE bldg_id = :old_bldg_id";
            $update_stmt = $dbh->prepare($update_rooms);
            $update_stmt->execute([':bldg_id'=>$bldg_id, ":old_bldg_id"=>$old_bldg_id]);
        }
    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
        exit;
    } 
    exit;
}
