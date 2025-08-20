<?php
include_once "../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT bulk_transfer_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
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

        $tags = $edge['node']['data']['JZ-q3J19dw']['data'];
        foreach ($tags as $index => $data) {
            $tag = $data['data']['RxpLOF3XrE'];
            if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
                echo "<br>Tag field empty<br>";
                continue;
            }
            echo "<br>Tag " . $data['data']['RxpLOF3XrE'];
            $dept_id = $data['data']['5c3qSm88bs'];
            $room_loc = $data['data']['6JHs3W0-CL'];
            if (!empty($data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'])) {
                $bldg_id = $data['data']['bYpfsUDuZx']['data']['IOw4-l7NsM'];
                echo "<br>" . $bldg_id . "<br>";
            }
            try {
                if (!empty($data['data']['SBu1DONXk2'])) {
                    $bldg_id = match (trim($data['data']['SBu1DONXk2'])) {
                        'Science 1' => 30,
                        'Science 2' => 36,
                        'Science 3' => 48,
                        'CENT PLANT OUTSIDE', 'CENT PLANT' => 11,
                        'LIBRARY' => 43,
                        'Student Union' => 53
                    };
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            // GET BLDG ID FROM BLDG NAME
            $select_q = "SELECT room_tag FROM room_table WHERE bldg_id = :bid AND room_loc = :rloc";
            $select_stmt = $dbh->prepare($select_q);
            $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
            $room_tag_found = false;
            if ($select_stmt->rowCount() > 0) {
                $room_tag = $select_stmt->fetchColumn();
                $room_tag_found = true;
            } else {
                $check_bldg_id = "SELECT bldg_id FROM bldg_table WHERE bldg_id = :bid";
                $bldg_id_stmt = $dbh->prepare($check_bldg_id);
                $bldg_id_stmt->execute([":bid" => $bldg_id]);
                if ($bldg_id_stmt->rowCount() > 0) {

                    $update_room_table = "INSERT INTO room_table (bldg_id, room_loc) VALUES (?, ?)";
                    $update_stmt = $dbh->prepare($update_room_table);
                    $update_stmt->execute([$bldg_id, $room_loc]);

                    $get_room_tag = "SELECT room_tag FROM room_table WHERE bldg_id = :bid AND room_loc = :rloc";
                    $select_stmt = $dbh->prepare($get_room_tag);
                    $select_stmt->execute([':bid' => $bldg_id, ":rloc" => $room_loc]);
                    $room_tag = $select_stmt->fetchColumn();
                } else {
                    echo "Bldg id not found. Skipping<br>";
                    continue;
                }
            }
            $update_q = "UPDATE asset_info SET dept_id = :dept, room_tag = :room_tag WHERE asset_tag = :tag";
            $update_stmt = $dbh->prepare($update_q);
            $update_stmt->execute([":dept" => $dept_id, ":room_tag" => $room_tag, ":tag" => $tag]);
        }



        echo "<br>Time " . $update_time . "<br>";
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

