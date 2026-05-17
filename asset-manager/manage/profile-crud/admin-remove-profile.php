<?php
include_once "../../../config.php";
if (isset($_POST['profile_name'])) {
    $email = $_POST['email'];
    $name = trim($_POST['profile_name']);

    $delete_q = "DELETE FROM user_asset_profile WHERE email = ? AND profile_name = ?";
    try {
        $query_repo->execute($delete_q, $email, $name);
        echo json_encode(["status"=>"success"]);
        exit;
    } catch (PDOException $e) {
        error_log("Failed removing profile " . $e->getMessage());
        echo json_encode(["status"=>"failure " . $e->getMessage()]);
        exit;
    }
}
echo json_encode(["status"=>"failure: POST not set"]);
exit;
?>
