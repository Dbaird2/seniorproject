<?php
include_once("../config.php");
require '../vendor/autoload.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT cust_responsibility_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['cust_responsibility_time'] ?? 0;
$highest_time = date('c', $raw_ms / 1000);

$apikey = $result['kuali_key'];

$url = "https://csub.kualibuild.com/app/api/v0/graphql";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "Content-Type: application/json",
    "Authorization: Bearer {$apikey}",
);

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$data = json_encode([
    "query" => 'query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { app(id: $appId) { id name documentConnection( args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID ) { totalCount edges { node { id data meta } } pageInfo { hasNextPage hasPreviousPage skip limit } } }}',
    "variables" => [
        "appId" => "67bf42240472a7027dd17e97",
        "skip" => 0,
        "limit" => 200,
        "sort" => [
            "meta.createdAt"
        ],
        "query" => "",
        "fields" => [
            "type" => "AND",
            "operators" => [
                [
                    "type" => "AND",
                    "operators" => [
                        [
                            "field" => "meta.workflowStatus",
                            "type" => "IS",
                            "value" => "Complete"
                        ],
                        [
                            "field" => "meta.createdAt",
                            "type" => "RANGE",
                            "min" => $highest_time
                        ]
                    ]
                ]
            ]
        ]
    ]
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
$resp2 = json_decode($resp);

$decode_true = json_decode($resp, true);
$edges = $decode_true['data']['app']['documentConnection']['edges'];

$ASI = "/^A[SI]?\d+$/";
$STU = "/^S[RC]?[TU]?\d+$/";
$CMP = "/^\d+/";
$FDN = "/^F[DN]?\d+$/";
$SPA = "/^SP\d+$/";
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

function addInfo($username, $email, $form_id, $school_id, $signature, $full_name, $role)
{
    echo '<br>Add Info<br>';
    echo 'Username ' . $username  . ' Email ' . $email . ' Form Id ' . $form_id . ' School id ' . $school_id . ' Signature ' . $signature . ' Full name ' . $full_name . ' Role ' . $role; 
    global $dbh, $dept_id;
    $select = 'SELECT username, email, form_id, signature, school_id FROM user_table WHERE email = :email';
    $stmt = $dbh->prepare($select);
    $stmt->execute([":email" => $email]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    $name_array = explode(' ', $full_name);
    $f_name = $name_array[0];
    $l_name_info = array_slice($name_array, 1);
    $l_name = implode(' ', $l_name_info);
    if (count($info) > 0) {
        if (empty($info['school_id'] || empty($info['form_id'] || empty($info['signature'])))) {
            $update = 'UPDATE user_table SET school_id = :school, form_id = :form , signature = :sig WHERE email = :email';
            $stmt = $dbh->prepare($update);
            $stmt->execute([':school' => $school_id, ':form' => $form_id, ':sig' => $signature, ':email' => $email]);
        }
        if ($role === 'custodian') {
            $select = 'SELECT dept_id FROM user_table WHERE :user = ANY(custodian)';
            $stmt = $dbh->prepare($select);
            $stmt->execute([':user' => $full_name]);
            $depts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $found = false;
            foreach ($depts as $id) {
                if ($id['dept_id'] === $dept_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $update = 'UPDATE user_table SET custodian = ARRAY_APPEND(custodian, :dept_id) WHERE email = :email';
                $stmt = $dbh->prepare($update);
                $stmt->execute([':dept_id' => $dept_id, ':email' => $email]);
            }
        }
    } else {
        $pw = randomPassword();
        $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
        $insert = 'INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, form_id, school_id, signature) VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $dbh->prepare($insert);
        $stmt->execute([$username, $hashed_pw, $email, $role, $f_name, $l_name, $dept_id, $form_id, $school_id, $signature]);
    }
}

function addDepartment($documentSetId, $dept_kuali_id, $c_display_name, $m_full_name)
{
    echo '<br>Add Department<br>';
    echo 'DocumentId: ' . $documentSetId . ' Kuali id: ' . $dept_kuali_id . ' Cust full name: ' . $c_display_name . ' Manager Full Name ' . $m_full_name;
    global $dbh;
    global $dept_id, $dept_name;
    $select_dept = "SELECT dept_id, dept_manager FROM department WHERE dept_id = :dept_id";
    $dept_stmt = $dbh->prepare($select_dept);
    $dept_stmt->execute([":dept_id" => $dept_id]);
    $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
    if (count($dept_info) > 0) {
        if ($dept_info['dept_manager'] !== $m_full_name) {
            $update_dept = "UPDATE department SET dept_manager = :manager WHERE dept_id = :dept_id";
            $stmt = $dbh->prepare($update_dept);
            $stmt->execute([':manager' => $m_full_name, ':dept_id' => $dept_id]);
        }
        $select_cust = 'SELECT dept_id, form_id, document_set_id FROM department WHERE custodian = ANY(:cust)';
        $stmt = $dbh->prepare($select_cust);
        $stmt->execute([':cust' => $c_display_name]);
        $info = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $found = false;
        foreach ($info as $row) {
            if ($row['dept_id'] === $dept_id) {
                $found = true;
            }
        }
        if (!$found) {
            $update = 'UPDATE department SET custodian = ARRAY_APPEND(custodian, :cust) WHERE dept_id = :id';
            $update_stmt = $dbh->prepare($update);
            $update_stmt->execute([':cust' => $c_display_name, ':id' => $dept_id]);
        }
    } else {
        $insert = 'INSERT INTO department (dept_id, dept_name, custodian, dept_manager, document_set_id, form_id) VALUES (?, ?, ?, ?, ?, ?)';
        $insert_stmt = $dbh->prepare($insert);
        $insert_stmt->execute([$dept_id, $dept_name, $c_display_name, $m_full_name, $documentSetId, $dept_kuali_id]);
    }
}

function addSignature($username, $email, $form_id, $signature, $school_id, $dept_id, $f_name, $l_name, $role = 'user')
{
    global $dbh;
    $select = 'SELECT username, email, form_id, signature, school_id FROM user_table WHERE email = :email';
    $stmt = $dbh->prepare($select);
    $stmt->execute([":email" => $email]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    $full_name = $f_name . ' ' . $l_name;
    echo '<br>Add Signature<br>';
    echo 'Username ' . $username  . ' Email ' . $email . ' Form Id ' . $form_id . ' School id ' . $school_id . ' Signature ' . $signature . ' Full name ' . $full_name . ' Role ' . $role; 
    if ($info) {
        if (empty($info['school_id'] || empty($info['form_id'] || empty($info['signature'])))) {
            $update = 'UPDATE user_table SET school_id = :school, form_id = :form , signature = :sig WHERE email = :email';
            $stmt = $dbh->prepare($update);
            $stmt->execute([':school' => $school_id, ':form' => $form_id, ':sig' => $signature, ':email' => $email]);
        }
        if ($role === 'custodian') {
            $select = 'SELECT dept_id FROM department WHERE :user = ANY(custodian)';
            $stmt = $dbh->prepare($select);
            $stmt->execute([':user' => $full_name]);
            $depts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $found = false;
            foreach ($depts as $id) {
                if ($id['dept_id'] === $dept_id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $update = 'UPDATE user_table SET custodian = ARRAY_APPEND(custodian, :dept_id) WHERE email = :email';
                $stmt = $dbh->prepare($update);
                $stmt->execute([':dept_id' => $dept_id, ':email' => $email]);
            }
        }
    } else {
        $pw = randomPassword();
        $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
        $insert = 'INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, form_id, school_id, signature) VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $dbh->prepare($insert);
        $new_dept_id = '{' . $dept_id . '}';
        $stmt->execute([$username, $hashed_pw, $email, $role, $f_name, $l_name, $new_dept_id, $form_id, $school_id, $signature]);
    }
}

try {
    foreach ($edges as $index => $edge) {
        $dept_id = $edge['node']['data']['XeTTtfl6XW']['data']['IOw4-l7NsM'] ?? $edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['IOw4-l7NsM'];
        $dept_name = $edge['node']['data']['XeTTtfl6XW']['data']['AkMeIWWhoj'] ?? $edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['AkMeIWWhoj'];
        $documentSetId = $edge['node']['data']['XeTTtfl6XW']['documentSetId'] ?? $edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['documentSetId'];
        $dept_kuali_id = $edge['node']['data']['XeTTtfl6XW']['id'] ?? $edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['id'];
        $update_time = $edge['node']['meta']['createdAt'];
        if (isset($edge['node']['data']['XhBe3DNaU4'])) {
            // NEW CUSTODIAN
            $c_display_name = $edge['node']['data']['XhBe3DNaU4']['displayName'];
            $c_full_name = '';
            $custodian_array = explode(' ', $c_display_name);
            $size = count($custodian_array);
            $c_email = trim($custodian_array[$size - 1], "()");
            $c_l_name = '';
            for ($i = 0; $i < $size - 1; $i++) {
                $c_full_name .= ' ' . $custodian_array[$i];
                if ($i !== 0) {
                    $c_l_name .= $custodian_array[$i];
                }
            }
            $c_full_name = trim($c_full_name);
            $email_split = explode('@', $c_email);
            $c_username = $email_split[0];
            $c_id = $edge['node']['data']['XhBe3DNaU4']['userId'];
            $c_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
            $type = $edge['node']['data']['XhBe3DNaU4']['signatureType'];
            $c_signature = $c_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $c_signature = $edge['node']['data']['XhBe3DNaU4']['signedName'];
            } else {
                $c_signature = $c_full_name;
            }
            addSignature($c_username, $c_email, $c_id, $c_signature, $c_school_id, $custodian_array[0], $c_l_name, 'custodian');
        }

        if (isset($edge['node']['data']['04PgxWqAbE'])) {
            // MANAGER/DEAN SIGNATURE
            $m4_display_name = $edge['node']['data']['04PgxWqAbE']['displayName'];
            $m4_full_name = '';
            $m4_array = explode(' ', $m4_display_name);
            $size = count($m4_array);
            $m4_email = trim($m4_array[$size - 1], "()");
            $m4_l_name = '';
            for ($i = 0; $i < $size - 1; $i++) {
                $m4_full_name .= ' ' . $m4_array[$i];
            }
            for ($i = 1; $i < $size - 1; $i++) {
                $m4_l_name .= ' ' . $m4_array[$i];
            }

            $m4_full_name = trim($m4_full_name);
            $m4_f_name = $m4_array[0];
            $m4_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
            $m4_email_array = explode('@', $m4_email);
            $m4_username = $m4_email_array[0];
            $m4_id = $edge['node']['data']['04PgxWqAbE']['userId'];
            $type = $edge['node']['data']['04PgxWqAbE']['signatureType'];
            $m4_signature = $m4_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $m4_signature = $edge['node']['data']['04PgxWqAbE']['signedName'];
            }
            addSignature($m4_username, $m4_email, $m4_id, $m4_signature, $m4_school_id, $m4_f_name, $m4_l_name, 'custodian');
        }
        if (isset($edge['node']['data']['jTxoK_Wsh7'])) {
            // MANAGER/DEAN INFORMATION
            $m2_display_name = $m2_full_name = $edge['node']['data']['jTxoK_Wsh7']['displayName'];
            $m2_email = $edge['node']['data']['jTxoK_Wsh7']['email'];
            $email_array = explode('@', $m2_email);
            $m2_username = $email_array[0];
            $m2_id = $edge['node']['data']['jTxoK_Wsh7']['id'];
            $m2_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
            $m2_signature = $m2_display_name;
            addInfo($m2_username, $m2_email, $m2_id, $m2_school_id, $m2_signature, $m2_display_name, 'custodian');
        }
        if (isset($edge['node']['data']['kS_kp-Oo1y'])) {
            // CUSTODIAN INFORMATION
            $m3_display_name =  $m3_full_name = $edge['node']['data']['kS_kp-Oo1y']['displayName'];
            $m3_email = $edge['node']['data']['kS_kp-Oo1y']['email'];
            $email_array = explode('@', $m3_email);
            $m3_username = $email_array[0];
            $m3_id = $edge['node']['data']['kS_kp-Oo1y']['id'];
            $m3_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
            $m3_signature = $m3_display_name;
            addInfo($m3_username, $m3_email, $m3_id, $m3_school_id, $m3_signature, $m3_display_name, 'custodian');
        }
        addDepartment($documentSetId, $dept_kuali_id, $c_full_name, $m4_full_name);
        $update_q = "UPDATE kuali_table SET cust_responsibility_time = ?";
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([$update_time]);
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

