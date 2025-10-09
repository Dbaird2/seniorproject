<?php
include_once __DIR__ . "/../../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT dw_bulk_time, kuali_key FROM kuali_table";
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
            $update_q = "UPDATE asset_info SET dept_id = :dept WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":dept" => $dept_id, ":tag" => $tag]);
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
                        $update_q = "UPDATE asset_info SET room_tag = :room_tag WHERE asset_tag = :tag";
                        $update_stmt = $dbh->prepare($update_q);
                        $update_stmt->execute([":room_tag" => $room_tag, ":tag" => $tag]);
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
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

