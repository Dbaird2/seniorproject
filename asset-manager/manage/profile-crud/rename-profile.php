<?php
include_once '../../../config.php';
check_auth();
if (isset($_POST['old_name'])) {
    $new_name = trim($_POST['new_name']);
    $email = $_SESSION['email'];
    $old_name = trim($_POST['old_name']);

    $update_q = "UPDATE user_asset_profile SET profile_name = ? WHERE email = ? AND profile_name = ?";
    try {
        $query_repo->execute($update_q, $email, $new_name, $old_name);
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
