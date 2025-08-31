<?php
include_once "../../config.php";
check_auth("high");

if (isset($_POST['delete-room'])) {
    try {
    $old_tag = (int)trim($_POST['old_room_tag']);
    $delete_q = "DELETE FROM room_table WHERE room_tag = :tag";
    $delete_stmt = $dbh->prepare($delete_q);
    $delete_stmt->execute([':tag'=>$old_tag]);
    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
    }

    header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
    exit;
}
if (isset($_POST['bldg'])) {
    $old_id = (int)trim($_POST['old_bldg_id']);
    $old_name = trim($_POST['old_name']);
    $old_loc = trim($_POST['old_room_loc']);
    $old_tag = (int)trim($_POST['old_room_tag']);

    $new_id = (int)trim($_POST['bldg_id']);
    $new_name = trim($_POST['name']);
    $new_loc = trim($_POST['room_loc']);
    $new_tag = (int)trim($_POST['room_tag']);

    try {
        if ($old_id !== $new_id && !empty($new_id)) {
            $update_bldg = "UPDATE bldg_table SET bldg_id = :new_bldg WHERE bldg_id = :old_bldg";
            $bldg_stmt = $dbh->prepare($update_bldg);
            $bldg_stmt->execute([":new_bldg"=>$new_id, ":old_bldg"=>$old_id]);

            $update_room = "UPDATE room_table SET bldg_id = :new_bldg WHERE bldg_id = :old_bldg";
            $room_stmt = $dbh->prepare($update_room);
            $room_stmt->execute([":new_bldg"=>$new_id, ":old_bldg"=>$old_id]);
        }
        if ($old_name !== $new_name && !empty($new_name)) {
            $update_bldg = "UPDATE bldg_table SET bldg_name = :new_bldg WHERE bldg_name = :old_bldg";
            $bldg_stmt = $dbh->prepare($update_bldg);
            $bldg_stmt->execute([":new_bldg"=>$new_name, ":old_bldg"=>$old_name]);
        }
        if ($old_loc !== $new_loc && !empty($new_loc)) {
            $update_room = "UPDATE room_table SET room_loc = :new_loc WHERE room_loc = :old_loc AND room_tag = :old_tag";
            $room_stmt = $dbh->prepare($update_room);
            $room_stmt->execute([":new_loc"=>$new_loc, ":old_loc"=>$old_loc, ":old_tag"=>$old_tag]);
        }
        if ($old_tag !== $new_tag && !empty($new_tag)) {
            $update_room = "UPDATE room_table SET room_tag = :new_tag WHERE room_tag = :old_tag";
            $room_stmt = $dbh->prepare($update_room);
            $room_stmt->execute([":new_tag"=>$new_tag, ":old_tag"=>$old_tag]);
        }

    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
        header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
        exit;
    } 
    header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
    exit;
}
    header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
    exit;
