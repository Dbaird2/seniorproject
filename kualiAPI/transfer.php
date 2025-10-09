<?php
include_once __DIR__ . "/../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT transfer_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['transfer_time'] ?? 0;
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
    "appId" => "67e451d2cc3194027dfce429",
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
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
$resp2 = json_decode($resp);

$decode_true = json_decode($resp, true);
$edges = $decode_true['data']['app']['documentConnection']['edges'];


$count = 1;
foreach ($edges as $index => $edge) {
    $raw_ms++;
    $update_time = $edge['node']['meta']['createdAt'];
    if (trim($edge['node']['data']['_GODY1FjEy']['label']) === 'From one department to another department') {
        echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
        deptChange();
    } else if (trim($edge['node']['data']['_GODY1FjEy']['label']) === 'Building/Room/Location change (Business Unit stays the same)') {
        echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
        bldgChange();
    }
}
        
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;
function checkBldg($bldg_name, $room_loc) {
    global $dbh;
    $select = 'SELECT bldg_id FROM bldg_table WHERE bldg_name = :name';
    $stmt = $dbh->prepare($select);
    $stmt->execute([':name'=>$bldg_name]);
    $bldg_id = $stmt->fetchColumn();
    if ($bldg_id) {
        $select = 'SELECT room_tag FROM room_table WHERE bldg_id = :id AND room_loc = :loc';
        $stmt = $dbh->prepare($select);
        $stmt->execute([':id'=>$bldg_id, ':loc'=>$room_loc]);
        $room_tag = $stmt->fetchColumn();
        if (!$room_tag) {
            $update_room = 'INSERT INTO room_table (room_loc, bldg_id) VALUES (?,?)';
            $stmt = $dbh->prepare($update_room);
            $stmt->execute([$room_loc, $bldg_id]);

            $select = 'SELECT room_tag FROM room_table WHERE bldg_id = :id AND room_loc = :loc';
            $stmt = $dbh->prepare($select);
            $stmt->execute([':id'=>$bldg_id, ':loc'=>$room_loc]);
            $room_tag = $stmt->fetchColumn();
        } 
        $update = 'UPDATE asset_info SET room_tag = :room WHERE asset_tag = :tag';
        $stmt = $dbh->prepare($update);
        $stmt->execute([':room'=>$room_tag, ':tag'=>$tag]);
        return true;
    }
}
function checkTag($tag) {
    global $dbh;
    $select = 'SELECT asset_tag FROM asset_info WHERE asset_tag = :tag';
    $stmt = $dbh->prepare($select);
    $stmt->execute([':tag'=>$tag]);
    $confirm_tag = $stmt->fetchColumn();
    return $confirm_tag !== false;
}
function deptChange() {
    global $dbh, $edge, $raw_ms;
    $tags = $edge['node']['data']['t7mH-1FlaO']['data'];
    foreach ($tags as $index => $data) {
        $tag = $data['data']['XZlIFEDX6Y'];
        checkTag($tag);
        if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
            echo "<br>Tag field empty<br>";
            continue;
        }
        if (!empty($data['data']['6JHs3W0-CL'])) {
            $room_loc = $data['data']['6JHs3W0-CL'];
        }
        if (isset($data['data']['U73d7kPH5b']['data']['IOw4-l7NsM'])) {
            $dept_id = $data['data']['U73d7kPH5b']['data']['IOw4-l7NsM'];
            $dept_name = $data['data']['U73d7kPH5b']['data']['AkMeIWWhoj'];
        }

        if (!empty($data['data']['zZztPX8Pcw'])) {
            $room_loc = $data['data']['zZztPX8Pcw'];
        }
        if (isset(['data']['hXHmCy0mek']['label'])) {
            $bldg_name = ['data']['hXHmCy0mek']['label'];
        } else if (isset(['data']['JjGDmuCa8_']['label'])) {
            $bldg_name = ['data']['JjGDmuCa8_']['label'];
        }
        echo '<br>Bldg name: ' . $bldg_name . ' Dept id: ' . $dept_id . ' Dept name: ' . $dept_name . ' Room Location ' . $room_loc . '<br>';
        
        if (!empty($bldg_name) && !empty($room_loc)) {
            checkBldg($bldg_name, $room_loc);
        }

        $dept_id = substr($dept_id, 0, 6);
        echo $dept_id . "<br>";
        if (preg_match('/^D/', $dept_id)) {
            echo "<br>Dept Id Format Good<br>";
        } else {
            continue;
        }
        $update_q = "UPDATE asset_info SET dept_id = :dept_id WHERE asset_tag = :tag";
        $update_stmt = $dbh->prepare($update_q);
        $update_stmt->execute([":dept_id" => $dept_id, ":tag" => $tag]);

        try {
            $update_kuali_time = "UPDATE kuali_table SET transfer_time = :time";
            $update_stmt = $dbh->prepare($update_kuali_time);
            $update_stmt->execute([":time"=>$raw_ms]);
        } catch (PDOException $e) {
            echo "error updating kuali_table " . $e->getMessage();
        }
        echo "<br>Time " . $raw_ms . "<br>";
        echo "<br>--------------------------------------<br>";
    }
}
function bldgChange() {
    global $dbh, $edge, $raw_ms;
    $tags = $edge['node']['data']['t7mH-1FlaO']['data'];
    foreach ($tags as $index => $data) {
        $tag = $data['data']['XZlIFEDX6Y'];
        checkTag($tag);
        if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
            echo "<br>Tag field empty<br>";
            continue;
        }
        if (!empty($data['data']['6JHs3W0-CL'])) {
            $room_loc = $data['data']['6JHs3W0-CL'];
        }
        

        if (!empty($data['data']['zZztPX8Pcw'])) {
            $room_loc = $data['data']['zZztPX8Pcw'];
        }
        if (isset(['data']['hXHmCy0mek']['label'])) {
            $bldg_name = ['data']['hXHmCy0mek']['label'];
        } else if (isset(['data']['JjGDmuCa8_']['label'])) {
            $bldg_name = ['data']['JjGDmuCa8_']['label'];
        }
        echo '<br>Bldg name: ' . $bldg_name . ' Dept id: ' . $dept_id . ' Dept name: ' . $dept_name . ' Room Location ' . $room_loc . '<br>';
        
        if (!empty($bldg_name) && !empty($room_loc)) {
            checkBldg($bldg_name, $room_loc);
        }

        try {
            $update_kuali_time = "UPDATE kuali_table SET transfer_time = :time";
            $update_stmt = $dbh->prepare($update_kuali_time);
            $update_stmt->execute([":time"=>$raw_ms]);
        } catch (PDOException $e) {
            echo "error updating kuali_table " . $e->getMessage();
        }
        echo "<br>Time " . $raw_ms . "<br>";
        echo "<br>--------------------------------------<br>";
    }
}
function busChange() {
    global $dbh, $edge, $raw_ms;
    $tags = $edge['node']['data']['t7mH-1FlaO']['data'];
    foreach ($tags as $index => $data) {
        $tag = $data['data']['XZlIFEDX6Y'];
        echo $tag . '<br>';
        checkTag($tag);
        if ($tag === '' || $tag === 'N/A' || $tag === 'NA' || $tag === NULL) {
            echo "<br>Tag field empty<br>";
            continue;
        }
        $new_bus = ['data']['ARcUfSX-vJ']['label'];
        $update = 'UPDATE asset_info SET bus_unit = :unit WHERE asset_tag = :tag';
        $stmt = $dbh->prepare($update);
        $stmt->execute([':unit'=>$new_bus, ':tag'=>$tag]);
        try {
            $update_kuali_time = "UPDATE kuali_table SET transfer_time = :time";
            $update_stmt = $dbh->prepare($update_kuali_time);
            $update_stmt->execute([":time"=>$raw_ms]);
        } catch (PDOException $e) {
            echo "error updating kuali_table " . $e->getMessage();
        }
        echo "<br>Time " . $raw_ms . "<br>";
        echo "<br>--------------------------------------<br>";
    }
}

