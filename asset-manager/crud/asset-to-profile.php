<?php
require_once '../../config.php';
check_auth();
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $tag = trim($_POST['asset_tag']);
    try {
        $insert_q = "INSERT INTO user_asset_profile 
        (email, profile_name, asset_tag)
        VALUES
        (?, ?, ?)";
        $insert_stmt = $dbh->prepare($insert_q);
        $insert_stmt->execute([$email, $profile_name, $asset_tag]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}

