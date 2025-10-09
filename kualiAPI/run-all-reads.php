<?php
include_once __DIR__ . '/../config.php';
set_time_limit(300);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$select = "SELECT * FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['dw_bulk_time'] ?? 0;
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
    "query" => 'query (
        $appId: ID!
        $skip: Int!
        $limit: Int!
        $sort: [String!]
        $query: String
        $fields: Operator
) {
    app(id: $appId) {
    id name documentConnection(
        args: {
        skip: $skip
            limit: $limit
            sort: $sort
            query: $query
            fields: $fields
            }
            keyBy: ID
            ) {
                totalCount edges {
                node { id data meta } }
                    pageInfo { hasNextPage hasPreviousPage skip limit }
                }
            }
        }',
"variables" => [
    "appId" => "68c73600df46a3027d2bd386",
    "skip" => $raw_ms,
    "limit" => 100,
    "sort" => ["meta.createdAt"],
    "query" => "",
    "fields" => [
        "type" => "AND",
        "operators" => [
            [
                "field" => "meta.workflowStatus",
                "type" => "IS",
                "value" => "Complete"
            ]
        ]
    ]
]
]);
// $data = '{"query":"query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { app(id: $appId) { id name documentConnection( args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID ) { totalCount edges { node { id data meta } } pageInfo { hasNextPage hasPreviousPage skip limit } } }}","variables":{
//   "appId": "67e451d2cc3194027dfce429",
//   "skip": 0,
//   "limit": 25,
//   "sort": [
//     "meta.updatedAt"
//   ],
//   "query": "",
//   "fields": {
//     "type": "AND",
//     "operators": [
//       {
//         "field": "meta.workflowStatus",
//         "type": "IS",
//         "value": "Complete"
//       },
//       {
//         "field": "meta.updatedAt",
//         "type": "RANGE",
//         "min": ' . $highest_time . '
//       }
//     ]
//   }
// }}';
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
$resp2 = json_decode($resp);

$decode_true = json_decode($resp, true);
$edges = $decode_true['data']['app']['documentConnection']['edges'];


$count = 1;
$count2 = $raw_ms + 0;
try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];

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
            echo "<br>Tag " . $data['data']['RxpLOF3XrE']. "<br>";
            $dept_id = $data['data']['5c3qSm88bs'];
            if (!empty($data['data']['6JHs3W0-CL'])) {
                $room_loc = $data['data']['6JHs3W0-CL'];
            }
            $dept_id = substr($dept_id, 0, 6);
            echo $dept_id . "<br>";
            if (preg_match('/^D/', $dept_id)) {
                echo "<br>Dept Id Format Good<br>";
            }
            if (!empty($data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'])) {
                $bldg_id = $data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'];
                $bldg_name = $data['data']['bYpfsUDuZx']['data']['AkMeIWWhoj'];
            }
            if (!empty($data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'])) {
                $bldg_id = $data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'];
                if ($bldg_id === '39A') {
                    $bldg_id = 39;
                }

                $bldg_name = $data['data']['BC0E2hOKv3']['data']['AkMeIWWhoj'];
            }
            $bldg_id = (int)$bldg_id;
            // UPDATE DATABASE BASED OF KUALI
            if (!empty($bldg_id) && !empty($bldg_name)) {
                echo "<br>Bldg ID " . $bldg_id . "<br>";
                echo "<br>Bldg Name " . $bldg_name . "<br>";
                $select = "SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = :id";
                $id_stmt = $dbh->prepare($select);
                $id_stmt->execute([':id'=>$bldg_id]);
                $select = "SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_name = :name";
                $name_stmt = $dbh->prepare($select);
                $name_stmt->execute([':name'=>$bldg_name]);
                if ($id_stmt->rowCount() === 0) {
                    $insert = "INSERT INTO bldg_table (bldg_id, bldg_name) VALUES (:id, :name)";
                    $stmt = $dbh->prepare($insert);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Building Was NOT found adding building to database. Automatically Added Building<br>";
                    $id_stmt = $dbh->prepare($select);
                    $id_stmt->execute([':id'=>$bldg_id]);
                }

                $db_bldg = $id_stmt->fetch(PDO::FETCH_ASSOC);
                if ($bldg_id !== $db_bldg['bldg_id']) {
                    $update = "UPDATE bldg_table SET bldg_id = :id WHERE bldg_name = :name";
                    $stmt = $dbh->prepare($update);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Bldg id was different. Fixing<br>";
                }
                if ($bldg_name !== $db_bldg['bldg_name']) {
                    $update = "UPDATE bldg_table SET bldg_name = :name WHERE bldg_id = :id";
                    $stmt = $dbh->prepare($update);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Bldg name was different. Fixing<br>";
                }
            } else {
                echo "<br>Building name or id was not found skipping<br>";
                continue;
            }


            $room_tag_found = false;
            try{
                $select_q = "SELECT room_tag FROM room_table WHERE bldg_id = :bid AND room_loc = :rloc";
                $select_stmt = $dbh->prepare($select_q);
                $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                if ($select_stmt->rowCount() === 0) {
                    $insert = "INSERT INTO room_table (room_loc, bldg_id) VALUES (:rloc, :bid)";
                    $insert_stmt = $dbh->prepare($insert);
                    $insert_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                    echo "<br>Inserted room into database<br>";

                    $select_stmt = $dbh->prepare($select_q);
                    $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                }
                $room_tag = $select_stmt->fetchColumn();
                $room_tag_found = true;

            } catch (PDOException $e) {
                echo "Error selecting room_tag line 163 ".$e->getMessage() . "<br>";
            }
            try {
                $select_tag = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
                $stmt = $dbh->prepare($select_tag);
                $stmt->execute([":tag"=>$tag]);
                if ($stmt->rowCount() > 0) {
                    if (!empty($bldg_id) && !empty($bldg_name) && !empty($room_loc)) {
                        echo "<br>Bldg ID " . $bldg_id . " ";
                        echo "Bldg Name " . $bldg_name . " ";
                        echo "Room location " . $room_loc . "<br>";
                        $update_q = "UPDATE asset_info SET dept_id = :dept, room_tag = :room_tag WHERE asset_tag = :tag";
                        $update_stmt = $dbh->prepare($update_q);
                        $update_stmt->execute([":dept" => $dept_id, ":room_tag" => $room_tag, ":tag" => $tag]);
                    }
                    echo "<br>Updated Tag in database<br>";
                } else {
                    echo "<br>Tag was not in database<br>";
                }
            } catch (PDOException $e) {
                echo "error updating asset " . $e->getMessage();
            }
            try {
                $update_kuali_time = "UPDATE kuali_table SET dw_bulk_time = :time";
                $update_stmt = $dbh->prepare($update_kuali_time);
                $update_stmt->execute([":time"=>$count2]);
            } catch (PDOException $e) {
                echo "error updating kuali_table " . $e->getMessage();
            }
            echo "<br>Time " . $update_time . "<br>";
            echo "<br>--------------------------------------<br>";
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['dw_check_time'] ?? 1744307816063;
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
        "appId" => "68bf09aaadec5e027fe35187",
        "skip" => $raw_ms,
        "limit" => 300,
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
$count = 1;
$count2 = 0 + $raw_ms;
try {
    foreach ($edges as $index => $edge) {
        $count2++;
        $update_time = $edge['node']['meta']['createdAt'];
        $check_out_type = $edge['node']['data']['fyaCF8g3Uh']['label'];
        $check_out = $check_in = false;
        $who_did_form = $edge['node']['data']['e0fZiLYomu']['label'];
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
        $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":tag" => $tag]);
        if ($select_stmt->rowCount() === 1) {
            if ($check_out) {
                $update_q = "UPDATE asset_info SET asset_notes = :note WHERE asset_tag = :tag";
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([":note" => $info, ":tag" => $tag]);

                $update_kuali = "UPDATE kuali_table SET check_out_time = :time";
                $update_stmt = $dbh->prepare($update_kuali);
                $update_stmt->execute([":time" => $update_time]);
            } else if ($check_in) {
                $update_q = "UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = :tag";
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([":tag" => $tag]);

                $update_kuali = "UPDATE kuali_table SET dw_check_time = :time";
                $update_stmt = $dbh->prepare($update_kuali);
                $update_stmt->execute([":time" => $count2]);
            }
        }
        echo "<br>" . $count++;
        echo "<br>Updating<br>Tag " . $tag . "<br>Time " . $update_time . "<br>";
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['dw_lsd_time'] ?? 0;
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
        "appId" => "68d09e41d599f1028a9b9457",
        "skip" => $raw_ms,
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

$count = 0 + $raw_ms;
try {
    foreach ($edges as $index => $edge) {
        $count++;
        $update_time = $edge['node']['meta']['createdAt'];
        if (isset($edge['node']['data']['y7nFCmsLEg'])) {
            $tag = $edge['node']['data']['y7nFCmsLEg'];
        } else {
            $tag = $edge['node']['data']['ufHf4QAJsc'];
        }

        echo "<br>Tag " . $tag . "<br>Time " . $update_time;
        $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag AND asset_status = 'In Service'";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":tag" => $tag]);
        if ($select_stmt->rowCount() === 1) {
            $update_q = "UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":tag" => $tag]);

            $update_kuali = "UPDATE kuali_table SET dw_lsd_time = :time";
            $update_stmt = $dbh->prepare($update_kuali);
            $update_stmt->execute([":time" => $count]);
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = $result['dw_psr_time'] ?? 0;
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
        "appId" => "68d09dcd7688dc028af9b5e7",
        "skip" => $raw_ms,
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

$count = 0 +$raw_ms;
try {
    foreach ($edges as $index => $edge) {
        $count++;
        $update_time = $edge['node']['meta']['createdAt'];
        $tag_data = $edge['node']['data']['W_Uw0hSpff']['data'];
        foreach ($tag_data as $data) {
            $tag = $data['data']['yks38VOkzw'];
        }

        echo "<br>Tag " . $tag . "<br>Time " . $update_time;
        $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag AND asset_status = 'In Service'";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":tag" => $tag]);
        if ($select_stmt->rowCount() === 1) {
            $update_q = "UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":tag" => $tag]);

            $update_kuali = "UPDATE kuali_table SET dw_psr_time = :time";
            $update_stmt = $dbh->prepare($update_kuali);
            $update_stmt->execute([":time" => $count]);
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$skip = (int)$result['cust_responsibility_time'] ?? 0;

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
    "query" => 'query (
        $appId: ID!
        $skip: Int!
        $limit: Int!
        $sort: [String!]
        $query: String
        $fields: Operator
) {
    app(id: $appId) {
    id name documentConnection(
        args: {
        skip: $skip
            limit: $limit
            sort: $sort
            query: $query
            fields: $fields
            }
            keyBy: ID
            ) {
                totalCount edges {
                node { id data meta } }
                    pageInfo { hasNextPage hasPreviousPage skip limit }
                }
            }
        }',
"variables" => [
    "appId" => "67bf42240472a7027dd17e97",
    "skip" => $skip,
    "limit" => 100,
    "sort" => ["meta.createdAt"],
    "query" => "",
    "fields" => [
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
                "min" => '0'
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
                $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, :dept_id) WHERE email = :email';
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
        $stmt = $dbh->prepare($insert);
        $new_dept_id = '{' . $dept_id . '}';
        $stmt->execute([$username, $hashed_pw, $email, $role, $f_name, $l_name, $new_dept_id, $form_id, $school_id, $signature]);
    }
}

function addDepartment($documentSetId, $dept_kuali_id, $c_display_name, $m_full_name, $dept_id, $dept_name)
{
    global $dbh;
    echo '<br>Add Department<br>';
    echo 'DocumentId: ' . $documentSetId . ' Kuali id: ' . $dept_kuali_id . ' Cust full name: ' . $c_display_name . ' Manager Full Name ' . $m_full_name . ' Dept Id ' . $dept_id . ' Dept Name ' . $dept_name;
    $select_dept = "SELECT dept_id, dept_manager FROM department WHERE dept_id = :dept_id";
    $dept_stmt = $dbh->prepare($select_dept);
    $dept_stmt->execute([":dept_id" => $dept_id]);
    $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
    if ($dept_info) {
        if ($dept_info['dept_manager'] !== $m_full_name) {
            $update_dept = "UPDATE department SET dept_manager = :manager WHERE dept_id = :dept_id";
            $stmt = $dbh->prepare($update_dept);
            $stmt->execute([':manager' => $m_full_name, ':dept_id' => $dept_id]);
        }
        $select_cust = 'SELECT dept_id, form_id, document_set_id FROM department WHERE :cust = ANY(custodian)';
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
        $custodian = '{'.$c_display_name.'}';
        $insert_stmt->execute([$dept_id, $dept_name, $custodian, $m_full_name, $documentSetId, $dept_kuali_id]);
    }
}

function addSignature($username, $email, $form_id, $signature, $school_id, $f_name, $l_name, $role = 'user')
{
    global $dbh, $dept_id;
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
                $update = 'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, :dept_id) WHERE email = :email';
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
        $skip++;
        if (isset($edge['node']['data']['XeTTtfl6XW']['data']['IOw4-l7NsM'])) {
        } else if (isset($edge['node']['data']['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data']['IOw4-l7NsM'])) {
            $array = $edge['node']['data']['r4XeMIe7yh']['data'];
        } else if (isset($edge['node']['data']['HBG7HehhU8']['data'][0]['data']['HN8JcizYYj']['data']['IOw4-l7NsM'])) {
            $array = $edge['node']['data']['HBG7HehhU8']['data'];
        }

        $update_time = $edge['node']['meta']['createdAt'];
        echo '<br>'.$update_time.'<br>';
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
                    addSignature($m4_username, $m4_email, $m4_id, $m4_signature, $m4_school_id, $m4_f_name, $m4_l_name, 'user');
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
                    addInfo($m2_username, $m2_email, $m2_id, $m2_school_id, $m2_signature, $m2_display_name, 'user');
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
                addDepartment($documentSetId, $dept_kuali_id, $c_full_name, $m4_full_name, $dept_id , $dept_name);
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
                addSignature($m4_username, $m4_email, $m4_id, $m4_signature, $m4_school_id, $m4_f_name, $m4_l_name, 'user');
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
                addInfo($m2_username, $m2_email, $m2_id, $m2_school_id, $m2_signature, $m2_display_name, 'user');
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
            addDepartment($documentSetId, $dept_kuali_id, $c_full_name, $m4_full_name, $dept_id, $dept_name);
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$update_q = "UPDATE kuali_table SET cust_responsibility_time = ?";
$update_stmt = $dbh->prepare($update_q);
$update_stmt->execute([$skip]);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['asset_addition_time'] ?? 0;
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
        "appId" => "67ec557474c52c027eca23d8",
        "skip" => 0,
        "limit" => 100,
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
                            "min" => (string)$raw_ms
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
$profile_map = [
    'EQUIP-10'  => 10,
    'NONCAPCOMP' => 10,
    'EQUIP-20'  => 20,
    'EQUIP-05'  => 5,
    'EQUIPAUTO' => 20,
    'OTHIMP-10' => 10,
    'OTHIMP-20' => 20,
    'OTHIMP-30' => 30,
    'OINTN'     => 10,
    'NONCAPOTHR' => 10,
    'NONCAPAUTO' => 20,
    'EQUIPCOMP' => 10
];
try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
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
            if (
                preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
                preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
                preg_match($SPA, $tag_num)
            ) {
                echo "<br> Match found " . $tag_num;
            } else continue;
            if (isset($tag['data']['XGD63KvFDV']['data']['IOw4-l7NsM'])) {
                $dept_id = $tag['data']['XGD63KvFDV']['data']['IOw4-l7NsM'];
            }

            $serial_num = $tag['data']['TuFLyAwO61'] ?? 'Unknown';
            $name = $tag['data']['wnpc592QUl'];
            $select_q = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
            $s_stmt = $dbh->prepare($select_q);
            $s_stmt->execute([":tag" => $tag_num]);
            $tag_taken = $s_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tag_taken) {
                $insert_q = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, dept_id, lifecycle, po) VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert_q);
                $ms_date = $edge['node']['meta']['workflowCompletedAt'] / 1000;
                $date = date('m-d-y', $ms_date);
                $insert_stmt->execute([$tag_num, $name, $date, $serial_num, $value, $dept_id, $asset_profile, $po]);
                $highest_time = $update_time > $highest_time ? $update_time : $highest_time;
                $insert_into_kuali_table = "UPDATE kuali_table SET asset_addition_time = :time";
                $update_stmt = $dbh->prepare($insert_into_kuali_table);
                $update_stmt->execute([":time" => $highest_time]);
            }
            echo "<br>Asset Profile " . $asset_profile . "<br>Value " . $value . "<br>Tag " . $tag_num .
                "<br>Dept " . $dept_id . "<br>SN " . $serial_num . "<br>Name " . $name . "<br>";
        }
    }
} catch (PDOException $e) {
    error_log("Error " . $e->getMessage());
    exit;
}
$raw_ms = (int)$result['asset_received_time'] ?? 0;
$highest_time = date('c', $raw_ms / 1000);

$subdomain = "subdomain";
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
        "appId" => "67b8c49871c3d6028236d586",
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
                            "min" => (string)$raw_ms
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
$new_time = $raw_ms;
$room_tag = 2051;

try {
    foreach ($edges as $index => $edge) {
        $update_time = (int)$edge['node']['meta']['createdAt'];
        $time = (int)$edge['node']['data']['wzgp7QHb7F'];
        $timestamp_sec = $time / 1000;
        $date = date("Y-m-d", $timestamp_sec);
        $tag_data = $edge['node']['data']['0nVFqyLknC']['data'];
        $num = $edge['node']['data']['0FlHusDHFt'];    // # OF PCS
        $po = $edge['node']['data']['3BdpFK5t1I'];
        if (preg_match('/(Order)(order)/i', $po, $matches, PREG_OFFSET_CAPTURE)) {
            $po = 0;
        } else {
            $po = (int)$po;
        }


        $model = $edge['node']['data']['CCqucq9BjK']['data'][0]['data']['_29h3triQJ']['label'];
        $dept_id = $edge['node']['data']['KMudjEpsXS']['data']['IOw4-l7NsM'];
        $lifecycle = 10;
        foreach ($tag_data as $tag) {
            if (!empty($tag['data']['1SI4ghT1Jt'])) {
                $tag_num = $tag['data']['1SI4ghT1Jt'];
            }
            if (
                preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
                preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
                preg_match($SPA, $tag_num)
            ) {
                echo " Match Found <br>";
            } else continue;

            $serial_num = $tag['data']['Wrnezf-g0C'] ?? '';
            $value = $tag['data']['QkRodcpQRN'];
            $length = strlen($value);
            $value = (float)substr_replace($value, '.', $length - 2, 0);
            $name = $tag['data']['vNv8CdzZjv'];
            if (
                $po === '' || $po === NULL || $tag_num === '' || $tag_num === NULL || $dept_id === '' || $dept_id === NULL || $serial_num === '' || $serial_num === NULL
                || $value === '' || $value === NULL || $name === '' || $name === NULL
            ) {
                continue;
            }
            $select_q = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
            try {
                $s_stmt = $dbh->prepare($select_q);
                if (!$s_stmt) {
                    throw new PDOException("Prepare failed: " . implode(" | ", $dbh->errorInfo()));
                }
                $executed = $s_stmt->execute([":tag" => $tag_num]);
                if (!$executed) {
                    throw new PDOException("Execute failed: " . implode(" | ", $s_stmt->errorInfo()));
                }
                $tag_taken = $s_stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error selecting " . $e->getMessage();
                $tag_taken = true;
            }
            if ($s_stmt->rowCount() <= 0) {
            $insert_q = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_model, po, dept_id, lifecycle, room_tag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            try {
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$tag_num, $name, $date, $serial_num, $value, $model, $po, $dept_id, $lifecycle, $room_tag]);
                echo '<br>Inserted<br>Tag Number ' . $tag_num . '<br>Serial ID ' . $serial_num . '<br>Value ' . $value . '<br>Name ' . $name;
                echo '<br>PO ' . $po . '<br>Model ' . $model . '<br>Dept ID ' . $dept_id . '<br>Time ' . $update_time . '<br>Date '  . $date . '<br><br>';
                if ($update_time > $raw_ms && $update_time > $new_time) {
                    $new_time = $update_time;
                }
            } catch (PDOException $e) {
                echo '<br>Failed to insert<br>Tag Number ' . $tag_num . '<br>Serial ID ' . $serial_num . '<br>Value ' . $value . '<br>Name ' . $name;
                echo '<br>PO ' . $po . '<br>Model ' . $model . '<br>Dept ID ' . $dept_id . '<br>Time ' . $update_time . '<br>Date '  . $date . '<br><br>';
                echo "Error inserting " . $e->getMessage();
            }
            } else {
                echo $tag_taken['asset_tag'] . " Taken<br>";
            }
        }
    }
    $insert_into_kuali_table = "UPDATE kuali_table SET asset_received_time = :time";
    $update_stmt = $dbh->prepare($insert_into_kuali_table);
    $update_stmt->execute([":time" => $new_time]);
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['bulk_psr_time'] ?? 0;
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
        "appId" => "67c9d5af2017390283de33d5",
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
                            "min" => (string)$raw_ms
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
$count = 1;
try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
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
            $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag AND asset_status = 'In Service'";
            $select_stmt = $dbh->prepare($select_q);
            $select_stmt->execute([":tag" => $tag]);
            if ($select_stmt->rowCount() === 1) {
                $update_q = "UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = :tag";
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([":tag" => $tag]);

                $update_kuali = "UPDATE kuali_table SET bulk_psr_time = :time";
                $update_stmt = $dbh->prepare($update_kuali);
                $update_stmt->execute([":time" => $update_time]);
                echo "<br>" . $count++;
                echo "<br>Updating<br>Tag " . $tag . "<br>Time " . $update_time . "<br>";
            }
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['bulk_transfer_time'] ?? 0;
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
    "query" => 'query (
        $appId: ID!
        $skip: Int!
        $limit: Int!
        $sort: [String!]
        $query: String
        $fields: Operator
) {
    app(id: $appId) {
    id name documentConnection(
        args: {
        skip: $skip
            limit: $limit
            sort: $sort
            query: $query
            fields: $fields
            }
            keyBy: ID
            ) {
                totalCount edges {
                node { id data meta } }
                    pageInfo { hasNextPage hasPreviousPage skip limit }
                }
            }
        }',
"variables" => [
    "appId" => "686554f17ba08e02806b14b5",
    "skip" => 0,
    "limit" => 100,
    "sort" => ["meta.createdAt"],
    "query" => "",
    "fields" => [
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
                "min" => (string)$raw_ms
            ]
        ]
    ]
]
]);
// $data = '{"query":"query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { app(id: $appId) { id name documentConnection( args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID ) { totalCount edges { node { id data meta } } pageInfo { hasNextPage hasPreviousPage skip limit } } }}","variables":{
//   "appId": "67e451d2cc3194027dfce429",
//   "skip": 0,
//   "limit": 25,
//   "sort": [
//     "meta.updatedAt"
//   ],
//   "query": "",
//   "fields": {
//     "type": "AND",
//     "operators": [
//       {
//         "field": "meta.workflowStatus",
//         "type": "IS",
//         "value": "Complete"
//       },
//       {
//         "field": "meta.updatedAt",
//         "type": "RANGE",
//         "min": ' . $highest_time . '
//       }
//     ]
//   }
// }}';
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
$resp2 = json_decode($resp);

$decode_true = json_decode($resp, true);
$edges = $decode_true['data']['app']['documentConnection']['edges'];


$count = 1;
try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];

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
            echo "<br>Tag " . $data['data']['RxpLOF3XrE']. "<br>";
            $dept_id = $data['data']['5c3qSm88bs'];
            if (!empty($data['data']['6JHs3W0-CL'])) {
                $room_loc = $data['data']['6JHs3W0-CL'];
            }
            $dept_id = substr($dept_id, 0, 6);
            echo $dept_id . "<br>";
            if (preg_match('/^D/', $dept_id)) {
                echo "<br>Dept Id Format Good<br>";
            }
            if (!empty($data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'])) {
                $bldg_id = $data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'];
                $bldg_name = $data['data']['bYpfsUDuZx']['data']['AkMeIWWhoj'];
            }
            if (!empty($data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'])) {
                $bldg_id = $data['data']['BC0E2hOKv3']['data']['IOw4-l7NsM'];
                if ($bldg_id === '39A') {
                    $bldg_id = 39;
                }

                $bldg_name = $data['data']['BC0E2hOKv3']['data']['AkMeIWWhoj'];
            }
            $bldg_id = (int)$bldg_id;
            // UPDATE DATABASE BASED OF KUALI
            if (!empty($bldg_id) && !empty($bldg_name)) {
                echo "<br>Bldg ID " . $bldg_id . "<br>";
                echo "<br>Bldg Name " . $bldg_name . "<br>";
                $select = "SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = :id";
                $id_stmt = $dbh->prepare($select);
                $id_stmt->execute([':id'=>$bldg_id]);
                $select = "SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_name = :name";
                $name_stmt = $dbh->prepare($select);
                $name_stmt->execute([':name'=>$bldg_name]);
                if ($id_stmt->rowCount() === 0) {
                    $insert = "INSERT INTO bldg_table (bldg_id, bldg_name) VALUES (:id, :name)";
                    $stmt = $dbh->prepare($insert);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Building Was NOT found adding building to database. Automatically Added Building<br>";
                    $id_stmt = $dbh->prepare($select);
                    $id_stmt->execute([':id'=>$bldg_id]);
                }

                $db_bldg = $id_stmt->fetch(PDO::FETCH_ASSOC);
                if ($bldg_id !== $db_bldg['bldg_id']) {
                    $update = "UPDATE bldg_table SET bldg_id = :id WHERE bldg_name = :name";
                    $stmt = $dbh->prepare($update);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Bldg id was different. Fixing<br>";
                }
                if ($bldg_name !== $db_bldg['bldg_name']) {
                    $update = "UPDATE bldg_table SET bldg_name = :name WHERE bldg_id = :id";
                    $stmt = $dbh->prepare($update);
                    $stmt->execute([':id'=>$bldg_id, ":name"=>$bldg_name]);
                    echo "<br>Bldg name was different. Fixing<br>";
                }
            } else {
                echo "<br>Building name or id was not found skipping<br>";
                continue;
            }


            $room_tag_found = false;
            try{
                $select_q = "SELECT room_tag FROM room_table WHERE bldg_id = :bid AND room_loc = :rloc";
                $select_stmt = $dbh->prepare($select_q);
                $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                if ($select_stmt->rowCount() === 0) {
                    $insert = "INSERT INTO room_table (room_loc, bldg_id) VALUES (:rloc, :bid)";
                    $insert_stmt = $dbh->prepare($insert);
                    $insert_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                    echo "<br>Inserted room into database<br>";

                    $select_stmt = $dbh->prepare($select_q);
                    $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                }
                $room_tag = $select_stmt->fetchColumn();
                $room_tag_found = true;

            } catch (PDOException $e) {
                echo "Error selecting room_tag line 163 ".$e->getMessage() . "<br>";
            }
            try {
                $select_tag = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
                $stmt = $dbh->prepare($select_tag);
                $stmt->execute([":tag"=>$tag]);
                if ($stmt->rowCount() > 0) {
                    if (!empty($bldg_id) && !empty($bldg_name) && !empty($room_loc)) {
                        echo "<br>Bldg ID " . $bldg_id . " ";
                        echo "Bldg Name " . $bldg_name . " ";
                        echo "Room location " . $room_loc . "<br>";
                        $update_q = "UPDATE asset_info SET dept_id = :dept, room_tag = :room_tag WHERE asset_tag = :tag";
                        $update_stmt = $dbh->prepare($update_q);
                        $update_stmt->execute([":dept" => $dept_id, ":room_tag" => $room_tag, ":tag" => $tag]);
                    }
                    echo "<br>Updated Tag in database<br>";
                } else {
                    echo "<br>Tag was not in database<br>";
                }
            } catch (PDOException $e) {
                echo "error updating asset " . $e->getMessage();
            }
            try {
                $update_kuali_time = "UPDATE kuali_table SET bulk_transfer_time = :time";
                $update_stmt = $dbh->prepare($update_kuali_time);
                $update_stmt->execute([":time"=>$update_time]);
            } catch (PDOException $e) {
                echo "error updating kuali_table " . $e->getMessage();
            }
            echo "<br>Time " . $update_time . "<br>";
            echo "<br>--------------------------------------<br>";
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['equip_lost_stol_time'] ?? 0;
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
        "appId" => "677c075baba4e3014ca39095",
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
                            "min" => (string)$raw_ms
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

try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
        if (isset($edge['node']['data']['y7nFCmsLEg'])) {
            $tag = $edge['node']['data']['y7nFCmsLEg'];
        } else {
            $tag = $edge['node']['data']['ufHf4QAJsc'];
        }

        echo "<br>Tag " . $tag . "<br>Time " . $update_time;
        $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag AND asset_status = 'In Service'";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":tag" => $tag]);
        if ($select_stmt->rowCount() === 1) {
            $update_q = "UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":tag" => $tag]);

            $update_kuali = "UPDATE kuali_table SET equip_lost_stol_time = :time";
            $update_stmt = $dbh->prepare($update_kuali);
            $update_stmt->execute([":time" => $update_time]);
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
$raw_ms = (int)$result['psr_time'] ?? 0;
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
        "appId" => "68093820dec1b8027f980167",
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
                            "min" => (string)$raw_ms
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

try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
        $tag_data = $edge['node']['data']['W_Uw0hSpff']['data'];
        foreach ($tag_data as $data) {
            $tag = $data['data']['yks38VOkzw'];
        }

        echo "<br>Tag " . $tag . "<br>Time " . $update_time;
        $select_q = "SELECT FROM asset_info WHERE asset_tag = :tag AND asset_status = 'In Service'";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":tag" => $tag]);
        if ($select_stmt->rowCount() === 1) {
            $update_q = "UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":tag" => $tag]);

            $update_kuali = "UPDATE kuali_table SET psr_time = :time";
            $update_stmt = $dbh->prepare($update_kuali);
            $update_stmt->execute([":time" => $update_time]);
        }
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
