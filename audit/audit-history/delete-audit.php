<?php
require_once "../../config.php";

check_auth();

$auditor = $_GET['auditor'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';
$audit_id = $_GET['audit_id'] ?? '';

$select = "SELECT audit_id, auditor, dept_id FROM audit_history WHERE dept_id = ? AND auditor = ? AND audit_id = ?";

$results = $query_repo->fetchAll($select, $dept_id, $auditor, $audit_id);
if ($results) {
    $delete = "DELETE FROM audit_history WHERE dept_id = ? AND auditor = ? AND audit_id = ?";
    $query_repo->execute($delete, $dept_id, $auditor, $audit_id);


    $delete = 'DELETE FROM audited_asset WHERE dept_id = ? AND audit_id = ?';
    $query_repo->execute($delete, $dept_id, $audit_id);
}
header("Location: https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php");
exit;
