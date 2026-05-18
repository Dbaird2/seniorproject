<?php
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once "search.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$transfer_data = [[]];
$variable = [[]];
$index = 0;
$its = false;
$cmp = $spa = $stu = $fdn = $asi = false;
foreach ($data['psr_tags'] as $index => $tag) {
    foreach ($_SESSION['data'] as $session) {
        if ($session['Tag Number'] === $tag['tag']) {
            $select = "SELECT type2 FROM asset_info WHERE asset_tag = :tag";
            $type = $query_repo->fetchColumn($select, $tag['tag']);
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

$email = $_SESSION['email'];
$submitter_info = $query_repo->getCustInfo($email);

$apikey = $submitter_info['kuali_key'] ?? '';

if (empty($submitter_info)) {
    searchName($submitter_info['f_name'] . ' ' . $submitter_info['l_name'], $dept_id);
    $submitter_info = $query_repo->getCustInfo($email);
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
    "displayName" => $full_name,
    "email" => $_SESSION['email'],
    "firstName" => $first_name,
    "id" => $form_id,
    "label" => $full_name,
    "lastName" => $last_name,
    "schoolId" => $school_id,
    "username" => $display_name
];

/* CUSTODIAN INFO */
$get_custodian = "SELECT unnest(custodian) FROM department WHERE dept_id = :dept";

$dept_custodian = $query_repo->fetchColumn($get_custodian, $_SESSION['info'][2]);

$email = $_SESSION['email'];
$custodian_info = $query_repo->getCustInfo($dept_custodian);

if (!$custodian_info) {
    searchName($dept_custodian, $dept_id);
    $custodian_info = $query_repo->getCustInfo($dept_custodian);
}
if (empty($school_id) || empty($form_id)) {
    searchName($full_name, $dept_id);
    $custodian_info = $query_repo->getCustInfo($email);
}

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

$manager_info = $query_repo->getDeptData($dept_id);

$dept_name = $manager_info['dept_name'];
$manager = $manager_info['dept_manager'];

try {
    $mana_info = $query_repo->getCustInfo($manager);
    if (!$manager_info) {
        searchName($manager, $dept_id);
        $mana_info = $query_repo->getCustInfo($manager);
    }
    if (empty($manager_info['form_id']) || empty($manager_info['school_id'])) {
        /* SEARCH CUST IN KUALI */
        searchName($manager, $dept_id);
        $mana_info = $query_repo->getCustInfo($manager);
    }
} catch (PDOException $e) {
    /* CUST DID NOT MATCH EXACTLY */
    searchName($manager, $dept_id);
    $mana_info = $query_repo->getCustInfo($manager);
    /* SEARCH CUST IN KUALI */
}
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
        'DONATION TO AN ELIGIBLE PUBLIC SCHOOL, PUBLIC SCHOOL DISTRICT OR ELIGIBLE ORGANIZATION (SEE SAM SECTION 3520.5)' => 'v0Lk5047Y',
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

$variables = [[]];
$variables['data'][$custodian_kuali_key] = $custodian_kuali;
$variables['data'][$bus_units_key] = $bus_units;
$variables['data']["COwZg-7nwQ"]['id'] = $form_type_id;
$variables['data']["COwZg-7nwQ"]['label'] = $form_type_label;
$variables['data']['tc1F0ohejI']['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']['tc1F0ohejI']['data']['IOw4-l7NsM'] = $_SESSION['info'][2];
$variables['data']['tc1F0ohejI']['label'] = $dept_name;
$variables['data'][$manager_kuali_key] = $manager_kuali;
$variables['data']['W_Uw0hSpff'] = $json_form;

$resp_data = $kuali->writeToKuali("68d09dcd7688dc028af9b5e7", $variables);
$decoded = json_decode($resp_data, true);

if ($decoded['submitDocument'] === 'Ok') {
    echo json_encode(['status' => 'Property Survery Report Ok']);
} else {
    echo json_encode(['status' => 'Property Survey Report Failed', 'res' => $resp_data]);
}
exit;
