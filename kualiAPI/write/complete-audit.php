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
$get_curr_ids = "SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq";
$curr_stmt = $dbh->query($get_curr_ids);
$curr_stmt->execute();
$curr_results = $curr_stmt->fetch(PDO::FETCH_ASSOC);

$select_audit = "SELECT unnest(check_forms) as check_forms FROM audit_history WHERE dept_id = :dept AND audit_id = :aid";
$stmt = $dbh->prepare($select_audit);
$stmt->execute([':dept'=>$dept_id, ':aid'=>$audit_id]);
$audit_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$audit_info) {
    die('Empty Audit');
}
// GET TAGS
$submit_audit = true;
$transfer = $lsd = $in_progress = $done = $progress = false;
$count = 1;
foreach ($audit_info as $index1=>$form) {
    $single_form = explode(',', $form['check_forms']);
    if ($single_form[2] === 'denied' || $single_form[2] === 'withdrawn' ) {
        continue;
    }
    $single_form[0];
    $single_form[1];
    if ($single_form[2] === 'in-progress') {
        $progress = true;
    }
    if ($single_form[1] === 'complete') {
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
                $submit_audit = false;
                break;
            } else {
                break;
            }
        }
    }
    if ($done === 'complete') {
        foreach ($tags as $tag) {
            if ($transfer) {
                $variables['data']['HgIvQwEnwb']['data'][$index1]['data']['xVdCwxjKl-'][] = trim($tag);
                $variables['data']['HgIvQwEnwb']['data'][$index1]['data']['2KqtRaCah1'][] = 'Transfer update after auditing';
                $variables['data']['HgIvQwEnwb']['data'][$index1]['id'][] = $count++ . '';
            } else if ($lsd) {
                $variables['data']['g3eXi7dYR2']['data'][$index1]['data']['vJyySSnsqZ'][] = trim($tag);
                $variables['data']['HgIvQwEnwb']['data'][$index1]['id'][] = $count++ . '';
            }
        }
    }
    $progress = $done = false;
    $transfer = $lsd = false;
}
if (!$submit_audit) {
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}

$subdomain = "csub";
// SUBMITTER INFO
$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $_SESSION['email']]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $submitter_info['kuali_key'] ?? '';
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
if (empty($school_id) || empty($form_id)) {
    searchName($full_name, $apikey, $dept_id);
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}
$variables['data']['E5WDwBqoR4'] = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];


$get_dept_custodians = "SELECT dept_id, dept_name, unnest(custodian) as cust, dept_manager FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_custodians);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$custodians = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $custodians[0]['dept_name'];

$cust_count = count($custodians);
$get_cust_info = "select email, form_id, school_id, username, f_name, l_name from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
$cust_name_split = explode(" ", $custodians[0]['cust']);
try {
    $get_cust_stmt = $dbh->prepare($get_cust_info);
    $get_cust_stmt->execute([":full_name"=>$custodians[0]['cust']]);
    $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($custodians[0]['cust'], $apikey, $dept_id);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // CUST DID NOT MATCH
    searchName($custodians[0]['cust'], $apikey, $dept_id);
    $get_cust_stmt = $dbh->prepare($get_cust_info);
    $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
    $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
    // SEARCH CUST IN KUALI
}
$variables['data']['lHuAQy0tZd']['displayName'] = $custodians[0]['cust'];
$variables['data']['lHuAQy0tZd']['email'] = $cust_info['email'];
$variables['data']['lHuAQy0tZd']['firstName'] = $cust_info['f_name'];
$variables['data']['lHuAQy0tZd']['id'] = $cust_info['form_id'];
$variables['data']['lHuAQy0tZd']['label'] = $custodians[0]['cust'];
$variables['data']['lHuAQy0tZd']['lastName'] = $cust_info['l_name'];
$variables['data']['lHuAQy0tZd']['schoolId'] = $cust_info['school_id'];
$variables['data']['lHuAQy0tZd']['username'] = $cust_info['username'];

$manager_name = $custodians[0]['dept_manager'];
try {
    $get_mana_stmt = $dbh->prepare($get_cust_info);
    $get_mana_stmt->execute([":full_name"=>$manager_name]);
    $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($mana_info['form_id']) || empty($mana_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($manager_name, $apikey, $dept_id);
        $get_mana_stmt = $dbh->prepare($get_cust_info);
        $get_mana_stmt->execute([":full_name" => $custodians[0]['cust']]);
        $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log(e->getMessage());
}
$variables['data']['55-0zfJWML']['displayName'] = $manager_name;
$variables['data']['55-0zfJWML']['email'] = $mana_info['email'];
$variables['data']['55-0zfJWML']['firstName'] = $mana_info['f_name'];
$variables['data']['55-0zfJWML']['id'] = $mana_info['form_id'];
$variables['data']['55-0zfJWML']['label'] = $manager_name;
$variables['data']['55-0zfJWML']['lastName'] = $mana_info['l_name'];
$variables['data']['55-0zfJWML']['schoolId'] = $mana_info['school_id'];
$variables['data']['55-0zfJWML']['username'] = $mana_info['username'];

if (isset($variables['data']['HgIvQwEnwb']['data'][0]['data']['xVdCwxjKl-'])) {
    $variables['data']['R0rMnJsvtQ']['id'] = 'yes';
    $variables['data']['R0rMnJsvtQ']['label'] = 'Yes';
} else {
    $variables['data']['R0rMnJsvtQ']['id'] = 'no';
    $variables['data']['R0rMnJsvtQ']['label'] = 'No';
}
if (isset($variables['data']['g3eXi7dYR2']['data'][0]['data']['vJyySSnsqZ'])) {
    $variables['data']['3WfG7CrNND']['id'] = 'yes';
    $variables['data']['3WfG7CrNND']['label'] = 'Yes';
} else {
    $variables['data']['3WfG7CrNND']['id'] = 'no';
    $variables['data']['3WfG7CrNND']['label'] = 'No';
}
if (!$apikey) {
    die("No API key found for user.");
}

$url = "https://{$subdomain}.kualibuild.com/app/api/v0/graphql";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "Content-Type: application/json",
    "Authorization: Bearer {$apikey}",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$data = '{"query":"mutation ($appId: ID!) { initializeWorkflow(args: {id: $appId}) { actionId }}","variables":{
"appId": "68e5ccf75911b5028c9e9d3e"
}}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);

$decoded_data = json_decode($resp, true);
$action_id = $decoded_data['data']['initializeWorkflow']['actionId'];
curl_close($curl);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$get_draft_id = json_encode([
    'query' => 'query ($actionId: String!) { action(actionId: $actionId) { id appId document { id } } }',
    'variables' => [
        'actionId' => $action_id
    ]
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $get_draft_id);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($curl);
var_dump($resp);
$decoded_data = json_decode($resp, true);
$document_id = $decoded_data['data']['action']['document']['id'];
$action_id = $decoded_data['data']['action']['id'];

curl_close($curl);


$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
if (!$action_id || !$document_id) {
    die("Missing required data.\nactionId: $action_id\ndocumentId: $document_id");
}

if (!$action_id) {
    die("ERROR: actionId is NULL before submitting the document.");
}
$now_array = new DateTime();
$now_array->setTimezone( new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

$ms_time = round(microtime(true) * 1000);
$variables['documentId'] = $document_id;
$variables['actionId'] = $action_id;
$variables['status'] = 'completed';
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => $variables
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
$resp_data = json_decode($resp, true);

curl_close($curl);

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
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
        $pdfUrl = "https://dataworks-7b7x.onrender.com/audit/audit_history/audit_email_pdf.php?dept_id=$dept_id&audit_id=$audit_id";
        $pdfData = file_get_contents($pdfUrl);
        if ($pdfData === false) {
            throw new Exception("Unable to download PDF from $pdfUrl");
        }

        $mail->addStringAttachment($pdfData, "audit_{$dept_id}_{$audit_id}.pdf", 'base64', 'application/pdf');
        $mail->Body    = '<h4>Attached link to completed audit PDF...</h4><br>
            <a href="https://dataworks-7b7x.onrender.com/auth/reset-password.php?token=' . $token . '&email='.$email.'">Reset Password</a>';

        $mail->send();
        
    } catch (Exception $e) {
        $message = "Invalid Email Address: ". $email;
        $success = false;
    }


header("Location: https://dataworks-7b7x.onrender.com/audit/audit-history/search-history.php");
exit;

?>
