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
    $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted FROM audit_history ORDER BY audit_id"; 
    $stmt = $dbh->prepare($select_query);
    $stmt->execute();
} else {
    $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted FROM audit_history WHERE dept_id LIKE :search ORDER BY audit_id";
    $search = '%' . $search . '%';
    $stmt = $dbh->prepare($select_query);
    $stmt->execute([':search' => $search]);
}
$audits = $stmt->fetchAll(PDO::FETCH_ASSOC);
$depts = "SELECT DISTINCT(a.dept_id) as dept_id, dept_name FROM asset_info a LEFT JOIN departments d ON a.dept_id = d.dept_id ORDER BY a.dept_id ";
$dept_stmt = $dbh->query($depts);
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($departments as $index => $dept) {
    foreach ($audits as $audit) {
        if ($audit['dept_id'] === $dept['dept_id']) {
            $dept_info[$dept['dept_id']][] = [$audit['audit_id'], $audit['audit_status'], $audit['forms_submitted'], $audit['auditor']];
        } else {
            $dept_info[$dept['dept_id']][] = [0, 'Not Started'];             
        }
    }
}
echo "<pre>";
var_dump($dept_info);
echo "</pre>";
$count = 0;
if (count($audits) > 0) {
?>
        <table class="is-history" id="is-history">
        <thead>
            <tr class="odd">
                <th>ID</th>
                <th>Name</th>
                <!-- <th>Auditor</th> -->
                <!-- <th>Audit Timestamp</th> -->
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
    foreach ($departments as $dept) {
        $count++;
        $count_dept = 0;
        $color = ($i++ % 2 == 0) ? 'even' : 'odd';
        echo "<tr class='$color'>";
        echo "<td><a href='#'>".$dept['dept_id']."</a></td>";
         
        if (!empty($dept_info[$dept['dept_id']])) {
            foreach ($dept_info[$dept['dept_id']] as $row) {
                if (empty($row[0])) {
                    continue;
                }
                echo "<td>" . $audit_type[(int)$row[0]] . "</td>";
                echo "<td>" . $row[1] . "</td>";
                if ($row[1] === 'In Progress') {
                    echo "<td>";
                    if (($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1,2,3]))) {
                        echo "<a href='continue/get-audit-hist-data.php?dept_id=".htmlspecialchars(urlencode($dept['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row[0]))."'>Continue</a>  ";
                    }
                    if (($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1,2,3])) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
                        echo "<a href='complete/start-bulk-transfer.php?dept_id=".htmlspecialchars(urlencode($dept['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row[0]))."&complete=true'>Start Forms</a>  ";
                    }
                    if ((($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1,2,3]))) && $row[2] === true) {
                        echo "<a href='https://dataworks-7b7x.onrender.com/kauliAPI/write/complete-audit.php?dept_id=".htmlspecialchars(urlencode($dept['dept_id']))."&audit_id=".htmlspecialchars(urlencode($row[0]))."'>Complete</a>";
                    }
                    echo "</td>";
                }
                echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=".htmlspecialchars(urlencode($row[3]))."'>PDF</a>  ";
                echo "<td><a href='download-excel.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=".htmlspecialchars(urlencode($row[3]))."'>Excel</a></td>";
                if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
                    echo "<td><a href='delete-audit.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=".htmlspecialchars(urlencode($row[3]))."'>Delete</a></td>";
                }
            }
        } else {
            echo "<td>Self Audit</td>";
            echo "<td>Not Started</td>";
            echo "<td>Management Audit</td>";
            echo "<td>Not Started</td>";
            echo "<td>Previous Self Audit</td>";
            echo "<td>Not Started</td>";
            echo "<td>Previous Management Audit</td>";
            echo "<td>Not Started</td>";

        }
        echo "</tr>";
    }
?>
        </tbody>
</table>
<?php } ?>
</body>
</html>

