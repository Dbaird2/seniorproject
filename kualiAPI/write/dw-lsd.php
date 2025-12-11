<?php
/**
 * dw-lsd.php
 * ------------------------------------------------------------
 * This script processes form submission data for the Loss/Stolen/Damaged
 * asset workflow. It performs the following tasks:
 *
 * 1. Reads JSON POST input containing asset tag, dept_id, audit_id, etc.
 * 2. Searches the user's session data to locate the matching asset record.
 * 3. Looks up additional asset information from the database (make, model, type).
 * 4. Gathers submitter, custodian, and manager information by querying:
 *      - Local database (department + user info)
 *      - Kuali Build user directory via helper functions (getNameInfo, getEmailInfo)
 * 5. Initializes a new Kuali Build workflow instance via GraphQL
 *      - Gets actionId
 *      - Retrieves the associated draft documentId
 * 6. Builds a large `$variables` array containing all required form fields,
 *    user signatures, department info, asset info, and metadata.
 * 7. Submits the completed Kuali Build document using a GraphQL
 *    `submitDocument` mutation.
 * 8. If submission succeeds:
 *      - Updates the local audit_history table by appending a status entry.
 *      - Returns a JSON success response.
 *    Otherwise returns an error payload.
 *
 * In short: This page receives asset/form input, fetches all needed personnel
 * and asset details, assembles a complete Kuali Build JSON payload, submits
 * the workflow document through GraphQL, and logs the result in audit_history.
 * ------------------------------------------------------------
 */
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once 'search.php';
include_once 'get-info.php';
if (!isset($_POST)) {
    die("Not submitted yet.");
}

$variables = [[]];
$echo = function($type, $msg) {
    echo $type . ': ' . $msg . '<br>';
    return;
};
$array_echo = function ($msg) {
    echo "<pre>";
    var_dump($msg);
    echo "</pre>";
    return;
};

$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$myself = $someone_else = false;
$index = 0;
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
        if (empty($variables['data']['8Rob3wGhC-'])) {
            $variables['data']['8Rob3wGhC-'] = 'N/A';
        }
        $variables['data']['2iwsFa0_2j'] = $data['tag'];
        $variables['data']['_E4bMX9gkw'] = (!empty($tag_info['make'])) ? $tag_info['make'] : 'N/A';
        $variables['data']['ZcxRfwh8uT'] = (!empty($tag_info['asset_model'])) ? $tag_info['asset_model'] : 'N/A';
        break;
    }
}
if (empty($variables['data']['2iwsFa0_2j'])) {
    echo json_encode(['error'=>'No Tag was found']);
    die('No Tag was found');
}

$dept_id = $_SESSION['info'][2];
$audit_dept_id = $data['dept_id'];

$subdomain = "csub";
// SUBMITTER INFO
$submitter = getSubmitterSig();
$apikey = $submitter['apikey'];
if (empty($apikey)) {
    die("API Key Not Found");
}
// NAME
$variables['data']['WDA7EMUZg_'] = $submitter['fullName'];


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
$form_data = '{"query":"mutation ($appId: ID!) { initializeWorkflow(args: {id: $appId}) { actionId }}","variables":{
"appId": "68e94e8a58fd2e028d5ec88f"
      }}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $form_data);

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
$custodian_info = getNameInfo($custodian, $audit_dept_id);


// CUSTODIAN
$variables['data']['NpD2RP-waL']['displayName'] = $custodian_info['displayName'];
$variables['data']['NpD2RP-waL']['schoolId'] = $custodian_info['schoolId'];
$variables['data']['NpD2RP-waL']['email'] = $custodian_info['email'];
$variables['data']['NpD2RP-waL']['id'] = $custodian_info['id'];
$variables['data']['NpD2RP-waL']['username'] = $custodian_info['username'];
$variables['data']['NpD2RP-waL']['firstName'] = $custodian_info['firstName'];
$variables['data']['NpD2RP-waL']['lastName'] = $custodian_info['lastName'];

$manager_info = getNameInfo($manager, $audit_dept_id);
// MANAGER
$variables['data']['5mMKjTfnND']['displayName'] = $manager_info['displayName'];
$variables['data']['5mMKjTfnND']['schoolId'] = $manager_info['schoolId'];
$variables['data']['5mMKjTfnND']['email'] = $manager_info['email'];
$variables['data']['5mMKjTfnND']['id'] = $manager_info['id'];
$variables['data']['5mMKjTfnND']['username'] = $manager_info['username'];
$variables['data']['5mMKjTfnND']['firstName'] = $manager_info['firstName'];
$variables['data']['5mMKjTfnND']['lastName'] = $manager_info['lastName'];
$submitter_sig = getEmailInfo($_SESSION['email'], $_SESSION['deptid']);

// DATE
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$current_date = $date->format('m/d/Y');
$variables['data']["sQZpV5OhTo"] = $current_date;

$variables['documentId'] = $document_id;
$variables['actionId'] = $action_id;
$variables['status'] = 'completed';

// SUBMITTER SIG
$sbmitter_info = getEmailInfo($_SESSION['email'], $_SESSION['deptid']);
$variables['data']['Tscy6BxbSj']['actionId'] = $action_id;
$variables['data']['Tscy6BxbSj']['date'] = $submitter_info['date'];
$variables['data']['Tscy6BxbSj']['displayName'] = $submitter_info['displayName'];
$variables['data']['Tscy6BxbSj']['signatureType'] = 'type';
$variables['data']['Tscy6BxbSj']['signedName'] = $submitter_info['lastName'];
$variables['data']['Tscy6BxbSj']['userId'] = $submitter_info['userId'];

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

$tag = $data['tag'];
$input_array =  $document_id . ',rlsd,in-progress,' . trim($tag);

$audit_id = $data['audit_id'];
$update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, :array) WHERE dept_id = :dept AND audit_id = :id";
if ($resp_data['data']['submitDocument'] === 'Ok') {
    $update_stmt = $dbh->prepare($update);
    $update_stmt->execute([':array'=>$input_array, ":dept"=>$dept_id, ":id"=>$audit_id]);
    echo json_encode(['status'=>'Loss/Stolen/Dmg Ok']);
} else {
    echo json_encode(['status'=>'Loss/Stolen/Dmg Failed', 'data'=>$resp_data]);
}
exit;

