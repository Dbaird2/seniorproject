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
foreach ($result as $row) {
    echo $row['tag'] . '<br>';
    echo $row['note'] . '<br>';
    echo $row['time'] . '<br>';
    echo $row['status'] . '<br>';
    echo $row['description'] . '<br>';
    echo $row['dept_id'] . '<br>';
    echo $row['audit_id'] . '<br>';
}
?>
