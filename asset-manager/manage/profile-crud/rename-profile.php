<?php
include_once '../../../config.php';
check_auth();
if (isset($_POST['old_name'])) {
    $new_name = trim($_POST['new_name']);
    $email = $_SESSION['email'];
    $old_name = trim($_POST['old_name']);

    $update_q = "UPDATE user_asset_profile SET profile_name = :new_name WHERE email = :email AND profile_name = :old_name";
    try {
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([":old_name" => $old_name, ":email" => $email, ":new_name" => $new_name]);
        echo json_encode(["status"=>"success", "old_name"=>$old_name, "new_name"=>$new_name]);
        exit;
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["status"=>"failure " . $e->getMessage()]);
        exit;
    }
}
echo json_encode(["status"=>"failure: POST not set"]);
exit;
