<?php
include_once '../../../config.php';
check_auth();
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $old_name = trim($_POST['old_name']);

    $update_q = "UPDATE user_asset_profile SET profile_name = :new_name WHERE email = :email AND profile_name = :profile_name";
    try {
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([":profile_name" => $old_name, ":email" => $email, ":new_name" => $profile_name]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
