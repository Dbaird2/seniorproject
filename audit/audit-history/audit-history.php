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
$get_curr_ids = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
$curr_stmt = $dbh->query($get_curr_ids);
$curr_stmt->execute();
$curr_results = $curr_stmt->fetch(PDO::FETCH_ASSOC);
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
if ($curr_results['curr_self_id'] === 1) {
    $audit_type[1] = 'Self Audit';
    $audit_type[2] = 'Previous Self Audit';
    $audit_type[3] = 'Overdue Self Audit';
} else {
    $audit_type[1] = 'Previosu Self Audit';
    $audit_type[2] = 'Self Audit';
    $audit_type[3] = 'Overdue Self Audit';
}
if ($curr_results['curr_mgmt_id'] === 4) {
    $audit_type[4] = 'Management Audit';
    $audit_type[5] = 'Previous Management Audit';
    $audit_type[6] = 'Overdue Management Audit';
} else {
    $audit_type[4] = 'Previosu Management Audit';
    $audit_type[5] = 'Management Audit';
    $audit_type[6] = 'Overdue Management Audit';
}
if ($curr_results['curr_spa_id'] === 8) {
    $audit_type[7] = 'SPA Audit';
    $audit_type[8] = 'Previous SPA Audit';
    $audit_type[9] = 'Overdue SPA Audit';
} else {
    $audit_type[7] = 'Previosu SPA Audit';
    $audit_type[8] = 'SPA Audit';
    $audit_type[9] = 'Overdue SPA Audit';
}
foreach ($audits as $row) {
    if (in_array($audit_type[(int)$row['audit_id']], ['SPA Audit', 'Management Audit', 'Self Audit'], true)) {
        $count++;
        $color = ($i++ % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td><a href='#'>".$row['dept_id']."</a></td>";
        echo "<td>" . $row['auditor'] . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', strtotime($row['finished_at'])) . "</td>";
        echo "<td>" . $row['audit_status'] . "</td>";
        echo "<td>" . $audit_type[(int)$row['audit_id']] . "</td>";
    if ($row['audit_status'] === 'In Progress') {
        if (($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $row['dept_id'] && in_array((int)$row['audit_id'], [1,2,3]))) {
            echo "<td><a href='continue/get-audit-hist-data.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."'>Continue Audit</a></td>";
        }
        if (($_SESSION['deptid'] === $row['dept_id'] && in_array((int)$row['audit_id'], [1,2,3])) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<td><a href='continue/get-audit-hist-data.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."&complete=true'>Complete Audit</a></td>";
        }
    }
        echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>PDF</a></td>";
        echo "<td><a href='download-excel.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>Excel</a></td>";
        if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<td><a href='delete-audit.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>Delete</a></td>";
        }
        echo "</tr>";
    }
}
    if ($i === 0) {
        $color = ($i % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td>No Current Audits Completed/In Progress</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "</tr>";
    }
?>
        </tbody>
    </table>
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
$i = 0;
foreach ($audits as $row) {
    if (in_array($audit_type[(int)$row['audit_id']], ['Previous SPA Audit', 'Previous Management Audit', 'Previous Self Audit','Overdue SPA Audit', 'Overdue Management Audit', 'Overdue Self Audit'], true)) {
        $color = ($i++ % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td><a href='#'>".$row['dept_id']."</a></td>";
        echo "<td>" . $row['auditor'] . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', strtotime($row['finished_at'])) . "</td>";
        echo "<td>" . $row['audit_status'] . "</td>";
        echo "<td>" . $audit_type[(int)$row['audit_id']] . "</td>";
    if ($row['audit_status'] === 'In Progress') {
        if (($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $row['dept_id'] && in_array((int)$row['audit_id'], [1,2,3]))) {
            echo "<td><a href='continue/get-audit-hist-data.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."'>Continue Audit</a></td>";
        }
        if (($_SESSION['deptid'] === $row['dept_id'] && in_array((int)$row['audit_id'], [1,2,3])) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<td><a href='complete/complete-audit.php?dept_id=".htmlspecialchars(urlencode($row['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row['audit_id']))."'>Complete Audit</a></td>";
        }
    }
        echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>PDF</a></td>";
        echo "<td><a href='download-excel.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>Excel</a></td>";
        if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<td><a href='delete-audit.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "&auditor=".htmlspecialchars(urlencode($row['auditor']))."'>Delete</a></td>";
        }
        echo "</tr>";
    }
}
    if ($i === 0) {
        $color = ($i++ % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td>No Previous Audits Completed/In Progress</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "</tr>";
    }
?>
        </tbody>
    </table>
</body>
</html>

