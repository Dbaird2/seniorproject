<?php

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!empty($data)) {
    $data_to_delete = $data['data'];
    $type = $data['type'];

    if ($type === 'user') {
        try {
            $delete_q = "DELETE FROM user_table WHERE email = :email";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":email"=>$data_to_delete]);
            
            echo json_encode(["status"=>"successfully deleted user"]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status"=>"failed to delete user" . $e->getMessage()]);
            exit;
        }
    }
    if ($type === 'dept') {
        try {
            $delete_q = "DELETE FROM department WHERE dept_id = :dept";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":dept_id"=>$data_to_delete]);
            
            echo json_encode(["status"=>"successfully deleted dept"]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status"=>"failed to delete department " . $e->getMessage()]);
            exit;
        }
    }
    if ($type === 'room') {
        try {
            $delete_q = "DELETE FROM room_table WHERE room_tag = :tag";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":tag"=>$data_to_delete]);
            
            echo json_encode(["status"=>"successfully deleted room"]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status"=>"failed to delete room " . $e->getMessage()]);
            exit;
        }
    }
    if ($type === 'asset') {
        try {
            $delete_q = "DELETE FROM user_table WHERE asset = :asset";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_stmt->execute([":asset"=>$data_to_delete]);
            
            echo json_encode(["status"=>"successfully deleted asset"]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status"=>"failed to delete asset " . $e->getMessage()]);
            exit;
        }
    }
}

