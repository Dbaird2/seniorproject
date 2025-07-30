<?php
require_once '../../config.php';
check_auth();
if (isset($_POST)) {
    $dept_id = trim($_POST['dept_id']);
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];

    $select_q = "SELECT asset_tag
    FROM asset_info
    WHERE dept_id = :dept_id";
    $select_stmt = $dbh->prepare($select_q);
    $select_stmt->execute([":dept_id" => $dept_id]);
    $asset_results = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

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
