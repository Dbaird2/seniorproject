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
echo json_encode(['tags' => $data]);
$its = false;
$cmp = $spa = $stu = $fdn = $asi = false;
foreach ($data['psr_tags'] as $index => $tag) {
    foreach ($_SESSION['data'] as $session) {
        if ($session['Tag Number'] === $tag['tag']) {
            $select = "SELECT type2 FROM asset_info WHERE asset_tag = :tag";
            $stmt = $dbh->prepare($select);
            $stmt->execute([":tag" => $tag['tag']]);
            $type = $stmt->fetchColumn();
            if (in_array($type, ['Laptop', 'Tablet', 'Desktop'])) {
                $its = true;
            }
            if ($session['Unit'] === 'BKCMP') {
                $cmp = true;
            } else if ($session['Unit'] === 'BKSPA') {
                $spa = true;
            } else if ($session['Unit'] === 'BKASI') {
                $asi = true;
            } else if ($session['Unit'] === 'BKSTU') {
                $stu = true;
            } else if ($session['Unit'] === 'BKFDN') {
                $fdn = true;
            }
            $transfer_data[$index]['Tag Number'] = $tag['tag'];
            $transfer_data[$index]['Descr'] = $session['Descr'];
            $transfer_data[$index]['Serial ID'] = $session['Serial ID'];
            if (!empty($session['VIN'])) {
                $transfer_data[$index]['VIN'] = $session['VIN'];
            }
            $transfer_data[$index]['code'] = $tag['code'];
            $transfer_data[$index]['reason'] = $tag['reason'];
            break;
        }
    }
}
$dept_id = $_SESSION['info'][2];

$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $email]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
if (empty($submitter_info)) {
    searchName($_SESSION[$submitter_info['f_name'] . ' ' . $submitter_info['l_name']);
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $email]);
    $submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
}
$apikey = $submitter_info['kuali_key'] ?? '';
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
$first_name = $submitter_info['f_name'];
$last_name = $submitter_info['l_name'];

$submitter_key = 'Jpy9KU-X3P';
$submitter_kauli = [
    "displayName"=> $full_name ,
    "email" => $_SESSION['email'],
    "firstName" => $first_name,
    "id" => $form_id,
    "label" => $full_name,
    "lastName" => $last_name,
    "schoolId" => $school_id,
    "username" => $display_name
];



$subdomain = "csub";
/* CUSTODIAN INFO */
$get_custodian = "SELECT unnest(custodian) FROM department WHERE dept_id = :dept";
$get_cust_stmt = $dbh->prepare($get_custodian);
$get_cust_stmt->execute([":dept" => $_SESSION['info'][2]]);
$dept_custodian = $get_cust_stmt->fetchColumn();

$select = "SELECT email, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE CONCAT(f_name, ' ', l_name) = :fullname";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":fullname" => $dept_custodian]);
if ($select_stmt->rowCount() <= 0) {
    searchName($dept_custodian);
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":fullname" => $dept_custodian]);
}
if (empty($school_id) || empty($form_id)) {
    searchName($full_name);
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
}
$custodian_info = $select_stmt->fetch(PDO::FETCH_ASSOC);

$cust_display_name = $custodian_info['username'];
$cust_full_name = $custodian_info['f_name'] . ' ' . $custodian_info['l_name'];
$cust_first_name = $custodian_info['f_name'];
$cust_last_name = $custodian_info['l_name'];
$cust_school_id = $custodian_info['school_id'];
$cust_signature = $custodian_info['signature'] ?? $cust_full_name;
$cust_form_id = $custodian_info['form_id'];
$cust_email = $custodian_info['email'];
$custodian_kuali_key = 'ryhlM_VqBn';
$custodian_kuali = [
    "displayName" => $cust_full_name,
    "email" => $cust_email,
    "firstName" => $cust_first_name,
    "id" => $cust_form_id,
    "label" => $cust_full_name,
    "lastName" => $cust_last_name,
    "schoolId" => $cust_school_id,
    "username" => $cust_display_name
];
/*----------------------------------------- CUSTODIAN INFO
 * ------------------------------------------ */

/* MANAGER INFO */
$get_dept_manager = "SELECT dept_id, dept_name, dept_manager FROM department WHERE dept_id = :dept_id";
$get_mana_stmt = $dbh->prepare($get_dept_manager);
$get_mana_stmt->execute([":dept_id" => $dept_id]);
if ($get_mana_stmt->rowCount() <= 0) {
    die("Department not found.");
}
$manager_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
$dept_name = $manager_info['dept_name'];
$manager = $manager_info['dept_manager'];

$get_mana_info = "SELECT l_name, f_name, email, form_id, school_id, username FROM user_table WHERE CONCAT(f_name, ' ', l_name) = :full_name";
try {
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name" => $manager]);
    if ($get_mana_stmt->rowCount() <= 0) {
        searchName($manager);
        $get_mana_stmt = $dbh->prepare($get_mana_info);
        $get_mana_stmt->execute([":full_name" => $manager]);
        $mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (empty($manager_info['form_id']) || empty($manager_info['school_id'])) {
        /* SEARCH CUST IN KUALI */
        searchName($manager);
        $get_mana_stmt = $dbh->prepare($get_mana_info);
        $get_mana_stmt->execute([":full_name" => $manager]);
    }
} catch (PDOException $e) {
    /* CUST DID NOT MATCH EXACTLY */
    searchName($manager);
    $get_mana_stmt = $dbh->prepare($get_mana_info);
    $get_mana_stmt->execute([":full_name" => $manager]);
    /* SEARCH CUST IN KUALI */
}
$mana_info = $get_mana_stmt->fetch(PDO::FETCH_ASSOC);
$manager_kuali_key = 'Jpy9KU-X3P';
$manager_kuali = [
    "displayName" => $manager,
    "email" => $mana_info['email'],
    "firstName" => $mana_info['f_name'],
    "id" => $mana_info['form_id'],
    "label" => $manager,
    "lastName" => $mana_info['l_name'],
    "schoolId" => $mana_info['school_id'],
    "username" => $mana_info['username']
];
/*----------------------------------------- MANAGER INFO
 * ------------------------------------------ */

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
            "LZEp-popmB" => (string)$data['reason'],
            "gFNvCD0-pH" => [
                "id" => $disposition_id,
                "label" => $data['code']
            ],
            "gNBhgBRLK0" => (string)$data['Descr'] . ' - ' . ($vin ? 'VIN: ' . (string)$data['VIN'] ?? '' : 'SN: ' . (string)$data['Serial ID'] ?? ''),
            "yks38VOkzw" => (string)$data['Tag Number'],
        ],
        'id' => (string)$index,
    ];
}
/* ADD REST OF UNITS */
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
    $bkasi = [
        "id" => "T6Rxq51PT",
        "label" => "BKASI"
    ];
}
$bus_units_key = '04lKcQ1Iy2';
$bus_units = [$bkcmp, $bkspa, $bkstu, $bkfdn, $bkasi];
$bus_units = array_filter($bus_units, fn($info) => (!empty($info['label']) && !empty($info['id'])));
if (!$vin) {
    $form_type_id = match ($its) {
    true => "iK43J2G3IH",
        false => "2DyVz03Xr"
};
$form_type_label = ($its === true) ? 'IT Equipment' : 'Other';
} else {
    $form_type_id = "p4UJVwrfG";
    $form_type_label = 'Vehicle';
}

$now_array = new DateTime();
$now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

$ms_time = round(microtime(true) * 1000);
$submit_form = json_encode([
    'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        $custodian_kuali_key => $custodian_kuali,
        $bus_units_key => $bus_units,
        "COwZg-7nwQ" => [
            "id" => $form_type_id,
            "label" => $form_type_label
        ],
        "tc1F0ohejI" => [
            "data" => [
                "AkMeIWWhoj" => $dept_name,
                "IOw4-l7NsM" => $_SESSION['info'][2]
            ],
            "label" => $dept_name
        ],
        $manager_kuali_key => $manager_kuali,
        "W_Uw0hSpff" => $json_form,
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

echo json_encode(['status' => $submit_form, 'response' => $resp_data]);
exit;
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
    global $dbh;
    $name_array = explode(' ', $search_name);
    $user_f_name = $name_array[0];
    $user_l_name = $name_array[1];
    if (!empty($name_array[2])) {
        $user_l_name .= ' ' . $name_array[2];
    }
    if (!empty($name_array[3])) {
        $user_l_name .=  ' ' . $name_array[3];
    }
    if (!empty($name_array[4])) {
        $user_l_name .=  ' ' . $name_array[4];
    }
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

        /* for debug only! */
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
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
            if (strtolower(trim($f_name)) !== strtolower(trim($user_f_name)) || strtolower(trim($l_name)) !== strtolower(trim($user_l_name))) {
                continue;
            }
            $select = "SELECT * from user_table WHERE email = :email";
            $select_stmt = $dbh->prepare($select);
            $select_stmt->execute([":email" => $email]);
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
                $params = [":email" => $email];
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

