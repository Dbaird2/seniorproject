<?php
include_once '../../config.php';
if (isset($_POST)) {
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];
    $tag = trim($_POST['asset_tag']);
    try {

        $result = $query_repo->fetchOne(
            "SELECT asset_tag FROM user_asset_profile WHERE email = ? AND profile_name = ? AND asset_tag = ?",
            $email,
            $profile_name,
            $tag
        );
        if (!$result) {
            try {

                $query_repo->execute(
                    "INSERT INTO user_asset_profile 
                    (email, profile_name, asset_tag)
                    VALUES
                    (?, ?, ?)",
                    $email,
                    $profile_name,
                    $tag
                );
            } catch (PDOException $e) {
                error_log("Error: " . $e->getMessage());
            }
        } else {
            echo json_encode(['status' => 'failed', 'reason' => 'tag already exists']);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
