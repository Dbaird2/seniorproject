<?php
require_once "../../../config.php";
if (isset($_POST)) {
    $dept_id = $_GET['dept_id'];
    $audit_id = (int)$_GET['audit_id'];
    $get_curr_ids = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
    $curr_stmt = $dbh->query($get_curr_ids)->execute();
    $curr_results = $curr_stmt->fetch(PDO::FETCH_ASSOC);

    if ($audit_id === 3) {
        $id = $curr_results['curr_self_id'] === 1 ? 2 : 1;
        $delete_q = "DELETE FROM audit_history WHERE audit_id = :id AND dept_id = :dept_id";
        $delete_smt  = $dbh->prepare($delete_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);

        $update_q = "UPDATE audit_history SET audit_status = 'Complete', audit_id = :id";
        $update_stmt = $dbh->prepare($update_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);
    } else if ($audit_id === 6) {
        $id = $curr_results['curr_self_id'] === 4 ? 5 : 4;
        $delete_q = "DELETE FROM audit_history WHERE audit_id = :id AND dept_id = :dept_id";
        $delete_smt  = $dbh->prepare($delete_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);

        $update_q = "UPDATE audit_history SET audit_status = 'Complete', audit_id = :id";
        $update_stmt = $dbh->prepare($update_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);
    } else if ($audit_id === 9) {
        $id = $curr_results['curr_self_id'] === 7 ? 8 : 7;
        $delete_q = "DELETE FROM audit_history WHERE audit_id = :id AND dept_id = :dept_id";
        $delete_smt  = $dbh->prepare($delete_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);

        $update_q = "UPDATE audit_history SET audit_status = 'Complete', audit_id = :id";
        $update_stmt = $dbh->prepare($update_q)->execute([":id"=>$id,":dept_id"=>$dept_id]);
    } else {
        try {
            $update_q = "UPDATE audit_history SET audit_status = 'Complete', finished_at = CURRENT_TIMESTAMP WHERE dept_id = :dept_id AND audit_id = :audit_id";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":dept_id"=>$dept_id,":audit_id"=>$audit_id]);
        } catch (PDOException $e) {
            error_log("Error updating: " . $e-getMessage());
            exit;
        }
    }
}
exit;

