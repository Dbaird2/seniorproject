<?php
include_once "../../config.php";
if (isset($_POST)) {
    $note = trim($_POST['asset_note']);
    $tag = trim($_POST['asset_tag']);
    $profile = trim($_POST['profile_name']);
    $email = $_SESSION['email'];

    try {
    $update_q = "UPDATE user_asset_profile SET asset_note = :asset_note WHERE profile_name = :profile AND asset_tag = :tag AND email = :email";
    $update_stmt = $dbh->prepare($update_q);
    $update_stmt->execute([":asset_note"=>$note, ":profile"=>$profile, ":tag"=>$tag, ":email"=>$email]);
    } catch (PDOException $e) {
        echo json_encode(["Status"=>"failed to insert", "Message"=>$e->getMessage()]);
        exit;
    }
    echo json_encode(["Status"=>"Successful note insert"]);
    exit;
}

echo json_encode(["Status"=>"METHOD not received"]);
exit;

