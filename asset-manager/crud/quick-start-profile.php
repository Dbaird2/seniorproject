<?php
include_once '../../config.php';
if (isset($_POST)) {
    $dept_name = trim($_POST['dept_name']);
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];

    $select_q = "SELECT asset_tag
    FROM asset_info
    WHERE dept_name = :dept_name";
    $select_stmt = $dbh->prepare($select_q);
    $select_stmt->execute([":dept_name" => $dept_name]);
    $asset_results = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    // ADD DELETE QUERY TO THIS
    $delete_q = "DELETE FROM user_asset_profile WHERE profile_name = :profile_name AND email = :email";
    $delete_stmt = $dbh->prepare($delete_q);
    $delete_stmt->execute([":profile_name"=>$profile_name,":email"=>$email]);

    try {
        $dbh->beginTransaction();
        foreach ($asset_results as $index=>$row) {
            $insert_q = "INSERT INTO user_asset_profile
            (profile_name, email, asset_tag)
            VALUES
            (?, ?, ?)";
            $insert_stmt = $dbh->prepare($insert_q);
            $insert_stmt->execute([$profile_name, $email, $row['asset_tag']]);
        }
        $dbh->commit();
    } catch (PDOException $e) {
        $dbh->rollback();
        error_log("Error with Quick Start: rolling back db");
    }
}
?>
