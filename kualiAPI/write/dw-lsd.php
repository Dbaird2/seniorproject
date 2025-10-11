<?php
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once 'search.php';
include_once 'get-info.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}

$variables = [[]];

$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$myself = $someone_else = false;
$index = 0;
echo json_encode(['tags'=>$data]);
$variables = [[]];
foreach($_SESSION['data'] as $session) {
    if ($session['Tag Number'] === $data['tag']) {
        $select = "SELECT make, type2, asset_model FROM asset_info WHERE asset_tag = :tag";
        $select_stmt = $dbh->prepare($select);
        $select_stmt->execute([":tag"=>$data['tag']]);
        $tag_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
        // ASSET
        $variables['data']['dpHYE3a-ml'] = $session['Descr'];
        $variables['data']['8Rob3wGhC-'] = $session['Serial ID'];
        $variables['data']['2iwsFa0_2j'] = $data['tag'];
        $variables['data']['_E4bMX9gkw'] = (!empty($tag_info['make'])) ? $tag_info['make'] : 'N/A';
        $variables['data']['ZcxRfwh8uT'] = (!empty($tag_info['asset_model'])) ? $tag_info['asset_model'] : 'N/A';
        break;
    }
}


$dept_id = $_SESSION['info'][2];

$subdomain = "csub";
// SUBMITTER INFO
$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $_SESSION['email']]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $submitter_info['kuali_key'];
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
// SUBMITTER
// SIG
$variables['data']['Tscy6BxbSj'] = $full_name;
// NAME
$variables['data']['WDA7EMUZg_'] = $full_name;
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
$email_array = explode('@', $email);
if (empty($school_id) || empty($form_id)) {
    searchEmail($email_array[0], $apikey, $dept_id);
    $select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
    $email = $_SESSION['email'];
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $display_name = $submitter_info['username'];
    $full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
        // SUBMITTER
    $school_id = $submitter_info['school_id'];
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'];

    $variables['data']['WDA7EMUZg_'] = $full_name;
}


$get_dept_manager = "SELECT dept_id, dept_name, dept_manager, custodian[1] as cust FROM department d WHERE dept_id = :dept_id";
$get_mana_stmt = $dbh->prepare($get_dept_manager);
$get_mana_stmt->execute([":dept_id"=>$dept_id]);
$dept_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
$dept_name = $dept_info['dept_name'];
$manager = trim($dept_info['dept_manager']);
// DEPARTMENT
$variables['data']['lVtSabqSUh']['label'] = $dept_name;
$variables['data']['lVtSabqSUh']['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']['lVtSabqSUh']['data']['IOw4-l7NsM'] = $dept_id;

$get_info = "select f_name, l_name, signature, email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
$get_info_email = "select f_name, l_name, signature, email, form_id, school_id, username from user_table where email = :email";
$get_info_name = "select f_name, l_name, signature, email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";


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
"appId": "68e94e8a58fd2e028d5ec88f"
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
$custodian = $dept_info['cust'];
$custodian_info = getSignature(query: $get_info, person_name: $custodian, type: 'info');


// CUSTODIAN
$variables['data']['NpD2RP-waL']['displayName'] = $custodian_info['displayName'];
$variables['data']['NpD2RP-waL']['schoolId'] = $custodian_info['schoolId'];
$variables['data']['NpD2RP-waL']['email'] = $custodian_info['email'];
$variables['data']['NpD2RP-waL']['id'] = $custodian_info['id'];
$variables['data']['NpD2RP-waL']['username'] = $custodian_info['username'];
$variables['data']['NpD2RP-waL']['firstName'] = $custodian_info['firstName'];
$variables['data']['NpD2RP-waL']['lastName'] = $custodian_info['lastName'];

$manager_info = getSignature(query: $get_info, person_name: $manager, type: 'info');
// MANAGER
$variables['data']['5mMKjTfnND']['displayName'] = $manager_info['displayName'];
$variables['data']['5mMKjTfnND']['schoolId'] = $manager_info['schoolId'];
$variables['data']['5mMKjTfnND']['email'] = $manager_info['email'];
$variables['data']['5mMKjTfnND']['id'] = $manager_info['id'];
$variables['data']['5mMKjTfnND']['username'] = $manager_info['username'];
$variables['data']['5mMKjTfnND']['firstName'] = $manager_info['firstName'];
$variables['data']['5mMKjTfnND']['lastName'] = $manager_info['lastName'];
$submitter_sig = getSignature(query: $select, email: $email, action_id: $action_id);

// DATE
$date = new DateTime();
$today = $date->format('m/d/Y');
$variables['data']['sQZpV5OhTo'] = $today;

$variables['documentId'] = $document_id;
$variables['actionId'] = $action_id;
$variables['status'] = 'completed';

echo "<pre>";
echo $custodian;
var_dump($custodian_info);
echo $variables['data']['sQZpV5OhTo'];
echo "</pre>";
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => $variables,
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
$resp_data = json_decode($resp, true);
if (!empty($data['dept_id']) && !empty($data['audit_id'])) {
    $id = $resp_data['data']['app']['documentConnection']['edges'][0]['node']['id'];
    $tag = $data['tag'];
    $doc_id = '68e94e8a58fd2e028d5ec88f';
    $input_array =  $id . ',lsd,in-progress, ' . $tag;

    $dept = $data['dept_id'];
    $audit_id = $data['audit_id'];
    $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ':array') WHERE dept_id = :dept AND audit_id = :id";
    $update_stmt = $dbh->prepare($update);
    $update_stmt->execute([':array'=>$input_array, ":dept"=>$dept, ":id"=>$audit_id]);
}
curl_close($curl);

echo json_encode(['form'=>$submit_form, 'resp data'=>$resp_data]);
exit;

