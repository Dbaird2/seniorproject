<?php
include_once '../../config.php';
include_once "search.php";
include_once "get-info.php";
include_once "../dataworks-read/dw-check-forms.php";
include_once "../../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ob_start();
$variables = [[]];
if (!isset($_GET['dept_id'])) {
    die('GET not set');
}
$dept_id = $_GET['dept_id'];
$audit_id = (int)$_GET['audit_id'];
echo $dept_id . '<br>';

$get_dept_name = "SELECT dept_name FROM department WHERE dept_id = ?";
$dept_name = $query_repo->fetchColumn($get_dept_name, $dept_id);
if (empty($dept_name)) {
    header("Location: https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php?status=failed&reason=dept-not-in-db");
    exit;
}
echo 'Department: ' . $dept_name . '<br>';

$get_curr_ids = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
$curr_results = $query_repo->fetchOne($get_curr_ids);

$select_audit = "SELECT unnest(check_forms) as check_forms FROM audit_history WHERE dept_id = ? AND audit_id = ?";
$audit_info = $query_repo->fetchAll($select_audit, $dept_id, $audit_id);

// GET TAGS
$submit_audit = true;
$transfer = $lsd = $in_progress = $done = $progress = false;
$transfer_status = $lsd_status = false;
$count = $count2 = 0;
//<--------------------Confirm Submit for audits without Kuali forms-------------->
if (empty($audit_info) && empty($_POST['confirm_submit'])) {
?>
    <h3>No forms were submitted</h3>
    <p>Are you sure you want to submit this audit?</p>

    <form method="post">
        <button type="submit" name="confirm_submit" value="yes">Yes, submit</button>
        <button type="submit" name="confirm_submit" value="no">No, go back</button>
    </form>
<?php
    exit;
}
if (empty($audit_info)) {

    if (($_POST['confirm_submit'] ?? '') === 'yes') {
        $submit_audit = true;
        $done = true;
        $query = "UPDATE audit_history
            SET audit_status = 'Complete',
                finished_at = NOW(),
                forms_submitted = true
            WHERE audit_id = ?
                AND dept_id = ?
            RETURNING audit_id, audit_status, finished_at
            ";
        $row = $query_repo->fetchOne($query, (int)$audit_id, $dept_id);

        if (!$row) {
            die("Audit not found in DB");
        }
    }
    if (($_POST['confirm_submit'] ?? '') === 'no') {
        header("Location: /audit/audit-history/search-history.php");
        exit;
    }
}
$progress = $done = false;
if ($audit_info) {
    foreach ($audit_info as $index1 => $form) {
        $single_form = explode(',', $form['check_forms']);
        if ($single_form[2] === 'denied' || $single_form[2] === 'withdrawn') {
            continue;
        }
        echo $single_form[0] . ' ' . $single_form[1] . ' ' . $single_form[2] . ' ' . $single_form[3] . '<br>';
        if ($single_form[2] === 'in-progress') {
            $progress = true;
        }
        if ($single_form[2] === 'complete') {
            $done = true;
        }

        if ($single_form[1] === 'transfer') {
            $transfer = true;
            $app_id = '68c73600df46a3027d2bd386';
        }
        if ($single_form[1] === 'rtransfer') {
            $transfer = true;
            $app_id = '68d09e38d599f1028a08969a';
        }
        if ($single_form[1] === 'lsd') {
            $lsd = true;
            $app_id = '68d09e41d599f1028a9b9457';
        }
        if ($single_form[1] === 'rlsd') {
            $lsd = true;
            $app_id = '68e94e8a58fd2e028d5ec88f';
        }
        $tags = array_slice($single_form, 3);
        if ($progress) {
            foreach ($tags as $tag) {
                $done = checkForm($single_form[0], $tag, $app_id, $form['check_forms']);
                if (!$done) {
                    echo '<br>Not Done ' . $single_form[2];
                    $submit_audit = false;
                    break;
                } else {
                    echo '<br>Done ' . $single_form[2];
                    break;
                }
            }
        }
        if ($done && !$progress) {
            $submit_audit = true;
            foreach ($tags as $tag) {
                echo 'For each of tags <br>';
                if ($transfer) {
                    echo 'Transfer <br>';
                    $variables['data']['HgIvQwEnwb']['data'][$count]['data']['xVdCwxjKl-'] = trim($tag);
                    $variables['data']['HgIvQwEnwb']['data'][$count]['data']['2KqtRaCah1'] = 'Transfer update after auditing';
                    $variables['data']['HgIvQwEnwb']['data'][$count]['id'] = (string) $count;
                    $count++;
                    $transfer_status = true;
                } else if ($lsd) {
                    echo 'Lsd <br>';
                    $variables['data']['g3eXi7dYR2']['data'][$count2]['data']['vJyySSnsqZ'] = trim($tag);
                    $variables['data']['g3eXi7dYR2']['data'][$count2]['id'] = (string) $count2;
                    $count2++;
                    $lsd_status = true;
                }
            }
            error_log('End of for each of tags');
        }
        $progress = $done = false;
        $transfer = $lsd = false;
    }
    if ($submit_audit) {
        $query = "
            UPDATE audit_history
            SET audit_status = 'Complete',
                finished_at = NOW(),
                forms_submitted = true
            WHERE audit_id = ?
                AND dept_id = ?
            RETURNING audit_id, audit_status, finished_at
            ";
        $row = $query_repo->fetchOne($query, (int)$audit_id, $dept_id);

        if (!$row) {
            die("Audit not found in DB");
        }
    }
}
if (!$submit_audit) {
    header("Location: https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php?status=failed&reason=in-progress");
    exit;
}

$subdomain = "csub";
// SUBMITTER INFO
$email = $_SESSION['email'];
$submitter_info = $query_repo->getUserInfo($_SESSION['email']);

$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
if (empty($school_id) || empty($form_id)) {
    searchName($full_name, $dept_id);
    $submitter_info = $query_repo->getUserInfo($_SESSION['email']);

    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}
echo '<br>Department: ' . $dept_name . '<br>';
$variables['data']['E5WDwBqoR4'] = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$variables['data']['Stimf2f9oY']['label'] = $dept_name;
$variables['data']['Stimf2f9oY']['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']['Stimf2f9oY']['data']['IOw4-l7NsM'] = $dept_id;


echo $dept_id . '<br>';
$custodians = $query_repo->getCustodians($dept_id);
$custodian = trim($custodians[0]['cust'], ' " ');
$dept_name = $custodians[0]['dept_name'];

$cust_count = count($custodians);
$cust_info = $query_repo->getCustInfo($custodian);
echo '<pre>complete_audit first part ';
var_dump($cust_info);
echo '</pre>';
if (empty($cust_info['form_id'])) {
    echo ' Cust Info not found <br>';
    $cust_info = getNameInfo($custodian, $dept_id);
    $variables['data']['lHuAQy0tZd']['displayName'] = $custodians[0]['cust'];
    $variables['data']['lHuAQy0tZd']['email'] = $cust_info['email'];
    $variables['data']['lHuAQy0tZd']['firstName'] = $cust_info['firstName'];
    $variables['data']['lHuAQy0tZd']['id'] = (string)$cust_info['id'];
    $variables['data']['lHuAQy0tZd']['label'] = $custodians[0]['cust'];
    $variables['data']['lHuAQy0tZd']['lastName'] = $cust_info['lastName'];
    $variables['data']['lHuAQy0tZd']['schoolId'] = $cust_info['schoolId'];
    $variables['data']['lHuAQy0tZd']['username'] = $cust_info['username'];
} else {
    echo ' Cust Info found <br>';
    $variables['data']['lHuAQy0tZd']['displayName'] = $custodians[0]['cust'] . '(' . $cust_info['email'] . ')';
    $variables['data']['lHuAQy0tZd']['email'] = $cust_info['email'];
    $variables['data']['lHuAQy0tZd']['firstName'] = $cust_info['f_name'];
    $variables['data']['lHuAQy0tZd']['id'] = (string)$cust_info['form_id'];
    $variables['data']['lHuAQy0tZd']['label'] = $custodians[0]['cust'];
    $variables['data']['lHuAQy0tZd']['lastName'] = $cust_info['l_name'];
    $variables['data']['lHuAQy0tZd']['schoolId'] = $cust_info['school_id'];
    $variables['data']['lHuAQy0tZd']['username'] = $cust_info['username'];
}
echo $custodians[0]['cust'] . ' ' . $cust_info['form_id'] . ' ' .  $cust_info['email'] . ' line 176<br>';
echo $dept_id . '<br>';
echo '<pre>';
var_dump($cust_info);
echo '</pre>';

$manager_name = $custodians[0]['dept_manager'];
try {
    $submitter_info = $query_repo->getUserInfo($manager_name);
    if (empty($mana_info['form_id']) || empty($mana_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($manager_name, $dept_id);
        
        $submitter_info = $query_repo->getUserInfo($custodians[0]['cust']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
$mana_info = getNameInfo($manager_name, $dept_id);
$variables['data']['55-0zfJWML']['displayName'] = $manager_name;
$variables['data']['55-0zfJWML']['email'] = $mana_info['email'];
$variables['data']['55-0zfJWML']['firstName'] = $mana_info['firstName'];
$variables['data']['55-0zfJWML']['id'] = $mana_info['id'];
$variables['data']['55-0zfJWML']['label'] = $manager_name;
$variables['data']['55-0zfJWML']['lastName'] = $mana_info['lastName'];
$variables['data']['55-0zfJWML']['schoolId'] = $mana_info['schoolId'];
$variables['data']['55-0zfJWML']['username'] = $mana_info['username'];

if ($lsd_status) {
    $variables['data']['R0rMnJsvtQ']['id'] = 'yes';
    $variables['data']['R0rMnJsvtQ']['label'] = 'Yes';
} else {
    $variables['data']['R0rMnJsvtQ']['id'] = 'no';
    $variables['data']['R0rMnJsvtQ']['label'] = 'No';
}
if ($transfer_status) {
    $variables['data']['3WfG7CrNND']['id'] = 'yes';
    $variables['data']['3WfG7CrNND']['label'] = 'Yes';
} else {
    $variables['data']['3WfG7CrNND']['id'] = 'no';
    $variables['data']['3WfG7CrNND']['label'] = 'No';
}


$now_array = new DateTime();
$now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

$ms_time = round(microtime(true) * 1000);

$resp_data = $kuali->writeToKuali("68e5ccf75911b5028c9e9d3e", $variables);
$decoded = json_decode($resp_data, true);

$insert = 'UPDATE audit_history SET complete_form_id = ? WHERE audit_id = ? AND dept_id = ?';
$query_repo->execute($insert, $decoded['document_id'], $audit_id, $dept_id);

echo "<pre>";
var_dump($decoded);
echo "</pre>";

/*
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dasonbaird25@gmail.com';
        $mail->Password   = $_SESSION['app_pass']; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('dasonbaird25@gmail.com', 'Dataworks No Reply');
        $mail->addAddress('distribution@csub.edu', 'Recipient');
        $mail->Subject = 'Audit PDF ' . $dept_id;
        $pdfUrl = "https://dataworks-7b7x.onrender.com/api/email-audit-pdf.php?dept_id=$dept_id&audit_id=$audit_id";
        $pdfData = file_get_contents($pdfUrl);
        if ($pdfData === false) {
            throw new Exception("Unable to download PDF from $pdfUrl");
        }

        $mail->addStringAttachment($pdfData, "audit_{$dept_id}_{$audit_id}.pdf", 'base64', 'application/pdf');
        $mail->Body    = '<h4>Attached link to completed audit PDF...</h4><br>
            <a href="https://dataworks-7b7x.onrender.com/auth/reset-password.php?token=' . $token . '&email='.$email.'">Reset Password</a>';

        $mail->send();
        
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
header("Location: https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php");
 */
exit;

?>