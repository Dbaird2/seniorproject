<?php
include_once '../../config.php';
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $tag = trim($_POST['asset_tag']);
    try {
        $select_tag = "SELECT asset_tag FROM user_asset_profile WHERE email = :email AND profile_name = :profile_name AND asset_tag = :asset_tag";
        $select_stmt = $dbh->prepare($select_tag);
        $select_stmt->execute([":email"=>$email,":profile_name"=>$profile_name,":asset_tag"=>$tag]);
        $result = $select_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            try {
                $insert_q = "INSERT INTO user_asset_profile 
                    (email, profile_name, asset_tag)
                    VALUES
                    (?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$email, $profile_name, $tag]);
            } catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
            }
        } else {
            echo json_decode(['status'=>'failed', 'reason'=>'tag already exists']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}

