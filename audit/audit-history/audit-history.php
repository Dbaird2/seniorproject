<?php
include_once("../../config.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit History</title>
    <link rel="stylesheet" href="audit-history.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="page-wrapper">
        <?php
        $get_curr_ids = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
        $curr_stmt = $dbh->query($get_curr_ids);
        $curr_stmt->execute();
        $curr_results = $curr_stmt->fetch(PDO::FETCH_ASSOC);
        $search = $_POST['search'];
        $type = $_POST['audit_type'];
        $status_search = (isset($_POST['audit-status'])) ? $_POST['audit-status'] : '';
        $and = $status_search === '' ? '' : ' AND ';
        if ($type === 'SPA Audits') {
            $id = $curr_results['curr_spa_id'];
            $prev_id = ($id === 7) ? 8 : 7;
            $old_and_going_id = 9;
            $curr_type = "SPA Audit";
            $old_type = "Previous SPA Audit";
            $query_type = " audit_id = ANY(ARRAY[7,8,9]) ";
        } else if ($type === 'Management Audits') {
            $id = $curr_results['curr_mgmt_id'];
            $prev_id = ($id === 4) ? 5 : 4;
            $old_and_going_id = 6;
            $curr_type = "Management Audit";
            $old_type = "Previous Management Audit";
            $query_type = " audit_id = ANY(ARRAY[4,5,6]) ";
        } else if ($type === 'Self Audits') {
            $id = $curr_results['curr_self_id'];
            $prev_id = ($id === 1) ? 2 : 1;
            $old_and_going_id = 3;
            $curr_type = "Self Audit";
            $old_type = "Previous Self Audit";
            $query_type = " audit_id = ANY(ARRAY[1,2,3]) ";
        }
        if ($search === 'all') {
            $depts = "SELECT DISTINCT(a.dept_id) as dept_id, dept_name FROM asset_info a LEFT JOIN department d ON a.dept_id = d.dept_id ORDER BY a.dept_id ";
            $dept_stmt = $dbh->query($depts);
            $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

            $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted, check_forms FROM audit_history WHERE " . $query_type . " ORDER BY audit_id";
            $stmt = $dbh->prepare($select_query);
            $stmt->execute();
        } else {
            $search = '%' . $search . '%';
            $depts = "SELECT DISTINCT(a.dept_id) as dept_id, dept_name FROM asset_info a LEFT JOIN department d ON a.dept_id = d.dept_id WHERE a.dept_id ILIKE :search OR d.dept_name ILIKE :search ORDER BY a.dept_id ";
            $dept_stmt = $dbh->prepare($depts);
            $dept_stmt->execute([":search" => $search]);
            $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

            $select_query = "SELECT dept_id, auditor, finished_at, audit_id, audit_status, forms_submitted, check_forms FROM audit_history WHERE dept_id ILIKE :search AND " . $query_type . " ORDER BY audit_id";
            $stmt = $dbh->prepare($select_query);
            $stmt->execute([':search' => $search]);
        }
        $audits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        if (count($departments) > 0) {
        ?>
            <!-- Added container div for better styling -->
            <div class="table-container">
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
                            $curr = $previous = $old_ongoing = false;
                            $count++;
                            $count_dept = 0;
                            $color = ($i++ % 2 == 0) ? 'even' : 'odd';
                            echo "<tr class='$color'>";
                            echo "<td>" . $dept['dept_id'] . "</td>";
                            echo "<td>" . $dept['dept_name'] . "</td>";
                            foreach ($audits as $index => $audit) {
                                if ($dept['dept_id'] === $audit['dept_id']) {
                                    if ($audit['audit_id'] === $id) {
                                        $curr = true;
                                        $curr_index = $index;
                                    }
                                    if ($audit['audit_id'] === $prev_id) {
                                        $previous = true;
                                        $previous_index = $index;
                                    }
                                    if ($audit['audit_id'] === $old_and_going_id) {
                                        $old_ongoing = true;
                                        $old_ongoing_index = $index;
                                    }
                                }
                            }
                            if ($curr) {
                                displayAuditData($curr_index, $dept['dept_id']);
                            } else {
                                notStart($curr_type);
                            }
                            if ($previous) {
                                displayAuditData($previous_index, $dept['dept_id']);
                            }
                            if ($old_ongoing) {
                                displayAuditData($old_ongoing_index, $dept['dept_id']);
                            }
                            if (!$old_ongoing && !$previous) {
                                notStart($old_type);
                            }

                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</body>

</html>
<?php function displayAuditData($curr_index, $dept)
{
    global $curr_results;
    global $audits;
    if ($curr_results['curr_self_id'] === 1) {
        $audit_type[1] = 'Self Audit';
        $audit_type[2] = 'Previous Self Audit';
        $audit_type[3] = 'Overdue Self Audit';
    } else {
        $audit_type[1] = 'Previous Self Audit';
        $audit_type[2] = 'Self Audit';
        $audit_type[3] = 'Overdue Self Audit';
    }
    if ($curr_results['curr_mgmt_id'] === 4) {
        $audit_type[4] = 'Management Audit';
        $audit_type[5] = 'Previous Management Audit';
        $audit_type[6] = 'Overdue Management Audit';
    } else {
        $audit_type[4] = 'Previous Management Audit';
        $audit_type[5] = 'Management Audit';
        $audit_type[6] = 'Overdue Management Audit';
    }
    if ($curr_results['curr_spa_id'] === 7) {
        $audit_type[7] = 'SPA Audit';
        $audit_type[8] = 'Previous SPA Audit';
        $audit_type[9] = 'Overdue SPA Audit';
    } else {
        $audit_type[7] = 'Previous SPA Audit';
        $audit_type[8] = 'SPA Audit';
        $audit_type[9] = 'Overdue SPA Audit';
    }
    $color = 'red';
    if ($audits[$curr_index]['audit_status'] === 'In Progress') {
        $color = 'yellow';
    } else if ($audits[$curr_index]['audit_status'] === 'Complete') {
        $color = 'green';
    }

    echo '<tr>';
    if ((($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$audits[$curr_index]['audit_id'], [1, 2, 3]))) && $audits[$curr_index]['forms_submitted'] === true) {
        echo "<td><a href='#' data-dept='$dept' data-id='$curr_index' class='modal-btn' style='color: #003DA5; text-decoration: none; padding: 8px 12px; background-color: #FFB81C; border-radius: 4px; display: inline-block; transition: all 0.3s ease;'><i class='fa fa-search'></i></a></td>";
        $check_forms = $audits[$curr_index]['check_forms'];
        echo "<div id='form-modal-$dept-$curr_index' class='modal'>";
        //$check_forms = $audits[$curr_index]['check_forms'];
        echo '<div class="modal-content">';
        echo "<span class='close'>&times;</span>";
        echo "<h3 style='color: #003DA5; border-bottom: 2px solid #FFB81C; padding-bottom: 10px; margin-top: 0;'>Audit Form Details</h3>";
        if (empty($check_forms)) {
            echo "<p style='color: #003DA5;'>No Loss/Stolen/Damaged Reports Submitted</p>";
            echo "<p style='color: #003DA5;'>No Transfer Reports Submitted</p>";
        } else {
            $ASI = "/^A[SI]?\d+$/";
            $STU = "/^S[RC]?[TU]?\d+$/";
            $CMP = "/^\d+/";
            $FDN = "/^F[DN]?\d+$/";
            $SPA = "/^SP\d+$/";
            $trim_forms = trim($check_forms, '{}');
            $form_array = explode(',', $trim_forms);
            $counter = 0;
            foreach ($form_array as $index => $form) {
                $form = trim($form, '"');
                if ($form === 'lsd') {
                    if ($index > 1) {
                        echo '<br>';
                    }
                    echo '<span style="color: #003DA5; font-weight: 600;">Loss/Stolen/Damaged Form: </span>';
                    continue;
                } else if ($form === 'transfer') {
                    if ($index > 1) {
                        echo '<br>';
                    }
                    echo '<span style="color: #003DA5; font-weight: 600;">Transfer Form </span>';
                    continue;
                }
                if ($form === 'in-progress') {
                    $color = '#FFFF00';
                    echo '<span style="color: #003DA5; font-weight: 600;">Status: </span>';

?>
            <span style='color: <?= $color ?> ;'>In Progress </span>
<?php
                    echo '<span style="color: #003DA5; font-weight: 600;"> Tags </span>';
                    continue;
                } else if ($form === 'complete') {
                    $color = '#00FF00';
                    echo '<span style="color: #003DA5; font-weight: 600;">Status: </span>';

?>
            <span style='color: <?= $color ?> ;'>Complete </span>
<?php
                    echo '<span style="color: #003DA5; font-weight: 600;"> Tags </span>';
                    continue;
                } else if ($form === 'withdrawn') {
                    $color = '#FF2F00';
                    echo '<span style="color: #003DA5; font-weight: 600;">Status: </span>';

?>
            <span style='color: <?= $color ?> ;'>Withdrawn </span>
<?php
                    echo '<span style="color: #003DA5; font-weight: 600;"> Tags </span>';
                    continue;
                } else if ($form === 'denied') {
                    $color = '#FF0000';
                    echo '<span style="color: #003DA5; font-weight: 600;">Status: </span>';
?>
            <span style='color: <?= $color ?> ;'>Denied </span>
            <span style="font-weight:700; color: #003DA5;"> Tags </span>
<?php
                    continue;
                }
                if (
                    preg_match($ASI, $form) || preg_match($STU, $form) ||
                    preg_match($CMP, $form) || preg_match($FDN, $form) ||
                    preg_match($SPA, $form)
                ) {
                    echo '<span style="font-weight:800; color: #003DA5;"> ' . $form . ' </span>';
                }
            }
        }
        echo '</div>';
        echo '</div>';
    } else {
        echo '<td></td>';
    }
    echo "<td>" . $audit_type[(int)$audits[$curr_index]['audit_id']] . "</td>";
    echo "<td style='color:" . $color . ";'>" . $audits[$curr_index]['audit_status'] . "</td>";
    if ($audits[$curr_index]['audit_status'] === 'In Progress') {
        echo "<td>";
        if (($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$audits[$curr_index]['audit_id'], [1, 2, 3]))) {
            echo "<a class='action-link continue-link' href='continue/get-audit-hist-data.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "'>Continue</a>  ";
        }
        if (($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$audits[$curr_index]['audit_id'], [1, 2, 3])) || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
            echo "<a class='action-link start-link' href='complete/start-bulk-transfer.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "&complete=true'>Start Forms</a>  ";
        }
        if ((($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') || ($_SESSION['deptid'] === $dept['dept_id'] && in_array((int)$audits[$curr_index]['audit_id'], [1, 2, 3]))) && $audits[$curr_index]['forms_submitted'] === true) {
            echo "<a class='action-link complete-link' href='https://dataworks-7b7x.onrender.com/kauliAPI/write/complete-audit.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "'>Complete</a>";
        }
        echo "</td>";
    }
    echo "<td><a class='action-link pdf-link' href='audit-details.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "&auditor=" . htmlspecialchars(urlencode($audits[$curr_index]['auditor'])) . "'>PDF</a>  ";
    echo "<a class='action-link excel-link' href='download-excel.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "&auditor=" . htmlspecialchars(urlencode($audits[$curr_index]['auditor'])) . "'>Excel</a></td>";
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
        echo "<td><a class='action-link delete-link' href='delete-audit.php?dept_id=" . htmlspecialchars(urlencode($dept['dept_id'])) . "&audit_id=" . htmlspecialchars(urlencode($audits[$curr_index]['audit_id'])) . "&auditor=" . htmlspecialchars(urlencode($audits[$curr_index]['auditor'])) . "'>Delete</a></td>";
    }
    echo '</tr>';
}

function notStart($type) {
    echo '<tr>';
    echo '<td>';
    echo "<td>". $type ."</td>";
    echo "<td style='color:red;'>Not Started</td>";
    echo '</tr>';
}
?>
<script>
 document.addEventListener('DOMContentLoaded', () => {
        const modal_btn = document.querySelectorAll('.modal-btn');
        const span = document.querySelectorAll('.close');
        modal_btn.forEach(function(btn) {
            btn.addEventListener('click', () => {
                const dept = btn.dataset.dept;
                const index = btn.dataset.id;
                document.getElementById('form-modal-' + dept + '-' + index).style.display = 'block';
                span.forEach(function(btn) {
                    btn.addEventListener('click', () => {
                        document.getElementById('form-modal-' + dept + '-' + index).style.display = 'none';
                    });
                });
                window.onclick = function(event) {
                    if (event.target == document.getElementById('form-modal-' + dept + '-' + index)) {
                        document.getElementById('form-modal-' + dept + '-' + index).style.display = "none";
                    }
                }
            });

        });

    });

</script>

