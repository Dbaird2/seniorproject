<?php
require_once __DIR__ .  "/../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT jsonb_array_elements(audit_data)->>'Tag Number' AS tag, jsonb_array_elements(audit_data)->>'Found Note' AS note, jsonb_array_elements(audit_data)->>'Found Timestamp' AS time, jsonb_array_elements(audit_data)->>'Tag Status' AS status, jsonb_array_elements(audit_data)->>'Descr' AS description, audit_id, dept_id
FROM audit_history
ORDER BY jsonb_array_elements(audit_data)->>'Tag Number' DESC";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
$array_dump = function($array) {
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
};
$array_dump($result);
$insert_note = 'INSERT INTO audited_asset (audit_id, dept_id, asset_tag, note) VALUES (:audit, :dept, :tag, :note) ON CONFLICT (asset_tag, dept_id, audit_id) DO UPDATE set note = EXCLUDED.note';
$insert_no_note = 'INSERT INTO audited_asset (audit_id, dept_id, asset_tag, note) VALUES (:audit, :dept, :tag, :note) ON CONFLICT (asset_tag, dept_id, audit_id) DO NOTHING';

$ASI = "/^A[SI]?\d+$/";
$STU = "/^S[RC]?[TU]?\d+$/";
$CMP = "/^\d+/";
$FDN = "/^F[DN]?\d+$/";
$SPA = "/^SP\d+$/";
foreach ($result as $row) {
    if ($row['status'] === 'Found' || $row['status'] === 'Extra') {
        if (
            preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
            preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
            preg_match($SPA, $tag_num)
        ) {
            if (!empty($row['note']) {
                $stmt = $dbh->prepare($insert_note);
                $stmt->execute([':audit'=>$row['audit_id'], ':dept'=>$row['dept_id'], ':tag'=>$row['tag'], ':note'=>$row['note']]);
                echo "Insert/Update ON Tag " . $row['tag'];
            } else {
                $stmt = $dbh->prepare($insert_no_note);
                $stmt->execute([':audit'=>$row['audit_id'], ':dept'=>$row['dept_id'], ':tag'=>$row['tag'], ':note'=>$row['note']]);
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
