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
/* $data = [
 *      [0 => '1234']
 *      ];
 */
$data = json_decode($encoded_data, true);
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
// SUBMITTER INFO
$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $email]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $submitter_info['kuali_key'] ?? '';
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? '';
$form_id = $submitter_info['form_id'] ?? '';


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
        $get_cust_stmt->execute([":full_name"=>$custodians[0]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
            // SEARCH CUST IN KUALI
            searchName($custodian[0]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // CUST DID NOT MATCH
        searchName($custodian[0]['cust']);
        $get_cust_stmt = $dbh->prepare($get_cust_info);
        $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
        $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
        // SEARCH CUST IN KUALI
    }
    $cust_1 = [
        "displayName" => $custodians[0]['cust'],
        "email" => $cust_info['email'],
        "firstName"=> $cust_name_split[0],
        "id"=> $cust_info['form_id'],
        "label"=> $custodians[0]['cust'],
        "lastName"=> $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' . $cust_name_split[3] ?? '',
        "schoolid"=> $cust_info['school_id'],
        "username"=> $cust_info['username']
    ];
case 2:
    if ($cust_count >= 2) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode($custodians[1]['cust'], " ");
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[1]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodian[1]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[1]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodian[1]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[1]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            // SEARCH CUST IN KUALI
        }
        $cust_2 = [
            "displayName" => $custodians[1]['cust'],
            "email" => $cust_info['email'],
            "firstName"=> $cust_name_split[0],
            "id"=> $cust_info['form_id'],
            "label"=> $custodians[1]['cust'],
            "lastName"=> $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' . $cust_name_split[3] ?? '',
            "schoolId"=> $cust_info['school_id'],
            "username"=> $cust_info['username']
        ];
    }
case 3:
    if ($cust_count >= 3) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode($custodians[2]['cust'], " ");
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[2]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodian[2]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[2]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodian[2]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[2]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            // SEARCH CUST IN KUALI
        }
        $cust_3 = [
            "displayName" => $custodians[2]['cust'],
            "email" => $cust_info['email'],
            "firstName"=> $cust_name_split[0],
            "id"=> $cust_info['form_id'],
            "label"=> $custodians[2]['cust'],
            "lastName"=> $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' . $cust_name_split[3] ?? '',
            "schoolId"=> $cust_info['school_id'],
            "username"=> $cust_info['username']
        ];
    }
case 4:
    if ($cust_count >= 4) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode($custodians[3]['cust'], " ");
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[3]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodian[3]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[3]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);

            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodian[3]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[3]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            // SEARCH CUST IN KUALI
        }
        $cust_4 = [
            "displayName" => $custodians[3]['cust'],
            "email" => $cust_info['email'],
            "firstName"=> $cust_name_split[0],
            "id"=> $cust_info['form_id'],
            "label"=> $custodians[3]['cust'],
            "lastName"=> $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' . $cust_name_split[3] ?? '',
            "schoolId"=> $cust_info['school_id'],
            "username"=> $cust_info['username']
        ];
    }
case 5:
    if ($cust_count >= 5) {
        $get_cust_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
        $cust_name_split = explode($custodians[4]['cust'], " ");
        try {
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name"=>$custodians[4]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($cust_info['form_id']) || empty($cust_info['school_id'])) {
                // SEARCH CUST IN KUALI
                searchName($custodian[4]['cust']);
                $get_cust_stmt = $dbh->prepare($get_cust_info);
                $get_cust_stmt->execute([":full_name" => $custodians[4]['cust']]);
                $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // CUST DID NOT MATCH
            searchName($custodian[4]['cust']);
            $get_cust_stmt = $dbh->prepare($get_cust_info);
            $get_cust_stmt->execute([":full_name" => $custodians[4]['cust']]);
            $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
            // SEARCH CUST IN KUALI
        }
        $cust_5 = [
            "displayName" => $custodians[4]['cust'],
            "email" => $cust_info['email'],
            "firstName"=> $cust_name_split[0],
            "id"=> $cust_info['form_id'],
            "label"=> $custodians[4]['cust'],
            "lastName"=> $cust_name_split[1] . ' ' . $cust_name_split[2] ?? '' . ' ' . $cust_name_split[3] ?? '',
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
    if (!empty($data['VIN'])) $vin = true;
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
            ];
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
            ];
    }
}
$reason = "Updating Department inventory after conducting " . $dept_id . " audit.";
$now = new DateTime();
$now->format('Y-m-d H:i:s');

$ms_time = round(microtime(true) * 1000);

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
        "Gf5oXuQkTBy"=> [ $cust_1 ],
        "JZ-q3J19dw"=> ["data"=> $json_form],
        "K3p03X2Jvx"=> $reason,
        "ne3KPx1Wy3"=> [
            "actionId"=> $action_id,
            "date"=> $now,
            "displayName"=> $full_name . " (" . $_SESSION['email'] . ")",
            "signatureType"=> "type",
            "signedName"=> $full_name,
            "userId"=> $form_id
        ],
        "K3p03X2Jvx"=> "Updating Department inventory after conducting D23026 audit. ",
        "R-jIGrtlfO"=> $ms_time,
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
function searchName($search_name = '', $key = '')
{
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
    json_encode([
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
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    curl_close($curl);
    var_dump($resp);
    $name_data = json_decode($resp, true);
    $name_edges = $name_data['data']['usersConnection']['edges'];
    foreach ($name_edges as $info) {
        $id = $info['node']['id'];
        $display_name = $info['node']['displayName'];
        $email = $info['node']['email'];
        $username = $info['node']['username'];
        $f_name = $info['node']['firstName'];
        $l_name = $info['node']['lastName'];
        $schoolid = $info['node']['schoolid'];
        // CHECK DB
        $select = "SELECT * from user_table WHERE email = :email";
        $select_stmt = $dbh->prepare($select);
        $select_stmt->execute([":email"=>$email]);
        if ($select_stmt->rowCount() <= 0) {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);

            $insert = "INSERT INTO user_table (form_id, username, email, f_name, l_name, school_id, u_role, pw) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $dbh->prepare($insert);
            $insert_stmt->execute([$id, $username, $email, $f_name, $l_name, $schoolid, 'User', $hashed_pw]);
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
            $update = "UPDATE user_info SET ";
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
            $update_stmt = $dbh->prepare($update);
            $update_stmt->execute($params);
        }
    }
}
