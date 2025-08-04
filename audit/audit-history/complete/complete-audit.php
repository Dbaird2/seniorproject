<?php
require_once "../../../config.php";
if (isset($_POST)) {
    $dept_id = $_POST['dept_id'];
    $audit_id = $_POST['audit_id'];
    try {
        $update_q = "UPDATE audit_history SET audit_status = 'Complete', finished_at = CURRENT_TIMESTAMP WHERE dept_id = :dept_id AND audit_id = :audit_id";
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([":dept_id"=>$dept_id,":audit_id"=>$audit_id]);
    } catch (PDOException $e) {
        error_log("Error updating: " . $e-getMessage());
        exit;
    }
}

