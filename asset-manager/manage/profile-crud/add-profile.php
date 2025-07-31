<?php
include_once "../../../config.php";
if (isset($_POST)) {
    $email = $_SESSION['email'];
    $name = $_POST['display_name'];
    $select_q = "SELECT COUNT(*) as profile_count from user_asset_profile WHERE email = :email";
    try {
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([$email]);
        $count = $select_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed checking profile limit" . $e->getMessage());
        echo json_encode(['failed'=>'selecting']);
    }
    if ((int) $count['profile_count'] <= 6) {
        $insert_q = "INSERT INTO user_asset_profile (email, profile_name) VALUES (?, ?)";
        try {
            $insert_stmt = $dbh->prepare($insert_q);
            $insert_stmt->execute([$email, $name]);
        } catch (PDOException $e) {
            echo json_encode(['failed'=>'inserting']);
            error_log("Failed adding profile " . $e->getMessage());
        }
    } else {
        echo json_encode(["status" => "failed", "Reason" => "Profile Limit reached"]);
    }
}

