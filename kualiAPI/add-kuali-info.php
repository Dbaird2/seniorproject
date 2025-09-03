<?php
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
function addInfo($display_name, $full_name, $email, $id, $school_id, $signature, $role = 'custodian', $dept_id = 'D21560')
{
    global $dbh;
    try {
        $select_q = "SELECT * FROM kuali_info WHERE id = ?";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("SELECT kuali_info error: " . $e->getMessage());
    }
    if ($select_stmt->rowCount() < 1) {
        try {
            $insert_q = "INSERT INTO kuali_info (id, display_name, full_name, email, school_id, signature, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $dbh->prepare($insert_q);
            $insert_stmt->execute([$id, $display_name, $full_name, $email, $school_id, $signature, '.{' . $dept_id . '}']);
        } catch (PDOException $e) {
            error_log("INSERT kuali_info error: " . $e->getMessage());
        }
    } else {
        try {
            $update_q = "UPDATE kuali_info SET signature = ?, dept_id = ARRAY_APPEND(dept_id, ?) WHERE id = ?";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([$display_name, $full_name, $email, $school_id, $signature, $dept_id, $id]);
        } catch (PDOException $e) {
            error_log("UPDATE kuali_info error: " . $e->getMessage());
        }
    }
    if ($dept_id !== 'D21560') {
        try {
            $select = "SELECT dept_id, email FROM user_table WHERE email = ?";
            $select_stmt = $dbh->prepare($select);
            $select_stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("SELECT user_table error: " . $e->getMessage());
            return;
        }
        if ($select_stmt->rowCount() > 0) {
            $dept_id = $select_stmt->fetch(PDO::FETCH_ASSOC)['dept_id'];
            // $dept_id = {'D21560','D10101'}
            $dept_id = trim($dept_id, '{}');
            $dept_id_array = explode(',', $dept_id);
            if (in_array($dept_id, $dept_id_array)) {
                // Department ID already exists
                return;
            } else {
                try {
                    $update = "UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?";
                    $update_stmt = $dbh->prepare($update);
                    $update_stmt->execute([$dept_id, $email]);
                } catch (PDOException $e) {
                    error_log("UPDATE user_table error: " . $e->getMessage());
                    return;
                }
            }
        } else {
            $name_array = explode(' ', $full_name);
            $size = count($name_array);
            try {
                $pw = randomPassword();
                $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
                $insert = "INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert);
                $insert_stmt->execute([$name_array[0] . '_' . $name_array[$size - 1], $hashed_pw, $email, 'admin', $name_array[0], $name_array[$size - 1], '{' . $dept_id . '}', $role]);
            } catch (PDOException $e) {
                error_log("INSERT user_table error: " . $e->getMessage());
                return;
            }

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
            Email: ' . $email . '<br>Password: ' . $pw . '<br>If you have any questions, concerns, or issues, feel free to reach out to distribution@csub.edu.</p><br>
        <a href="https://dataworks-7b7x.onrender.com">Dataworks Link</a>';
                $mail->AltBody = 'Click this link to access Dataworks...';

                $mail->send();
            } catch (Exception $e) {
                error_log("Error sending email: " . $e->getMessage());
                return;
            }
        }
    }
}
try {
    foreach ($edges as $index => $edge) {
        $dept_id = $edge['node']['data']['XeTTtfl6XW']['data']['IOw4-l7NsM'] ?? $edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['IOw4-l7NsM'];
        $update_time = $edge['node']['meta']['createdAt'];
        if (isset($edge['node']['data']['XhBe3DNaU4'])) {
            // NEW CUSTODIAN
            $c_display_name = $edge['node']['data']['XhBe3DNaU4']['displayName'];
            $c_full_name = '';
            $custodian_array = explode(' ', $c_display_name);
            $size = count($custodian_array);
            $c_email = trim($custodian_array[$size - 1], "()");
            for ($i = 0; $i < $size - 1; $i++) {
                $c_full_name .= ' ' . $custodian_array[$i];
            }
            $c_full_name = trim($c_full_name);
            $c_id = $edge['node']['data']['XhBe3DNaU4']['userId'];
            $c_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
            $type = $edge['node']['data']['XhBe3DNaU4']['signatureType'];
            $c_signature = $c_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $c_signature = $edge['node']['data']['XhBe3DNaU4']['signedName'];
            }

            addInfo($c_display_name, $c_full_name, $c_email, $c_id, $c_school_id, $c_signature, $role = 'custodian',  $dept_id);
        }
        if (isset($edge['node']['data']['Oe0m5rZUcD'])) {
            // PERSON FILLING OUT FORM
            $m_full_name = '';
            $m_display_name = $edge['node']['data']['Oe0m5rZUcD']['displayName'];
            $manager_array = explode(' ', $m_display_name);
            $size = count($manager_array);
            $m_email = trim($manager_array[$size - 1], "()");
            for ($i = 0; $i < $size - 1; $i++) {
                $m_full_name .= ' ' . $manager_array[$i];
            }
            $m_full_name = trim($m_full_name);
            $m_dept_id = 'D21560';

            $m_id = $edge['node']['data']['Oe0m5rZUcD']['userId'];
            $m_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
            $type = $edge['node']['data']['Oe0m5rZUcD']['signatureType'];
            $m_signature = $m_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $m_signature = $edge['node']['data']['Oe0m5rZUcD']['signedName'];
            }

            addInfo($m_display_name, $m_full_name, $m_email, $m_id, $m_school_id, $m_signature, $role = 'admin', $m_dept_id);
        }
        if (isset($edge['node']['data']['Ut5TV4CKpt'])) {
            // TRAINER
            $d_display_name = $edge['node']['data']['Ut5TV4CKpt']['displayName'];
            $d_full_name = '';
            $distribution_array = explode(' ', $d_display_name);
            $size = count($distribution_array);
            $d_email = trim($distribution_array[$size - 1], "()");
            for ($i = 0; $i < $size - 1; $i++) {
                $d_full_name .= ' ' . $distribution_array[$i];
            }
            $d_full_name = trim($d_full_name);

            $d_id = $edge['node']['data']['Ut5TV4CKpt']['userId'];
            $d_school_id = $edge['node']['meta']['lastModifiedBy']['schoolId'];
            $type = $edge['node']['data']['Ut5TV4CKpt']['signatureType'];
            $d_signature = $d_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $d_signature = $edge['node']['data']['Ut5TV4CKpt']['signedName'];
            }

            addInfo($d_display_name, $d_full_name, $d_email, $d_id, $d_school_id, $d_signature, $role = 'admin');
        }
        if (isset($edge['node']['data']['04PgxWqAbE'])) {
            // MANAGER/DEAN
            $m4_display_name = $edge['node']['data']['04PgxWqAbE']['displayName'];
            $m4_full_name = '';
            $m4_array = explode(' ', $m4_display_name);
            $size = count($m4_array);
            $m4_email = trim($m4_array[$size - 1], "()");
            for ($i = 0; $i < $size - 1; $i++) {
                $m4_full_name .= ' ' . $m4_array[$i];
            }
            $m4_full_name = trim($m4_full_name);
            $m4_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];

            $m4_id = $edge['node']['data']['04PgxWqAbE']['userId'];
            $type = $edge['node']['data']['04PgxWqAbE']['signatureType'];
            $m4_signature = $m4_full_name;
            if ($type === 'type') {
                // Handle specific type case
                $m4_signature = $edge['node']['data']['04PgxWqAbE']['signedName'];
            }

            addInfo($m4_display_name, $m4_full_name, $m4_email, $m4_id, $m4_school_id, $m4_signature, $role = 'manager', $dept_id);
        }
        if (isset($edge['node']['data']['jTxoK_Wsh7'])) {
            // MANAGER/DEAN
            $m2_display_name = $m2_full_name = $edge['node']['data']['jTxoK_Wsh7']['displayName'];
            $m2_email = $edge['node']['data']['jTxoK_Wsh7']['email'];

            $m2_id = $edge['node']['data']['jTxoK_Wsh7']['id'];
            $m2_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
            $m2_signature = $m2_display_name;

            addInfo($m2_display_name, $m2_full_name, $m2_email, $m2_id, $m2_school_id, $m2_signature, $role = 'manager', $dept_id);
        }
        if (isset($edge['node']['data']['kS_kp-Oo1y'])) {
            // MANAGER/DEAN
            $m3_display_name =  $m3_full_name = $edge['node']['data']['kS_kp-Oo1y']['displayName'];
            $m3_email = $edge['node']['data']['kS_kp-Oo1y']['email'];

            $m3_id = $edge['node']['data']['kS_kp-Oo1y']['id'];
            $m3_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
            $m3_signature = $m3_display_name;

            addInfo($m3_display_name, $m3_full_name, $m3_email, $m3_id, $m3_school_id, $m3_signature, $role = 'manager', $dept_id);
        }
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
