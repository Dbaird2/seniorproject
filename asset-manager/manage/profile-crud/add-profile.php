<?php
include_once "../../../config.php";
if (isset($_POST['profile_name'])) {
    $email = $_SESSION['email'];
    $name = trim($_POST['profile_name']);
    $select_q = "SELECT COUNT(DISTINCT(profile_name, email)) as profile_count from user_asset_profile WHERE email = :email";
    try {
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([$email]);
        $count = $select_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed checking profile limit" . $e->getMessage());
        echo json_encode(['failed'=>'selecting']);
        exit;
    }
    if ((int) $count['profile_count'] <= 8) {
        $select_q = "SELECT profile_name, email from user_asset_profile WHERE profile_name = :profile AND email = :email";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":profile"=>$name,":email"=>$email]);
        if ($select_stmt->rowCount() <= 0) {
            $insert_q = "INSERT INTO user_asset_profile (email, profile_name) VALUES (?, ?)";
            try {
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$email, $name]);
                echo json_encode(['status' => 'success']);
                exit;
            } catch (PDOException $e) {
                echo json_encode(['failed'=>'inserting']);
                error_log("Failed adding profile " . $e->getMessage() . ' profile_name value ' . $name);
                exit;
            }
        } else {
            echo json_encode(["status" => "failed", "Reason" => "Profile Limit reached"]);
            exit;
        }
    } else {
        echo json_encode(["status"=>"failed", "reason"=> "Profile Name Already Used"]);
        exit;
    }
}
