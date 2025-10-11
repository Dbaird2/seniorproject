<?php
require_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once 'search.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);

$select = "SELECT bus_unit, asset_name, dept_id, serial_num, asset_price, asset_type, make, type2, asset_model FROM asset_info WHERE asset_tag = :tag";
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":tag"=>$data['tag']]);
$session = $select_stmt->fetch(PDO::FETCH_ASSOC);
$lsd_data['Unit'] = $session['bus_unit'];
$lsd_data['Tag Number'] = $data['tag'];
$lsd_data['Descr'] = $session['asset_name'];
$lsd_data['Serial ID'] = $session['serial_num'];
if (!empty($session['make'])) {
    $lsd_data['Make'] = $session['make'];
} else {
    $lsd_data['Make'] = 'N/A';
}
$lsd_data['Model'] = $session['asset_model'];
$lsd_data['Dept'] = $session['dept_id'];
$lsd_data['reason'] = $data['reason'];
$lsd_data['lsd'] = $data['lsd'];
$lsd_data['who'] = $data['who'];
$lsd_data['position'] = $data['position'];
$lsd_data['upd'] = $data['upd'];
$lsd_data['borrower'] = $data['borrower'];
$lsd_data['item_type'] = $data['item_type'];
if ($data['who'] === 'Myself') {
    $myself = true;
} else if ($data['who'] === 'someone-else' && !empty($data['borrower'])) {
    $someone_else = true;
}

if ($data['from_page'] !== 'search') {
    $dept_id = $_SESSION['info'][2];
} else {
    $dept_id = $_SESSION['deptid'];
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
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
$email_array = explode('@', $email);
if (empty($school_id) || empty($form_id)) {
    searchName($full_name);
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


$get_dept_manager = "SELECT dept_id, dept_name, dept_manager FROM department WHERE dept_id = :dept_id";
$get_mana_stmt = $dbh->prepare($get_dept_manager);
$get_mana_stmt->execute([":dept_id"=>$dept_id]);
$dept_info = $get_mana_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $dept_info['dept_name'];
$manager = $dept_info['dept_manager'];
echo json_encode(["data"=>$manager . ' ' . $dept_name]);

$get_mana_info = "select l_name, f_name, email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
try {
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name"=>$manager]);
    $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($mana_info['form_id']) || empty($mana_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($manager);
        $get_mana_stmt = $dbh->prepare($get_mana_info);
        $get_mana_stmt->execute([":full_name" => $manager]);
        $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // CUST DID NOT MATCH
    searchName($manager);
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name" => $manager]);
    $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    // SEARCH CUST IN KUALI
}
$mana_f_name = $mana_info['f_name'];
$mana_l_name = $mana_info['l_name'];
$mana_email = $mana_info['email'];
$mana_form_id = $mana_info['form_id'];
$mana_form_sig = $mana_info['signature'];
$mana_form_sid = $mana_info['school_id'];
$mana_form_user = $mana_info['username'];

if (!empty($lsd_data['borrower'])) {
    try {
        $get_borrower_stmt = $dbh->prepare($get_mana_info);
        $get_borrower_stmt->execute([":full_name"=>$lsd_data['borrower']]);
        $borrower_info = $get_borrower_stmt->fetch(PDO::FETCH_ASSOC);
        $bor_email_array = explode('@', $lsd_data['borrower']);
        if (empty($borrower_info['form_id']) || empty($borrower_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($lsd_data['borrower']);
            $get_borrower_stmt = $dbh->prepare($get_mana_info);
            $get_borrower_stmt->execute([":full_name" => $lsd_data['borrower']]);
            $borrower_info = $get_borrower_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($lsd_data['borrower']);
        $get_borrower_stmt = $dbh->prepare($get_mana_info);
        $get_borrower_stmt->execute([":full_name" => $lsd_data['borrower']]);
        $borrower_info = $get_borrower_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $bor_f_name = $mana_info['f_name'];
    $bor_l_name = $mana_info['l_name'];
    $bor_email = $mana_info['email'];
    $bor_form_id = $mana_info['form_id'];
    $bor_form_sig = $mana_info['signature'];
    $bor_form_sid = $mana_info['school_id'];
    $bor_form_user = $mana_info['username'];
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

$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
$now_array = new DateTime();
$now_array->setTimezone( new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

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

$variables['documentId'] = $document_id;
$variables['actionId'] = $action_id;
$variables['status']= 'completed';
if ($lsd_data['who'] === 'someone-else') {
    $variables['data']["N00EmVKFnd"]["displayName"] = $lsd_data['borrower'];
    $variables['data']["N00EmVKFnd"]["email"] = $bor_email;
    $variables['data']["N00EmVKFnd"]["firstName"] = $bor_f_name;
    $variables['data']["N00EmVKFnd"]["id"] = $bor_form_id;
    $variables['data']["N00EmVKFnd"]["label"] = $lsd_data['borrower'];
    $variables['data']["N00EmVKFnd"]["lastName"] = $bor_l_name;
    $variables['data']["N00EmVKFnd"]["schoolId"] = $bor_form_sid;
    $variables['data']["N00EmVKFnd"]["username"] = $bor_form_user;
    $variables['data']["Sg2RTLnC5r"]["id"] = "SDqr0xnNfnM";
    $variables['data']["Sg2RTLnC5r"]["label"] = "I am initiating this submission on behalf of";
} else {
    $variables['data']["Sg2RTLnC5r"]["id"] = "w-25nbYAp";
    $variables['data']["Sg2RTLnC5r"]["label"] = "Myself";
}
// MANAGER
$variables['data']["0Qm43mG2vV"]['displayName'] = $manager;
$variables['data']["0Qm43mG2vV"]['email'] = $mana_email;
$variables['data']["0Qm43mG2vV"]['firstName'] = $mana_f_name;
$variables['data']["0Qm43mG2vV"]['id'] = $mana_form_id;
$variables['data']["0Qm43mG2vV"]['label'] = $manager;
$variables['data']["0Qm43mG2vV"]['lastName'] = $mana_l_name;
$variables['data']["0Qm43mG2vV"]['schoolId'] = $mana_form_sid;
$variables['data']["0Qm43mG2vV"]['username'] = $mana_form_user;
// ITEM TYPE
$variables['data']["6lJyeq9g1v"]['id'] = $item_type_id;
$variables['data']["6lJyeq9g1v"]['label'] = $lsd_data['item_type'];
if ($lsd_data['item_type'] === 'IT Equipment') {
    // PUT IN REST OF IT ADDENDUM
}
// REPORTED TO UPD
$variables['data']["7BHQb4jTbS"]['id'] = $upd_id;
$variables['data']["7BHQb4jTbS"]['label'] = $lsd_data['upd'];
if ($lsd_data['upd'] === 'Yes') {
    // PUT IN UPD ADDENDUM
}
$variables['data']["7Gzhcg_35S"] = $lsd_data['Serial ID'];
$variables['data']["9eJvzLeMS0"]['id'] = "9JrVQuqdIQS";
$variables['data']['9eJvzLeMS0']["label"] = "Staff / Faculty";
// SUBMITTER SIGNATURE
$variables['data']["EeUWxyyaOUR"]['actionId'] = $action_id;
$variables['data']["EeUWxyyaOUR"]['date'] = $now;
$variables['data']["EeUWxyyaOUR"]['displayName'] = $$full_name . ' ('.$_SESSION['email'].')';
$variables['data']["EeUWxyyaOUR"]['signatureType'] = 'type';
$variables['data']["EeUWxyyaOUR"]['signedName'] = $full_name;
$variables['data']["EeUWxyyaOUR"]['userId'] = $form_id;
// DEPT IF STAFF
$variables['data']["GOiwf3tjc0"]['label'] = $dept_name;
$variables['data']["GOiwf3tjc0"]['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']["GOiwf3tjc0"]['data']['IOw4-l7NsM'] = $_SESSION['info'][2];
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
exit;
