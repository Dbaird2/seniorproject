<?php
include_once "../../../config.php";
if (isset($_POST)) {
    $email = $_POST['email'];
    $name = trim($_POST['profile_name']);

    $delete_q = "DELETE FROM user_asset_profile WHERE email = :email AND profile_name = :name";
    try {
        $delete_stmt = $dbh->prepare($delete_q);
        $delete_stmt->execute([":email" => $email, ":name" => $name]);
    } catch (PDOException $e) {
        error_log("Failed removing profile " . $e->getMessage());
    }
}
?>
