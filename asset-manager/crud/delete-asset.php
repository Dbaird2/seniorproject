<?php
require_once '../../config.php';
check_auth();
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $tag = trim($_POST['asset_tag']);
    try {
        $delete_q = "DELETE FROM user_asset_profile 
        WHERE asset_tag = :asset_tag AND 
        profile_name = :name AND
        email = :email";
        $delete_stmt = $dbh->prepare($delete_q);
        $delete_stmt->execute([":asset_tag"=>$tag,":name"=>$profile_name,":email"=>$email]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
