<?php
require_once "../config.php";
header('Content-Type: application/json');

$json = file_get_contents('php://input');

$post_data = json_decode($json, true);


$update = "UPDATE user_asset_profile SET color = ? WHERE profile_name = ? AND email = ? AND asset_tag = ?";
try {
    $query_repo->execute($update, $post_data['color'], $post_data['profile_name'], $_SESSION['email'], $post_data['asset_tag']);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit;
}
exit;
