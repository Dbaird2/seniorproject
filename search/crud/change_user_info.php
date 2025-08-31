<?php
include_once "../../config.php";

if (isset($_POST['user'])) {
    $new_dept = trim($_POST['dept_ids']);
    $new_role = trim($_POST['role']);
    $old_dept = trim($_POST['old_dept']);
    $old_role = trim($_POST['old_role']);
    $email = trim($_POST['old_email']);
    $delete = false;
    try {
        if (isset($_POST['delete-user'])) {
            $delete_q = "DELETE FROM user_table WHERE email = :email";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([':email'=>$email]);
            header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    try {
        if (!empty($new_dept) && $new_dept !== $old_dept) {
            $update_q = "UPDATE user_table SET dept_id = :dept::VARCHAR[] WHERE email = :email";
            $update_stmt = $dbh->prepare($update_q);
            $new_dept = '{' .$new_dept.'}';
            $update_stmt->execute([":dept"=>$new_dept, ":email"=>$email]);
        } 
        if (!empty($new_role) && $new_role !== $old_role) {
            $update_q = "UPDATE user_table SET u_role= :role WHERE email = :email";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":role"=>$new_role, ":email"=>$email]);
        }
    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
        exit;
    }
}
exit;
