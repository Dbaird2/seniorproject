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
if (empty($data)) {
    die("No data");
}
echo json_encode(['tags'=>$data]);
$variables = [[]];
$email = $_SESSION['email'];
$audit_dept = $data['dept_id'];

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
$name_select = "SELECT school_id, form_id, f_name, l_name, email, signature FROM user_info WHERE CONCAT(f_name, ' ', l_name) = :name";
$dept_select = 'SELECT dept_manager FROM department WHERE dept_id = :id';

if ($data['form_type'] === 'dept') {
    $by_dept = [];
    foreach ($data['tags'] as $asset) {
        $by_dept[$asset['dept_name']]['tags'][] = ['tag' => $asset['tag'], 'why' => $asset['why'], 'in_bldg'=>$asset['in_bldg'],'where'=>$asset['where'],'bldg'=>$asset['bldg'], 'room'=>$asset['room'], 'dept_name'=>$asset['dept_name']];
    }

    foreach ($by_dept as $dept_name => $depts) {
        foreach ($depts as $dept) {
            foreach ($dept as $index => $asset) {
                $it_select = "SELECT type2, serial_num, asset_name, bus_unit from asset_info WHERE asset_tag = :tag";
                $it_stmt = $dbh->prepare($it_select);
                $it_stmt->execute([":tag"=>$asset['tag']]);
                $it_related = $it_stmt->fetch(PDO::FETCH_ASSOC);
                $variables['data']['t7mH-1FlaO']['data'][$index]['data']["pwkDQndmwN"] = $it_related['asset_name'];
                if (!empty($asset['notes'])) {
                    $variables['data']['t7mH-1FlaO']['data'][0]['data']['WzqON1QbTK'] = $asset['notes'];
                }
                if (in_array($it_related['type2'], ['Laptop', 'Tablet', 'Desktop'])) {
                    $variables['data']['xPQtXjuWnk']['id'] = 'yes';
                    $variables['data']['xPQtXjuWnk']['label'] = 'Yes';
                    $it = true;
                } 
               
                $variables['data']['t7mH-1FlaO']['data'][$index]['data']["XZlIFEDX6Y"] = $asset['tag'];
                if ($asset['in_bldg'] === 'Yes') {
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['id'] = 'yes';
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['label'] = $asset['in_bldg'];
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['qtAPPojYXt'] = $asset['where'];
                } else {
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['id'] = 'no';
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['label'] = $asset['in_bldg'];
                }
                if (!empty($asset['room']) && !empty($asset['bldg'])) {
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['WzqON1QbTK'] = $asset['bldg'];
                    $variables['data']['t7mH-1FlaO']['data'][$index]['data']['zZztPX8Pcw'] = $asset['room'];
                }
                $variables['data']['K3p03X2Jvx'] = $asset['why'];
                $get_dept_info = "SELECT dept_manager, dept_id FROM department WHERE dept_name = :dept";
                $dept_stmt = $dbh->prepare($get_dept_info);
                $dept_stmt->execute([':dept'=>$asset['dept_name']]);
                $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
                $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['label'] = $asset['dept_name'];
                $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['data']['AkMeIWWhoj'] = $asset['dept_name'];
                $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['data']['IOw4-l7NsM'] = $dept_info['dept_id'];
                $variables['data']['t7mH-1FlaO']['data'][$index]['id'] = $index.'';
            }
        }
        if (!$it) {
            $variables['data']['xPQtXjuWnk']['id'] = 'no';
            $variables['data']['xPQtXjuWnk']['label'] = 'No';
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
        if (!empty($data['audit_id'])) {
            $audit_id = $data['audit_id'];
            $dept = $data['dept_id'];
            $input_array = $document_id . ',rtransfer,in-progress'; 
            foreach ($data['tags'] as $tag_info) {
                $input_array .= ',' . $tag_info['tag'];
            }
            $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, :array) WHERE dept_id = :dept AND audit_id = :id";
            $update_stmt = $dbh->prepare($update);
            $update_stmt->execute([':array'=>$input_array, ":dept"=>$dept_id, ":id"=>$audit_id]);
        }
        curl_close($curl);
    }
} else if ($data['form_type'] === 'location') {
    foreach ($data['tags'] as $index=>$asset) {
        $variables['data']['t7mH-1FlaO']['data'][$index]['id'] = $index.'';
        $it_select = "SELECT type2, serial_num, asset_name, bus_unit from asset_info WHERE asset_tag = :tag";
        $it_stmt = $dbh->prepare($it_select);
        $it_stmt->execute([":tag"=>$asset['tag']]);
        $it_related = $it_stmt->fetch(PDO::FETCH_ASSOC);
        $variables['data']['t7mH-1FlaO']['data'][$index]['data']["pwkDQndmwN"] = $it_related['asset_name'];
        if (!empty($asset['notes'])) {
            $variables['data']['t7mH-1FlaO']['data'][0]['data']['WzqON1QbTK'] = $asset['notes'];
        }
        if (in_array($it_related['type2'], ['Laptop', 'Tablet', 'Desktop'])) {
            $variables['data']['xPQtXjuWnk']['id'] = 'yes';
            $variables['data']['xPQtXjuWnk']['label'] = 'Yes';
            $it = true;
        } 
        $variables['data']['t7mH-1FlaO']['data'][$index]['data']["XZlIFEDX6Y"] = $asset['tag'];
        if ($asset['in_bldg'] === 'Yes') {
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['id'] = 'yes';
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['label'] = $asset['in_bldg'];
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['qtAPPojYXt'] = $asset['where'];
        } else {
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['id'] = 'no';
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['93UQc2my9e']['label'] = $asset['in_bldg'];
        }
        if (!empty($asset['room']) && !empty($asset['bldg'])) {
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['WzqON1QbTK'] = $asset['bldg'];
            $variables['data']['t7mH-1FlaO']['data'][$index]['data']['zZztPX8Pcw'] = $asset['room'];
        }
        $variables['data']['K3p03X2Jvx'] = $asset['why'];
        $get_dept_info = "SELECT dept_manager, dept_id FROM department WHERE dept_name = :dept";
        $dept_stmt = $dbh->prepare($get_dept_info);
        $dept_stmt->execute([':dept'=>$asset['dept_name']]);
        $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
        $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['label'] = $asset['dept_name'];
        $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['data']['AkMeIWWhoj'] = $asset['dept_name'];
        $variables['data']['t7mH-1FlaO']['data'][$index]['data']["U73d7kPH5b"]['data']['IOw4-l7NsM'] = $dept_info['dept_id'];
    }
    if (!$it) {
        $variables['data']['xPQtXjuWnk']['id'] = 'no';
        $variables['data']['xPQtXjuWnk']['label'] = 'No';
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
    $variables['data']['ne3KPx1Wy3'] = $submitter_sig;

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
    if (!empty($data['audit_id'])) {
        $audit_id = $data['audit_id'];
        $dept = $data['dept_id'];
        $input_array = $document_id . ',rtransfer,in-progress'; 
        foreach ($data['tags'] as $tag_info) {
            $input_array .= ',' . trim($tag_info['tag']);
        }
        $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, :array) WHERE dept_id = :dept AND audit_id = :id";
        $update_stmt = $dbh->prepare($update);
        $update_stmt->execute([':array'=>$input_array, ":dept"=>$data['dept_id'], ":id"=>$data['audit_id']]);
    }
    curl_close($curl);
}


echo json_encode(['form'=>$submit_form, 'resp data'=>$resp_data]);
exit;


