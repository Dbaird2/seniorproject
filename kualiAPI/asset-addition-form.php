<?php
include_once "../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT asset_addition_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
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
                            "min" => $highest_time
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
    $dbh->beginTransaction();
    foreach ($edges as $index => $edge) {
        $asset_profile = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['pZEr8FpYK_']['label'];
        $key = $asset_profile;
        if (isset($profile_map[$key])) {
            $asset_profile = $profile_map[$key];
        } else if ($key === 'SOFTWARE') {
            continue;
        } else {
            $asset_profile = 10;
        }
        $value = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['PxtY2-Q3bL'];
        $length = strlen($value);
        $value = (float)substr_replace($value, '.', $length - 2, 0);

        if ($asset_profile === 'BLDGIMP') {
            echo 'Skipped BLDGIMP <br>';
            continue;
        }
        $update_time = $edge['node']['meta']['createdAt'];
        $time = $edge['node']['data']['wzgp7QHb7F'];
        $timestamp_sec = $time / 1000;
        $date = date("Y-m-d", $timestamp_sec);
        $tag_data = $edge['node']['data']['PUcYspMrJZ']['data'];


        foreach ($tag_data as $tag) {
            $dept_id = $tag['data']['XGD63KvFDV']['data']['IOw4-l7NsM'];
            $tag_num = $tag['data']['hYk-CuEHw-'];
            if (
                preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
                preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
                preg_match($SPA, $tag_num)
            ) {
            } else continue;
            $serial_num = $tag['data']['TuFLyAwO61'] ?? 'Unknown';
            $name = $tag['data']['wnpc592QUl'];
            $select_q = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
            $s_stmt = $dbh->prepare($select_q);
            $s_stmt = $s_stmt->execute([":tag" => $tag_num]);
            $tag_taken = $s_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tag_taken) {
                $insert_q = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, dept_id, lifecycle) VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$tag_num, $name, $date, $serial_num, $value, $dept_id, $asset_profile]);
            }
        }
        if (
            preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
            preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
            preg_match($SPA, $tag_num)
        ) {
        } else continue;
        $highest_time = $update_time > $highest_time ? $update_time : $highest_time;
    }
    $insert_into_kuali_table = "UPDATE kuali_table SET asset_addition_time = :time";
    $update_stmt = $dbh->prepare($insert_into_kuali_table);
    $update_stmt->execute([":time" => $highest_time]);
    $dbh->commit();
} catch (PDOException $e) {
    $dbh->rollBack();
}

