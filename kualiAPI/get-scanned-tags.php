<?php
require_once __DIR__ .  "/../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT 
    jsonb_array_elements(audit_data)->>'Tag Number' AS tag, 
    jsonb_array_elements(audit_data)->>'Found Note' AS note, 
    jsonb_array_elements(audit_data)->>'Found Timestamp' AS time, 
    jsonb_array_elements(audit_data)->>'Tag Status' AS status, 
    jsonb_array_elements(audit_data)->>'Found Room Tag' as room_tag,
    jsonb_array_elements(audit_data)->>'Descr' AS description, audit_id, dept_id
    FROM audit_history
    ORDER BY jsonb_array_elements(audit_data)->>'Tag Number' DESC";

$result = $query_repo->fetchAll($select);
$array_dump = function($array) {
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
};
$array_dump($result);
$insert_note = 'INSERT INTO audited_asset (audit_id, dept_id, asset_tag, note, room_tag) VALUES (?, ?, ?, ?, ?) ON CONFLICT (asset_tag, dept_id, audit_id) DO UPDATE SET note = EXCLUDED.note, room_tag = EXCLUDED.room_tag';
$insert_no_note = 'INSERT INTO audited_asset (audit_id, dept_id, asset_tag, note, room_tag) VALUES (?, ?, ?, ?, ?) ON CONFLICT (asset_tag, dept_id, audit_id) DO NOTHING';

$ASI = "/^A[SI]?\d+$/";
$STU = "/^S[RC]?[TU]?\d+$/";
$CMP = "/^\d+/";
$FDN = "/^F[DN]?\d+$/";
$SPA = "/^SP\d+$/";
foreach ($result as $row) {
    if ($row['status'] === 'Found' || $row['status'] === 'Extra') {
        if (
            preg_match($ASI, $row['tag']) || preg_match($STU, $row['tag']) ||
            preg_match($CMP, $row['tag']) || preg_match($FDN, $row['tag']) ||
            preg_match($SPA, $row['tag'])
        ) {
            if (!empty($row['note'])) {
                $query_repo->execute($insert_note, $row['audit_id'], $row['dept_id'], $row['tag'], $row['note'], $row['room_tag']);
                echo "Insert/Update ON Tag " . $row['tag'];
            } else {
                $query_repo->execute($insert_no_note, $row['audit_id'], $row['dept_id'], $row['tag'], $row['note'], $row['room_tag']);
                echo "Insert ON Tag " . $row['tag'];
            }
        } else {
            echo 'Tag is not a real tag <br>';
        }
    } else {
        echo 'Tag not found in audit <br>';
    }
}
?>
