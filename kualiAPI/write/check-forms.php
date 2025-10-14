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
/* DATA FROM POST */
/* CHECK IN OR OUT */
$form_type = $data['form_type'];
/* MYSELF/SOMEONEELSE */
$who = $data['who'];
$note = $data['notes'];
$condition = $data['condition'];
$tag = $data['tag'];
$asset_type = $data['item_type'];
$now_array = new DateTime();
$now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
$now = $now_array->format('Y-m-d\TH:i:s.v\Z');

/* SERIAL */
$tag_info = 'SELECT serial_num, asset_name, type2 FROM asset_info WHERE asset_tag = :tag';
$tag_stmt = $dbh->prepare($tag_info);
$tag_stmt->execute([':tag' => $tag]);
$tag_data = $tag_stmt->fetch(PDO::FETCH_ASSOC);
$serial = $tag_data['serial_num'] ?? 'N/A';
$asset_name = $tag_data['asset_name'] ?? 'Unknown Asset';

/* DEPT NAME */
$dept_id = $_SESSION['deptid'];
$select_dept = 'SELECT dept_name, document_set_id, form_id FROM department WHERE dept_id = :dept_id';
$dept_stmt = $dbh->prepare($select_dept);
$dept_stmt->execute([':dept_id' => $dept_id]);
$dept_data = $dept_stmt->fetch(PDO::FETCH_ASSOC);
$dept_name = $dept_data['dept_name'] ?? 'Unknown Department';
$documentsetid = $dept_data['document_set_id'] ?? '';
$kuali_id = $dept_data['form_id'] ?? '';
if (empty($kuali_id) || empty($documentsetid)) {
    $dept_key = 'isFMbCuv8e';
    $dept_info = [
        'data' => [
            'AkMeIWWhoj' => $dept_name,
            'IOw4-l7NsM' => $_SESSION['deptid']
        ],
        'label' => $dept_name
    ];
} else {
    $dept_key = 'isFMbCuv8e';
    $dept_info = [
        'data' => [
            'AkMeIWWhoj' => $dept_name,
            'IOw4-l7NsM' => $_SESSION['deptid']
        ],
        'id' => $kuali_id,
        'documentSetId' => $documentsetid,
        'label' => $dept_name
    ];
}

$location_key = "XE0n2IZXBC";
$location = "Bakersfield";
$street_key = "Smva-ICjnV";
$street = "9001 Stockdale Hwy";
$condition_id = match ($condition) {
"New" => "PMMV9ld3ML",
    "Good" => "uPq0cgV51",
    "Used" => "2zmA7sZQnX",
    "Damaged" => "s0MNB7p9vx"
};
$condition_key = 'UTQZbrKiio';
$kuali_condition = [
    "id" => $condition_id,
    "label" => $condition
];

$asset_type_id = match ($asset_type) {
"Laptop" => "VMjSpx4-H",
    "Desktop" => "UHFK_j1G7L",
    "Tablet" => "-wWkrsS_A_"
};
$asset_type_key = 'aUVT1BLN6V';
$kuali_asset_type = [
    "id" => $asset_type_id,
    "label" => $asset_type
];
if ($who === 'someone-else'){
    $who = 'Someone Else';
}
$who_id = match ($who) {
    "Myself" => "fK-8m6dzx",
    "Someone Else" => "y89ptC2TA"
};
$who_key = 'e0fZiLYomu';
$kuali_who = [
    "id" => $who_id,
    "label" => $who
];

$form_type = match ($form_type) {
"check-in" => "Returning Equipment",
    "check-out" => "Checking Out Equipment"
};

$form_type_id = match ($form_type) {
"Returning Equipment" => "",
    "Checking Out Equipment" => "Nwnp1xzbH"
};
$form_type_key = 'fyaCF8g3Uh';
$kuali_form_type= [
    'id' => $form_type_id,
    'label' => $form_type
];
/*-----------------------------------------------------------------------------*/
$select_key = "SELECT kuali_key FROM user_table WHERE email = :email";
$key_stmt = $dbh->prepare($select_key);
$key_stmt->execute([":email"=>$_SESSION['email']]);
$apikey = $key_stmt->fetchColumn();

$subdomain = 'csub';
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
$form_data = '{"query":"mutation ($appId: ID!) { initializeWorkflow(args: {id: $appId}) { actionId }}","variables":{
"appId": "68bf09aaadec5e027fe35187"
          }}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $form_data);

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
/*-----------------------------------------------------------------------------*/
$date = new DateTime();
$date->setTimezone(new DateTimeZone('America/Los_Angeles'));
if ($form_type === 'check-in') {

    $check_type_date_key = '73dNIwQS0c';
    $check_type_date = $date->format('m/d/Y');

    $custodian = "SELECT unnest(custodian) AS custodian FROM department WHERE dept_id = :dept_id";
    $custodian_stmt = $dbh->prepare($custodian);
    $custodian_stmt->execute([':dept_id' => $_SESSION['deptid']]);
    $custodian_data = $custodian_stmt->fetch(PDO::FETCH_ASSOC);
    $custodian_name = $custodian_data[0]['custodian'] ?? '';

    $get_custodian = "SELECT email, form_id, school_id, f_name, l_name, username FROM user_table WHERE CONCAT(f_name, ' ', l_name) = :fullname";
    $custodian_stmt = $dbh->prepare($get_custodian);
    $custodian_stmt->execute([':fullname' => $custodian_name]);
    if ($custodian_stmt->rowCount() <= 0) {
        searchName($custodian_name, $apikey, $dept_id);
        $custodian_stmt->execute([':fullname' => $custodian_name]);
    }
    $custodian_info = $custodian_stmt->fetch(PDO::FETCH_ASSOC);
    $cust_email_array = explode('@', $custodian_info['email']);
    if ($cust_email_array[0] !== $custodian_info['username']) {
        $update_user = 'UPDATE user_table SET username = :username WHERE email = :email';
        $update_stmt = $dbh->prepare($update_user);
        $update_stmt->execute([':username' => $cust_email_array[0], ":email" => $custodian_info['username'] . '@csub.edu']);
    }
    if (empty($custodian_info['form_id']) || empty($custodian_info['school_id'])) {
        searchEmail($custodian_info['email'], $apikey, $dept_id);
        $custodian_stmt->execute([':fullname' => $custodian_name]);
        $custodian_info = $custodian_stmt->fetch(PDO::FETCH_ASSOC);
    }

    $authority_key = '_fBI_Ezliu';
    $authority_info['_fBI_Ezliu'] = [
        'displayName' => $custodian_info['f_name'] . ' ' . $custodian_info['l_name'],
        'email' => $_SESSION['email'],
        'firstName' => $custodian_info['f_name'],
        'id' => $custodian_info['form_id'],
        'label' => $custodian_info['f_name'] . ' ' . $custodian_info['l_name'],
        'schoolId' => $custodian_info['school_id'],
        'username' => $custodian_info['username']
    ];
} else {
    $check_type_date_key = '-StvOCXWsX';
    $check_type_date = $date->format('m/d/Y');


    $manager_select = "SELECT dept_manager FROM department WHERE dept_id = :dept_id";
    $manager_stmt = $dbh->prepare($manager_select);
    $manager_stmt->execute([':dept_id' => $_SESSION['deptid']]);
    $manager_data = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    $manager_name = $manager_data['dept_manager'] ?? '';
    try {
        $get_manager = "SELECT email, form_id, school_id, f_name, l_name, username FROM user_table WHERE CONCAT(f_name, ' ', l_name) = :fullname";
        $manager_stmt = $dbh->prepare($get_manager);
        $manager_stmt->execute([':fullname' => $manager_name]);
        if ($manager_stmt->rowCount() <= 0) {
            searchName($manager_name, $apikey, $dept_id);
            $manager_stmt->execute([':fullname' => $manager_name]);
        }

    } catch (PDOException $e) {
        searchName($manager_name, $apikey, $dept_id);
        $manager_stmt->execute([':fullname' => $manager_name]);
        $manager_info = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $manager_info = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    $manager_email_array = explode('@', $manager_info['email']);
    if ($manager_email_array[0] !== $manager_info['username']) {
        $update_user = 'UPDATE user_table SET username = :username WHERE email = :email';
        $update_stmt = $dbh->prepare($update_user);
        $update_stmt->execute([':username' => $manager_email_array[0], ":email" => $manager_info['email']]);
    }
    if (empty($manager_info['form_id']) || empty($manager_info['school_id'])) {
        searchEmail($manager_info['email'], $apikey, $dept_id);
        $manager_stmt->execute([':fullname' => $manager_name]);
        $manager_info = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    }
    /* GET MANAGER ID FOR GRAPHQL */
    $authority_key = 'NdN80WJusb';
    $authority_info = [
        'displayName' => $manager_info['f_name'] . ' ' . $manager_info['l_name'],
        'email' => $manager_info['email'],
        'firstName' => $manager_info['f_name'],
        'id' => $manager_info['form_id'],
        'label' => $manager_info['f_name'] . ' ' . $manager_info['l_name'],
        'schoolId' => $manager_info['school_id'],
        'username' => $manager_info['username']
    ];
}
if ($who !== 'Myself') {
    $borrower = $data['borrower'];
    try {
        $get_borrower = "SELECT form_id, display_name, email, first_name, last_name, school_id, username FROM user_table WHERE CONCAT(first_name, ' ' , last_name) = :fullname";
        $borrower_stmt = $dbh->prepare($get_borrower);
        $borrower_stmt->execute([':fullname' => $borrower]);
        if ($borrower_stmt->rowCount() <= 0) {
            searchName($borrower, $apikey, $dept_id);
            $borrower_stmt->execute([':fullname' => $borrower]);
        }
        $borrower_info = $borrower_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        searchName($borrower, $apikey, $dept_id);
        $borrower_stmt->execute([':fullname' => $borrower]);
        $borrower_info = $borrower_stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (empty($borrower_info['form_id']) || empty($borrower_info['school_id'])) {
        searchName($borrower, $apikey, $dept_id);
        $borrower_stmt->execute([':fullname' => $borrower]);
        $borrower_info = $borrower_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $borrower_info['J06VDujK2F'] = [
        'displayName' => $borrower_info['f_name'] . ' ' . $borrower_info['l_name'],
        'email' => $borrower_info['email'],
        'firstName' => $borrower_info['f_name'],
        'id' => $borrower_info['form_id'],
        'label' => $borrower_info['display_name'],
        'lastName' => $borrower_info['l_name'],
        'schoolId' => $borrower_info['school_id'],
        'username' => $borrower_info['username']
    ];
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
    { submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
        'variables' => [
            'documentId' => $document_id,
            'data' => [
                /* NOTE */
                "0LZvRo9vT5" => $note,
                /* TAG */
                "BOZIA6hewQ" => $tag,
                /* MS TIME STAMP */
                $check_type_date_key => $check_type_date,
                /* MYSELF / SOMEONE ELSE */
                $who_key => $kuali_who,
                /* RETURNING / CHECK OUT */
                $form_type_key => $kuali_form_type,
                $location_key => $location,
                $street_key => $street,
                /* DESCRIPTION */
                "cQOz4UQ0rQ" => $asset_name,
                "jYTHHgL10M" => $serial,
                $dept_key => $dept_info,
                /* CONDITION */
                $condition_key => $kuali_condition,
                /* LAPTOP, TABLET, etc */
                $asset_type_key => $kuali_asset_type,
                /* MANAGER OR CUST */
                $authority_key => $authority_info,
                $borrower_key => $borrower_info
            ],
            'actionId' => $action_id,
            'status' => 'completed'
        ]
    ]);
} else {
    /*---------------------------------*/
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('America/Los_Angeles'));
    /* SIGNATURE */
    $get_sig_data = 'SELECT form_id, signature, school_id, f_name, l_name, username FROM user_table WHERE email = :email';
    $get_stmt = $dbh->prepare($get_sig_data);
    $get_stmt->execute([':email' => $_SESSION['email']]);
    $submitter_info = $get_stmt->fetch(PDO::FETCH_ASSOC);
    /* GET FIRST PART OF EMAIL TO USE FOR SEARCHING */
    $email_array = explode('@', $_SESSION['email']);
    if ($email_array[0] !== $submitter_info['username']) {
        $update_user = 'UPDATE user_table SET username = :username WHERE email = :email';
        $update_stmt = $dbh->prepare($update_user);
        $update_stmt->execute([':username' => $email_array[0], ":email" => $_SESSION['email']]);
    }
    if (empty($submitter_info['form_id']) || empty($submitter_info['school_id'])) {
        searchEmail($_SESSION['email'], $apikey, $dept_id);
        $get_stmt->execute([':email' => $_SESSION['email']]);
        $submitter_info = $get_stmt->fetch(PDO::FETCH_ASSOC);
    }

    $submitter_sig = $submitter_info['signature'] ?? $submitter_info['f_name'] . ' ' . $submitter_info['l_name'];
    $submitter_id = $submitter_info['school_id'];
    $submitter_username = $submitter_info['username'];
    $submitter_form_id = $submitter_info['form_id'];
    $submitter_first = $submitter_info['f_name'];
    $submitter_last = $submitter_info['l_name'];
    $submitter_sig_key = 'JXLJ_AOov-'; 
    $submitter_sig_info = [
        'actionId' => $action_id,
        'date' => $now,
        'displayName' => $submitter_first . ' ' . $submitter_last . ' (' . $_SESSION['email'] . ')',
        'signatureType' => 'type',
        'signedName' => $submitter_sig ?? $submitter_first . ' ' . $submitter_last,
        'userId' => $submitter_form_id
    ];
    $check_type_date = $date->format('m/d/Y');
    /*
    $variables['documentId'] = $document_id;
    $variables['data']['0LZvRo9vT5'] = $note;
    $variables['data']['BOZIA6hewQ'] = $tag;
    $variables['data'][$check_type_date_key] = $check_type_date;
    $variables['data'][$who_key] = $kuali_who;
    $variables['data'][$stree_key] = $street;
    $variables['data']['cQOz4UQ0rQ'] = $asset_name;
    $variables['data']['jYTHHgL10M'] = $serial;
    $variables['data'][$dept_key] = $dept_info;
    $variables['data'][$condition_key] = $kuali_condition;
    $variables['data'][$asset_type_key] = $kuali_asset_type;
    $variables['data'][$authority_key] = $authority_info;
    $variables['data'][$submitter_sig_key] = $sugmitter_sig_info;
*/
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
    { submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
        'variables' => [
            'documentId' => $document_id,
            'data' => [
                "0LZvRo9vT5" => $note,
                /* TAG */
                "BOZIA6hewQ" => $tag,
                /* MS TIME STAMP */
                $check_type_date_key => $check_type_date,
                /* MYSELF / SOMEONE ELSE */
                $who_key => $kuali_who,
                /* RETURNING / CHECK OUT */
                $form_type_key => $kuali_form_type,
                $location_key => $location,
                $street_key => $street,
                /* DESCRIPTION */
                "cQOz4UQ0rQ" => $asset_name,
                "jYTHHgL10M" => $serial,
                $dept_key => $dept_info,
                /* CONDITION */
                $condition_key => $kuali_condition,
                /* LAPTOP, TABLET, etc */
                $asset_type_key => $kuali_asset_type,
                /* MANAGER OR CUST */
                $authority_key => $authority_info,
                $submitter_sig_key => $submitter_sig_info
            ],
            'actionId' => $action_id,
            'status' => 'completed'
        ]
    ]);
    /*---------------------------------*/

}

/*-----------------------------------------------------------------------------*/
curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($curl);
curl_close($curl);

echo json_encode([
    'data' => $submit_form,
    'resp' => $resp
]);
exit;


