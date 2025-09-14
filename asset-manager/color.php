<?php
require_once "../config.php";
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$json = file_get_contents('php://input');

$post_data = json_decode($json, true);


$update = "UPDATE user_asset_profile SET color = :color WHERE profile_name = :profile AND email = :email AND asset_tag = :tag" ;
try {
    $update_stmt = $dbh->prepare($update);
    $update_stmt->execute([":color"=>$post_data['color'], ':profile'=>$post_data['profile_name'], ':email'=>$_SESSION['email'], ":tag"=>$post_data['asset_tag']]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit;
}
exit;
