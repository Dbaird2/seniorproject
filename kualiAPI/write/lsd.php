<?php
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once 'search.php';
include_once 'get-info.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$tag_data = $data;
$audit_dept = $data['dept_id'];
$variables = [[]];
foreach ($_SESSION['data'] as $session) {
    if ($session['Tag Number'] === $data['tag']) {
        $tag_info = $query_repo->getAssetData($data['tag']);

        $data['type2'] = $tag_info['type2'];
        $data['Make'] = $tag_info['make'];
        $data['Unit'] = $session['Unit'];
        $data['Model'] = $session['Model'] ?? 'N/A';
        $variables['data']['y7nFCmsLEg'] = $data['tag'];
        $variables['data']['pNvpNnuav8'] = $session['Descr'];
        $data['Descr'] = $session['Descr'];
        $variables['data']['7Gzhcg_35S'] = $session['Serial ID'];
        $data['Serial ID'] = $session['Serial ID'];
        if (!empty($tag_info['make'])) {
            $variables['data']['Qb1ac69GLa'] = $tag_info['make'];
        } else {
            $variables['data']['Qb1ac69GLa'] = 'N/A';
        }
        $variables['data']['y9obJL9NAo'] = $tag_info['asset_model'];
        $lsd_data['VIN'] = $session['VIN'];
        $variables['data']['dyaoRcFcOD'] = $data['reason']; // GOOD
        // UPD YES -------------------------------------------------------------
        if (strtolower($data['upd']) === 'yes') {

            $variables['data']['ox__1hiShH']['label'] = $data['insurance'];
            // DATE REPORTED
            $date = new DateTime($data['date_reported']);
            $new_date = $date->format('m/d/Y');
            $variables['data']["4Zogjk4pQu"] = $new_date;
            //
            if (isset($data['authorized']) && !empty($data['authorized'])) {
                $variables['data']['LzLr2MJkD1'] = $data['authorized'];
            }
            if (isset($data['security']) && !empty($data['security'])) {
                $variables['data']['iMnLnEYNVk'] = $data['security'];
            }

            $time_split = explode(':', $data['time_reported']);
            $seconds = (int)$time_split[0] * 3600 + (int)$time_split[1] * 60;
            $variables['data']['1CBE3qoL2S'] = $seconds;

            if (strtolower($data['insurance']) === 'yes') {
                $variables['data']['B4_rSiiFLc'] = $data['state'] ?? 'N/A';
                $variables['data']['4XDMj4Dg_M'] = $data['zip'] ?? 'N/A';
                $variables['data']['B7qvma1zkp'] = $data['city'] ?? 'N/A';
                $variables['data']['_IddsKM2a6'] = $data['street'] ?? 'N/A';
                $variables['data']['krQQJpyLAR'] = $data['company'] ?? 'N/A';
                $variables['data']['ox__1hiShH']['id'] = 'yes';
            } else {
                $variables['data']['ox__1hiShH']['id'] = 'no';
            }
            $variables['data']['zG7O4wyxU0']['label'] = $data['reported'];
            if ($variables['data']['zG7O4wyxU0']['label'] === 'Yes') {
                $variables['data']['zG7O4wyxU0']['id'] = 'yes';
                $variables['data']['UMKALbaGtJ'] = $data['explain'];
            }
            $variables['data']['SBIzD8D7Z0'] = $data['precautions'];
            $variables['data']['FOqDM2nFYU'] = $data['recovery_steps'];
            $variables['data']['Ak2ZRPlsUo']['label'] = $data['assigned_staff'];
            if ($data['assigned_staff'] === 'Yes') {
                $variables['data']['Ak2ZRPlsUo']['id'] = 'yes';
                $variables['data']['TY_xRD84-r'] = $data['who_assigned'];
            } else {
                $variables['data']['Ak2ZRPlsUo']['id'] = 'no';
            }
            // GET INFO
            $variables['data']['g06BrWDC42']['label'] = $data['secured'];
            if ($data['secured'] === 'Yes') {
                $variables['data']['g06BrWDC42']['id'] = 'yes';

                if (preg_match('/@/i', $data['access_keys'])) {
                    $email = explode('@', $data['access_keys']);
                    $access_keys = getEmailInfo($email[0], $audit_dept);
                } else {
                    $access_keys = getNameInfo($data['access_keys'], $audit_dept);
                }
                $variables['data']['Ctc-VTU0KG']['displayName'] = $access_keys['displayName'];
                $variables['data']['Ctc-VTU0KG']['email'] = $access_keys['email'];
                $variables['data']['Ctc-VTU0KG']['firstName'] = $access_keys['firstName'];
                $variables['data']['Ctc-VTU0KG']['id'] = $access_keys['id'];
                $variables['data']['Ctc-VTU0KG']['label'] = $access_keys['label'];
                $variables['data']['Ctc-VTU0KG']['lastName'] = $access_keys['lastName'];
                $variables['data']['Ctc-VTU0KG']['schoolId'] = $access_keys['schoolId'];
                $variables['data']['Ctc-VTU0KG']['username'] = $access_keys['username'];
            } else {
                $variables['data']['g06BrWDC42']['id'] = 'no';
            }

            $by_whom = getEmailInfo($data['by_whom'], $audit_dept);
            $variables['data']['KMAw0Ejpx6']['displayName'] = $by_whom['displayName'];
            $variables['data']['KMAw0Ejpx6']['email'] = $by_whom['email'];
            $variables['data']['KMAw0Ejpx6']['firstName'] = $by_whom['firstName'];
            $variables['data']['KMAw0Ejpx6']['id'] = $by_whom['id'];
            $variables['data']['KMAw0Ejpx6']['label'] = $by_whom['label'];
            $variables['data']['KMAw0Ejpx6']['lastName'] = $by_whom['lastName'];
            $variables['data']['KMAw0Ejpx6']['schoolId'] = $by_whom['schoolId'];
            $variables['data']['KMAw0Ejpx6']['username'] = $by_whom['username'];

            $variables['data']['J8QRY5L38L'] = $data['upd_location'];

            $time_split = explode(':', $data['time_last_seen']);
            $seconds = (int)$time_split[0] * 3600 + (int)$time_split[1] * 60;
            $variables['data']['h2_zTuDZQd'] = $seconds;
            $date = new DateTime($data['date_last_seen']);
            $new_date = $date->format('m/d/Y');
            $variables['data']['1SSyW5r5fB'] = $new_date;
        }
        // ---------------------------------------------------------------------------------------
        // IT EQUIPMENT YES
        if ($data['item_type'] === 'IT Equipment') {
            $variables['data']['soVHBJJb_4'] = $data['describe_asset'];
            $variables['data']['ZfhX3CCX7D']['label'] = $data['encrypted'];
            if ($data['encrypted'] === 'Yes') {
                $variables['data']['ZfhX3CCX7D']['id'] = 'yes';
                $variables['data']['8YYaqGi1u4'] = $lsd_data['encrypted_data'];
            } else {
                $variables['data']['ZfhX3CCX7D']['id'] = 'no';
            }
            $variables['data']['TC9A_cNoXu']['label'] = $data['confidential'];
            if ($data['confidential'] === 'Yes') {
                $variables['data']['lDIEb-U1m9'] = $data['confidential_data'];
                $variables['data']['TC9A_cNoXu']['id'] = 'yes';
            } else {
                $variables['data']['TC9A_cNoXu']['id'] = 'no';
            }
        }
        // ---------------------------------------------------------------------------------------
        break;
    }
}

$dept_id =  (isset($data['dept_id']) && !empty($data['dept_id'])) ? $data['dept_id'] : $_SESSION['info'][2];

// SUBMITTER INFO
$email = $_SESSION['email'];
$submitter_info = $query_repo->getUserInfo($email);

$display_name = $submitter_info['username'];
$full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
$school_id = $submitter_info['school_id'] ?? '';
$signature = $submitter_info['signature'] ?? $full_name;
$form_id = $submitter_info['form_id'] ?? '';
$email_array = explode('@', $email);
if (empty($school_id) || empty($form_id)) {
    searchEmail($email_array[0], $dept_id);
    $submitter_info = $query_repo->getUserInfo($email);
    $display_name = $submitter_info['username'];
    $full_name = $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
    $school_id = $submitter_info['school_id'] ?? '';
    $signature = $submitter_info['signature'] ?? $full_name;
    $form_id = $submitter_info['form_id'] ?? '';
}

$dept_info = $query_repo->getDeptData($dept_id);

$dept_name = $dept_info['dept_name'];
$manager = trim($dept_info['dept_manager']);

$manager_info = getNameInfo($manager, $audit_dept);
$variables['data']['0Qm43mG2vV']['displayName'] = $manager_info['displayName'];
$variables['data']['0Qm43mG2vV']['email'] = $manager_info['email'];
$variables['data']['0Qm43mG2vV']['firstName'] = $manager_info['firstName'];
$variables['data']['0Qm43mG2vV']['id'] = $manager_info['id'];
$variables['data']['0Qm43mG2vV']['lastName'] = $manager_info['lastName'];
$variables['data']['0Qm43mG2vV']['schoolId'] = $manager_info['schoolId'];
$variables['data']['0Qm43mG2vV']['username'] = $manager_info['username'];

$submitter_sig = getEmailInfo($email, $_SESSION['deptid']);

if (!empty($data['borrower'])) {
    // GET BORROWER INFO FROM getSignature();
    if (preg_match('/@/i', $data['borrower'])) {
        $email = explode('@', $data['borrower']);
        $borrower_signature = getEmailInfo($email[0], $audit_dept);
    } else {
        $borrower_signature = getNameInfo($data['borrower'], $audit_dept);
    }
    $variables['data']["N00EmVKFnd"]['displayName'] = $borrower_signature['displayName'];
    $variables['data']["N00EmVKFnd"]['email'] = $borrower_signature['email'];
    $variables['data']["N00EmVKFnd"]['firstName'] = $borrower_signature['firstName'];
    $variables['data']["N00EmVKFnd"]['id'] = $borrower_signature['id'];
    $variables['data']["N00EmVKFnd"]['label'] = $borrower_signature['label'];
    $variables['data']["N00EmVKFnd"]['lastName'] = $borrower_signature['lastName'];
    $variables['data']["N00EmVKFnd"]['schoolId'] = $borrower_signature['schoolId'];
    $variables['data']["N00EmVKFnd"]['username'] = $borrower_signature['username'];
}
$upd_id = match ($data['upd']) {
    "No" => "CbModhwutSo",
    "Yes" => "YU12SPShKnx"
};

$item_type_id = match ($data['item_type']) {
    "Instructional Equipment" => "iZ6HWywjL",
    "IT Equipment" => "Ycmcbo5hp",
    "Other" => "813J2qxw1"
};

$lsd_id = match ($data['lsd']) {
    "Lost" => "bqRxkqovw",
    "Stolen" => "fmp7EdgUx",
    "Destroyed" => "-rR6VXHWp"
};
$who = match ($data['who']) {
    'Myself' => 'Myself',
    'someone-else' => 'I am initiating this submission on behalf of'
};
$lsd_who_id = match ($who) {
    'Myself' => 'w-25nbYAp',
    'Someone Else' => 'SDqr0xnNfnM'
};
$date = new DateTime($data['date_discovered']);
$date_discovered = $date->format('m/d/Y');

$variables['data']['Sg2RTLnC5r']['id'] = $lsd_who_id;
$variables['data']['fy16ygj_ST'] = $date_discovered;
$variables['data']['Sg2RTLnC5r']['label'] = $who;
$variables['data']["9eJvzLeMS0"]['id'] = "9JrVQuqdIQS";
$variables['data']["9eJvzLeMS0"]["label"] = "Staff / Faculty";
$variables['data']["6lJyeq9g1v"]['id'] = $item_type_id;
$variables['data']["6lJyeq9g1v"]["label"] = $data['item_type'];
// REPORTED TO UPD?
$variables['data']["7BHQb4jTbS"]['id'] = $upd_id;
$variables['data']["7BHQb4jTbS"]["label"] = $data['upd'];
// SERIAL NUMBER
$variables['data']["7Gzhcg_35S"] = $data['Serial ID'];
// SUBMITTER SIGNATURE
$variables['data']["EeUWxyyaOUR"] = $submitter_sig;
// DEPT IF STAFF
$variables['data']["GOiwf3tjc0"]['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']["GOiwf3tjc0"]['data']['IOw4-l7NsM'] = $dept_id;
$variables['data']["GOiwf3tjc0"]['label'] = $dept_name;
// MAKE
$variables['data']["Qb1ac69GLa"] = $data['Make'] ?? 'N/A';
// LSD
$variables['data']["Sc5_swYeHS"]['id'] = $lsd_id;
$variables['data']["Sc5_swYeHS"]['label'] = $data['lsd'];
// NARRATIVE
$variables['data']["dyaoRcFcOD"] = $data['reason'];
// DESCR
$variables['data']["pNvpNnuav8"] = $data['Descr'];
// TAG
$variables['data']["y7nFCmsLEg"] = $data['tag'];
// MODEL
$variables['data']["y9obJL9NAo"] = $data['Model'] ?? 'N/A';
// DATE MISSING
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$current_date = $date->format('m/d/Y');
$variables['data']["MiLvvsoH5a"] = $current_date;
// CURRENT DATE
$variables['data']["vedcAP4N1t"] = $current_date;
// DATE MISSING
$variables['data']["MiLvvsoH5a"] = $current_date;
// CURRENT DATE
$variables['data']["vedcAP4N1t"] = $current_date;

$ms_time = round(microtime(true) * 1000);

$resp_data = $kuali->writeToKuali("68d09e41d599f1028a9b9457", $variables);
$decoded = json_decode($resp_data, true);
$tag = $data['tag'];
$input_array =  $decoded['document_id'] . ',lsd,in-progress,' . trim($tag);


$audit_id = $data['audit_id'];
$update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ?) WHERE dept_id = ? AND audit_id = ?";
if ($decoded['status'] === 'Ok') {
    $query_repo->execute($update, $input_array, $dept_id, $audit_id);
    echo json_encode(['status' => 'Loss/Stolen/Dmg Ok']);
} else {
    echo json_encode(['status' => 'Loss/Stolen/Dmg Failed', 'data' => $resp_data]);
}
exit;
