<?php
include_once '../../config.php';
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $tag = trim($_POST['asset_tag']);
    try {
        $delete_q = "DELETE FROM user_asset_profile 
        WHERE asset_tag = ? AND 
        profile_name = ? AND
        email = ?";
        $query_repo->execute($delete_q, $tag, $profile_name, $email);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
