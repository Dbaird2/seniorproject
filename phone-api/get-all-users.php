<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data);

if (isset($_POST)) {
    $select_count = "SELECT COUNT(*) FROM user_table WHERE u_role = 'custodian'";
    $select_stmt = $dbh->query($select_count);
    $row_count = $select_stmt->fetchColumn();
    $select = "SELECT CONCAT(f_name ,' ', l_name) as custodian, STRING_AGG(c, ',') as dept_id FROM user_table, unnest(dept_id) as c group by custodian;";
    $select_stmt = $dbh->query($select);
    $data = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["data" =>$data, 'count'=>$row_count]);
    exit;
}

