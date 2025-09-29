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
$select = "SELECT bus_unit, asset_name, dept_id, serial_num, asset_price, asset_type, make, type2, asset_model FROM asset_info WHERE asset_tag = :tag";
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":tag"=>$data['tag']]);
$session = $select_stmt->fetch(PDO::FETCH_ASSOC);
$lsd_data['Unit'] = $session['bus_unit'];
$lsd_data['Tag Number'] = $data['tag'];
$lsd_data['Descr'] = $session['asset_name'];
$lsd_data['Serial ID'] = $session['serial_num'];
if (!empty($tag_info['make'])) {
    $lsd_data['Make'] = $tag_info['make'];
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
if ($tag['who'] === 'Myself') {
    $myself = true;
} else if ($tag['who'] === 'someone-else' && !empty($tag['borrower'])) {
    $someone_else = true;
}

if ($data['from_page'] !== 'search') {
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


$get_dept_manager = "SELECT dept_id, dept_name, dept_manager as cust FROM department d WHERE dept_id = :dept_id";
$get_mana_stmt = $dbh->prepare($get_dept_manager);
$get_mana_stmt->execute([":dept_id"=>$dept_id]);
$dept_info = $get_mana_stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_name = $dept_info['dept_name'];
$manager = $dept_info['manager'];

$get_mana_info = "select email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
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
$get_mana_info = "select l_name, f_name, email, form_id, school_id, username from user_table where CONCAT(f_name, ' ', l_name) = :full_name";
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
$now = new DateTime();
$now->format('Y-m-d H:i:s');

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

$ms_time = round(microtime(true) * 1000);
if ($lsd_data['who'] === 'Myself') {
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
    'variables' => [
        'documentId' => $document_id,
        'data' => [
            // WHO
            "Sg2RTLnC5r"=> [
                "id"=> "w-25nbYAp",
                "label"=> "Myself"
            ],
            // MANAGER IF STAFF
            "0Qm43mG2vV" => [
                "displayName" => $manager,
                "email" => $mana_email,
                "firstName" => $mana_f_name,
                "id" => $mana_form_id,
                "label" => $manager,
                "lastName" => $mana_l_name,
                "schoolId" => $mana_form_sid,
                "username" => $mana_form_user
            ],
            // ITEM TYPE (IT EQUIP, INSTRUCTIONAL, OTHER)
            "6lJyeq9g1v" => [
                "id" => $item_type_id,
                "label"=> $lsd_data['item_type']
            ],
            // REPORTED TO UPD?
            "7BHQb4jTbS" => [
                "id" => $upd_id,
                "label" => $lsd_data['upd']
            ],
            // SERIAL NUMBER
            "7Gzhcg_35S" => $lsd_data['Serial ID'],
            "9eJvzLeMS0" => [
                "id" =>"9JrVQuqdIQS",
                "label"=> "Staff / Faculty"
            ],
            // SUBMITTER SIGNATURE
            "EeUWxyyaOUR" => [
                "actionId"=> $action_id,
                "date" => $now,
                "displayName" => $full_name . " (".$_SESSION['email'].")",
                "signatureType"=> "type",
                "signedName"=> $full_name,
                "userId" => $form_id
            ],
            // DEPT IF STAFF

            "GOiwf3tjc0"=> [
                "data"=> [
                    "AkMeIWWhoj"=> $dept_name,
                    "IOw4-l7NsM"=> $_SESSION['info'][2]
                ],
                "label" => $dept_name
            ],
            // MAKE
            "Qb1ac69GLa" => $lsd_data['Make'] ?? 'N/A',
            // LSD
            "Sc5_swYeHS"=> [
                "id"=> $lsd_id,
                "label"=> $lsd_data['lsd']
            ],
            // NARRATIVE
            "dyaoRcFcOD" => $lsd_data['reason'],
            // DESCR
            "pNvpNnuav8" => $lsd_data['Descr'],
            // TAG
            "y7nFCmsLEg" => $lsd_data['Tag Number'],
            // MODEL
            "y9obJL9NAo" => $lsd_data['Model'] ?? 'N/A'
        ],
        'actionId' => $action_id,
        'status' => 'completed'
    ]
    ]);
} else if ($lsd_data['who'] === 'someone-else') {
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
{ submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
'variables' => [
    'documentId' => $document_id,
    'data' => [
        "Sg2RTLnC5r" => [
            "id" => "SDqr0xnNfnM",
            "label" => "I am initiating this submission on behalf of"
        ],
        // MANAGER IF STAFF
        "0Qm43mG2vV" => [
            "displayName" => $manager,
            "email" => $mana_email,
            "firstName" => $mana_f_name,
            "id" => $mana_form_id,
            "label" => $manager,
            "lastName" => $mana_l_name,
            "schoolId" => $mana_form_sid,
            "username" => $mana_form_user
        ],
        // ITEM TYPE (IT EQUIP, INSTRUCTIONAL, OTHER)
        "6lJyeq9g1v" => [
            "id" => $item_type_id,
            "label"=> $lsd_data['item_type']
        ],
        // REPORTED TO UPD?
        "7BHQb4jTbS" => [
            "id" => $upd_id,
            "label" => $lsd_data['upd']
        ],
        // SERIAL NUMBER
        "7Gzhcg_35S" => $lsd_data['Serial ID'],
        "9eJvzLeMS0" => [
            "id" =>"9JrVQuqdIQS",
            "label"=> "Staff / Faculty"
        ],
        // SUBMITTER SIGNATURE
        "EeUWxyyaOUR" => [
            "actionId"=> $action_id,
            "date" => $now,
            "displayName" => $full_name . " (".$_SESSION['email'].")",
            "signatureType"=> "type",
            "signedName"=> $full_name,
            "userId" => $form_id
        ],
        // DEPT IF STAFF

        "GOiwf3tjc0"=> [
            "data"=> [
                "AkMeIWWhoj"=> $dept_name,
                "IOw4-l7NsM"=> $_SESSION['info'][2]
            ],
            "label" => $dept_name
        ],
        // MAKE
        "Qb1ac69GLa" => $lsd_data['Make'] ?? 'N/A',
        // LSD
        "Sc5_swYeHS"=> [
            "id"=> $lsd_id,
            "label"=> $lsd_data['lsd']
        ],
        "N00EmVKFnd" => [
            "displayName" => $lsd_data['borrower'],
            "email" => $bor_email,
            "firstName" => $bor_f_name,
            "id" => $bor_form_id,
            "label" => $lsd_data['borrower'],
            "lastName" => $bor_l_name,
            "schoolId" => $bor_form_sid,
            "username" => $bor_form_user
        ],
        // NARRATIVE
        "dyaoRcFcOD" => $lsd_data['reason'],
        // DESCR
        "pNvpNnuav8" => $lsd_data['Descr'],
        // TAG
        "y7nFCmsLEg" => $lsd_data['Tag Number'],
        // MODEL
        "y9obJL9NAo" => $lsd_data['Model'] ?? 'N/A'
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
$id = $resp_data['data']['app']['documentConnection']['edges'][0]['node']['id'];
$tag = $lsd_data['Tag Number'];
$doc_id = '68c73600df46a3027d2bd386';
$input_array = $tag. ',' . $id . ',' . $doc_id . ',in-progress';

$dept = $data['dept_id'][0];
$audit_id = $data['audit_id'][0];
$update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ':array') WHERE dept_id = :dept AND audit_id = :id";
$update_stmt = $dbh->prepare($update);
$update_stmt->execute([':array'=>$input_array, ":dept"=>$dept, ":id"=>$audit_id]);
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
