<?php
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once "search.php";
include_once "get-info.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (!isset($_POST)) {
    die("Not submitted yet.");
}
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
/* DATA FROM POST */
/* CHECK IN OR OUT */
$form_type = trim($data['form_type']);
/* MYSELF/SOMEONEELSE */
$who = trim($data['who']);
$note = trim($data['notes']);
$audit_dept = trim($data['dept_id']);
$condition = trim($data['condition']);
$tag = $data['tag'];
$asset_type = trim($data['item_type']);
$now_array = new DateTime();
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

/* SERIAL */
$tag_info = 'SELECT serial_num, asset_name, type2 FROM asset_info WHERE asset_tag = ?';
$tag_data = $query_repo->fetchOne($tag_info, $tag);
$serial = $tag_data['serial_num'] ?? 'N/A';
$asset_name = $tag_data['asset_name'] ?? 'Unknown Asset';

/* DEPT INFO */
$dept_id = $_SESSION['deptid'];
$dept_data = $query_repo->getDeptData($dept_id);

$dept_name = $dept_data['dept_name'] ?? 'Unknown Department';
$documentsetid = $dept_data['document_set_id'] ?? '';
$variables['data']['isFMbCuv8e']['data']['AkMeIWWhoj'] = $dept_name;
$variables['data']['isFMbCuv8e']['data']['IOw4-l7NsM'] = $dept_id;
$variables['data']['isFMbCuv8e']['label'] = $dept_name;

$kuali_id = $dept_data['form_id'] ?? '';
if (!empty($kuali_id) && !empty($documentsetid)) {
    $variables['data']['isFMbCuv8e']['documentSetId'] = $documentsetid;
    $variables['data']['isFMbCuv8e']['label'] = $dept_name;
}
/* --------------------------------- */

$location = "Bakersfield";
$variables['data']['XE0n2IZXBC'] = $location;

$street = "9001 Stockdale Hwy";
$variables['data']['Smva-ICjnV'] = $street;

$condition_id = match ($condition) {
    "New" => "PMMV9ld3ML",
    "Good" => "uPq0cgV51",
    "Used" => "2zmA7sZQnX",
    "Damaged" => "s0MNB7p9vx"
};
$variables['data']['UTQZbrKiio']['id'] = $condition_id;
$variables['data']['UTQZbrKiio']['label'] = $condition;

$asset_type_id = match ($asset_type) {
    "Laptop" => "VMjSpx4-H",
    "Desktop" => "UHFK_j1G7L",
    "Tablet" => "-wWkrsS_A_"
};
$variables['data']['aUVT1BLN6V']['id'] = $asset_type_id;
$variables['data']['aUVT1BLN6V']['label'] = $asset_type;

if ($who === 'someone-else') {
    $who = 'Someone Else';
}
$who_id = match ($who) {
    "Myself" => "fK-8m6dzx",
    "Someone Else" => "y89ptC2TA"
};
$variables['data']['e0fZiLYomu']['id'] = $who_id;
$variables['data']['e0fZiLYomu']['label'] = $who;

$form_type = match ($form_type) {
    "check-in" => "Returning Equipment",
    "check-out" => "Checking Out Equipment"
};

$form_type_id = match ($form_type) {
    "Returning Equipment" => "z0IRqD2_Z",
    "Checking Out Equipment" => "Nwnp1xzbH"
};

$variables['data']['fyaCF8g3Uh']['id'] = $form_type_id;
$variables['data']['fyaCF8g3Uh']['label'] = $form_type;

$date = new DateTime();
$date->setTimezone(new DateTimeZone('America/Los_Angeles'));
$select_manager = 'SELECT dept_manager FROM department WHERE dept_id = :dept';
if ($who !== 'Myself') {

    $manager = $query_repo->fetchColumn($select_manager, $audit_dept);
    $manager_info = getNameInfo($manager, $audit_dept);
    $variables['data']['FOTHZUATua']['displayName'] = $manager_info['displayName'];
    $variables['data']['FOTHZUATua']['email'] = $manager_info['email'];
    $variables['data']['FOTHZUATua']['firstName'] = $manager_info['firstName'];
    $variables['data']['FOTHZUATua']['id'] = $manager_info['id'];
    $variables['data']['FOTHZUATua']['label'] = $manager_info['label'];
    $variables['data']['FOTHZUATua']['lastName'] = $manager_info['lastName'];
    $variables['data']['FOTHZUATua']['schoolId'] = $manager_info['schoolId'];
    $variables['data']['FOTHZUATua']['username'] = $manager_info['username'];

    $borrower = trim($data['borrower']);
    $borrowers_info = getNameInfo($borrower, $audit_dept);
    $get_dept_name = "SELECT dept_name FROM department WHERE dept_id = ?";
    $new_dept_name = $query_repo->fetchColumn($get_dept_name, $data['dept_id']);
    if ($new_dept_name) {
        $variables['data']['isFMbCuv8e']['data']['AkMeIWWhoj'] = $new_dept_name;
        $variables['data']['isFMbCuv8e']['data']['IOw4-l7NsM'] = $audit_dept;
        $variables['data']['isFMbCuv8e']['label'] = $new_dept_name;
    }

    $variables['data']['J06VDujK2F']['displayName'] = $borrowers_info['displayName'];
    $variables['data']['J06VDujK2F']['email'] = $borrowers_info['email'];
    $variables['data']['J06VDujK2F']['firstName'] = $borrowers_info['firstName'];
    $variables['data']['J06VDujK2F']['id'] = $borrowers_info['id'];
    $variables['data']['J06VDujK2F']['label'] = $borrowers_info['label'];
    $variables['data']['J06VDujK2F']['lastName'] = $borrowers_info['lastName'];
    $variables['data']['J06VDujK2F']['schoolId'] = $borrowers_info['schoolId'];
    $variables['data']['J06VDujK2F']['username'] = $borrowers_info['username'];
} else {
    /*---------------------------------*/
    /* SIGNATURE */
    $submitter_info = getSubmitterSig();
    $check_type_date = $date->format('m/d/Y');
    // $variables['data']['JXLJ_AOov-']['actionId'] = $action_id;
    $variables['data']['JXLJ_AOov-']['date'] = $now;
    $variables['data']['JXLJ_AOov-']['displayName'] = $submitter_info['displayName'];
    $variables['data']['JXLJ_AOov-']['signatureType'] = 'type';
    $variables['data']['JXLJ_AOov-']['signedName'] = $submitter_info['signature'];
    $variables['data']['JXLJ_AOov-']['userId'] = $submitter_info['userId'];
    /*---------------------------------*/
    $manager = $query_repo->fetchColumn($select_manager, $dept_id);

    $manager_info = getNameInfo($manager, $dept_id);
    $variables['data']['FOTHZUATua']['displayName'] = $manager_info['displayName'];
    $variables['data']['FOTHZUATua']['email'] = $manager_info['email'];
    $variables['data']['FOTHZUATua']['firstName'] = $manager_info['firstName'];
    $variables['data']['FOTHZUATua']['id'] = $manager_info['id'];
    $variables['data']['FOTHZUATua']['label'] = $manager_info['label'];
    $variables['data']['FOTHZUATua']['lastName'] = $manager_info['lastName'];
    $variables['data']['FOTHZUATua']['schoolId'] = $manager_info['schoolId'];
    $variables['data']['FOTHZUATua']['username'] = $manager_info['username'];
}
$custodian = "SELECT unnest(custodian) AS custodian FROM department WHERE dept_id = :dept_id LIMIT 1";
$custodian_name = $query_repo->fetchColumn($custodian, $data['dept_id']);

$custodian_info = getNameInfo($custodian_name, $audit_dept);
$check_type_date = $date->format('m/d/Y');
if ($form_type === 'Returning Equipment') {
    $variables['data']['73dNIwQS0c'] = $check_type_date;

    $variables['data']['_fBI_Ezliu']['displayName'] = $custodian_info['displayName'];
    $variables['data']['_fBI_Ezliu']['email'] = $custodian_info['email'];
    $variables['data']['_fBI_Ezliu']['firstName'] = $custodian_info['firstName'];
    $variables['data']['_fBI_Ezliu']['id'] = $custodian_info['id'];
    $variables['data']['_fBI_Ezliu']['label'] = $custodian_info['label'];
    $variables['data']['_fBI_Ezliu']['schoolId'] = $custodian_info['schoolId'];
    $variables['data']['_fBI_Ezliu']['username'] = $custodian_info['username'];
} else {
    $variables['data']['-StvOCXWsX'] = $check_type_date;

    $variables['data']['NdN80WJusb']['displayName'] = $custodian_info['displayName'];
    $variables['data']['NdN80WJusb']['email'] = $custodian_info['email'];
    $variables['data']['NdN80WJusb']['firstName'] = $custodian_info['firstName'];
    $variables['data']['NdN80WJusb']['id'] = $custodian_info['id'];
    $variables['data']['NdN80WJusb']['label'] = $custodian_info['label'];
    $variables['data']['NdN80WJusb']['schoolId'] = $custodian_info['schoolId'];
    $variables['data']['NdN80WJusb']['username'] = $custodian_info['username'];
}

$variables['data']['0LZvRo9vT5'] = $note;
$variables['data']['BOZIA6hewQ'] = $tag;
$variables['data']['cQOz4UQ0rQ'] = $asset_name;
$variables['data']['jYTHHgL10M'] = $serial;

$resp_data = $kuali->writeToKuali("68bf09aaadec5e027fe35187", $variables);
$decoded = json_decode($resp_data, true);
if ($decoded['status'] === 'Ok') {
    echo json_encode(['status' => 'Check in/out Ok']);
} else {
    echo json_encode(['status' => 'Check in/out Failed', 'data' => $decoded]);
}
exit;
