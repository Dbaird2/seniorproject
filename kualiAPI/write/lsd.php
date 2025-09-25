<?php
require_once "../../config.php";
include_once "../../vendor/autoload.php";
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
foreach($_SESSION['data'] as $session) {
    if ($session['Tag Number'] === $data['tag']) {
        $lsd_data['Unit'] = $session['Unit'];
        $lsd_data['Tag Number'] = $data['tag'];
        $lsd_data['Descr'] = $session['Descr'];
        $lsd_data['Serial ID'] = $session['Serial ID'];
        $lsd_data['VIN'] = $session['VIN'];
        $lsd_data['Dept'] = $session['Dept'];
        $lsd_data['Found Room Number'] = $session['Found Room Number'];
        $lsd_data['Found Building Name'] = $session['Found Building Name'];
        $lsd_data['reason'] = $data['reason'];
        $lsd_data['lsd'] = $data['lsd'];
        $lsd_data['who'] = $data['who'];
        $lsd_data['position'] = $data['position'];
        $lsd_data['upd'] = $data['upd'];
        $lsd_data['Found Note'] = $session['Found Note'];
        if ($tag['who'] === 'Myself') {
            $myself = true;
        } else if ($tag['who'] === 'Someone Else' && !empty($tag['borrower'])) {
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


$get_dept_custodians = "SELECT dept_id, dept_name, unnest(custodian) as cust FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_custodians);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$custodians = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $custodians[0]['dept_name'];

$cust_count = count($custodians);
$cust_1 = $cust_2 = $cust_3 = $cust_4 = $cust_5 = [];
switch ($cust_count) {
case 1:
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode(" ", $custodians[0]['cust']);
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[0]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodians[0]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodians[0]['cust']);
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
case 2:
    if ($cust_count >= 2) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode(" " ,$custodians[1]['cust']);
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[1]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodians[1]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[1]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodians[1]['cust']);
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
case 3:
    if ($cust_count >= 3) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode(" " ,$custodians[2]['cust']);
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[2]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodians[2]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[2]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodians[2]['cust']);
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
case 4:
    if ($cust_count >= 4) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode(" ", $custodians[3]['cust']);
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[3]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodians[3]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[3]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);

            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodians[3]['cust']);
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
case 5:
    if ($cust_count >= 5) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode(" ", $custodians[4]['cust']);
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[4]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodians[4]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[4]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodians[4]['cust']);
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
$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
$now = new DateTime();
$now->format('Y-m-d H:i:s');

$ms_time = round(microtime(true) * 1000);
if ($lsd_data['who'] === 'Myself') {
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
        ...$custs,
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
        // WHO
                "Sg2RTLnC5r"=> [
                  "id"=> "w-25nbYAp",
                  "label"=> "Myself"
                ],
                // MANAGER IF STAFF
                "0Qm43mG2vV": {
                  "displayName": "Anthony Rathburn",
                  "email": "arathburn@csub.edu",
                  "firstName": "Anthony",
                  "id": "64cac1e0df946ca476378823",
                  "label": "Anthony Rathburn",
                  "lastName": "Rathburn",
                  "schoolId": "001502085",
                  "username": "arathburn"
                },
                // ITEM TYPE (IT EQUIP, INSTRUCTIONAL, OTHER)
                "6lJyeq9g1v": {
                  "id": "iZ6HWywjL",
                  "label": $lsd_data['item_type']
},
                // REPORTED TO UPD?
                "7BHQb4jTbS": {
                  "id": "CbModhwutSo",
                  "label": "No"
                },
                    // SERIAL NUMBER
                "7Gzhcg_35S": $lsd_data['Serial ID'],
                "9eJvzLeMS0": {
                  "id": "9JrVQuqdIQS",
                  "label": "Staff / Faculty"
                },
                    // SUBMITTER SIGNATURE
                "EeUWxyyaOUR": {
                  "actionId": "68c0a83fc097f9fb447b2a6b",
                  "date": "2025-09-09T22:28:52.798Z",
                  "displayName": "Shauna Van Grinsven (svan-grinsven@csub.edu)",
                  "signatureType": "type",
                  "signedName": "Shauna Van Grinsven",
                  "temporaryUrl": "/app/forms/api/v2/files/689e27eb42b1b41b1ba762aa/undefined",
                  "userId": "678fb95909bf8c07c9aac978"
                },
                    // DEPT IF STAFF
                "GOiwf3tjc0": {
                  "data": {
                    "AkMeIWWhoj": "Geological Sciences",
                    "IOw4-l7NsM": "D10380"
                  },
                  "label": "Geological Sciences"
                },
                    // MAKE
                "Qb1ac69GLa": "Supreme Air",
                    // LSD
                "Sc5_swYeHS": {
                  "id": "bqRxkqovw",
                  "label": "Lost"
                },
                    // NARRATIVE
                "dyaoRcFcOD": "The Geology Department does not have this fume hood in any classroom or lab, nor do I have documentation of its previous location from the former property custodians. ",
                // DESCR
                "pNvpNnuav8": "Supreme Air Fume Hood",
                // TAG
                "y7nFCmsLEg": "18458",
                // MODEL
                "y9obJL9NAo": "Supreme Air 5ft"
              },
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
]);
} else if ($lsd_data['who'] === 'Someone else') {
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
        ...$custs,
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
        "data": {
                "0Qm43mG2vV": {
                  "displayName": "Anthony Rathburn",
                  "email": "arathburn@csub.edu",
                  "firstName": "Anthony",
                  "id": "64cac1e0df946ca476378823",
                  "label": "Anthony Rathburn",
                  "lastName": "Rathburn",
                  "schoolId": "001502085",
                  "username": "arathburn"
                },
                "1w1_RfeMoG": "Distribution Lead",
                "6lJyeq9g1v": {
                  "id": "iZ6HWywjL",
                  "label": "Instructional Equipment"
                },
                "7BHQb4jTbS": {
                  "id": "CbModhwutSo",
                  "label": "No"
                },
                "7Gzhcg_35S": "R152617",
                "9BeWmJXRb4q": {
                  "actionId": "68c0c18bc097f9fb4480aa34",
                  "date": "2025-09-10T15:20:51.553Z",
                  "displayName": "Aditi Arya (aarya1@csub.edu)",
                  "signatureType": "type",
                  "signedName": "Aditi Arya",
                  "temporaryUrl": "/app/forms/api/v2/files/689e27eb42b1b41b1ba762aa/undefined",
                  "userId": "661f0788ab7adcea4cb4c680"
                },
                "9eJvzLeMS0": {
                  "id": "9JrVQuqdIQS",
                  "label": "Staff / Faculty"
                },
                "CS44boCJiU": {
                  "actionId": "68c197784513111b2de1f67a",
                  "date": "2025-09-10T22:13:24.303Z",
                  "displayName": "Rigoberto Razo (rrazo2@csub.edu)",
                  "signatureType": "type",
                  "signedName": "Rigoberto Razo",
                  "temporaryUrl": "/app/forms/api/v2/files/689e27eb42b1b41b1ba762aa/undefined",
                  "userId": "67be4dd110ac2eb10e3feddd"
                },
                "EeUWxyyaOUR": {
                  "actionId": "68c0a83fc097f9fb447b2a6b",
                  "date": "2025-09-09T22:28:52.798Z",
                  "displayName": "Shauna Van Grinsven (svan-grinsven@csub.edu)",
                  "signatureType": "type",
                  "signedName": "Shauna Van Grinsven",
                  "temporaryUrl": "/app/forms/api/v2/files/689e27eb42b1b41b1ba762aa/undefined",
                  "userId": "678fb95909bf8c07c9aac978"
                },
                "GOiwf3tjc0": {
                  "data": {
                    "AkMeIWWhoj": "Geological Sciences",
                    "IOw4-l7NsM": "D10380"
                  },
                  "documentSetId": "67b77af288e621894856e7d0",
                  "id": "67b77af288e621894856e7d1",
                  "label": "Geological Sciences"
                },
                "IMt9oe8wL5": {
                  "actionId": "68c0bd454513111b2dd4e0d0",
                  "date": "2025-09-10T00:08:28.082Z",
                  "displayName": "Anthony Rathburn (arathburn@csub.edu)",
                  "signatureType": "type",
                  "signedName": "Anthony Rathburn",
                  "temporaryUrl": "/app/forms/api/v2/files/689e27eb42b1b41b1ba762aa/undefined",
                  "userId": "64cac1e0df946ca476378823"
                },
                "MiLvvsoH5a": 1757376000000,
                "Qb1ac69GLa": "Supreme Air",
                "Sc5_swYeHS": {
                  "id": "bqRxkqovw",
                  "label": "Lost"
                },
                "Sg2RTLnC5r": {
                  "id": "w-25nbYAp",
                  "label": "Myself"
                },
                "SjD_YMtQeG": "Dept Chair",
                "TVsWI68kxB": "Energy and Sustainability Manager",
                "Z6iBjLMEkD": 1757376000000,
                "dyaoRcFcOD": "The Geology Department does not have this fume hood in any classroom or lab, nor do I have documentation of its previous location from the former property custodians. ",
                "fy16ygj_ST": 1757376000000,
                "pNvpNnuav8": "Supreme Air Fume Hood",
                "s-uc7R_TFv": 1757462400000,
                "tIGEHgQi2s": {
                  "id": "no",
                  "label": "\"was not\" the result of negligence"
                },
                "vedcAP4N1t": 1757376000000,
                "xJenX4GBZs": "Instructional Support Techician",
                "y7nFCmsLEg": "18458",
                "y9obJL9NAo": "Supreme Air 5ft",
                "yIpeMtiT_7": 1757462400000
              },
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
curl_close($curl);
exit;
//var_dump($resp);
function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $pass[] = '-';
    $pass[] = '1';
    $pass[] = '2';
    $pass[] = '3';
    $pass[] = 'A';
    return implode($pass);
}
function searchName($search_name = '')
{
    global $apikey;
    global $dept_id;
    $name_array = explode(' ' ,$search_name);
    $user_f_name = $name_array[0];
    $user_l_name = $name_array[1] . ' ' . $name_array[2] ?? '' . ' ' . $name_array[3] ?? '' . ' ' . $name_array[4] ?? '';
    global $dbh;
    $subdomain = "csub";

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
    $kuali_json = json_encode([
        'query' => 'query ($query: String) {
        usersConnection(args: { query: $query }) {
        edges {
        node { id displayName email username firstName lastName schoolId }
}
}
}',
    'variables' => [
        'query' => $search_name
    ]
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $kuali_json);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    //var_dump($resp);
    $name_data = json_decode($resp, true);
    $name_edges = $name_data['data']['usersConnection']['edges'];
    foreach ($name_edges as $info) {
        $id = $info['node']['id'];
        $display_name = $info['node']['displayName'];
        $email = $info['node']['email'];
        $username = $info['node']['username'];
        $f_name = $info['node']['firstName'];
        $l_name = $info['node']['lastName'];
        $schoolid = $info['node']['schoolId'];
        if (strtolower(trim($f_name)) !== strtolower(trim($user_f_name)) || strtolower(trim($l_name)) !== strtolower(trim($user_l_name)))  {
            continue;
        }
        // CHECK DB
        $select = "SELECT * from user_table WHERE email = :email";
        $select_stmt = $dbh->prepare($select);
        $select_stmt->execute([":email"=>$email]);
        if ($select_stmt->rowCount() <= 0) {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);

            $insert = "INSERT INTO user_table (form_id, username, email, f_name, l_name, school_id, u_role, pw, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $dbh->prepare($insert);
            $insert_stmt->execute([$id, $username, $email, $f_name, $l_name, $schoolid, 'user', $hashed_pw, '{' . $dept_id . '}']);
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
                $mail->addAddress($email, 'User');
                $mail->Subject = 'Account Auto Create';
                $mail->Body    = '<p><strong>This email is to notify you of your automatic account creation for dataworks<strong>. <br>Dataworks is Senior Project group project designed to help with auditing and asset tracking.
                    Email: ' . $email . '<br>Password: ' . $pw . '<br>If you have any questions, concerns, or issues, feel free to reach out to distribution@csub.edu for more info.</p><br>
                    <a href="https://dataworks-7b7x.onrender.com">Dataworks Link</a>';
                $mail->AltBody = 'Click this link to access Dataworks...';

                $mail->send();
            } catch (Exception $e) {
                error_log("Error sending email: " . $e->getMessage());
                return;
            }
        } else {
            $user = $select_stmt->fetch(PDO::FETCH_ASSOC);
            $update = "UPDATE user_table SET ";
            $count = 0;
            $params = [":email"=>$email];
            if (empty($user['school_id'])) {
                $count++;
                $update .= 'school_id = :school';
                $params[":school"] = $schoolid;
            }
            if (empty($user['form_id'])) {
                if ($count == 1) {
                    $update .= ', form_id = :form';
                } else {
                    $update .= 'form_id = :form';
                }
                $count++;
                $params[":form"] = $id;
            }
            $update .= " WHERE email = :email";
            if ($count > 0) {
                $update_stmt = $dbh->prepare($update);
                $update_stmt->execute($params);
            }
        }
    }
}
