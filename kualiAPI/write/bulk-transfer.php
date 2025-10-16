<?php
require_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once "search.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$tag_data = $data;
$transfer_data = [[]];
$index = 0;
foreach ($data['bulk_t_tags'] as $tag) {
    foreach($_SESSION['data'] as $session) {
        if ($session['Tag Number'] === $tag) {
            $transfer_data[$index]['Unit'] = $session['Unit'];
            $transfer_data[$index]['Tag Number'] = $tag;
            $transfer_data[$index]['Descr'] = $session['Descr'];
            $transfer_data[$index]['Serial ID'] = $session['Serial ID'];
            $transfer_data[$index]['VIN'] = $session['VIN'];
            $transfer_data[$index]['Dept'] = $session['Dept'];
            $transfer_data[$index]['Found Room Number'] = $session['Found Room Number'];
            $transfer_data[$index]['Found Building Name'] = $session['Found Building Name'];
            $transfer_data[$index++]['Found Note'] = $session['Found Note'];
            break;
        }
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
$apikey = $submitter_info['kuali_key'] ?? '';
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
if (empty($school_id) || empty($form_id)) {
    searchName($full_name, $apikey, $dept_id);
}


$get_dept_custodians = "SELECT dept_id, dept_name, unnest(custodian) as cust FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_custodians);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$custodians = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $custodians[0]['dept_name'];

$cust_count = count($custodians);
$cust_1 = $cust_2 = $cust_3 = $cust_4 = $cust_5 = [];
$get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
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
$l_name = implode(' ', array_filter([
    $cust_name_split[1] ?? '',
    $cust_name_split[2] ?? '',
    $cust_name_split[3] ?? '',
], 'strlen'));
$cust_1 = [
    "displayName" => $custodians[0]['cust'],
    "email" => $cust_info['email'],
    "firstName"=> $cust_name_split[0],
    "id"=> $cust_info['form_id'],
    "label"=> $custodians[0]['cust'],
    "lastName"=> $l_name,
    "schoolId"=> $cust_info['school_id'],
    "username"=> $cust_info['username']
];
if ($cust_count >= 2) {
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" " ,$custodians[1]['cust']);
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[1]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[1]['cust'], $apikey, $dept_id);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[1]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[1]['cust'], $apikey, $dept_id);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[1]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $l_name = implode(' ', array_filter([
        $cust_name_split[1] ?? '',
        $cust_name_split[2] ?? '',
        $cust_name_split[3] ?? '',
    ], 'strlen'));
    $cust_2 = [
        "displayName" => $custodians[1]['cust'],
        "email" => $cust_info['email'],
        "firstName"=> $cust_name_split[0],
        "id"=> $cust_info['form_id'],
        "label"=> $custodians[1]['cust'],
        "lastName"=> $l_name,
        "schoolId"=> $cust_info['school_id'],
        "username"=> $cust_info['username']
    ];
}
if ($cust_count >= 3) {
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" " ,$custodians[2]['cust']);
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[2]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[2]['cust'], $apikey, $dept_id);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[2]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[2]['cust'], $apikey, $dept_id);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[2]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $l_name = implode(' ', array_filter([
        $cust_name_split[1] ?? '',
        $cust_name_split[2] ?? '',
        $cust_name_split[3] ?? '',
    ], 'strlen'));
    $cust_3 = [
        "displayName" => $custodians[2]['cust'],
        "email" => $cust_info['email'],
        "firstName"=> $cust_name_split[0],
        "id"=> $cust_info['form_id'],
        "label"=> $custodians[2]['cust'],
        "lastName"=> $l_name,
        "schoolId"=> $cust_info['school_id'],
        "username"=> $cust_info['username']
    ];
}
if ($cust_count >= 4) {
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" ", $custodians[3]['cust']);
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[3]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[3]['cust'], $apikey, $dept_id);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[3]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);

        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[3]['cust'], $apikey, $dept_id);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[3]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $l_name = implode(' ', array_filter([
        $cust_name_split[1] ?? '',
        $cust_name_split[2] ?? '',
        $cust_name_split[3] ?? '',
    ], 'strlen'));
    $cust_4 = [
        "displayName" => $custodians[3]['cust'],
        "email" => $cust_info['email'],
        "firstName"=> $cust_name_split[0],
        "id"=> $cust_info['form_id'],
        "label"=> $custodians[3]['cust'],
        "lastName"=> $l_name,
        "schoolId"=> $cust_info['school_id'],
        "username"=> $cust_info['username']
    ];
}
if ($cust_count >= 5) {
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" ", $custodians[4]['cust']);
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[4]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[4]['cust'], $apikey, $dept_id);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[4]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[4]['cust'], $apikey, $dept_id);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[4]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $l_name = implode(' ', array_filter([
        $cust_name_split[1] ?? '',
        $cust_name_split[2] ?? '',
        $cust_name_split[3] ?? '',
    ], 'strlen'));
    $cust_5 = [
        "displayName" => $custodians[4]['cust'],
        "email" => $cust_info['email'],
        "firstName"=> $cust_name_split[0],
        "id"=> $cust_info['form_id'],
        "label"=> $custodians[4]['cust'],
        "lastName"=> $l_name,
        "schoolId"=> $cust_info['school_id'],
        "username"=> $cust_info['username']
    ];
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
"appId": "68c73600df46a3027d2bd386"
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
$json_form = [];
foreach ($transfer_data as $index => $data) {
    $vin = false;
    if (!empty($data['VIN'])) $vin = true;
    $json_form['data'][] = [
        "data" => [
            "5c3qSm88bs"=> (string)$dept_id,
            "6JHs3W0-CL"=> (string)$data['Found Room Number'],
            "RxpLOF3XrE"=> (string)$data['Tag Number'],
            "SBu1DONXk2"=> (string)$dept_name . ' (' . $data['Found Building Name'] . ')',
            "_pHzQVxouz"=> (string)$custodians[0]['cust'],
            "vOI5qaQ5hL"=> (string)$data['Descr'] . ' - ' . ($vin ? 'VIN: ' . (string)$data['VIN'] ?? '' : 'SN: ' . (string)$data['Serial ID'] ?? ''),
        ], 
        'id'=>(string)$index,
    ];
}
$custs = [
    "Gf5oXuQkTBy"=> $cust_1
];
if (!empty($cust_2)) {
    $custs = [
        "Gf5oXuQkTBy"=> $cust_1,
        "i6O5npcOWj" => $cust_2
    ];
}
if (!empty($cust_3)) {
    $custs = [
        "Gf5oXuQkTBy"=> $cust_1,
        "i6O5npcOWj" => $cust_2,
        "2W25abEJ4O" => $cust_3
    ];
}
if (!empty($cust_4)) {
    $custs = [
        "Gf5oXuQkTBy"=> $cust_1,
        "i6O5npcOWj" => $cust_2,
        "2W25abEJ4O" => $cust_3,
        "DZrHu6ITkF" => $cust_4
    ];
}
if (!empty($cust_5)) {
    $custs = [
        "Gf5oXuQkTBy"=> $cust_1,
        "i6O5npcOWj" => $cust_2,
        "2W25abEJ4O" => $cust_3,
        "DZrHu6ITkF" => $cust_4,
        "_MkyBYDNix" => $cust_5
    ];
}
echo json_encode(['Custodians'=>$custs]);
$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
$now_array = new DateTime();
$now_array->setTimezone( new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

$ms_time = round(microtime(true) * 1000);
if (empty($cust_2)) {
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        "_GODY1FjEy" => [
            "id"=> "9A_6UOlDb",
            "label"=> "From one department to another department "
        ],
        "VFp8qQLrUk"=> $full_name,
        "Gf5oXuQkTBy"=> $cust_1,
        "JZ-q3J19dw"=> $json_form,
        "ne3KPx1Wy3"=> [
            "actionId"=> $action_id,
            "date"=> $now,
            "displayName"=> $full_name . " (" . $_SESSION['email'] . ")",
            "signatureType"=> "type",
            "signedName"=> $full_name,
            "userId"=> $form_id
        ],
        "K3p03X2Jvx"=> $reason,
        "R-jIGrtlfO"=> $ms_time,
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
]);
} else if (empty($cust_3)) {
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        "_GODY1FjEy" => [
            "id"=> "9A_6UOlDb",
            "label"=> "From one department to another department "
        ],
        "VFp8qQLrUk"=> $full_name,
        "Gf5oXuQkTBy"=> $cust_1,
        "i6O5npcOWj" => $cust_2,
        "JZ-q3J19dw"=> $json_form,
        "ne3KPx1Wy3"=> [
            "actionId"=> $action_id,
            "date"=> $now,
            "displayName"=> $full_name . " (" . $_SESSION['email'] . ")",
            "signatureType"=> "type",
            "signedName"=> $full_name,
            "userId"=> $form_id
        ],
        "K3p03X2Jvx"=> $reason,
        "R-jIGrtlfO"=> $ms_time,
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
]);
}
curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
$resp_data = json_decode($resp, true);
$input_array = $document_id . ',transfer,in-progress'; 
foreach ($transfer_data as $tag_info) {
    $input_array .= ',' . $tag_info['Tag Number'];
}

$audit_id = $tag_data['bulk_t_tags'][0]['audit_id'];
$update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ':array') WHERE dept_id = :dept AND audit_id = :id";
$update_stmt = $dbh->prepare($update);
$update_stmt->execute([':array'=>$input_array, ":dept"=>$dept, ":id"=>$audit_id]);

curl_close($curl);
echo json_encode([$ms_time
    ,$tag_data
    ,$document_id
    ,$full_name
    ,$cust_1
    ,$json_form
    ,$reason
    ,$action_id
    ,$now
    ,$form_id
    ,$resp
    ,$dept_id
    ,$_SESSION['info']
    ,$update_stmt
]);
exit;

