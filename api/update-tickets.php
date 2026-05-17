<?php
try {
    if (isset($_GET['id'])) {
        require_once "../config.php";
        $id = $_GET['id'];
        $action = $_GET['action'];

        if ($action === 'delete') {
            try {
                $query_repo->execute("DELETE FROM ticket_table WHERE id = ?", $id);
                echo json_encode(["status"=>"deleted ticket " . $id]);
                exit;
            } catch (PDOException $e) {
                echo json_encode(["Error with deleting ticket table at ID: " . $id]);
                exit;
            }
        } else if ($action === 'completed') {
            try {
                $query_repo->execute("UPDATE ticket_table SET ticket_status = 'Complete' WHERE id = ?", $id);
                echo json_encode(["status"=>"updated ticket " . $id]);
                exit;
            } catch (PDOException $e) {
                echo json_encode(["Error with updating ticket table at ID: " . $id]);
                exit;
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
} catch (Exception $e) {
    error_log($e->getMessage());
}
echo json_encode(["status"=>"POST not available"]);
exit;

