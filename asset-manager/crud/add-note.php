<?php
include_once "../../config.php";
if (isset($_POST)) {
    $note = trim($_POST['asset_note']);
    $tag = trim($_POST['asset_tag']);
    $profile = trim($_POST['profile_name']);
    $email = $_SESSION['email'];

    try {
        $query_repo->execute("UPDATE user_asset_profile SET asset_note = ? WHERE profile_name = ? AND asset_tag = ? AND email = ?", $note, $profile, $tag, $email);
    } catch (PDOException $e) {
        echo json_encode(["Status" => "failed to insert", "Message" => $e->getMessage()]);
        exit;
    }
    echo json_encode(["Status" => "Successful note insert"]);
    exit;
}

echo json_encode(["Status" => "METHOD not received"]);
exit;
