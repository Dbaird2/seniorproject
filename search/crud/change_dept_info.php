<?php
include_once "../../config.php";
check_auth("high");

if (isset($_POST['dept'])) {
    $old_dept_id = $_POST['old_dept'];
    $old_dept_name = $_POST['old_name'];
    $old_cust = $_POST['old_cust'];
    $old_manager = $_POST['old_manager'];

    $new_id = trim($_POST['dept']);
    $new_name = trim($_POST['name']);
    $new_cust = trim($_POST['cust']);
    $new_manager = trim($_POST['manager']);

    $params = [];
    $set_array = [];
    $where_array = [];
    $count = 0;
    if (isset($_POST['delete'])) {
        try {
            $delete_q = "DELETE FROM department WHERE dept_id = :dept";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":dept"=>$old_dept_id]);
            header("Location: https://dataworks-7b7x.onrender.com/search/search.php");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }

    try {
        if ($old_cust !== $new_cust) {
            $new_cust_array = str_getcsv($new_cust, ',', '"');
            if (is_array($new_cust_array) && !empty($new_cust_array)) {
                $new_cust_format = '{' . $new_cust . '}';
                $update_q = "UPDATE department SET custodian = :new_cust WHERE dept_id = :dept";
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([":new_cust"=>$new_cust_format, ":dept"=>$old_dept_id]);
            }
        }
        if ($old_manager !== $new_manager) {
            $update_q = "UPDATE department SET manager = :new_mana WHERE dept_id = :dept";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":new_mana"=>$new_manager, ":dept"=>$old_dept_id]);
        }
        if ($new_id !== $old_dept_id) {
            $update_q = "UPDATE department SET dept_id = :new_id WHERE dept_id = :old_id";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":new_id"=>$new_id, ":old_id"=>$old_dept_id]);

            $update_assets = "UPDATE asset_info SET dept_id = :new_id WHERE dept_id = :old_id";
            $update_stmt = $dbh->prepare($update_assets);
            $update_stmt->execute([":new_id"=>$new_id, ":old_id"=>$old_dept_id]);
        }
        if ($old_dept_name !== $new_name) {
            $update_q = "UPDATE department SET dept_name = :new_name WHERE dept_id = :old_name";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":new_name"=>$new_name, ":old_name"=>$old_dept_name]);
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    exit;
}
