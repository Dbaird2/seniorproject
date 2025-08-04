<?php 
include_once ("../../config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
<link rel="stylesheet" href="audit-history.css">
</head>
<body>
<?php
$search = $_POST['search'];
$status_search = (isset($_POST['audit-status'])) ? $_POST['audit-status'] : '';
$and = $status_search === '' ? '' : ' AND ';
if ($search === 'all') {
    $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status FROM audit_history ORDER BY finished_at DESC"; 
    $stmt = $dbh->prepare($select_query);
    $stmt->execute();
} else {
    $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status FROM audit_history WHERE dept_id LIKE :search ORDER BY finished_at DESC";
    $search = '%' . $search . '%';
    $stmt = $dbh->prepare($select_query);
    $stmt->execute([':search' => $search]);
}
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
        <table class="is-history" id="is-history">
        <thead>
            <tr class="odd">
                <th>Department</th>
                <th>Auditor</th>
                <th>Audit Timestamp</th>
                <th>Audit Status</th>
                <th>Audit Type</th>
            </tr>
        </thead>
        <tbody>
<?php
$i= 0;
$audit_type = [
    1 => 'Self Audit',
    2 => 'Self Audit',
    3 => 'Management Audit',
    4 => 'Management Audit',
    5 => 'FDN Audit',
    6 => 'FDN Audit',
    7 => 'SPA Audit',
    8 => 'SPA Audit'
];
foreach ($audits as $row) {
        $color = ($i++ % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td><a href='#'>".$row['dept_id']."</a></td>";
        echo "<td>" . $row['auditor'] . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', strtotime($row['finished_at'])) . "</td>";
        echo "<td>" . $row['audit_status'] . "</td>";
        echo "<td>" . $audit_type[(int)$row['audit_id']] . "</td>";
    if ($row['audit_status'] === 'In Progress') {
        echo "<td><a href='https://dataworks-7b7x.onrender.com/audit/continue/get-audit-hist-data.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."&audit_type=".htmlspecialchars(urlencode($audit_type[(int)$row['audit_id']]))."><button>Continue Audit</button></a></td>";
        echo "<td><a href='complete/complete-audit.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."><button>Complete Audit</button></a></td>";
    }
        echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>PDF</a></td>";
        echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>Excel</a></td>";
        echo "</tr>";
}
?>
        </tbody>
    </table>
</body>
</html>

