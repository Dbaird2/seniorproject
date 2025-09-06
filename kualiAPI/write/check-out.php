<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST['update'])) {
    die("Not submitted yet.");
}
$check_out_form = $lsd = $psr = false;

$old_tag = $_POST['old_tag'];
$old_name = $_POST['old_name'];
$old_dept = $_POST['old_dept'];
$old_room_tag = $_POST['old_room_tag'];
$old_sn = $_POST['old_sn'];
$old_price = $_POST['old_price'];
$old_po = $_POST['old_po'];

if (!empty($_POST['disposed']) && $_POST['disposed'] === 'disposed') {
    if ($_POST['reason'] === 'lsd') {
        $lsd = true;
    } else if ($_POST['reason'] === 'psr') {
        $psr = true;
    }
}
if (!empty($_POST['dept']) && !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['device']) && !empty($_POST['condition'])) {
    $dept_id = $_POST['dept'];
    $f_name = $_POST['first_name'];
    $l_name = $_POST['last_name'];
    $device = $_POST['device'];
    $condition = $_POST['condition'];
    $check_form_type = $_POST['check-forms'];
    $check_out_form = true;
}

$subdomain = "csub";
$select = "SELECT kuali_key FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->query($select);
$select_stmt->execute([":email" => $email]);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $result['kuali_key'];
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
"appId": "677d53d969ef4601572b80ae"
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
$now = new DateTime();
$asset_condition  = match ($condition) {
'Good' => '63N9VVbdk',
    'Used' => '2zmA7sZQnX',
    'New' => 'PMMV9ld3ML',
    default => 'Unknown',
};
$check_type = match ($check_form_type) {
'Checking Out Equipment' => 'Nwnp1xzbH',
    'Returning Equipment' => 'z0IRqD2_Z',
    default => 'Unknown',
};
$asset_type = match ($device) {
'Laptop' => 'XMtnWYKFx',
    'Tablet' => '-wWkrsS_A_',
    'Desktop' => 'UHFK_j1G7L',
    default => 'Unknown',
};
$for_whom = match ($role) {
'Myself' => 'fK-8m6dzx',
    'Someone else' => 'y89ptC2TA',
    default => 'Unknown',
};
$now->format('Y-m-d H:i:s');
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String) 
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        'e0fZiLYomu' => "fK-8m6dzx",
        'aUVT1BLN6V' => "XMtnWYKFx", 
        'UTQZbrKiio' => "63N9VVbdk", 
        'fyaCF8g3Uh' => 'Nwnp1xzbH', 
        'J06VDujK2F' => 'Bella Ramirez',
        '-StvOCXWsX' => '08/08/2025',
        'Smva-ICjnV' => '9001 Stockdale Highway',
        'XE0n2IZXBC' => 'Bakersfield',
        'BOZIA6hewQ' => '12345678',
        'jYTHHgL10M' => '12345SA',
        'cQOz4UQ0rQ' => 'DELL Laptop',
        "isFMbCuv8e" => [
            "data" => [
                "AkMeIWWhoj" => "Chemistry & Biochemistry",
                "IOw4-l7NsM" => "D10320"
            ],
            "documentSetId" => "67b77932c4c6216b449c7a72",
            "id" => "67b77932c4c6216b449c7a73",
            "label" => "Chemistry & Biochemistry"
        ],
        "J06VDujK2F" => [
            "displayName" => "Bella Ramirez",
            "email" => "bramirez82@csub.edu",
            "firstName" => "Bella",
            "id" => "661190f37d856f68bf9e663e",
            "lastName" => "Ramirez",
            "schoolId" => "201552642",
        ],

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

