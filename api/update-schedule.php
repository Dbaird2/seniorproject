<?php
try {
    if (isset($_GET['id'])) {
        require_once "../config.php";
        $id = $_GET['id'];
        $action = $_GET['action'];
        $date = $_GET['delete_date'];

        if ($action === 'delete') {
            try {
                $query_repo->execute("DELETE FROM audit_schedule WHERE dept_id = ? AND audit_date = ?", $id, $date);
                echo json_encode(["status"=>"deleted schedule " . $id]);
                exit;
            } catch (PDOException $e) {
                echo json_encode(["Error with deleting schedule table at ID: " . $id]);
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

