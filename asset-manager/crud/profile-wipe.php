<?php
include_once '../../config.php';
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    try {
        $delete_q = "DELETE FROM user_asset_profile
    WHERE email = :email AND profile_name = :name";
        $delete_stmt = $dbh->prepare($delete_q);
        $delete_stmt->execute([":email" => $email, ":name" => $profile_name]);

        $insert_q = "INSERT INTO user_asset_profile (email, profile_name)
    VALUES (?, ?)";
        $insert_stmt = $dbh->prepare($insert_q);
        $insert_stmt->execute([$email, $profile_name]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
