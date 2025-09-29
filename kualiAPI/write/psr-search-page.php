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
$transfer_data = [[]];
$index = 0;
echo json_encode(['tags'=>$data]);
$its = false;
$cmp = $spa = $stu = $fdn = $asi = false;
$select = "SELECT serial_num, asset_name, type2 FROM asset_info WHERE asset_tag = :tag";
$stmt = $dbh->prepare($select);
$stmt->execute([":tag"=>$data['tag']]);
$type = $stmt->fetch(PDO::FETCH_ASSOC);
if (in_array($type, ['Laptop', 'Tablet', 'Desktop'])) {
        $its = true;
    }
    if ($_SESSION['Unit'] === 'BKCMP') {
        $cmp = true;
    } else if ($_SESSION['Unit'] === 'BKSPA') {
        $spa = true;
    } else if ($_SESSION['Unit'] === 'BKASI') {
        $asi = true;
    } else if ($_SESSION['Unit'] === 'BKSTU') {
        $stu = true;
    } else if ($_SESSION['Unit'] === 'BKFDN') {
        $fdn = true;
    }
    $transfer_data[$index]['Tag Number'] = $data['tag'];
    $transfer_data[$index]['Descr'] = $type['asset_name'];
    $transfer_data[$index]['Serial ID'] = $type['serial_num'];
    $transfer_data[$index]['code'] = $data['code'];
    $transfer_data[$index]['reason'] = $data['reason'];
if ($data['from_page'][0] !== 'search') {
    $dept_id = $_SESSION['info'][2];
} else {
    $dept_id = $_SESSION['dept_id'];
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
if (empty($school_id) || empty($form_id)) {
    searchName($full_name);
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
    $apikey = $submitter_info['kuali_key'] ?? '';
    $display_name = $submitter_info['username'];
    $full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}
$custodian_kuali['ryhlM_VqBn'] = [
    "displayName" => $full_name,
    "email" => $_SESSION['email'],
    "firstName"=> $submitter_info['f_name'],
    "id"=> $form_id,
    "label"=> $full_name,
    "lastName"=> $submitter_info['l_name'],
    "schoolId"=> $school_id,
    "username"=> $display_name
];


$get_dept_manager = "SELECT dept_id, dept_name, manager FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_manager);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$manager_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
$dept_name = $manager_info['dept_name'];
$manager = $manager_info['manager'];

$cust_1 = [];
$get_mana_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
try {
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name"=>$manager]);
    $manager_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    if ($get_mana_stmt->rowCount() <= 0) {
        searchName($manager);
        $get_mana_stmt = $dbh->prepare($get_mana_info);
        $get_mana_stmt->execute([":full_name" => $manager]);
        $mana_info = $get_cmana_stmt->fetch(PDO::FETCH_ASSOC);
    } else if (empty($manager_info['form_id']) || empty($manager_info['school_id'])) {
        // SEARCH CUST IN KUALI
        searchName($manager);
        $get_mana_stmt = $dbh->prepare($get_cust_info);
        $get_mana_stmt->execute([":full_name" => $manager]);
        $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // CUST DID NOT MATCH
    searchName($manager);
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name" => $manager]);
    $mana_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
    // SEARCH CUST IN KUALI
}

$manager_kuali['Jpy9KU-X3P'] = [
    "displayName"=> $manager,
    "email"=> $mana_info['email'],
    "firstName"=> $mana_info['f_name'],
    "id"=> $mana_info['form_id'],
    "label"=> $manager,
    "lastName"=> $mana_info['l_name'],
    "schoolId"=> $mana_info['school_id'],
    "username"=> $mana_info['username']
];

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
"appId": "68d09dcd7688dc028af9b5e7"
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
    $disposition_id = match ($data['code']) {
        'UNIVERSAL WASTE — SALVAGE DEALER, RECYCLER (E-WASTE)' => 'CuUeXWzGh',
            'LOST, STOLEN OR DESTROYED (REFER TO SAM SECTION 8643 FOR INSTRUCTIONS)' => '2qPaeKyjo',
            'VALUELESS UNABLE TO BE RECYCLED (TO BE LEGALLY/SAFELY DISPOSED OF)' => '_tTo3w-3o',
            'TO BE CANABALIZED (SALVAGED FOR PARTS)' => 'XG6kUAxX1',
            'SHIPPED TO SCRAP / SALVAGE DEALER (TO BE RECYCLED) NOTE: FOR E-WASTE USE # 10' => 'zNDbUOd2L',
            'DONATION TO AN ELIGIBLE PUBLIC SCHOOL, PUBLIC SCHOOL DISTRICT OR ELIGIBLE ORGANIZATION  (SEE SAM SECTION 3520.5)' => 'v0Lk5047Y',
            'SHIP TO PROPERTY REUSE PROGRAM (NO POOR OR JUNK MATERIAL)' => 'GgGdDSrGF',
            'DONATION OF COMPUTERS FOR SCHOOLS PROGRAM' => 'wbwDDzEkI',
            'SALE (SEE SAM SECTION 3520)' => 'WWQDBaKR0',
            'TRADE-IN (SHOW TRADE-IN PRICE OFFERED)' => 'faprYWvqs'
    };
    $json_form['data'][] = [
        "data" => [
            "LZEp-popmB"=> (string)$data['reason'],
            "gFNvCD0-pH" => [
                "id"=> $disposition_id,
                "label"=> $data['code']
            ],
            "yks38VOkzw"=> (string)$data['tag'],
            "SBu1DONXk2"=> (string)$dept_name . ' (' . $data['Found Building Name'] . ')',
            "gNBhgBRLK0"=> (string)$data['Descr'] . ' - ' . ($vin ? 'VIN: ' . (string)$data['VIN'] ?? '' : 'SN: ' . (string)$data['Serial ID'] ?? ''),
        ], 
        'id'=>(string)$index,
    ];
}
// ADD REST OF UNITS
$bkcmp = $bkspa = $bkstu = $bkasi = $bkfdn = [];
if ($cmp) {
    $bkcmp =
        [
            "id" => "-tjpXbOsL",
            "label" => "BKCMP"
        ];
} 
if ($bkspa) {
    $bkspa =
        [
            "id" => "fRgMXRs7y",
            "label" => "BKSPA"
        ];
}
if ($bkstu) {
    $bkstu =  [
        "id" => "AV0oL2gsT",
        "label" => "BKSTU"
    ];
}
if ($bkfdn) {
    $bkfdn = [
        "id" => "VUhtYqoWm",
        "label" => "BKFDN"
    ];
}
if ($bkasi) {
    $bkasi =[
        "id" => "T6Rxq51PT",
        "label" => "BKASI"
    ];
}
$bus_units['04lKcQ1Iy2'] = [$bkcmp, $bkspa, $bkstu, $bkfdn, $bkasi];
$bus_units['04lKcQ1Iy2'] = array_filter($bus_units['04lKcQ1Iy2'], fn($info) => (!empty($info['label']) && !empty($info['id'])));

$form_type_id = match ($its) {
    true => "iK43J2G3IH",
    false => "2DyVz03Xr"
};
$form_type_label = ($its === true) ? 'IT Equipment' : 'Other';

$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
$now = new DateTime();
$now->format('Y-m-d H:i:s');

$ms_time = round(microtime(true) * 1000);
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        $bus_units,
        "COwZg-7nwQ" => [
            "id" => $form_type_id,
            "label" => $form_type_label
        ],
        $manager_kuali,
        "W_Uw0hSpff"=> $json_form,
        $custodian_kuali,
        "tc1F0ohejI"=> [
            "data"=> [
                "AkMeIWWhoj" => $dept_name,
                "IOw4-l7NsM"=> $_SESSION['info'][2]
            ],
            "label"=> $dept_name
        ]
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
$resp_data = json_decode($resp, true);

echo json_encode(['status' => 'success']);
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
                /*
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
 */
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
