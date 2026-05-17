<?php
include_once '../../config.php';
if (isset($_POST)) {
    $dept_name = trim($_POST['dept_name']);
    $profile_name = trim($_POST['profile_name']);
    $email = $_SESSION['email'];

    $select_q = "SELECT dept_id from department where dept_name = ?";
    $dept = $query_repo->fetchOne($select_q, $dept_name);

    $dept_id = $dept['dept_id'];

    $select_q = "SELECT asset_tag
    FROM asset_info
    WHERE dept_id = ?";
    $asset_results = $query_repo->fetchAll($select_q, $dept_id);

    // ADD DELETE QUERY TO THIS
    $delete_q = "DELETE FROM user_asset_profile WHERE profile_name = ? AND email = ?";
    $query_repo->execute($delete_q, $profile_name, $email);

    try {
        $query_repo->beginTransaction();
        foreach ($asset_results as $index => $row) {
            $insert_q = "INSERT INTO user_asset_profile
                (profile_name, email, asset_tag) VALUES (?, ?, ?)";
            $query_repo->execute($insert_q, $profile_name, $email, $row['asset_tag']);
        }
        $query_repo->commit();
    } catch (PDOException $e) {
        $query_repo->rollback();
        error_log("Error with Quick Start: rolling back db");
    }
}
