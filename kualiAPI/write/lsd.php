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
        $lsd_data['Unit'] = $session['Unit'];
        $lsd_data['Tag Number'] = $data['tag'];
        $lsd_data['Descr'] = $session['Descr'];
        $lsd_data['Serial ID'] = $session['Serial ID'];
        if (!empty($tag_info['make'])) {
            $lsd_data['Make'] = $tag_info['make'];
        } else {
            $lsd_data['Make'] = 'N/A';
        }
        $lsd_data['Model'] = $tag_info['asset_model'];
        $lsd_data['VIN'] = $session['VIN'];
        $lsd_data['Dept'] = $session['Dept'];
        $lsd_data['Found Room Number'] = $session['Found Room Number'];
        $lsd_data['Found Building Name'] = $session['Found Building Name'];
        $lsd_data['reason'] = $data['reason']; // GOOD
        $lsd_data['lsd'] = $data['lsd'];    // GOOD
        $lsd_data['who'] = $data['who']; // GOOD 
        $lsd_data['position'] = $data['position']; // GOOD
        $lsd_data['borrower'] = $data['borrower']; // GOOD
        $lsd_data['Found Note'] = $session['Found Note'];
        $lsd_data['date_reported'] = $data['date_reported']; // GOOD
        $lsd_data['upd'] = $data['upd'];
        if (strtolower($lsd_data['upd']) === 'yes') {
            $lsd_data['insurance'] = $data[''];
            if (strtolower($lsd_data['insurance']) === 'yes') { 
                $lsd_data['state'] = $data['state'];
                $lsd_data['zip'] = $data['zip'];
                $lsd_data['city'] = $data['city'];
                $lsd_data['street'] = $data['street'];
                $lsd_data['company'] = $data['company'];
            /*
                $variables['data'][''] = $lsd_data['state'];
                $variables['data'][''] = $lsd_data['zip'];
                $variables['data'][''] = $lsd_data['city'];
                $variables['data'][''] = $lsd_data['street'];
                $variables['data'][''] = $lsd_data['company'];
             */
            }
            $lsd_data['explain'] = $data['explain'];
            $lsd_data['reported'] = $data['reported'];
            $lsd_data['security'] = $data['security'];
            $lsd_data['authorized'] = $data['authorized'];
            $lsd_data['precautions'] = $data['precations'];
            $lsd_data['recovery_steps'] = $data['recover_steps'];
            $lsd_data['who_assigned'] = $data['who_assigned'];
            $lsd_data['assigned_staff'] = $data['assigned_staff'];
            $lsd_data['access_keys'] = $data['access_keys'];
            $lsd_data['secured'] = $data['secured'];
            $lsd_data['upd_location'] = $data['upd_location'];
            $lsd_data['by_whom'] = $data['by_whom'];
            $lsd_data['time_last_seen'] = $data['time_last_seen'];
            $lsd_data['date_last_seen'] = $data['date_last_seen'];
            /*
                $variables['data']['ox__1hiShH'] = $lsd_data['insurance'];
                $variables['data']['UMKALbaGtJ'] = $lsd_data['explain'];
                $variables['data]['zG7O4wyxU0'] = $lsd_data['reported'];
                $variables['data'][''] = $lsd_data['security'];
                $variables['data'][''] = $lsd_data['authorized'];
                $variables['data']['SBIzD8D7Z0'] = $lsd_data['precautions'];
                $variables['data']['FOqDM2nFYU'] = $lsd_data['recovery_steps'];
                $variables['data']['TY_xRD84-r'] = $lsd_data['who_assigned'];
                $variables['data']['Ak2ZRPlsUo'] = $lsd_data['assigned_staff'];
                // GET INFO
                $access_key = getSignature(query: $select, type: 'info'
                $variables['data']['Ctc-VTU0KG'] = $access_key;
                $variables['data']['g06BrWDC42'] = $lsd_data['secured'];
                $variables['data']['J8QRY5L38L'] = $lsd_data['upd_location'];
                $by_whom = getSignature(query: $select, type: 'info'
                $variables['data']['KMAw0Ejpx6'] = $by_whom;
                $variables['data']['h2_zTuDZQd'] = $lsd_data['time_last_seen'];
                $variables['data']['1SSyW5r5fB'] = $lsd_data['date_last_seen'];
             */
        }
        $lsd_data['item_type'] = $data['item_type'];
        if ($lsd_data['item_type'] === 'IT Equipment') {
            $lsd_data['encrypted'] = $data['encrypted'];
            $variables['data']['ZfhX3CCX7D']['label'] = $lsd_data['encrypted'];
            if ($lsd_data['encrypted'] === 'Yes') {
                $variables['data']['ZfhX3CCX7D']['id'] = 'yes';
                $lsd_data['encrypted_data'] = $data['encrypted_data'];
                $variables['data']['8YYaqGi1u4'] = $lsd_data['encrypted_data'];
            } else {
                $variables['data']['ZfhX3CCX7D']['id'] = 'no';

            }
            $lsd_data['confidential'] = $data['confidential'];
            $variables['data']['TC9A_cNoXu']['label'] = $lsd_data['confidential'];
            if ($lsd_data['confidential'] === 'Yes') {
                $lsd_data['confidential_data'] = $data['confidential_data'];
                $variables['data']['lDIEb-U1m9'] = $lsd_data['confidential_data'];
                $variables['data']['TC9A_cNoXu']['id'] = 'yes';
            } else {
                $variables['data']['TC9A_cNoXu']['id'] = 'no';
            }
        }
        if ($data['who'] === 'Myself') {
            $myself = true;
        } else if ($data['who'] === 'someone-else' && !empty($data['borrower'])) {
            $someone_else = true;
        }
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
    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}


$get_dept_manager = "SELECT dept_id, dept_name, dept_manager FROM department d WHERE dept_id = :dept_id";
$get_mana_stmt = $dbh->prepare($get_dept_manager);
$get_mana_stmt->execute([":dept_id"=>$dept_id]);
$dept_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
$dept_name = $dept_info['dept_name'];
$manager = trim($dept_info['dept_manager']);

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
"appId": "68d09e41d599f1028a9b9457"
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

$manager_info = getSignature(query: $get_info, person_name: $manager, type: 'info');
$submitter_sig = getSignature(query: $select, email: $email, action_id: $action_id);

if (!empty($lsd_data['borrower'])) {
    // GET BORROWER INFO FROM getSignature();
    if (preg_match('/@/i', $lsd_data['borrower'])) {
        $borrower_signature = getSignature(query: $get_info_email, email: $lsd_data['borrower'], action_id: $action_id);
    } else {
        $borrower_signature = getSignature(query: $get_info_name, person_name: $lsd_data['borrower'], action_id: $action_id);
    }
    $variables['data']["N00EmVKFnd"] = $borrower_info;
}
$upd_id = match ($lsd_data['upd']) {
"No" => "CbModhwutSo",
    "Yes" => "YU12SPShKnx"
};

$item_type_id = match ($lsd_data['item_type']) {
"Instructional Equipment" => "iZ6HWywjL",
    "IT Equipment" => "Ycmcbo5hp",
    "Other" => "813J2qxw1"
};

$lsd_id = match ($lsd_data['lsd']) {
"Lost" => "bqRxkqovw",
    "Stolen" => "fmp7EdgUx",
    "Destroyed" => "-rR6VXHWp"
};
$who = match ($lsd_data['who']) {
    'Myself' => 'Myself',
    'someone-else' => 'I am initiating this submission on behalf of'
};
$lsd_who_id = match ($who) {
    'Myself' => 'w-25nbYAp',
    'Someone Else' => 'SDqr0xnNfnM'
};
$variables['documentId'] = $document_id;
$variables['data']['0Qm43mG2vV'] = $manager_info;
$variables['data']['Sg2RTLnC5r']['id'] = $lsd_who_id;
$variables['data']['Sg2RTLnC5r']['label'] = $who;
$variables['data']["9eJvzLeMS0"]['id'] = "9JrVQuqdIQS";
$variables['data']["9eJvzLeMS0"]["label"] = "Staff / Faculty";
$variables['data']["6lJyeq9g1v"]['id'] = $item_type_id;
$variables['data']["6lJyeq9g1v"]["label"] = $lsd_data['item_type'];
            // REPORTED TO UPD?
$variables['data']["7BHQb4jTbS"]['id'] = $upd_id;
$variables['data']["7BHQb4jTbS"]["label"] = $lsd_data['upd'];
            // SERIAL NUMBER
$variables['data']["7Gzhcg_35S"] = $lsd_data['Serial ID'];
            // SUBMITTER SIGNATURE
$variables['data']["EeUWxyyaOUR"] = $submitter_sig;
            // DEPT IF STAFF
$variables['data']["GOiwf3tjc0"]['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']["GOiwf3tjc0"]['data']['IOw4-l7NsM'] = $dept_id;
$variables['data']["GOiwf3tjc0"]['label'] = $dept_name;
            // MAKE
$variables['data']["Qb1ac69GLa"] = $lsd_data['Make'] ?? 'N/A';
// LSD
$variables['data']["Sc5_swYeHS"]['id'] = $lsd_id;
$variables['data']["Sc5_swYeHS"]['label'] = $lsd_data['lsd'];
// NARRATIVE
$variables['data']["dyaoRcFcOD"] = $lsd_data['reason'];
// DESCR
$variables['data']["pNvpNnuav8"] = $lsd_data['Descr'];
// TAG
$variables['data']["y7nFCmsLEg"] = $lsd_data['Tag Number'];
// MODEL
$variables['data']["y9obJL9NAo"] = $lsd_data['Model'] ?? 'N/A';
// DATE MISSING
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$current_date = $date->format('m/d/Y');
$variables['data']["MiLvvsoH5a"] = $current_date;
// CURRENT DATE
$variables['data']["vedcAP4N1t"] = $current_date;
// DATE DISCOVERED MISSING
$variables['data']["fy16ygj_ST"] = $lsd_data['date_reported'];
// DATE MISSING
$variables['data']["MiLvvsoH5a"] = $current_date;
// CURRENT DATE
$variables['data']["vedcAP4N1t"] = $current_date;
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
if (!empty($data['dept_id']) && !empty($data['audit_id'])) {
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

