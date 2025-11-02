<?php
include_once "../../config.php";
include_once "../../vendor/autoload.php";
include_once "search.php";
include_once "get-info.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
if ($data['form'] === 'check-in') {
    checkOut($data);
} else if ($data['form'] === 'check-out') {
    checkOut($data);
} else if ($data['form'] === 'transfer') {
    transfer($data);
}

function checkIn($data) {
    global $dbh;
}

function checkOut($data) {
    global $dbh;
    /* CHECK IN OR OUT */
    $form_type = trim($data['form']);
    /* MYSELF/SOMEONEELSE */
    $who = trim($data['who']);
    $note = trim($data['notes']);
    $condition = trim($data['condition']);
    if (!empty(trim($data['borrower']))) {
        $select = "SELECT dept_id[1] FROM user_table WHERE CONCAT(f_name, ' ', l_name) ILIKE :borrower";
        $stmt = $dbh->prepare($select);
        $stmt->execute([':borrower'=>'%'.$data['borrower'].'%']);
        $audit_dept = $stmt->fetchColumn();
    } else {
        $audit_dept = $_SESSION['deptid'];
    }


    $tag = $data['tag'];
    $asset_type = trim($data['item_type']);
    $now_array = new DateTime();
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');

    /* SERIAL */
    $tag_info = 'SELECT serial_num, asset_name, type2 FROM asset_info WHERE asset_tag = :tag';
    $tag_stmt = $dbh->prepare($tag_info);
    $tag_stmt->execute([':tag' => $tag]);
    $tag_data = $tag_stmt->fetch(PDO::FETCH_ASSOC);
    $serial = $tag_data['serial_num'] ?? 'N/A';
    $asset_name = $tag_data['asset_name'] ?? 'Unknown Asset';

    /* DEPT INFO */
    $dept_id = $_SESSION['deptid'];
    $select_dept = 'SELECT dept_name, document_set_id, form_id FROM department WHERE dept_id = :dept_id';
    $dept_stmt = $dbh->prepare($select_dept);
    $dept_stmt->execute([':dept_id' => $_SESSION['deptid']]);
    $dept_data = $dept_stmt->fetch(PDO::FETCH_ASSOC);
    $dept_name = $dept_data['dept_name'] ?? 'Unknown Department';
    $documentsetid = $dept_data['document_set_id'] ?? '';
    $variables['data']['isFMbCuv8e']['data']['AkMeIWWhoj'] = $dept_name;
    $variables['data']['isFMbCuv8e']['data']['IOw4-l7NsM'] = $dept_id;
    $variables['data']['isFMbCuv8e']['label'] = $dept_name;
    $kuali_id = $dept_data['form_id'] ?? '';
    if (!empty($kuali_id) && !empty($documentsetid)) {
        $variables['data']['isFMbCuv8e']['documentSetId'] = $documentsetid;
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

    if ($who === 'someone-else'){
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
    if ($who !== 'Myself') {
        $borrower = trim($data['borrower']);
        $borrowers_info = getNameInfo($borrower, $audit_dept);
        echo "<pre>";
        var_dump($borrowers_info);
        echo "</pre>";
        $get_dept_name = "SELECT dept_name FROM department WHERE dept_id = :id";
        $stmt = $dbh->prepare($get_dept_name);
        $stmt->execute([':id'=>$audit_dept]);
        $new_dept_name = $stmt->fetchColumn();
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
        $variables['data']['JXLJ_AOov-']['actionId'] = $action_id;
        $variables['data']['JXLJ_AOov-']['date'] = $now;
        $variables['data']['JXLJ_AOov-']['displayName'] = $submitter_info['displayName'];
        $variables['data']['JXLJ_AOov-']['signatureType'] = 'type';
        $variables['data']['JXLJ_AOov-']['signedName'] = $submitter_info['signedName'];
        $variables['data']['JXLJ_AOov-']['userId'] = $submitter_info['userId'];
        /*---------------------------------*/
    }
    $custodian = "SELECT unnest(custodian) AS custodian FROM department WHERE dept_id = :dept_id LIMIT 1";
    $custodian_stmt = $dbh->prepare($custodian);
    $custodian_stmt->execute([':dept_id' => $audit_dept]);
    $custodian_name = $custodian_stmt->fetchColumn();
    echo 'Custodian ' . $custodian_name . '<br>';

    $custodian_info = getNameInfo($custodian_name, $audit_dept);
    echo json_encode(['custodian info' => $custodian_info, 'custodian name' => $custodian_name]);
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

    $variables['documentId'] = $document_id;
    $variables['actionId'] = $action_id;
    $variables['status'] = 'completed';
    $variables['data']['0LZvRo9vT5'] = $note;
    $variables['data']['BOZIA6hewQ'] = $tag;
    $variables['data']['cQOz4UQ0rQ'] = $asset_name;
    $variables['data']['jYTHHgL10M'] = $serial;
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
    { submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
    'variables' => $variables,
    ]);

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
}

function transfer($data) {
    global $dbh;
    $email = $_SESSION['email'];
    $audit_dept = $_SESSION['deptid'];

    $subdomain = "csub";
    // SUBMITTER INFO
    $select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
    $email = $_SESSION['email'];
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);
    $apikey = $submitter_info['kuali_key'];
    if (empty($apikey)) {
        die("API Key Not Found");
    }
    // IS THIS A (form type)
    $form_type_id = match($data['form_type']) {
    'bus' => 'BhQ_qXc6Tji',
        'dept' => '9A_6UOlDb',
        'location' => 'LfK1qG_G6'
    };
    $form_type = match($data['form_type']) {
    'bus' => 'Business Unit change (for example from BKSPA to BKCMP)',
        'dept' => 'From one department to another department ',
        'location' => 'Building/Room/Location change (Business Unit stays the same)'
    };
    $variables['data']['_GODY1FjEy']['id'] = $form_type_id;
    $variables['data']['_GODY1FjEy']['label'] = $form_type;

    // ASSETS
    $bus_id = function ($type) {
        $id = match ($type) {
        'BKCMP' => 'NLNTmkvx_u',
            'BKSPA' => 'ztmVBnRjT1',
            'BKSTU' => 'Duom3fxkyA',
            'BKFDN' => 'Xi6koaglZc',
            'BKASI' => 'E9lk-ahtpd',
    };
        return $id;
    };

    $it = false;

    // SUBMITTER NAME
    $select_sub = "SELECT f_name, l_name FROM user_table WHERE email = :email";
    $stmt = $dbh->prepare($select_sub);
    $stmt->execute([':email'=>$_SESSION['email']]);
    $submitter_name = $stmt->fetch();
    $variables['data']['VFp8qQLrUk'] = trim($submitter_name['f_name']) . ' ' . trim($submitter_name['l_name']); 

    $now = new DateTime();
    $variables['data']['R-jIGrtlfO'] = $now->format('m/d/Y');

    $email_select = "SELECT school_id, form_id, f_name, l_name, email, signature FROM user_info WHERE email = :email";
    $name_select = "SELECT school_id, form_id, f_name, l_name, email, signature FROM user_info WHERE CONCAT(f_name, ' ', l_name) ILIKE :name";
    $dept_select = 'SELECT dept_manager FROM department WHERE dept_id = :id';

    if ($data['in_bldg'] === 'Yes') {
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['id'] = 'yes';
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['label'] = $data['in_bldg'];
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['qtAPPojYXt'] = $data['where'];
    } else {
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['id'] = 'no';
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['93UQc2my9e']['label'] = $data['in_bldg'];
    }
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["XZlIFEDX6Y"] = $data['tag'];
    if (!empty($data['room']) && !empty($data['bldg'])) {
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['hXHmCy0mek']['label'] = $data['bldg'];
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['zZztPX8Pcw'] = $data['room'];
    }
    $variables['data']['t7mH-1FlaO']['data'][0]['id'] = '0';
    $it_select = "SELECT type2, serial_num, asset_name, bus_unit from asset_info WHERE asset_tag = :tag";
    $it_stmt = $dbh->prepare($it_select);
    $it_stmt->execute([":tag"=>$data['tag']]);
    $it_related = $it_stmt->fetch(PDO::FETCH_ASSOC);
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["pwkDQndmwN"] = $it_related['asset_name'];
    $variables['data']['t7mH-1FlaO']['data'][0]['data']["XZlIFEDX6Y"] = $data['tag'];
    $get_dept_info = "SELECT dept_manager, dept_id FROM department WHERE dept_name = :dept";
    if (!empty($data['notes'])) {
        $variables['data']['t7mH-1FlaO']['data'][0]['data']['WzqON1QbTK'] = $data['notes'];
    }
    if (in_array($it_related['type2'], ['Laptop', 'Tablet', 'Desktop'])) {
        $variables['data']['xPQtXjuWnk']['id'] = 'yes';
        $variables['data']['xPQtXjuWnk']['label'] = 'Yes';
        $it = true;
    } else {
        $variables['data']['xPQtXjuWnk']['id'] = 'no';
        $variables['data']['xPQtXjuWnk']['label'] = 'No';
    }
        $dept_stmt = $dbh->prepare($get_dept_info);
        $dept_stmt->execute([':dept'=>$data['dept_name']]);
        $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
    if ($data['form_type'] === 'dept') {

        $variables['data']['K3p03X2Jvx'] = $data['why'];
        $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['label'] = $data['dept_name'];
        $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['data']['AkMeIWWhoj'] = $data['dept_name'];
        $variables['data']['t7mH-1FlaO']['data'][0]['data']["U73d7kPH5b"]['data']['IOw4-l7NsM'] = $dept_info['dept_id'];
        if (!$it) {
            $variables['data']['xPQtXjuWnk']['id'] = 'no';
            $variables['data']['xPQtXjuWnk']['label'] = 'No';
        }
    }

    // GET CURRENT MANAGER INFO
    $stmt = $dbh->prepare($dept_select);
    $stmt->execute([':id'=>$_SESSION['deptid']]);
    $current_manager = $stmt->fetchColumn();
/*
$email_regex = '/\b(@)\b/i';
if (preg_match($email_regex, $
 */
    $current_manager_info = getNameInfo($current_manager, $audit_dept);
    $variables['data']['u7YkM8hmb-']['displayName'] = $current_manager_info['displayName'];
    $variables['data']['u7YkM8hmb-']['email'] = $current_manager_info['email'];
    $variables['data']['u7YkM8hmb-']['firstName'] = $current_manager_info['firstName'];
    $variables['data']['u7YkM8hmb-']['id'] = $current_manager_info['id'];
    $variables['data']['u7YkM8hmb-']['label'] = $current_manager_info['label'];
    $variables['data']['u7YkM8hmb-']['lastName'] = $current_manager_info['lastName'];
    $variables['data']['u7YkM8hmb-']['schoolId'] = $current_manager_info['schoolId'];
    $variables['data']['u7YkM8hmb-']['username'] = $current_manager_info['username'];


    // MANAGER
    $manager = trim($dept_info['dept_manager']);
    $manager_info = getNameInfo($manager, $dept_info['dept_id']);

    $variables['data']['SZ24nXDBVk']['displayName'] = $manager_info['displayName'];
    $variables['data']['SZ24nXDBVk']['email'] = $manager_info['email'];
    $variables['data']['SZ24nXDBVk']['firstName'] = $manager_info['firstName'];
    $variables['data']['SZ24nXDBVk']['id'] = $manager_info['id'];
    $variables['data']['SZ24nXDBVk']['label'] = $manager_info['label'];
    $variables['data']['SZ24nXDBVk']['lastName'] = $manager_info['lastName'];
    $variables['data']['SZ24nXDBVk']['schoolId'] = $manager_info['schoolId'];
    $variables['data']['SZ24nXDBVk']['username'] = $manager_info['username'];

    echo json_encode(['variables'=>$variables]);


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
    "appId": "68d09e38d599f1028a08969a"
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

    $submitter_sig = getEmailInfo($_SESSION['email'], $_SESSION['deptid']);
    $variables['data']['ne3KPx1Wy3']['actionId'] = $action_id;
    $variables['data']['ne3KPx1Wy3']['date'] = $submitter_sig['date'];
    $variables['data']['ne3KPx1Wy3']['displayName'] = $submitter_sig['displayName'];
    $variables['data']['ne3KPx1Wy3']['signatureType'] = 'type';
    $variables['data']['ne3KPx1Wy3']['signedName'] = $submitter_sig['signedName'];
    $variables['data']['ne3KPx1Wy3']['userId'] = $submitter_sig['userId'];

    $variables['documentId'] = $document_id;
    $variables['actionId'] = $action_id;
    $variables['status'] = 'completed';

    $ms_time = round(microtime(true) * 1000);
    $submit_form = json_encode([
        'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
    { submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
    'variables' => $variables,
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    $resp_data = json_decode($resp, true);
    echo json_encode(['data'=>$data]);
    curl_close($curl);


    echo json_encode(['form'=>$submit_form, 'resp data'=>$resp_data]);
    exit;


}
