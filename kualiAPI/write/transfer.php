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
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
echo json_encode(['tags'=>$data]);
$variables = [[]];
$email = $_SESSION['email'];
$audit_dept = $data['dept_id'];

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

// IS THIS IT RELATED?
$it_select = "SELECT type2, serial_id, asset_name, bus_unit from asset_info WHERE asset_tag = :tag";
$it_stmt = $dbh->prepare($it_select);
$it_stmt->execute([":tag"=>$data['asset_tag']]);
$it_related = $it_stmt->fetch(PDO::FETCH_ASSOC);
if (in_array($it_related['type2'], ['Laptop', 'Tablet', 'Desktop'])) {
    $variables['data']['xPQtXjuWnk']['id'] = 'yes';
    $variables['data']['xPQtXjuWnk']['label'] = 'Yes';
} else {
    $variables['data']['xPQtXjuWnk']['id'] = 'no';
    $variables['data']['xPQtXjuWnk']['label'] = 'No';
}
// IS THIS A (form type)
$form_type_id = match($data['form_type'])) {
    'bus' => 'BhQ_qXc6Tji',
    'dept' => '9A_6UOlDb',
    'location' => 'LfK1qG_G6'
};
$form_type = match($data['form_type'])) {
    'bus' => 'Business Unit change (for example from BKSPA to BKCMP)',
    'dept' => 'From one department to another department ',
    'location' => 'Building/Room/Location change (Business Unit stays the same)'
};
$variables['data']['_GODY1FjEy']['id'] = $form_type_id;
$variables['data']['_GODY1FjEy']['label'] = $form_type;

$email_select = "SELECT school_id, form_id, f_name, l_name, email, signature FROM user_info WHERE email = :email";
$name_select = "SELECT school_id, form_id, f_name, l_name, email, signature FROM user_info WHERE CONCAT(f_name, ' ', l_name) = :name";
$dept_select = 'SELECT dept_manager FROM department WHERE dept_id = :id';

// GET CURRENT MANAGER INFO
$stmt = $dbh->prepare($dept_select);
$stmt->execute([':id'=>$_SESSION['deptid']]);
$current_manager = $stmt->fetchColumn();

$current_manager_info = getInfoName($current_manager, $audit_dept);
$variables['data']['u7YkM8hmb']['displayName'] = $current_manager_info['displayName'];
$variables['data']['u7YkM8hmb']['email'] = $current_manager_info['email'];
$variables['data']['u7YkM8hmb']['firstName'] = $currnet_manager_info['firstName'];
$variables['data']['u7YkM8hmb']['id'] = $current_manager_info['id'];
$variables['data']['u7YkM8hmb']['label'] = $current_manager_info['label'];
$variables['data']['u7YkM8hmb']['lastName'] = $current_manager_info['lastName'];
$variables['data']['u7YkM8hmb']['schoolId'] = $current_manager_info['schoolId'];
$variables['data']['u7YkM8hmb']['username'] = $current_manager_info['username'];

// ASSETS
$bus_id = function ($type) {
    $id = match ($type) {
        'BKCMP' => 'NLNTmkvx_u',
        'BKSPA' => 'ztmVBnRjT1',
        'BKSTU' => 'Duom3fxkyA',
        'BKFDN' => 'Xi6koaglZc',
        'BKASI' => 'E9lk-ahtpd',
    };
    return $id;
};
if ($data['bldg'] === 'Yes') {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['id'] = 'yes';
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['label'] = $data['bldg'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['Ppr7oMwHAA'] = $data['old_where_stored'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['qtAPPojYXt'] = $data['new_where_stored'];
} else {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['id'] = 'no';
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['label'] = $data['bldg'];
}
if (!empty($data['notes'])) {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['WzqON1QbTK'] = $data['notes'];
}

if ($data['form_type'] === 'dept' || $data['form_type'] === 'location') {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['Ppr7oMwHAA'] = $data['new_bldg'];
    if (!empty($data['new_room'])) {
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['zZztPX8Pcw'] = $data['new_room'];
    }
}
if ($data['form_type'] === 'dept') {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']['U73d7kPH5b'] = $data['new_dept'];
    // WHY
    $variables['data']['K3p03X2Jvx'] = $data['why'];
    // MANAGER
    $get_dept_info = "SELECT dept_manager, dept_id FROM department WHERE dept_name = :dept";
    $dept_stmt = $dbh->prepare($get_dept_info);
    $dept_stmt->execute([':dept'=>$data['dept_name']]);
    $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
    $manager = trim($dept_info['dept_manager']);
    $manager_info = getIfnoName($manager, $dept_info['dept_id']);
        $info = [
            'displayName' => $person_name,
            'email'     => $person_info['email'],
            'firstName'    => $person_info['f_name'],
            'id'   => $person_info['form_id'],
            'label'     => $person_info['f_name'].' '.$person_info['l_name'],
            'lastName'    => $person_info['l_name'],
            'schoolId' => $person_info['school_id'],
            'username'  => $person_info['username'],
        ];
    $variables['data']['SZ24nXDBVk']['displayName'] = $manager_info['displayName'];
    $variables['data']['SZ24nXDBVk']['email'] = $manager_info['email'];
    $variables['data']['SZ24nXDBVk']['firstName'] = $manager_info['firstName'];
    $variables['data']['SZ24nXDBVk']['id'] = $manager_info['id'];
    $variables['data']['SZ24nXDBVk']['label'] = $manager_info['label'];
    $variables['data']['SZ24nXDBVk']['lastName'] = $manager_info['lastName'];
    $variables['data']['SZ24nXDBVk']['schoolId'] = $manager_info['schoolId'];
    $variables['data']['SZ24nXDBVk']['username'] = $manager_info['username'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['label'] = $data['dept_name'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['data']['AkMeIWWhoj'] = $data['dept_name'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['data']['IOw4-l7NsM'] = $dept_info['dept_id'];
} else if ($data['form_type'] === 'bus') {
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["dIvxPBYxpw"]['label'] = $it_related['bus_unit'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["dIvxPBYxpw"]['id'] = $bus_id($it_related['bus_unit']);

    $variables['data']['t7mH-1FlaO']['data'][0]['data']["dIvxPBYxpw"]['label'] = $data['new_bus'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["dIvxPBYxpw"]['id'] = $bus_id($data['new_bus']);
}
$variables['data']['t7mH-1FlaO']['data'][0]['data']["XZlIFEDX6Y"] = $data['tag'];
$variables['data']['t7mH-1FlaO']['data'][0]['data']["pwkDQndmwN"] = $it_related['asset_name'];


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
"appId": "68d09e41d599f1028a9b9457"
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

$submitter_sig = getSigEmail($_SESSION['email'], $_SESSION['deptid']);
$variables['data']['ne3KPx1Wy3'] = $submitter_sig;

$variables['documentId'] = $document_id;
$variables['actionId'] = $action_id;
$variables['status'] = 'completed';

$ms_time = round(microtime(true) * 1000);
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
if ($data['audit'] === 'true') {
    $id = $resp_data['data']['app']['documentConnection']['edges'][0]['node']['id'];
    $tag = $lsd_data['Tag Number'];
    $doc_id = '68c73600df46a3027d2bd386';
    $input_array = $id . ',' . $doc_id . ',in-progress, ' . $tag;

    $dept = $data['dept_id'];
    $audit_id = $data['audit_id'];
    $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ':array') WHERE dept_id = :dept AND audit_id = :id";
    $update_stmt = $dbh->prepare($update);
    $update_stmt->execute([':array'=>$input_array, ":dept"=>$dept, ":id"=>$audit_id]);
}
curl_close($curl);

echo json_encode(['form'=>$submit_form, 'resp data'=>$resp_data]);
exit;

