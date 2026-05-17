<?php
include_once '../../config.php';
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    try {
        $delete_q = "DELETE FROM user_asset_profile
            WHERE email = :email AND profile_name = :name";
        $query_repo->execute($delete_q, $email, $profile_name);

        $insert_q = "INSERT INTO user_asset_profile (email, profile_name)
            VALUES (?, ?)";
        $query_repo->execute($insert_q, $email, $profile_name);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
