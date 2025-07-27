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
if ($search === 'all') {
    $select_query = "SELECT dept_id, auditor, finished_at FROM audit_history ORDER BY finished_at DESC"; 
    $stmt = $dbh->prepare($select_query);
    $stmt->execute();
} else {
    $select_query = "SELECT dept_id, auditor, finished_at FROM audit_history WHERE dept_id LIKE :search ORDER BY finished_at DESC";
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
                <th>Audit ID</th>
            </tr>
        </thead>
        <tbody>
<?php
$i= 0;
foreach ($audits as $row) {
    $color = ($i++ % 2 == 0) ? 'even' : 'odd';
    echo "<tr class='$color'>";
    echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($row['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row['audit_id'])) . "'>" . $row['dept_id'] . "</a></td>";
    echo "<td>" . $row['auditor'] . "</td>";
    echo "<td>" . date('Y-m-d H:i:s', strtotime($row['finished_at'])) . "</td>";
    echo "</tr>";
}
?>
        </tbody>
    </table>
</body>
</html>

