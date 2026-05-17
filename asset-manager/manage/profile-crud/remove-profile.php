<?php
include_once "../../../config.php";
if (isset($_POST['profile_name'])) {
    $email = $_SESSION['email'];
    $name = trim($_POST['profile_name']);

    $delete_q = "DELETE FROM user_asset_profile WHERE email = :email AND profile_name = :name";
    try {
        $query_repo->execute($delete_q, $email, $name);
    } catch (PDOException $e) {
        error_log("Failed removing profile " . $e->getMessage());
    }
}
