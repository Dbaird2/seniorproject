<?php

if (isset($_GET['id'])) {
    require_once "../config.php";
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'delete') {
        try {
            $delete_q = "DELETE FROM ticket_table WHERE id = :id";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":id"=>$id]);
            echo json_encode(["status"=>"deleted ticket " . $id]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["Error with deleting ticket table at ID: " . $id]);
            exit;
        }
    } else if ($action === 'completed') {
        try {
            $update_q = "UPDATE ticket_table SET ticket_status = 'Complete' WHERE id = :id";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":id"=>$id]);
            echo json_encode(["status"=>"updated ticket " . $id]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["Error with updating ticket table at ID: " . $id]);
            exit;
        }
    }
}
echo json_encode(["status"=>"POST not available"]);
exit;

