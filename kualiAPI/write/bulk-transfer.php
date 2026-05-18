<?php
require_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once "search.php";
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$tag_data = $data;
$transfer_data = [[]];
$variables = [[]];

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
$submitter_info = $query_repo->getUserInfo($_SESSION['email']);

$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
if (empty($school_id) || empty($form_id)) {
    searchName($full_name, $dept_id);
}

$custodians = $query_repo->getCustodians($dept_id);
$dept_name = $custodians[0]['dept_name'];

$cust_count = count($custodians);
$cust_1 = $cust_2 = $cust_3 = $cust_4 = $cust_5 = [];
$get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
$cust_name_split = explode(" ", $custodians[0]['cust']);
try {

    $cust_info = $query_repo->getCustInfo($custodians[0]['cust']);
    if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($custodians[0]['cust'], $dept_id);
        $cust_info = $query_repo->getCustInfo($custodians[0]['cust']);
    }
} catch (PDOException $e) {
    // CUST DID NOT MATCH
    searchName($custodians[0]['cust'], $dept_id);
    $cust_info = $query_repo->getCustInfo($custodians[0]['cust']);

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
        $cust_info = $query_repo->getCustInfo($custodians[1]['cust']);

        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[1]['cust'], $dept_id);
            $cust_info = $query_repo->getCustInfo($custodians[1]['cust']);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[1]['cust'], $dept_id);
        $cust_info = $query_repo->getCustInfo($custodians[1]['cust']);

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
    $variables['data']['i6O5npcOWj'] = $cust_2;
}
if ($cust_count >= 3) {
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" " ,$custodians[2]['cust']);
    try {
        $cust_info = $query_repo->getCustInfo($custodians[2]['cust']);

        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[2]['cust'], $dept_id);
            $cust_info = $query_repo->getCustInfo($custodians[2]['cust']);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[2]['cust'], $dept_id);
        $cust_info = $query_repo->getCustInfo($custodians[2]['cust']);

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
        $cust_info = $query_repo->getCustInfo($custodians[3]['cust']);

        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[3]['cust'], $dept_id);
            $cust_info = $query_repo->getCustInfo($custodians[3]['cust']);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[3]['cust'], $dept_id);
        $cust_info = $query_repo->getCustInfo($custodians[3]['cust']);

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
        $cust_info = $query_repo->getCustInfo($custodians[4]['cust']);

        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[4]['cust'], $dept_id);
            $cust_info = $query_repo->getCustInfo($custodians[4]['cust']);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[4]['cust'], $dept_id);
        $cust_info = $query_repo->getCustInfo($custodians[4]['cust']);

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

$json_form = [];
foreach ($transfer_data as $index => $data) {
    $vin = false;
    if (!empty($data['VIN'])) $vin = true;
    $json_form['data'][] = [
        "data" => [
            "5c3qSm88bs"=> (string)$dept_id,
            "6JHs3W0-CL"=> (string)$data['Found Room Number'] ?? 'CHCKD',
            "RxpLOF3XrE"=> (string)$data['Tag Number'],
            "SBu1DONXk2"=> (string)$dept_name . ' (' . $data['Found Building Name'] . ')',
            "_pHzQVxouz"=> (string)$custodians[0]['cust'],
            "vOI5qaQ5hL"=> (string)$data['Descr'] . ' - ' . ($vin ? 'VIN: ' . (string)$data['VIN'] ?? '' : 'SN: ' . (string)$data['Serial ID'] ?? ''),
        ], 
        'id'=>(string)$index,
    ];
}
$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
$now_array = new DateTime();
$now_array->setTimezone( new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

$ms_time = round(microtime(true) * 1000);
$variables['data']['_GODY1FjEy']['id'] = '9A_6UOlDb';
$variables['data']['_GODY1FjEy']['label'] = 'From one department to another department ';
$variables['data']['VFp8qQLrUk'] = $full_name;
$variables['data']['Gf5oXuQkTBy'] = $cust_1;
$variables['data']['JZ-q3J19dw'] = $json_form;
$variables['data']['ne3KPx1Wy3']['date'] = $now;
$variables['data']['ne3KPx1Wy3']['displayName'] = $full_name . " (" . $_SESSION['email'] . ")";
$variables['data']['ne3KPx1Wy3']['signatureType'] = "type";
$variables['data']['ne3KPx1Wy3']['signedName'] = $full_name;
$variables['data']['ne3KPx1Wy3']['userId'] = $form_id;
$variables['data']['K3p03X2Jvx'] = $reason;
$variables['data']['R-jIGrtlfO'] = $ms_time;

$resp_data = $kuali->writeToKuali("68c73600df46a3027d2bd386", $variables);
$decoded = json_decode($resp_data, true);
$input_array = $decoded['document_id'] . ',transfer,in-progress'; 
foreach ($transfer_data as $tag_info) {
    $input_array .= ',' . trim($tag_info['Tag Number']);
}

$audit_id = $tag_data['audit_id'];

if ($decoded['status'] === 'Ok') {
    $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ?) WHERE dept_id = ? AND audit_id = ?";
    $query_repo->execute($update, $input_array, $dept_id, $audit_id);
    echo json_encode(['status'=>'Bulk Transfer Ok']);
} else {
    echo json_encode(['status'=>'Bulk Transfer Failed', 'res'=>$resp_data]);
}
exit;

