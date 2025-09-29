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
/* DATA FROM COMPLETE AUDIT */
$tag = $data['tag'];
$select_tag = "SELECT asset_name, serial_num, type2 FROM asset_info WHERE asset_tag = :tag";
$select_stmt = $dbh->prepare($select_tag);
$select_stmt->execute([":tag"=>$tag]);
$asset_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$descr = $asset_info['asset_name'];
$serial_num = $asset_info['serial_num'];
if (!empty($_SESSION['data'])) {
    foreach ($_SESSION['data'] as $data) {
        if ($data['Tag Number'] === $tag) {
            $descr = $data['Descr'];
            $serial_num = $data['Serial ID'] ?? 'N/A';
            break;
        }
    }
}

$form_type = $data['form_type'];
$check_type = $data['check_type'];
$borrower = $data['borrower'];
$condition = $data['condition'];
$notes = $data['notes'];
$email = $_SESSION['email'];
$select_tag = "SELECT asset_name, serial_num, type2 FROM asset_info WHERE asset_tag = :tag";
$select_stmt = $dbh->prepare($select_tag);
$select_stmt->execute([":tag"=>$tag]);
$condition_id = match ($condition) {
"New" => "PMMV9ld3ML",
    "Good" => "uPq0cgV51",
    "Used" => "2zmA7sZQnX",
    "Damaged" => ""
};
$condition_combined = [
    "id"=>$condition_id,
    "label"=>$condition
];

$asset_type_id = match ($asset_type) {
"Laptop" => "VMjSpx4-H",
    "Desktop" => "UHFK_j1G7L",
    "Tablet" => "",
    "Other" => ""
};
$asset_type_combined = [
    "id"=>$asset_type_id,
    "label" => $asset_type
];

$form_type_id = match ($form_type) {
"Myself" => "fK-8m6dzx",
    "Someone Else" => "y89ptC2TA"
};
$form_type_combined = [
    "id" => $form_type_id,
    "label" => $form_type
];

$check_type = match ($check_type) {
    "check-in" => "Returning Equipment",
    "check-out" => "Checking Out Equipment"
};

$check_type_id = match ($check_type) {
"Returning Equipment" => "",
    "Checking Out Equipment" => "Nwnp1xzbH"
};


$get_cust_info = "select manager, unnest(custodian) as cust from user_table where dept_id = :dept";
try {
    $get_cust_stmt = $dbh->prepare($get_cust_info);
    $get_cust_stmt->execute([":dept"=>$dept_id]);
    $dept_info = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // CUST DID NOT MATCH
    searchName($custodians[0]['cust']);
    $get_cust_stmt = $dbh->prepare($get_cust_info);
    $get_cust_stmt->execute([":full_name" => $custodians[0]['cust']]);
    $cust_info = $get_cust_stmt->fetch(PDO::FETCH_ASSOC);
    // SEARCH CUST IN KUALI
}
$ms_time = round(microtime(true) * 1000);
if ($check_type === "Checking Out Equipment") {
    $check_type_date["-StvOCXWsX"] = $ms_time;

    $manager_info = "SELECT email, form_id, signature, f_name, l_name, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) as full_name = :full_name";
    $manager_stmt = $dbh->prepare($manager_info);
    $manager_stmt->execute([':full_name'=>$dept_info[0]['manager']]);
    $manager_info = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($manager_info['school_id']) || empty($manager_info['form_id'])) {
        searchName($dept_info[0]['manager']);
        $manager_info = "SELECT form_id, signature, f_name, l_name, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) as full_name = :full_name";
        $manager_stmt = $dbh->prepare($manager_info);
        $manager_stmt->execute([':full_name'=>$dept_info[0]['manager']]);
        $manager_info = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $authority_person['NdN80WJusb'] = [
        'displayName'=>$dept_info[0]['manager'],
        'email' => $manager_info['email'],
        'firstName' => $manager_info['f_name'],
        'id' => $manager_info['form_id'],
        'label' => $manager_info['f_name'] . ' ' . $manager_info['l_name'],
        'lastName' => $manager_info['l_name'],
        'schoolId' => $manager_info['school_id'],
        'username' => $manager_info['username']
    ];
} else {
    $check_type_date["73dNIwQS0c"] = $ms_time;
    $cust_info = "SELECT email, form_id, signature, f_name, l_name, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) as full_name = :full_name";
    $cust_stmt = $dbh->prepare($cust_info);
    $cust_stmt->execute([':full_name'=>$dept_info[0]['cust']]);
    $cust_info = $cust_stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($cust_info['school_id']) || empty($cust_info['form_id'])) {
        searchName($dept_info[0]['cust']);
        $cust_info = "SELECT form_id, signature, f_name, l_name, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) as full_name = :full_name";
        $cust_stmt = $dbh->prepare($cust_info);
        $cust_stmt->execute([':full_name'=>$dept_info[0]['manager']]);
        $cust_info = $cust_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $authority_person['_fBI_Ezliu'] = [
        'displayName'=>$dept_info[0]['cust'],
        'email' => $cust_info['email'],
        'firstName' => $cust_info['f_name'],
        'id' => $cust_info['form_id'],
        'label' => $cust_info['f_name'] . ' ' . $manager_info['l_name'],
        'lastName' => $cust_info['l_name'],
        'schoolId' => $cust_info['school_id'],
        'username' => $cust_info['username']
    ];
}

$check_type_combined = [
    "id"=>$check_type_id,
    "label"=>$check_type 
];

$dept_id = $_SESSION['info'][2];
$get_dept_custodians = "SELECT dept_id, dept_name, unnest(custodian) as cust FROM department d WHERE dept_id = :dept_id";
$get_cust_stmt = $dbh->prepare($get_dept_custodians);
$get_cust_stmt->execute([":dept_id"=>$dept_id]);
$custodians = $get_cust_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $custodians[0]['dept_name'];
$custodian = $custodians[0]['cust'];

$dept_part = [
    "data" => [
        "AkMeIWWhoj" => $dept_name,
        "IOw4-l7NsM" => $dept_id
    ],
    "label" => $dept_name
];

$subdomain = "csub";
// SUBMITTER INFO
$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $_SESSION['email']]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
$apikey = $submitter_info['kuali_key'] ?? '';
if (empty($apikey)) {
    die("API Key Not Found");
}
$display_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'] . ' (' . $_SESSION['email'] .')';
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
if (empty($school_id) || empty($form_id)) {
    searchName($full_name);
    $select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
    $full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
    $display_name = $full_name . ' ' . '('.$_SESSION['email'].')';
    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}
$now = new DateTime();
$now->format('Y-m-d H:i:s');

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
"appId": "68bf09aaadec5e027fe35187"
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
if ($form_type === 'Someone Else') {
    $get_borrower_info = "SELECT form_id, username, f_name, l_name, school_id FROM user_table WHERE CONCAT(f_name, ' ' , l_name) = :full_name";
    $borrower_stmt = $dbh->prepare($get_borrower_info);
    $borrower_stmt->execute([":full_name"=>$borrower]);
    $borrower_info = $borrower_stmt->fetch(PDO::FETCH_ASSOC);
    $borrower_graph_info['J06VDujK2F'] = [
        "displayName"=> $borrower_info['f_name'] . ' ' . $borrower_info['l_name'],
        "email"=> $borrower_info['email'],
        "firstName"=> $borrower_info['f_name'],
        "id"=> $borrower_info['form_id'],
        "label"=> $borrower_info['f_name'] . ' ' . $borrower_info['l_name'],
        "lastName"=> $borrower_info['l_name'],
        "schoolId"=> $borrower_info['school_id'],
        "username"=> $borrower_info['username']
    ];
} else {
    $submitter_signature_data = [
        "actionId"=> $action_id,
        "date"=> $now,
        "displayName"=> $display_name,
        "signatureType"=> "type",
        "signedName"=> $signature,
        "userId"=> $form_id
    ];
}
$custs = [
    "Gf5oXuQkTBy"=> $cust_1
];
$reason = "Updating Department inventory after conducting " . $_SESSION['info'][4] . " " . $_SESSION['info'][3] . " audit.";
if ($check_type === "Checking Out Equipment" && $form_type === "Someone Else") { // DONE
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        // NOTE
        "0LZvRo9vT5"=> $note,
        // TAG
        "BOZIA6hewQ" => $tag,
        // BORROWER SOMEONE ELSE
        $borrower_graph_info,
        // MS TIME STAMP
        $check_type_date,
        // MYSELF / SOMEONE ELSE
        "e0fZiLYomu" => $form_type_combined,
        // RETURNING / CHECK OUT
        "fyaCF8g3Uh" => $check_type_combined,
        "XE0n2IZXBC"=> "Bakersfield",
        "Smva-ICjnV" => "9001 Stockdale Hwy. ",
        // DESCRIPTION
        "cQOz4UQ0rQ"=> $descr,
        "jYTHHgL10M" => $serial_num,
        "isFMbCuv8e" => $dept_part,
        // CONDITION
        "UTQZbrKiio" => $condition_combined,
        // LAPTOP, TABLET, etc
        "aUVT1BLN6V" => $asset_type_combined,
        // MANAGER OR CUST
        $authority_person,
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
    ]);
} else if ($check_type === "Checking Out Equipment" && $form_type === "Someone Else") { // DONE
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        // NOTE
        "0LZvRo9vT5"=> $note,
        // TAG
        "BOZIA6hewQ" => $tag,
        // MS TIME STAMP
        $check_type_date,
        // BORROWER SOMEONE ELSE
        $borrower_graph_info,
        // MYSELF / SOMEONE ELSE
        "e0fZiLYomu" => $form_type_combined,
        // RETURNING / CHECK OUT
        "fyaCF8g3Uh" => $check_type_combined,
        "XE0n2IZXBC"=> "Bakersfield",
        "Smva-ICjnV" => "9001 Stockdale Hwy. ",
        // DESCRIPTION
        "cQOz4UQ0rQ"=> $descr,
        "jYTHHgL10M" => $serial_num,
        "isFMbCuv8e" => $dept_part,
        // CONDITION
        "UTQZbrKiio" => $condition_combined,
        // LAPTOP, TABLET, etc
        "aUVT1BLN6V" => $asset_type_combined,
        // MANAGER OR CUST
        $authority_person,
    ],
    'actionId' => $action_id,
    'status' => 'completed'
]
    ]);
} else { // IN MYSELF
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        // NOTE
        "0LZvRo9vT5"=> $note,
        // TAG
        "BOZIA6hewQ" => $tag,
        // BORROWER SOMEONE ELSE
        $borrower_graph_info,
        // MS TIME STAMP
        $check_type_date,
        // MYSELF / SOMEONE ELSE
        "e0fZiLYomu" => $form_type_combined,
        // RETURNING / CHECK OUT
        "fyaCF8g3Uh" => $check_type_combined,
        "XE0n2IZXBC"=> "Bakersfield",
        "Smva-ICjnV" => "9001 Stockdale Hwy. ",
        // DESCRIPTION
        "cQOz4UQ0rQ"=> $descr,
        "jYTHHgL10M" => $serial_num,
        "isFMbCuv8e" => $dept_part,
        // FOR MYSELF
        "JXLJ_AOov-" => $submitter_signature_data,
        // CONDITION
        "UTQZbrKiio" => $condition_combined,
        // LAPTOP, TABLET, etc
        "aUVT1BLN6V" => $asset_type_combined,
        // MANAGER OR CUST
        $authority_person,
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
echo json_encode([$ms_time
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
]);
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
