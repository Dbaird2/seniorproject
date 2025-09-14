<?php
header('Content-Type: application/json');

$json = file_get_contents('php://input');

$post_data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON decoding error: " . json_last_error_msg();
    exit();
}

$update = "UPDATE user_asset_profile SET color = :color WHERE profile_name = :profile AND email = :email";
try {
    $update_stmt = $dbh->prepare($update);
    $update_stmt->execute([":color"=>$post_data['color'], ':profile'=>$post_data['profile_name'], ':email'=>$post_data['email']]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit;
}
exit;
