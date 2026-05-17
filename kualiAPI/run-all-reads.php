<?php
include_once __DIR__ . '/../config.php';
set_time_limit(900);

file_put_contents(__DIR__ . '/debug.log', date('c') . " FILE LOADED\n", FILE_APPEND);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');
error_reporting(E_ALL);


$select = "SELECT * FROM kuali_table";
$result = $query_repo->fetchOne($select);

deleteOverdueSchedule();
propertyTransfer();
addKualiInfo();
assetAddition();
assetReceived();
bulkPsr();
bulkTransfer();
check();
lsd();
psr();
dwBulkTransfer();
dwCheck();
dwLsd();
dwPsr();
checkFormStatus();
getAuditSchedules();
//completeAudit();
dwCompleteAudit();
dwLsdV2();
//SPABusChange();

function addKualiInfo()
{
    echo '<br>Add Kuali Info<br>';
    global $dbh, $result, $kuali, $query_repo;
    $skip = (int)$result['cust_responsibility_time'] ?? 0;

    $decode_true = $kuali->baseReads("67bf42240472a7027dd17e97", $skip);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

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

    function addInfo($username, $email, $form_id, $school_id, $signature, $full_name, $role, $dept_id)
    {
        echo '<br>Add Info<br>';
        echo 'Username ' . $username  . ' Email ' . $email . ' Form Id ' . $form_id . ' School id ' . $school_id . ' Signature ' . $signature . ' Full name ' . $full_name . ' Role ' . $role . ' Department ' . $dept_id;
        global $query_repo;

        $select = 'SELECT username, email, form_id, signature, school_id FROM user_table WHERE email = ?';
        $info = $query_repo->fetchOne($select, $email);

        $name_array = explode(' ', $full_name);
        $f_name = $name_array[0];
        $l_name = implode(' ', array_slice($name_array, 1));

        if ($info) {
            if (empty($info['school_id']) || empty($info['form_id']) || empty($info['signature'])) {
                $update = 'UPDATE user_table SET school_id = ?, form_id = ?, signature = ? WHERE email = ?';
                $query_repo->execute($update, $school_id, $form_id, $signature, $email);
            }
            if ($role === 'custodian') {
                $select = 'SELECT dept_id FROM department WHERE ? = ANY(custodian)';
                $depts = $query_repo->fetchAll($select, $full_name);

                $found = false;
                foreach ($depts as $id) {
                    if ($id['dept_id'] === $dept_id) {
                        $found = true;
                        break;
                    }
                }

                $select = 'SELECT 1 FROM user_table WHERE ? = ANY(dept_id) AND email = ?';
                $cust_found = $query_repo->fetchOne($select, $dept_id, $email);
                if (!$cust_found) {
                    $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?';
                    $query_repo->execute($update, $dept_id, $email);
                }

                if (!$found) {
                    $select = 'SELECT email, dept_id FROM user_table WHERE ? = ANY(dept_id)';
                    $users = $query_repo->fetchAll($select, $dept_id);
                    $found = false;
                    foreach ($users as $user) {
                        if ($user['email'] === $email) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?';
                        $query_repo->execute($update, $dept_id, $email);
                    }
                }
            }
        } else {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
            $new_dept_id = '{' . $dept_id . '}';
            $insert = 'INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, form_id, school_id, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $query_repo->execute($insert, $username, $hashed_pw, $email, $role, $f_name, $l_name, $new_dept_id, $form_id, $school_id, $signature);
        }
    }

    function addDepartment($documentSetId, $dept_kuali_id, $c_display_name, $m_full_name, $dept_id, $dept_name)
    {
        echo '<br>Add Department<br>';
        echo 'DocumentId: ' . $documentSetId . ' Kuali id: ' . $dept_kuali_id . ' Cust full name: ' . $c_display_name . ' Manager Full Name ' . $m_full_name . ' Dept Id ' . $dept_id . ' Dept Name ' . $dept_name;
        global $query_repo;

        $select = 'SELECT dept_id, dept_manager FROM department WHERE dept_id = ?';
        $dept_info = $query_repo->fetchOne($select, $dept_id);

        if ($dept_info) {
            if ($dept_info['dept_manager'] !== $m_full_name) {
                $update = 'UPDATE department SET dept_manager = ? WHERE dept_id = ?';
                $query_repo->execute($update, $m_full_name, $dept_id);
            }

            $select = 'SELECT dept_id, form_id, document_set_id FROM department WHERE ? = ANY(custodian)';
            $info = $query_repo->fetchAll($select, $c_display_name);

            $found = false;
            foreach ($info as $row) {
                if ($row['dept_id'] === $dept_id) {
                    $found = true;
                }
            }
            if (!$found) {
                $update = 'UPDATE department SET custodian = ARRAY_APPEND(custodian, ?) WHERE dept_id = ?';
                $query_repo->execute($update, $c_display_name, $dept_id);
            }
        } else {
            $custodian = '{' . $c_display_name . '}';
            $insert = 'INSERT INTO department (dept_id, dept_name, custodian, dept_manager, document_set_id, form_id) VALUES (?, ?, ?, ?, ?, ?)';
            $query_repo->execute($insert, $dept_id, $dept_name, $custodian, $m_full_name, $documentSetId, $dept_kuali_id);
        }
    }

    function addSignature($username, $email, $form_id, $signature, $school_id, $f_name, $l_name, $dept_id, $role = 'user')
    {
        global $query_repo;
        $full_name = $f_name . ' ' . $l_name;
        echo '<br>Add Signature<br>';
        echo 'Username ' . $username  . ' Email ' . $email . ' Form Id ' . $form_id . ' School id ' . $school_id . ' Signature ' . $signature . ' Full name ' . $full_name . ' Role ' . $role . ' Department' . $dept_id;

        $select = 'SELECT username, email, form_id, signature, school_id FROM user_table WHERE email = ?';
        $info = $query_repo->fetchOne($select, $email);

        if ($info) {
            if (empty($info['school_id']) || empty($info['form_id']) || empty($info['signature'])) {
                $update = 'UPDATE user_table SET school_id = ?, form_id = ?, signature = ? WHERE email = ?';
                $query_repo->execute($update, $school_id, $form_id, $signature, $email);
            }
            if ($role === 'custodian') {
                $select = 'SELECT dept_id FROM department WHERE ? = ANY(custodian)';
                $depts = $query_repo->fetchAll($select, $full_name);

                $found = false;
                foreach ($depts as $id) {
                    if ($id['dept_id'] === $dept_id) {
                        $found = true;
                        break;
                    }
                }

                $select = 'SELECT 1 FROM user_table WHERE ? = ANY(dept_id) AND email = ?';
                $cust_found = $query_repo->fetchOne($select, $dept_id, $email);
                if (!$cust_found) {
                    $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?';
                    $query_repo->execute($update, $dept_id, $email);
                }

                if (!$found) {
                    $select = 'SELECT email, dept_id FROM user_table WHERE ? = ANY(dept_id)';
                    $users = $query_repo->fetchAll($select, $dept_id);
                    $found = false;
                    foreach ($users as $user) {
                        if ($user['email'] === $email) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?';
                        $query_repo->execute($update, $dept_id, $email);
                    }
                }
            }
        } else {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
            $new_dept_id = '{' . $dept_id . '}';
            $insert = 'INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, form_id, school_id, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $query_repo->execute($insert, $username, $hashed_pw, $email, $role, $f_name, $l_name, $new_dept_id, $form_id, $school_id, $signature);
        }
    }

    try {
        foreach ($edges as $index => $edge) {
            $skip++;
            $array = null;
            if (isset($edge['node']['data']['XeTTtfl6XW']['data']['IOw4-l7NsM'])) {
                // single-dept path handled in else block below
            } else if (isset($edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['IOw4-l7NsM'])) {
                $array = $edge['node']['data']['r4XeMIe7yh']['data'];
            } else if (isset($edge['node']['data']['HBG7HehhU8']['data'][0]['data']['HN8JcizYYj']['data']['IOw4-l7NsM'])) {
                $array = $edge['node']['data']['HBG7HehhU8']['data'];
            }

            if (isset($array)) {
                foreach ($array as $dept_info) {
                    if (isset($edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['IOw4-l7NsM'])) {
                        $dept_id = $dept_info['data']['Gsxde2JR77']['data']['IOw4-l7NsM'];
                        $dept_name = $dept_info['data']['Gsxde2JR77']['data']['AkMeIWWhoj'];
                        $documentSetId = $dept_info['data']['Gsxde2JR77']['documentSetId'];
                        $dept_kuali_id = $dept_info['data']['Gsxde2JR77']['id'];
                    } else if (isset($edge['node']['data']['HBG7HehhU8']['data'][0]['data']['HN8JcizYYj']['data']['IOw4-l7NsM'])) {
                        $dept_id = $dept_info['data']['HN8JcizYYj']['data']['IOw4-l7NsM'];
                        $dept_name = $dept_info['data']['HN8JcizYYj']['data']['AkMeIWWhoj'];
                        $documentSetId = $dept_info['data']['HN8JcizYYj']['documentSetId'];
                        $dept_kuali_id = $dept_info['data']['HN8JcizYYj']['id'];
                    }

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
                        $c_username = explode('@', $c_email)[0];
                        $c_id = $edge['node']['data']['XhBe3DNaU4']['userId'];
                        $c_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
                        $type = $edge['node']['data']['XhBe3DNaU4']['signatureType'];
                        $c_signature = ($type === 'type')
                            ? $edge['node']['data']['XhBe3DNaU4']['signedName']
                            : $c_full_name;
                        addSignature($c_username, $c_email, $c_id, $c_signature, $c_school_id, $custodian_array[0], $c_l_name, $dept_id, 'custodian');
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
                        $m4_username = explode('@', $m4_email)[0];
                        $m4_id = $edge['node']['data']['04PgxWqAbE']['userId'];
                        $type = $edge['node']['data']['04PgxWqAbE']['signatureType'];
                        $m4_signature = ($type === 'type')
                            ? $edge['node']['data']['04PgxWqAbE']['signedName']
                            : $m4_full_name;
                        addSignature($m4_username, $m4_email, $m4_id, $m4_signature, $m4_school_id, $m4_f_name, $m4_l_name, $dept_id, 'user');
                    }

                    if (isset($edge['node']['data']['jTxoK_Wsh7'])) {
                        // MANAGER/DEAN INFORMATION
                        $m2_display_name = $m2_full_name = $edge['node']['data']['jTxoK_Wsh7']['displayName'];
                        $m2_email = $edge['node']['data']['jTxoK_Wsh7']['email'];
                        $m2_username = explode('@', $m2_email)[0];
                        $m2_id = $edge['node']['data']['jTxoK_Wsh7']['id'];
                        $m2_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
                        addInfo($m2_username, $m2_email, $m2_id, $m2_school_id, $m2_display_name, $m2_display_name, 'user', $dept_id);
                    }

                    if (isset($edge['node']['data']['kS_kp-Oo1y'])) {
                        // CUSTODIAN INFORMATION
                        $m3_display_name = $m3_full_name = $edge['node']['data']['kS_kp-Oo1y']['displayName'];
                        $m3_email = $edge['node']['data']['kS_kp-Oo1y']['email'];
                        $m3_username = explode('@', $m3_email)[0];
                        $m3_id = $edge['node']['data']['kS_kp-Oo1y']['id'];
                        $m3_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
                        addInfo($m3_username, $m3_email, $m3_id, $m3_school_id, $m3_display_name, $m3_display_name, 'custodian', $dept_id);
                    }

                    addDepartment($documentSetId, $dept_kuali_id, $c_full_name, $m4_full_name, $dept_id, $dept_name);
                }
            } else {
                $dept_id = $edge['node']['data']['XeTTtfl6XW']['data']['IOw4-l7NsM'];
                $dept_name = $edge['node']['data']['XeTTtfl6XW']['data']['AkMeIWWhoj'];
                $documentSetId = $edge['node']['data']['XeTTtfl6XW']['documentSetId'];
                $dept_kuali_id = $edge['node']['data']['XeTTtfl6XW']['id'];

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
                    $c_username = explode('@', $c_email)[0];
                    $c_id = $edge['node']['data']['XhBe3DNaU4']['userId'];
                    $c_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
                    $type = $edge['node']['data']['XhBe3DNaU4']['signatureType'];
                    $c_signature = ($type === 'type')
                        ? $edge['node']['data']['XhBe3DNaU4']['signedName']
                        : $c_full_name;
                    addSignature($c_username, $c_email, $c_id, $c_signature, $c_school_id, $custodian_array[0], $c_l_name, $dept_id, 'custodian');
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
                    $m4_username = explode('@', $m4_email)[0];
                    $m4_id = $edge['node']['data']['04PgxWqAbE']['userId'];
                    $type = $edge['node']['data']['04PgxWqAbE']['signatureType'];
                    $m4_signature = ($type === 'type')
                        ? $edge['node']['data']['04PgxWqAbE']['signedName']
                        : $m4_full_name;
                    addSignature($m4_username, $m4_email, $m4_id, $m4_signature, $m4_school_id, $m4_f_name, $m4_l_name, $dept_id, 'user');
                }

                if (isset($edge['node']['data']['jTxoK_Wsh7'])) {
                    // MANAGER/DEAN INFORMATION
                    $m2_display_name = $m2_full_name = $edge['node']['data']['jTxoK_Wsh7']['displayName'];
                    $m2_email = $edge['node']['data']['jTxoK_Wsh7']['email'];
                    $m2_username = explode('@', $m2_email)[0];
                    $m2_id = $edge['node']['data']['jTxoK_Wsh7']['id'];
                    $m2_school_id = $edge['node']['data']['jTxoK_Wsh7']['schoolId'];
                    addInfo($m2_username, $m2_email, $m2_id, $m2_school_id, $m2_display_name, $m2_display_name, 'user', $dept_id);
                }

                if (isset($edge['node']['data']['kS_kp-Oo1y'])) {
                    // CUSTODIAN INFORMATION
                    $m3_display_name = $m3_full_name = $edge['node']['data']['kS_kp-Oo1y']['displayName'];
                    $m3_email = $edge['node']['data']['kS_kp-Oo1y']['email'];
                    $m3_username = explode('@', $m3_email)[0];
                    $m3_id = $edge['node']['data']['kS_kp-Oo1y']['id'];
                    $m3_school_id = $edge['node']['data']['kS_kp-Oo1y']['schoolId'];
                    addInfo($m3_username, $m3_email, $m3_id, $m3_school_id, $m3_display_name, $m3_display_name, 'custodian', $dept_id);
                }

                addDepartment($documentSetId, $dept_kuali_id, $c_full_name, $m4_full_name, $dept_id, $dept_name);
            }
        }
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }

    $update = "UPDATE kuali_table SET cust_responsibility_time = ?";
    $query_repo->execute($update, $skip);
}

function assetAddition()
{
    echo '<br>Asset Addition<br>';
    global $dbh, $result, $kuali, $query_repo;
    $raw_ms = (int)$result['asset_addition_time'] ?? 0;

    $decode_true = $kuali->baseReads("67ec557474c52c027eca23d8", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    $profile_map = [
        'EQUIP-10'   => 10,
        'NONCAPCOMP' => 10,
        'EQUIP-20'   => 20,
        'EQUIP-05'   => 5,
        'EQUIPAUTO'  => 20,
        'OTHIMP-10'  => 10,
        'OTHIMP-20'  => 20,
        'OTHIMP-30'  => 30,
        'OINTN'      => 10,
        'NONCAPOTHR' => 10,
        'NONCAPAUTO' => 20,
        'EQUIPCOMP'  => 10
    ];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            if (!isset($edge['node']['data']['PUcYspMrJZ'])) {
                echo "<br> Skipping Tag Not Available <br>";
                continue;
            }

            $tag_data = $edge['node']['data']['PUcYspMrJZ']['data'];
            $asset_profile = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['pZEr8FpYK_']['label'] ?? 'EQUIP-10';
            $key = $asset_profile;

            if (isset($profile_map[$key])) {
                $asset_profile = $profile_map[$key];
            } else if ($key === 'SOFTWARE') {
                continue;
            } else {
                $asset_profile = 10;
            }

            $value = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['PxtY2-Q3bL'] ?? 1;
            $length = strlen($value);
            $value = (float)substr_replace($value, '.', $length - 2, 0);

            if ($asset_profile === 'BLDGIMP') {
                echo 'Skipped BLDGIMP <br>';
                continue;
            }

            if (isset($edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['WZ5fZCt1qz']['data'])) {
                $dept_id = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['WZ5fZCt1qz']['data']['IOw4-l7NsM'];
            }

            $po = 1;

            foreach ($tag_data as $tag) {
                $tag_num = $tag['data']['hYk-CuEHw-'];
                if (checkTagType($tag_num)) {
                    echo "<br> Match found " . $tag_num;
                } else continue;

                if (isset($tag['data']['XGD63KvFDV']['data']['IOw4-l7NsM'])) {
                    $dept_id = $tag['data']['XGD63KvFDV']['data']['IOw4-l7NsM'];
                }

                $serial_num = $tag['data']['TuFLyAwO61'] ?? 'Unknown';
                $name = $tag['data']['6dtRzO-_qZ'] ?? $tag['data']['wnpc592QUl'];

                $tag_taken = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tag_num);
                $it_status = checkItType($name);
                $fund = $tag['data']['TSeIOwwu6t'];

                if (!$tag_taken) {
                    $ms_date = $edge['node']['meta']['workflowCompletedAt'] / 1000;
                    $date = date('m-d-y', $ms_date);
                    echo '<br>IT STATUS ' . $it_status . '<br>';
                    $insert = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, dept_id, lifecycle, po, is_IT, fund) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $query_repo->execute($insert, $tag_num, $name, $date, $serial_num, $value, $dept_id, $asset_profile, $po, $it_status, $fund);
                }

                echo "<br>Asset Profile " . $asset_profile . "<br>Value " . $value . "<br>Tag " . $tag_num .
                    "<br>Dept " . $dept_id . "<br>SN " . $serial_num . "<br>Name " . $name . "<br>";
            }
        }
    } catch (PDOException $e) {
        error_log("Error " . $e->getMessage());
        return;
    }

    $update = "UPDATE kuali_table SET asset_addition_time = ?";
    $query_repo->execute($update, $raw_ms);
}

function assetReceived()
{
    echo '<br>Asset received<br>';
    global $dbh, $result, $kuali, $query_repo;
    $raw_ms = (int)$result['asset_received_time'] ?? 0;

    $decode_true = $kuali->baseReads("67b8c49871c3d6028236d586", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    $room_tag = 2051;

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $fund = $edge['node']['data']['di6FE1yIML'];
            $time = (int)$edge['node']['data']['wzgp7QHb7F'];
            $date = date("Y-m-d", $time / 1000);
            $tag_data = $edge['node']['data']['0nVFqyLknC']['data'];
            $po = $edge['node']['data']['3BdpFK5t1I'];
            if (preg_match('/(Order)(order)/i', $po)) {
                $po = 0;
            } else {
                $po = (int)$po;
            }

            $model = $edge['node']['data']['L6q0gWhZ-Q']['label'];
            if (strcasecmp($model, 'Other') === 0) {
                $model = trim($_POST['vendor_name'] ?? 'Other');
            }

            $dept_id = $edge['node']['data']['KMudjEpsXS']['data']['IOw4-l7NsM'];
            $lifecycle = 10;

            foreach ($tag_data as $tag) {
                $tag_num = '';
                if (!empty($tag['data']['1SI4ghT1Jt'])) {
                    $tag_num = $tag['data']['1SI4ghT1Jt'];
                }
                if (checkTagType($tag_num)) {
                    echo " Match Found <br>";
                } else continue;

                $serial_num = $tag['data']['Wrnezf-g0C'] ?? '';
                $value = $tag['data']['he_zIFgDiT'];
                $length = strlen($value);
                $value = (float)substr_replace($value, '.', $length - 2, 0);
                $name = $tag['data']['vNv8CdzZjv'];

                if (
                    $po === '' || $po === NULL || $tag_num === '' || $tag_num === NULL || $dept_id === '' || $dept_id === NULL
                    || $serial_num === '' || $serial_num === NULL || $value === '' || $value === NULL || $name === '' || $name === NULL
                ) {
                    continue;
                }

                try {
                    $tag_taken = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tag_num);
                } catch (PDOException $e) {
                    echo "Error selecting " . $e->getMessage();
                    $tag_taken = true;
                }

                if (!$tag_taken) {
                    try {
                        $it_status = checkItType($model);
                        $insert = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_model, po, dept_id, lifecycle, room_tag, is_IT, fund) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $query_repo->execute($insert, $tag_num, $name, $date, $serial_num, $value, $model, $po, $dept_id, $lifecycle, $room_tag, $it_status, $fund);
                        echo '<br>Inserted<br>Tag Number ' . $tag_num . '<br>Serial ID ' . $serial_num . '<br>Value ' . $value . '<br>Name ' . $name;
                        echo '<br>PO ' . $po . '<br>Model ' . $model . '<br>Dept ID ' . $dept_id . '<br>Date ' . $date . '<br><br>';
                    } catch (PDOException $e) {
                        echo '<br>Failed to insert<br>Tag Number ' . $tag_num . '<br>Serial ID ' . $serial_num . '<br>Value ' . $value . '<br>Name ' . $name;
                        echo '<br>PO ' . $po . '<br>Model ' . $model . '<br>Dept ID ' . $dept_id . '<br>Date ' . $date . '<br><br>';
                        echo "Error inserting " . $e->getMessage();
                    }
                } else {
                    echo $tag_taken['asset_tag'] . " Taken<br>";
                }
            }
        }

        $update = "UPDATE kuali_table SET asset_received_time = ?";
        $query_repo->execute($update, $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function bulkPsr()
{
    echo '<br>Bulk PSR<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['bulk_psr_time'] ?? 0;

    $decode_true = $kuali->baseReads("67c9d5af2017390283de33d5", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    $count = 1;
    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            if (!isset($edge['node']['data']['DtFI8bQn4g']['data'])) {
                echo "<br>Skipping Tag Not Available<br>";
                continue;
            }

            $tag_data = $edge['node']['data']['DtFI8bQn4g']['data'];
            foreach ($tag_data as $data) {
                $tag = $data['data']['6_z3IcanWR'];
                if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                    echo "<br>Tag field empty<br>";
                    continue;
                }

                $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
                if ($found) {
                    $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
                    echo "<br>" . $count++;
                    echo "<br>Updating<br>Tag " . $tag . "<br>";
                }
            }
        }

        echo '<br>BULK_PSR_TIME ' . $raw_ms . '<br>';
        $query_repo->execute("UPDATE kuali_table SET bulk_psr_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function bulkTransfer()
{
    echo '<br>Bulk Transfer<br>';
    global $dbh, $result, $kuali, $query_repo;
    $raw_ms = (int)$result['bulk_transfer_time'] ?? 0;

    $decode_true = $kuali->baseReads("686554f17ba08e02806b14b5", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            echo '<br> RAW MS COUNTER: ' . $raw_ms . '<br>';

            if (trim($edge['node']['data']['_GODY1FjEy']['label']) !== 'From one department to another department') {
                echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
                continue;
            }

            $tags = $edge['node']['data']['JZ-q3J19dw']['data'];
            foreach ($tags as $index => $data) {
                $tag = $data['data']['RxpLOF3XrE'];
                if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                    echo "<br>Tag field empty<br>";
                    continue;
                }

                echo "<br>Tag " . $tag . "<br>";
                $dept_id = substr($data['data']['5c3qSm88bs'], 0, 6);
                $room_loc = $data['data']['6JHs3W0-CL'] ?? null;

                echo $dept_id . "<br>";
                if (preg_match('/^D/', $dept_id)) {
                    echo "<br>Dept Id Format Good<br>";
                }

                $bldg_id = $bldg_name = null;
                if (!empty($data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'])) {
                    $bldg_id = $data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'];
                    $bldg_name = $data['data']['bYpfsUDuZx']['data']['AkMeIWWhoj'];
                }
                if (!empty($data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'])) {
                    $bldg_id = $data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'];
                    if ($bldg_id === '39A') $bldg_id = 39;
                    $bldg_name = $data['data']['BC0E2hOKv3']['data']['AkMeIWWhoj'];
                }
                $bldg_id = (int)$bldg_id;

                if (empty($bldg_id) || empty($bldg_name)) {
                    echo "<br>Building name or id was not found skipping<br>";
                    continue;
                }

                echo "<br>Bldg ID " . $bldg_id . "<br>";
                echo "<br>Bldg Name " . $bldg_name . "<br>";

                $db_bldg = $query_repo->fetchOne("SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = ?", $bldg_id);
                if (!$db_bldg) {
                    $query_repo->execute("INSERT INTO bldg_table (bldg_id, bldg_name) VALUES (?, ?)", $bldg_id, $bldg_name);
                    echo "<br>Building Was NOT found adding building to database. Automatically Added Building<br>";
                    $db_bldg = $query_repo->fetchOne("SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = ?", $bldg_id);
                }
                if ($bldg_id !== $db_bldg['bldg_id']) {
                    $query_repo->execute("UPDATE bldg_table SET bldg_id = ? WHERE bldg_name = ?", $bldg_id, $bldg_name);
                    echo "<br>Bldg id was different. Fixing<br>";
                }
                if ($bldg_name !== $db_bldg['bldg_name']) {
                    $query_repo->execute("UPDATE bldg_table SET bldg_name = ? WHERE bldg_id = ?", $bldg_name, $bldg_id);
                    echo "<br>Bldg name was different. Fixing<br>";
                }

                try {
                    $room_tag = $query_repo->fetchOne("SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?", $bldg_id, $room_loc);
                    if (!$room_tag) {
                        $query_repo->execute("INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)", $room_loc, $bldg_id);
                        echo "<br>Inserted room into database<br>";
                        $room_tag = $query_repo->fetchOne("SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?", $bldg_id, $room_loc);
                    }
                    $room_tag = $room_tag['room_tag'];
                } catch (PDOException $e) {
                    echo "Error selecting room_tag " . $e->getMessage() . "<br>";
                }

                try {
                    $asset = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tag);
                    if ($asset) {
                        echo "<br>Bldg ID " . $bldg_id . " Bldg Name " . $bldg_name . " Room location " . $room_loc . "<br>";
                        $query_repo->execute("UPDATE asset_info SET dept_id = ?, room_tag = ? WHERE asset_tag = ?", $dept_id, $room_tag, $tag);
                        echo "<br>Updated Tag in database<br>";
                    } else {
                        echo "<br>Tag was not in database<br>";
                    }
                } catch (PDOException $e) {
                    echo "error updating asset " . $e->getMessage();
                }

                try {
                    $query_repo->execute("UPDATE kuali_table SET bulk_transfer_time = ?", $raw_ms);
                } catch (PDOException $e) {
                    echo "error updating kuali_table " . $e->getMessage();
                }

                echo "<br>--------------------------------------<br>";
            }
        }
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function check()
{
    echo '<br>Check out<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['check_out_time'] ?? 0;

    $decode_true = $kuali->baseReads("677d53d969ef4601572b80ae", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    $count = 1;
    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            echo '<br> RAW MS COUNTER: ' . $raw_ms . '<br>';

            $check_out_type = $edge['node']['data']['fyaCF8g3Uh']['label'];
            $check_out = $check_in = false;
            $dept = $edge['node']['data']['isFMbCuv8e']['data']['IOw4-l7NsM'] ?? 'Unknown Dept';

            if ($check_out_type === 'Checking Out Equipment') {
                $borrower = $edge['node']['data']['JsHBzpz-AT']['displayName'] ?? $edge['node']['data']['JXLJ_AOov-']['displayName'];
                $borrow_array = explode(" ", $borrower);
                $borrower = $borrow_array[0] . " " . $borrow_array[count($borrow_array) - 2];
                $info = "CHCKD," . $dept . ' ' . $borrower;
                $check_out = true;
            } else {
                $check_in = true;
            }

            $tag = $edge['node']['data']['AvjKneaxPz'][1]['jswe8fMFPT'] ?? $edge['node']['data']['BOZIA6hewQ'];

            if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                echo "<br>Tag field empty<br>";
                continue;
            }

            $asset = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ?", $tag);
            if ($asset) {
                if ($check_out) {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = ? WHERE asset_tag = ?", $info, $tag);
                } else if ($check_in) {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = ?", $tag);
                }
            } else {
                $serial = $edge['node']['data']['jYTHHgL10M'];
                $type2 = $edge['node']['data']['aUVT1BLN6V'];
                $name = $edge['node']['data']['cQOz4UQ0rQ'];
                $query_repo->execute('INSERT INTO asset_info (asset_tag, asset_name, type2, serial, dept_id) VALUES (?,?,?,?,?)', $tag, $name, $type2, $serial, $dept);
            }

            echo "<br>" . $count++;
            echo "<br>Updating<br>Tag " . $tag . "<br>";
        }

        echo 'CHECKOUT RAW_MS COUNT: ' . $raw_ms . '<br>';
        $query_repo->execute("UPDATE kuali_table SET check_out_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function lsd()
{
    echo '<br>LSD<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['equip_lost_stol_time'] ?? 0;

    $decode_true = $kuali->baseReads("677c075baba4e3014ca39095", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $tag = isset($edge['node']['data']['y7nFCmsLEg'])
                ? $edge['node']['data']['y7nFCmsLEg']
                : $edge['node']['data']['ufHf4QAJsc'];

            echo "<br>Tag " . $tag . "<br>";

            $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
            if ($found) {
                $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
            }

            $raw_ms++;
            $query_repo->execute("UPDATE kuali_table SET equip_lost_stol_time = ?", $raw_ms);
        }
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function psr()
{
    echo '<br>PSR<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['psr_time'] ?? 0;

    $decode_true = $kuali->baseReads("68093820dec1b8027f980167", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $tag_data = $edge['node']['data']['W_Uw0hSpff']['data'];
            foreach ($tag_data as $data) {
                $tag = $data['data']['yks38VOkzw'];
            }

            echo "<br>Tag " . $tag . "<br>";

            $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
            if ($found) {
                $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
            }
        }

        $query_repo->execute("UPDATE kuali_table SET psr_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function propertyTransfer()
{
    echo '<br>Property Transfer<br>';
    global $dbh, $result, $kuali, $query_repo;
    $raw_ms = (int)$result['transfer_time'] ?? 0;

    $decode_true = $kuali->baseReads("67e451d2cc3194027dfce429", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    foreach ($edges as $index => $edge) {
        $raw_ms++;
        $form_type = $edge['node']['data']['-SBfvXlL1f'];
        foreach ($form_type as $type) {
            if ($type['id'] === 'S5VuLLJDQ1c') {
                deptChange($edge, $raw_ms);
            } else if ($type['id'] === 'HR0TQfdrMn2') {
                busChange($edge);
            } else {
                bldgChange($edge, $raw_ms);
            }
        }
    }

    $query_repo->execute("UPDATE kuali_table SET transfer_time = ?", $raw_ms);
}

function busChange($edge)
{
    global $query_repo;
    $tags = $edge['node']['data']['K6NZgw5Vgh']['data'];
    foreach ($tags as $tag) {
        $old_tag = $tag['data']['biJxrXUqIw'];
        $new_tag = $tag['data']['XQH80E5rNZ'];
        $query_repo->execute('UPDATE asset_info SET asset_tag = ? WHERE asset_tag = ?', $new_tag, $old_tag);
    }
}

function checkBldg($bldg_name, $room_loc, $tag)
{
    global $query_repo;

    $bldg = $query_repo->fetchOne('SELECT bldg_id FROM bldg_table WHERE bldg_name = ?', $bldg_name);
    if (!$bldg) return false;

    $bldg_id = $bldg['bldg_id'];
    $room = $query_repo->fetchOne('SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?', $bldg_id, $room_loc);

    if (!$room) {
        $query_repo->execute('INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)', $room_loc, $bldg_id);
        $room = $query_repo->fetchOne('SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?', $bldg_id, $room_loc);
    }

    $query_repo->execute('UPDATE asset_info SET room_tag = ? WHERE asset_tag = ?', $room['room_tag'], $tag);
    return true;
}

function checkTag($tag)
{
    global $query_repo;
    $result = $query_repo->fetchOne('SELECT asset_tag FROM asset_info WHERE asset_tag = ?', $tag);
    return $result !== false;
}

function deptChange($edge, $raw_ms)
{
    global $query_repo;

    $tags = $edge['node']['data']['t7mH-1FlaO']['data'];
    $manager = $edge['node']['data']['OdeViTatve']['displayName'];
    $custodian = $edge['node']['data']['9Zu2I3A53B']['displayName'];

    foreach ($tags as $index => $data) {
        $tag = $data['data']['XZlIFEDX6Y'];
        checkTag($tag);

        if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
            echo "<br>Tag field empty<br>";
            continue;
        }

        $dept_id = $dept_name = null;
        if (isset($data['data']['U73d7kPH5b']['data']['IOw4-l7NsM'])) {
            $dept_id = $data['data']['U73d7kPH5b']['data']['IOw4-l7NsM'];
            $dept_name = $data['data']['U73d7kPH5b']['data']['AkMeIWWhoj'];
        } else if (isset($data['data']['qvczWxUOzQ']['data']['IOw4-l7NsM'])) {
            $dept_id = $data['data']['qvczWxUOzQ']['data']['IOw4-l7NsM'];
            $dept_name = $data['data']['qvczWxUOzQ']['data']['AkMeIWWhoj'];
        }

        $room_loc = $data['data']['zZztPX8Pcw'] ?? $data['data']['CeMwzz3mnp'] ?? $data['data']['6JHs3W0-CL'] ?? null;
        $bldg_name = $data['data']['hXHmCy0mek']['label'] ?? $data['data']['YtHlHUNY_q']['label'] ?? null;

        echo '<br>Bldg name: ' . $bldg_name . ' Dept id: ' . $dept_id . ' Dept name: ' . $dept_name . ' Room Location ' . $room_loc . '<br>';

        if (!empty($bldg_name) && !empty($room_loc)) {
            checkBldg($bldg_name, $room_loc, $tag);
        }

        $dept_id = substr($dept_id, 0, 6);
        echo $dept_id . "<br>";
        if (!preg_match('/^D/', $dept_id)) {
            continue;
        }
        echo "<br>Dept Id Format Good<br>";

        $query_repo->execute("UPDATE asset_info SET dept_id = ? WHERE asset_tag = ?", $dept_id, $tag);

        $dept_info = $query_repo->fetchOne('SELECT dept_name, dept_manager FROM department WHERE dept_id = ?', $dept_id);
        if (!$dept_info) {
            $query_repo->execute('INSERT INTO department (dept_id, dept_name, custodian, dept_manager) VALUES (?, ?, ?, ?)', $dept_id, $dept_name, $custodian, $manager);
        } else {
            $check_cust = $query_repo->fetchOne('SELECT 1 FROM department WHERE ? = ANY(custodian)', $custodian);
            if (!$check_cust) {
                $query_repo->execute('UPDATE department SET custodian = ARRAY_APPEND(custodian, ?), dept_manager = ? WHERE dept_id = ?', $custodian, $manager, $dept_id);
            } else {
                $query_repo->execute('UPDATE department SET dept_manager = ? WHERE dept_id = ?', $manager, $dept_id);
            }
        }

        echo "<br>Time " . $raw_ms . "<br>";
        echo "<br>--------------------------------------<br>";
    }
}

function bldgChange($edge, $raw_ms)
{
    global $query_repo;
    $tags = $edge['node']['data']['t7mH-1FlaO']['data'];

    foreach ($tags as $index => $data) {
        $tag = $data['data']['XZlIFEDX6Y'];
        checkTag($tag);

        if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
            echo "<br>Tag field empty<br>";
            continue;
        }

        $room_loc = $data['data']['zZztPX8Pcw'] ?? $data['data']['CeMwzz3mnp'] ?? $data['data']['6JHs3W0-CL'] ?? null;
        $bldg_name = $data['data']['hXHmCy0mek']['label'] ?? $data['data']['YtHlHUNY_q']['label'] ?? null;

        echo '<br>Bldg name: ' . $bldg_name . ' Room Loc ' . $room_loc . '<br>';

        if (!empty($bldg_name) && !empty($room_loc)) {
            checkBldg($bldg_name, $room_loc, $tag);
        }

        echo "<br>Time " . $raw_ms . "<br>";
        echo "<br>--------------------------------------<br>";
    }
}

function SPABusChange()
{
    echo '<br>Bus Change<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['bus_change_time'] ?? 0;

    $decode_true = $kuali->baseReads("691df89db23137028e39230a", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    foreach ($edges as $index => $edge) {
        $raw_ms++;
        $tag_array = $edge['node']['data']['z64jO_p-uG']['data'];

        foreach ($tag_array as $index => $tag_details) {
            $tag = $tag_details['data']['ep7IXpogXq'];
            $tag_check = $query_repo->fetchOne('SELECT asset_tag FROM asset_info WHERE asset_tag = ?', $tag);

            $new_tag = '';
            $new_name = '';
            $old_tag = ''; // TODO: old_tag source unclear — needs investigation

            if ($tag_check) {
                $query_repo->execute('UPDATE asset_info SET asset_tag = ? WHERE asset_tag = ?', $new_tag, $old_tag);
            } else {
                $query_repo->execute('INSERT INTO asset_info (asset_tag, asset_name, serial_num) VALUES (?, ?, ?)', $new_tag, $new_name, 'N/A');
            }

            $query_repo->execute('UPDATE kuali_table SET bus_change_time = ?', $raw_ms);
        }
    }
}

function dwBulkTransfer()
{
    echo '<br>DW Bulk Transfer<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['dw_bulk_time'] ?? 0;

    $decode_true = $kuali->baseReads("68c73600df46a3027d2bd386", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;

            if (trim($edge['node']['data']['_GODY1FjEy']['label']) !== 'From one department to another department') {
                echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
                continue;
            }

            $tags = $edge['node']['data']['JZ-q3J19dw']['data'];
            foreach ($tags as $index => $data) {
                $tag = $data['data']['RxpLOF3XrE'];
                if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                    echo "<br>Tag field empty<br>";
                    continue;
                }

                echo "<br>Tag " . $tag . "<br>";
                $dept_id = substr($data['data']['5c3qSm88bs'], 0, 6);
                $room_loc = $data['data']['6JHs3W0-CL'] ?? null;

                echo $dept_id . "<br>";
                if (preg_match('/^D/', $dept_id)) {
                    echo "<br>Dept Id Format Good<br>";
                }

                $raw_bldg = $data['data']['SBu1DONXk2'];
                $bldg_parts = explode('(', $raw_bldg);
                $bldg_name = str_replace(')', '', $bldg_parts[1]);

                $bldg = $query_repo->fetchOne("SELECT bldg_id FROM bldg_table WHERE bldg_name = ?", $bldg_name);
                $bldg_id = $bldg['bldg_id'] ?? null;

                if (empty($bldg_id) || empty($bldg_name)) {
                    echo "<br>Building name or id was not found skipping<br>";
                    continue;
                }

                echo "<br>Bldg ID " . $bldg_id . "<br>";
                echo "<br>Bldg Name " . $bldg_name . "<br>";

                $db_bldg = $query_repo->fetchOne("SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = ?", $bldg_id);
                if (!$db_bldg) {
                    $query_repo->execute("INSERT INTO bldg_table (bldg_id, bldg_name) VALUES (?, ?)", $bldg_id, $bldg_name);
                    echo "<br>Building Was NOT found adding building to database. Automatically Added Building<br>";
                    $db_bldg = $query_repo->fetchOne("SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = ?", $bldg_id);
                }
                if ($bldg_id !== $db_bldg['bldg_id']) {
                    $query_repo->execute("UPDATE bldg_table SET bldg_id = ? WHERE bldg_name = ?", $bldg_id, $bldg_name);
                    echo "<br>Bldg id was different. Fixing<br>";
                }
                if ($bldg_name !== $db_bldg['bldg_name']) {
                    $query_repo->execute("UPDATE bldg_table SET bldg_name = ? WHERE bldg_id = ?", $bldg_name, $bldg_id);
                    echo "<br>Bldg name was different. Fixing<br>";
                }

                try {
                    $room = $query_repo->fetchOne("SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?", $bldg_id, $room_loc);
                    if (!$room) {
                        $query_repo->execute("INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)", $room_loc, $bldg_id);
                        echo "<br>Inserted room into database<br>";
                        $room = $query_repo->fetchOne("SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?", $bldg_id, $room_loc);
                    }
                    $room_tag = $room['room_tag'];
                } catch (PDOException $e) {
                    echo "Error selecting room_tag " . $e->getMessage() . "<br>";
                }

                try {
                    $asset = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tag);
                    if ($asset) {
                        echo "<br>Bldg ID " . $bldg_id . " Bldg Name " . $bldg_name . " Room location " . $room_loc . "<br>";
                        $query_repo->execute("UPDATE asset_info SET dept_id = ?, room_tag = ? WHERE asset_tag = ?", $dept_id, $room_tag, $tag);
                        echo "<br>Updated Tag in database<br>";
                    } else {
                        echo "<br>Tag was not in database<br>";
                    }
                } catch (PDOException $e) {
                    echo "error updating asset " . $e->getMessage();
                }

                echo "<br>--------------------------------------<br>";
            }
        }

        $query_repo->execute("UPDATE kuali_table SET dw_bulk_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function dwCheck()
{
    echo 'DW Check out<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['dw_check_time'] ?? 0;

    $decode_true = $kuali->baseReads("68bf09aaadec5e027fe35187", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    $count = 1;
    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $check_out_type = $edge['node']['data']['fyaCF8g3Uh']['label'];
            $check_out = $check_in = false;

            if ($check_out_type === 'Checking Out Equipment') {
                $dept = $edge['node']['data']['isFMbCuv8e']['data']['IOw4-l7NsM'] ?? 'Unknown Dept';
                $borrower = $edge['node']['data']['JsHBzpz-AT']['displayName'] ?? $edge['node']['data']['JXLJ_AOov-']['displayName'];
                $borrow_array = explode(" ", $borrower);
                $borrower = $borrow_array[0] . " " . $borrow_array[count($borrow_array) - 2];
                $info = "CHCKD," . $dept . ' ' . $borrower;
                $check_out = true;
            } else {
                $check_in = true;
            }

            $tag = $edge['node']['data']['AvjKneaxPz'][1]['jswe8fMFPT'] ?? $edge['node']['data']['BOZIA6hewQ'];

            if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                echo "<br>Tag field empty<br>";
                continue;
            }

            $asset = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ?", $tag);
            if ($asset) {
                if ($check_out) {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = ? WHERE asset_tag = ?", $info, $tag);
                } else if ($check_in) {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = ?", $tag);
                }
            }

            echo "<br>" . $count++;
            echo "<br>Updating<br>Tag " . $tag . "<br>";
        }

        $query_repo->execute("UPDATE kuali_table SET dw_check_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function dwLsd()
{
    echo '<br>DW LSD<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['dw_lsd_time'] ?? 0;

    $decode_true = $kuali->baseReads("68d09e41d599f1028a9b9457", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $tag = isset($edge['node']['data']['y7nFCmsLEg'])
                ? $edge['node']['data']['y7nFCmsLEg']
                : $edge['node']['data']['ufHf4QAJsc'];

            echo "<br>Tag " . $tag . "<br>";

            $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
            if ($found) {
                $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
            }
        }

        $query_repo->execute("UPDATE kuali_table SET dw_lsd_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function dwLsdV2()
{
    echo '<br>DW LSD 2<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = (int)$result['dw_lsd_time_v2'] ?? 0;

    $decode_true = $kuali->baseReads("68e94e8a58fd2e028d5ec88f", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $tag = $edge['node']['data']['2iwsFa0_2j'];

            echo "<br>Tag " . $tag . "<br>";

            $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
            if ($found) {
                $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
            }
        }

        $query_repo->execute("UPDATE kuali_table SET dw_lsd_time_v2 = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function dwPsr()
{
    echo '<br>DW PSR<br>';
    global $result, $kuali, $query_repo;
    $raw_ms = $result['dw_psr_time'] ?? 0;

    $decode_true = $kuali->baseReads("68d09dcd7688dc028af9b5e7", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;
            $tag_data = $edge['node']['data']['W_Uw0hSpff']['data'];
            foreach ($tag_data as $data) {
                $tag = $data['data']['yks38VOkzw'];
            }

            echo "<br>Tag " . $tag . "<br>";

            $found = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'", $tag);
            if ($found) {
                $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
            }
        }

        $query_repo->execute("UPDATE kuali_table SET dw_psr_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    }
}

function checkFormStatus()
{
    global $query_repo, $kuali;
    $select = "SELECT unnest(check_forms) AS form_id, dept_id, audit_id FROM audit_history WHERE check_forms IS NOT NULL AND CAST(check_forms AS TEXT) ILIKE '%in-progress%'";
    $forms_to_check = $query_repo->fetchAll($select);

    foreach ($forms_to_check as $form) {
        $parts = explode(',', $form['form_id']);
        $id = '';

        foreach ($parts as $index => $ele) {
            if ($index === 0) {
                $id = trim($ele);
                echo $form['dept_id'] . ' ' . $id . '<br>';
                continue;
            }
            if ($index === 1) {
                $type = match (trim($ele)) {
                    'rlsd'      => '68e94e8a58fd2e028d5ec88f',
                    'lsd'       => '68d09e41d599f1028a9b9457',
                    'transfer'  => '68c73600df46a3027d2bd386',
                    'rtransfer' => '68d09e38d599f1028a08969a',
                };
                continue;
            }
            if ($index === 2) {
                continue;
            }
            $decoded = $kuali->baseReads($type, $ele);

            if (empty($decoded['data']['app']['documentConnection']['edges'])) {
                echo 'No edges found <br>';
                continue;
            }

            $found = false;
            $status = '';
            $edges = $decoded['data']['app']['documentConnection']['edges'];
            foreach ($edges as $edge) {
                if (!empty($edge['node']['meta']['workflowStatus']) && $id === trim($edge['node']['id'])) {
                    $status = $edge['node']['meta']['workflowStatus'];
                    echo 'ID found <br>';
                    $found = true;
                    break;
                }
            }

            if ($found) break;
        }

        if (!$found || empty($status)) continue;

        $formParts = explode(',', $form['form_id']);
        if (count($formParts) < 3) continue;

        $formStatus = strtolower(trim($formParts[2]));
        if ($status === $formStatus || $status === 'in-progress') continue;

        if (in_array($status, ['complete', 'withdrawn', 'denied']) && $formStatus === 'in progress') {
            $new_form = str_replace('in-progress', $status, $form['form_id']);
            $update = "UPDATE audit_history
                SET check_forms = array_append(array_remove(COALESCE(check_forms, '{}'::text[]), ?), ?)
                WHERE audit_id = ? AND dept_id = ?";
            $query_repo->execute($update, $form['form_id'], $new_form, $form['audit_id'], $form['dept_id']);
        }
    }
}

function deleteOverdueSchedule()
{
    global $query_repo;
    echo '<br>Delete Overdue Schedule<br>';
    $query_repo->execute('DELETE FROM audit_schedule WHERE audit_date < CURRENT_TIMESTAMP');
}

function getAuditSchedules()
{
    global $kuali, $query_repo;
    echo '<br>getAuditSchedules<br>';

    $result = $query_repo->fetchOne("SELECT * FROM kuali_table");
    $raw_ms = (int)$result['schedule_time'] ?? 0;

    $decode_true = $kuali->baseReads("682622ce355ca4027e35d52a", $raw_ms);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $index => $edge) {
            $raw_ms++;

            $time = $edge['node']['data']['tYz59qALVK'];
            $date_raw = $edge['node']['data']['ChU6eQjeRf'];
            $new_date = $time + ($date_raw) / 1000;
            $date = (new DateTime("@$new_date"))->format('Y/m/d H:i:s');

            if ((int)microtime(true) > $new_date) {
                continue;
            }

            $custodian = $edge['node']['data']['Unwly2UM1p']['displayName'];
            $manager = $edge['node']['data']['epSRSrkGXT']['displayName'] ?? '';

            if (!empty($edge['node']['data']['G_0VlXBs4s'])) {
                foreach ($edge['node']['data']['G_0VlXBs4s']['data'] as $dept) {
                    $dept_id = $dept['data']['dTFWWegtgK']['data']['IOw4-l7NsM'];
                    $dept_name = $dept['data']['dTFWWegtgK']['data']['AkMeIWWhoj'];
                    echo $dept_id . ' ' . $dept_name . '<br>';

                    if (!empty($manager)) {
                        $insert = "INSERT INTO department (dept_id, dept_manager, dept_name, custodian) VALUES (?,?,?,?) ON CONFLICT (dept_id) DO UPDATE SET dept_manager = EXCLUDED.dept_manager";
                        $query_repo->execute($insert, $dept_id, $manager, $dept_name, '{' . $custodian . '}');
                    }

                    echo $custodian . '<br>';
                    echo $date . '<br>';
                    $query_repo->execute('INSERT INTO audit_schedule (dept_id, audit_date, custodian) VALUES (?, ?, ?)', $dept_id, $date, $custodian);
                }
            }
        }

        $query_repo->execute("UPDATE kuali_table SET schedule_time = ?", $raw_ms);
    } catch (PDOException $e) {
        echo "Error with database " . $e->getMessage();
        return;
    } catch (Exception $e) {
        echo "General error " . $e->getMessage();
        return;
    }
}

function completeAudit()
{
    global $result, $kuali, $query_repo;
    echo '<br>Complete Audit<br>';

    $skip = $result['complete_schedule'] ?? 0;
    $decode_true = $kuali->baseReads("67e450e3cc3194027d15a8e2", $skip);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        if (!isset($edge['node']['data']['4Oqb_ktloM']['data']['IOw4-l7NsM'])) {
            echo 'No department ID found for document ID: ' . $edge['node']['id'] . "<br>";
            continue;
        }

        $dept_id = $edge['node']['data']['4Oqb_ktloM']['data']['IOw4-l7NsM'];
        $dept_name = $edge['node']['data']['4Oqb_ktloM']['data']['AkMeIWWhoj'];

        $dept_exists = $query_repo->fetchOne('SELECT dept_id FROM department WHERE dept_id = ?', $dept_id);
        if ($dept_exists) {
            $manager = $edge['node']['data']['55-0zfJWML']['displayName'];
            $custodian = $edge['node']['data']['lHuAQy0tZd']['displayName'];

            $cust_in_dept = $query_repo->fetchOne('SELECT 1 FROM department WHERE ? = ANY(custodian) AND dept_id = ?', $custodian, $dept_id);
            if (!$cust_in_dept) {
                $query_repo->execute('UPDATE department SET custodian = ARRAY_APPEND(custodian, ?), dept_manager = ? WHERE dept_id = ?', $custodian, $manager, $dept_id);
            } else {
                $query_repo->execute('UPDATE department SET dept_manager = ? WHERE dept_id = ?', $manager, $dept_id);
            }

            $audit_ids = $query_repo->fetchOne('SELECT audit_id, dept_id FROM audit_history AS a, unnest(a.check_forms) AS t WHERE t ILIKE ?', '%' . $edge['node']['id'] . '%');
            $audit_freq = $query_repo->fetchOne('SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq');

            if ($audit_ids['audit_id'] == 6) {
                $prev_mgmt = ($audit_freq['curr_mgmt_id'] == 4) ? 5 : $audit_freq['curr_mgmt_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_mgmt);
            } else if ($audit_ids['audit_id'] == 9) {
                $prev_spa = ($audit_freq['curr_spa_id'] == 7) ? 8 : $audit_freq['curr_spa_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_spa);
            } else if ($audit_ids['audit_id'] == 3) {
                $prev_self = ($audit_freq['curr_self_id'] == 1) ? 2 : $audit_freq['curr_self_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_self);
            } else {
                $query_repo->execute("UPDATE audit_history SET audit_status = 'Complete' WHERE dept_id = ? AND audit_id = ?", $dept_id, $audit_ids['audit_id']);
            }
        }

        $query_repo->execute('UPDATE kuali_table SET complete_schedule = ?', $skip);
        echo "Document ID: " . $edge['node']['id'] . " - Department ID: " . $dept_id . " - Department Name: " . $dept_name . "<br>";
    }
}

function dwCompleteAudit()
{
    global $result, $kuali, $query_repo;
    echo '<br>DW Complete Audit<br>';

    $skip = (int)$result['dw_complete_schedule'] ?? 0;
    $decode_true = $kuali->baseReads("68e5ccf75911b5028c9e9d3e", $skip);
    $edges = $decode_true['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        if (!isset($edge['node']['data']['Stimf2f9oY']['data']['IOw4-l7NsM'])) {
            echo 'No department ID found for document ID: ' . $edge['node']['id'] . "<br>";
            continue;
        }

        $dept_id = $edge['node']['data']['Stimf2f9oY']['data']['IOw4-l7NsM'];
        $dept_name = $edge['node']['data']['Stimf2f9oY']['data']['AkMeIWWhoj'];

        $dept_exists = $query_repo->fetchOne('SELECT dept_id FROM department WHERE dept_id = ?', $dept_id);
        if ($dept_exists) {
            $manager = $edge['node']['data']['55-0zfJWML']['displayName'];
            $custodian = $edge['node']['data']['lHuAQy0tZd']['displayName'];

            $cust_in_dept = $query_repo->fetchOne('SELECT 1 FROM department WHERE ? = ANY(custodian) AND dept_id = ?', $custodian, $dept_id);
            if (!$cust_in_dept) {
                $query_repo->execute('UPDATE department SET custodian = ARRAY_APPEND(custodian, ?), dept_manager = ? WHERE dept_id = ?', $custodian, $manager, $dept_id);
            } else {
                $query_repo->execute('UPDATE department SET dept_manager = ? WHERE dept_id = ?', $manager, $dept_id);
            }

            $audit_ids = $query_repo->fetchOne('SELECT dept_id, audit_id FROM audit_history WHERE complete_form_id = ?', $edge['node']['id']);
            $audit_freq = $query_repo->fetchOne('SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq');

            if ($audit_ids['audit_id'] == 6) {
                $prev_mgmt = ($audit_freq['curr_mgmt_id'] == 4) ? 5 : $audit_freq['curr_mgmt_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_mgmt);
            } else if ($audit_ids['audit_id'] == 9) {
                $prev_spa = ($audit_freq['curr_spa_id'] == 7) ? 8 : $audit_freq['curr_spa_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_spa);
            } else if ($audit_ids['audit_id'] == 3) {
                $prev_self = ($audit_freq['curr_self_id'] == 1) ? 2 : $audit_freq['curr_self_id'];
                updateOldAudit($dept_id, $audit_ids['audit_id'], $prev_self);
            } else {
                $query_repo->execute("UPDATE audit_history SET audit_status = 'Complete' WHERE complete_form_id = ?", $edge['node']['id']);
            }
        }

        echo "Document ID: " . $edge['node']['id'] . " - Department ID: " . $dept_id . " - Department Name: " . $dept_name . "<br>";
        $query_repo->execute('UPDATE kuali_table SET dw_complete_schedule = ?', $skip);
    }
}

function updateOldAudit($dept_id, $audit_id, $new_audit_id)
{
    global $query_repo;
    // NOTE: original SQL was missing a comma between SET clauses — fixed here
    $update = "UPDATE audit_history SET audit_status = 'Complete', audit_id = ? WHERE dept_id = ? AND audit_id = ?";
    $query_repo->execute($update, $new_audit_id, $dept_id, $audit_id);
}

function checkTagType(string $tag_num)
{
    $ASI = "/^A[SI]?\d+$/";
    $STU = "/^S[RC]?[TU]?\d+$/";
    $CMP = "/^\d+/";
    $FDN = "/^F[DN]?\d+$/";
    $SPA = "/^SP\d+$/";

    return (preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
        preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
        preg_match($SPA, $tag_num));
}

function checkItType(string $name)
{
    $it_regex = '/\b(LENOVO)|(APPLE)|(DELL)|(HP)|(CPU)|(MACBOOK)|(CHROMEBOOK)|(TABLET)|(SERVER)|(PRECISION\s\d*\sTOWER)|(iPAD)\b/i';
    return preg_match($it_regex, $name) ? 1 : 0;
}
