<?php
include_once "../../config.php";
if (isset($_POST)) {
    $note = trim($_POST['asset_note']);
    $tag = trim($_POST['asset_tag']);
    $profile = trim($_POST['profile_name']);

    try {
    $update_q = "INSERT INTO user_asset_profile SET asset_note = :asset_note WHERE profile_name = :profile AND asset_tag = :tag";
    $update_stmt = $dbh->prepare($update_q);
    $update_stmt->execute([':asset_note'=>$note, ':profile'=>$profile, ':tag'=>$tag]);
    } catch (PDOException $e) {
        echo json_encode(["Status"=>"failed to insert", "Message"=>$e->getMessage()]);
        exit;
    }
    echo json_encode(["Status"=>"Successful note insert"]);
    exit;
}

echo json_encode(["Status"=>"METHOD not received"]);
exit;

