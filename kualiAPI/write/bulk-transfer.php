<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
/* $data = [ 
 *      [0 => '1234']
 *      ];
 */      
$data = json_decode($encoded_data);
$transfer_data = [[]];
foreach ($data as $tag) {
    if ($_SESSION['data'] === $tag) {
        $transfer_data['Unit'][] = $_SESSION['Unit'];
        $transfer_data['Tag Number'][] = $tag;
        $transfer_data['Descr'][] = $_SESSION['Descr'];
        $transfer_data['Serial ID'][] = $_SESSION['Serial ID'];
        $transfer_data['VIN'][] = $_SESSION['VIN'];
        $transfer_data['Dept'][] = $_SESSION['Dept'];
        $transfer_data['Found Room Number'][] = $_SESSION['Found Room Number'];
        $transfer_data['Found Building Name'][] = $_SESSION['Found Building Name'];
        $transfer_data['Found Note'][] = $_SESSION['Found Note'];
    }
}
$dept_id = $_SESSION['info'][2];

$subdomain = "csub";
$select = "SELECT kuali_key, f_name, l_name FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->query($select);
$select_stmt->execute([":email" => $email]);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $result['kuali_key'];
$full_name = $result['f_name'] . ' ' . $result['l_name'];

$get_dept_custodians = "SELECT dept_id, dept_name, unnest(custodian) as cust FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_custodians);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$custodians = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);

$cust_count = count($custodians);
switch ($cust_count) {
case 1:
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode($custodians[0]['cust'], " ");
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[0]['cust']);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        die("Custodian not in DB");
        // SEARCH CUST IN KUALI
    }
    $cust_1 = [
        "displayName" => $custodians[0]['cust'],
        "email" => $cust_info['email'],
        "firstName": $cust_name_split[0],
        "id": $cust_info['form_id'],
        "label": $custodians[0]['cust'],
        "lastName": $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' $cust_name_split[3] ?? '',
        "schoolId": $cust_info['school_id'],
        "username": $cust_info['username']
    ]
case 2:
    $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
    $cust_name_split = explode($custodians[1]['cust'], " ");
    try {
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name"=>$custodians[1]['cust']);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        die("Custodian not in DB");
        // SEARCH CUST IN KUALI
    }
    $cust_2 = [
        "displayName" => $custodians[1]['cust'],
        "email" => $cust_info['email'],
        "firstName": $cust_name_split[0],
        "id": $cust_info['form_id'],
        "label": $custodians[0]['cust'],
        "lastName": $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' $cust_name_split[3] ?? '',
        "schoolId": $cust_info['school_id'],
        "username": $cust_info['username']
    ]
}
if (!$apikey) {
    die("No API key found for user.");
}

$select = "SELECT * FROM kuali_info WHERE email = :email";
$select_stmt = $dbh->prepare($select);
$select_stmt->bindParam(':email', $email);
$select_stmt->execute();
$kuali_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$id = $kuali_info['id'];
$display_name = $kuali_info['display_name'];
$full_name = $kuali_info['full_name'];
$school_id = $kuali_info['school_id'];
$signature = $kuali_info['signature'];

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
echo $action_id . "<br>";
curl_close($curl);

echo "PART 2 <BR>";
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
echo "<br>";
$decoded_data = json_decode($resp, true);
$document_id = $decoded_data['data']['action']['document']['id'];
$action_id = $decoded_data['data']['action']['id'];

echo $document_id . "<br>";
curl_close($curl);


echo "<br>PART 3 <br>";
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
foreach ($transfer_data as $index => $data) {
    $vin = false;
    if (!empty) $vin = true;
    if ($vin) {
    $json_form[] = [
        "data" => [
            "2OhJaMhWaL"=> null,
            "5c3qSm88bs"=> $dept_id,
            "6JHs3W0-CL"=> $data['Found Room Number'],
            "RxpLOF3XrE"=> $data['Tag Number'],
            "SBu1DONXk2"=> $dept_name,
            "_pHzQVxouz"=> $new_custodian_full_name,
            "vOI5qaQ5hL"=> $data['Descr'] . ' - ' . $data['VIN']
        ]
    ]
    } else {
    $json_form[] = [
        "data" => [
            "2OhJaMhWaL"=> null,
            "5c3qSm88bs"=> $dept_id,
            "6JHs3W0-CL"=> $data['Found Room Number'],
            "RxpLOF3XrE"=> $data['Tag Number'],
            "SBu1DONXk2"=> $dept_name,
            "_pHzQVxouz"=> $new_custodian_full_name,
            "vOI5qaQ5hL"=> $data['Descr'] . ' - ' . $data['Serial ID']
        ]
    ]

    }
}

}
$now = new DateTime();
$now->format('Y-m-d H:i:s');
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
            "VFp8qQLrUk": $full_name,
        "JZ-q3J19dw"=> ["data"=> $json_form],


        'NdN80WJusb' => [                     
            'displayName' => 'Jesse Bergkamp',
            'email'       => 'jbergkamp@csub.edu',
            'firstName'   => 'Jesse',
            'id'          => '64cac1e7df946ca476378aac',
            'lastName'    => 'Bergkamp',
            'schoolId'    => '001505881',
            'username'    => 'jbergkamp',
        ],
        'JXLJ_AOov-' => [
            'actionId'     => $action_id,
            'displayName'  => 'Tabitha Marsh (tmarsh3@csub.edu)',
            'signatureType' => 'type',
            'signedName'   => 'TMarsh',
            'temporaryUrl' => '/app/forms/api/v2/files/68b07a8e32979d6024965bde/undefined',
            'userId'       => '64cac396df946ca476387107',
        ],
        '0LZvRo9vT5' => 'This is a API test, please reject this form',
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
echo "<br>documentId: $document_id<br>";

