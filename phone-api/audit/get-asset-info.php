<?php
header("Access-Control-Allow-Oirigin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../../config.php";


$decoded_data = file_get_contents('php://input');

$data = json_decode($decoded_data, true);
if (!empty($data['tag'])) {
    $select = "SELECT * FROM asset_info WHERE asset_tag = :tag";
    $stmt = $dbh->prepare($select);
    $stmt->execute([':tag'=>$data['tag']]);
    $tag_info = $stmt->fetch();
    if ($tag_info) {
        if (isset($data['email'])) {
            $select = 'SELECT u_role FROM user_table WHERE email = :email';
            $stmt = $dbh->prepare($select);
            $stmt->execute([':email'=>$data['email']]);
            $role = $stmt->fetch();
        }
        if (in_array($role, ['admin', 'management'])) {
            $select = 'SELECT curr_mgmt_id FROM audit_freq';
        } else {
            $select = 'SELECT curr_self_id FROM audit_freq';
        }
        $stmt = $dbh->query($select);
        $audit_id = $stmt->fetch();
        /*
        $update = 'INSERT INTO audited_asset (audit_id, dept_id, asset_tag) VALUES (?, ?, ?) 
            ON CONFLICT (audit_id, asset_tag) DO UPDATE SET dept_id = EXCLUDED.dept_id';
        $stmt = $dbh->prepare($update);
        $stmt->execute([$audit_id, $dept_id, $data['tag']]);
         */
    }
    echo json_encode(['status'=>'success', 'data'=>$tag_info]);
    exit;
}
echo json_encode(['POST'=>$data, 'status'=>'failure']);
exit;
