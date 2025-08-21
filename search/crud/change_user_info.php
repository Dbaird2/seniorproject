<?php
include_once "../../config.php";

if (isset($_POST['user'])) {
    $new_dept = trim($_POST['dept_ids']);
    $new_role = trim($_POST['role']);
    $old_dept = trim($_POST['old_dept']);
    $old_role = trim($_POST['old_role']);
    $email = trim($_POST['email']);

    try {
    if ($new_dept === '' && $new_role === '') {
        exit;
    } else if ($new_dept !== '') {
        $update_q = "UPDATE user_table SET dept_id = :dept::VARCHAR[] WHERE email = :email";
        $dept_id_array = array_values(array_filter(explode(',', $new_dept), fn($v) => trim($v) !== ''));
        $dept_id_array = array_map('trim', $dept_id_array);
        $dept_pg_array = '{' . implode(',', array_map(function($val) {
            return '"' . addslashes($val) . '"';
        }, $dept_id_array)) . '}';
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([":dept"=>$new_dept, ":email"=>$email]);

    } 
    if ($new_role !== '') {
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
