<?php

if (isset($_GET['id'])) {
    require_once "../config.php";
    $id = $_GET['id'];
    $action = $_GET['action'];
    $date = $_GET['delete_date'];

    if ($action === 'delete') {
        try {
            $delete_q = "DELETE FROM audit_schedule WHERE dept_id = :id AND audit_date = :date";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":id"=>$id, ':date'=>$date]);
            echo json_encode(["status"=>"deleted schedule " . $id]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["Error with deleting schedule table at ID: " . $id]);
            exit;
        }
    }
}
echo json_encode(["status"=>"POST not available"]);
exit;

