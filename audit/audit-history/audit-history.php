<?php
include_once("../../config.php");
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
    $prev_mgmt_id = ($curr_results['curr_mgmt_id'] === 4) ? 5 : 4;
    $prev_self_id = ($curr_results['curr_self_id'] === 1) ? 2 : 1;
    $prev_spa_id = ($curr_results['curr_spa_id'] === 7) ? 8 : 7;
    $and = $status_search === '' ? '' : ' AND ';
    if ($search === 'all') {
        $depts = "SELECT DISTINCT(a.dept_id) as dept_id, dept_name FROM asset_info a LEFT JOIN department d ON a.dept_id = d.dept_id ORDER BY a.dept_id ";
        $dept_stmt = $dbh->query($depts);
        $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

        $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted FROM audit_history ORDER BY audit_id";
        $stmt = $dbh->prepare($select_query);
        $stmt->execute();
    } else {
        $search = '%' . $search . '%';
        $depts = "SELECT DISTINCT(a.dept_id) as dept_id, dept_name FROM asset_info a LEFT JOIN department d ON a.dept_id = d.dept_id WHERE a.dept_id ILIKE :search OR d.dept_name ILIKE :search ORDER BY a.dept_id ";
        $dept_stmt = $dbh->prepare($depts);
        $dept_stmt->execute([":search" => $search]);
        $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

        $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted FROM audit_history WHERE dept_id ILIKE :search ORDER BY audit_id";
        $stmt = $dbh->prepare($select_query);
        $stmt->execute([':search' => $search]);
    }
    $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($departments as $index => $dept) {
        foreach ($audits as $audit) {
            if ($audit['dept_id'] === $dept['dept_id']) {
                $dept_info[$dept['dept_id']][] = [$audit['audit_id'], $audit['audit_status'], $audit['forms_submitted'], $audit['auditor']];
            }
        }
    }
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
                $i = 0;

                foreach ($departments as $index => $dept) {
                    $count++;
                    $count_dept = 0;
                    $color = ($i++ % 2 == 0) ? 'even' : 'odd';
                    echo "<tr class='$color'>";
                    echo "<td>" . $dept['dept_id'] . "</td>";
                    echo "<td>" . $dept['dept_name'] . "</td>";
                    $curr_self = $curr_mgmt = $curr_spa = false;
                    $prev_self = $prev_mgmt = $prev_spa = false;
                    $curr_self_i = $curr_mgmt_i = $curr_spa_i = -1;
                    $prev_self_i = $prev_mgmt_i = $prev_spa_i = -1;
                    if (!empty($dept_info[$dept['dept_id']])) {
                        $row = $dept_info[$dept['dept_id']];
                        $audit_count = count($row);

                        foreach ($row as $index => $id) {
                            if ($id[0] === $curr_results['curr_mgmt_id']) {
                                $curr_mgmt_i = $index;
                                $curr_mgmt = true;
                            } else if ($id[0] === $curr_results['curr_self_id']) {
                                $curr_self_i = $index;
                                $curr_self = true;
                            } else if ($id[0] === $curr_results['curr_spa_id']) {
                                $curr_spa_i = $index;
                                $curr_spa = true;
                            } else if ($id[0] === $prev_mgmt_id) {
                                $prev_mgmt_i = $index;
                                $prev_mgmt = true;
                            } else if ($id[0] === $prev_self_id) {
                                $prev_self_i = $index;
                                $prev_self = true;
                            } else if ($id[0] === $prev_spa_id) {
                                $prev_spa_i = $index;
                                $prev_spa = true;
                            }
                        }
                        if (!$curr_self) {
                            echo "<td>Self Audit</td>";
                            echo "<td style='color:red;'>Not Started</td>";
                        } else {
                            displayAuditData($row[$curr_self_i], $dept);
                        }
                        if (!$curr_mgmt) {
                            echo "<td>Management Audit</td>";
                            echo "<td style='color:red;'>Not Started</td>";
                        } else {
                            displayAuditData($row[$curr_mgmt_i], $dept);
                        }
                        if (!$curr_spa) {
                        } else {
                            displayAuditData($row[$curr_spa_i], $dept);
                        }
                        if (!$prev_self) {
                            echo "<td>Previous Self Audit</td>";
                            echo "<td style='color:red;'>Not Started</td>";
                        } else {
                            displayAuditData($row[$prev_self_i], $dept);
                        }
                        if (!$prev_mgmt) {
                            echo "<td>Previous Management Audit</td>";
                            echo "<td style='color:red;'>Not Started</td>";
                        } else {
                            displayAuditData($row[$prev_mgmt_i], $dept);
                        }
                        if (!$prev_spa) {
                        } else {
                            displayAuditData($row[$prev_spa_i], $dept);
                        }
                    } else {
                        echo "<td>Self Audit</td>";
                        echo "<td style='color:red;'>Not Started</td>";
                        echo "<td>Management Audit</td>";
                        echo "<td style='color:red;'>Not Started</td>";
                        echo "<td>Previous Self Audit</td>";
                        echo "<td style='color:red;'>Not Started</td>";
                        echo "<td>Previous Management Audit</td>";
                        echo "<td style='color:red;'>Not Started</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php } ?>
</body>

</html>
<?php function displayAuditData($row, $dept)
{
    global $curr_results;
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
    $color = 'red';
    if ($row[1] === 'In Progress') {
        $color = 'yellow';
    } else if ($row[1] === 'Complete') {
        $color = 'green';
    }
    echo "<td>" . $audit_type[(int)$row[0]] . "</td>";
    echo "<td style='color:'" . $color . ";'>" . $row[1] . "</td>";
    if ($row[1] === 'In Progress') {
        echo "<td>";
        if (($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1, 2, 3]))) {
            echo "<a href='continue/get-audit-hist-data.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "'>Continue</a>  ";
        }
        if (($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1, 2, 3])) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<a href='complete/start-bulk-transfer.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&complete=true'>Start Forms</a>  ";
        }
        if ((($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$row[0], [1, 2, 3]))) && $row[2] === true) {
            echo "<a href='https://dataworks-7b7x.onrender.com/kauliAPI/write/complete-audit.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "'>Complete</a>";
        }
        echo "</td>";
    }
    echo "<td><a href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=" . htmlspecialchars(urlencode($row[3])) . "'>PDF</a>  ";
    echo "<a href='download-excel.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=" . htmlspecialchars(urlencode($row[3])) . "'>Excel</a></td>";
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
        echo "<td><a href='delete-audit.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($row[0])) . "&auditor=" . htmlspecialchars(urlencode($row[3])) . "'>Delete</a></td>";
        echo "<br>";
    }
}

