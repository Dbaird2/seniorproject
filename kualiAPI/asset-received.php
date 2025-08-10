<?php
include_once "../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT asset_received_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms =  $result['asset_received_time'] ?? 0;
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

try {
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
        $time = $edge['node']['data']['wzgp7QHb7F'];
        $timestamp_sec = $time / 1000;
        $date = date("Y-m-d", $timestamp_sec);
        $tag_data = $edge['node']['data']['0nVFqyLknC']['data'];
        $num = $edge['node']['data']['0FlHusDHFt'];    // # OF PCS
        $po = (int)$edge['node']['data']['3BdpFK5t1I'];

        $model = $edge['node']['data']['CCqucq9BjK']['data'][0]['data']['_29h3triQJ']['label'];
        $dept_id = $edge['node']['data']['KMudjEpsXS']['data']['IOw4-l7NsM'];
        $lifecycle = 10;
        foreach ($tag_data as $tag) {
            $tag_num = $tag['data']['1SI4ghT1Jt'];
            if (
                preg_match($ASI, $tag_num) || preg_match($STU, $tag_num) ||
                preg_match($CMP, $tag_num) || preg_match($FDN, $tag_num) ||
                preg_match($SPA, $tag_num)
            ) {
            } else continue;
            echo $update_time;

            $serial_num = $tag['data']['Wrnezf-g0C'] ?? 'Unknown';
            $value = $tag['data']['QkRodcpQRN'] ?? 1;
            $length = strlen($value);
            $value = (float)substr_replace($value, '.', $length - 2, 0);
            $name = $tag['data']['vNv8CdzZjv'];
            $select_q = "SELECT asset_tag FROM asset_info WHERE asset_tag = :tag";
            $s_stmt = $dbh->prepare($select_q);
            $s_stmt->execute([":tag" => $tag_num]);
            $tag_taken = $s_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tag_taken) {
                $insert_q = "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_model, po, dept_id, lifecycle) VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $dbh->prepare($insert_q);
                $insert_stmt->execute([$tag_num, $name, $date, $serial_num, $value, $model, $po, $dept_id, $lifecycle]);
            }

            echo '<br>Tag Number ' . $tag_num . '<br>Serial ID ' . $serial_num . '<br>Value ' . $value . '<br>Name ' . $name;
            echo '<br>PO ' . $po . '<br>Model ' . $model . '<br>Dept ID ' . $dept_id . '<br>Date ' . $date . '<br><br>';
        }
        $highest_time = $update_time > $highest_time ? $time : $highest_time;
    }
    $insert_into_kuali_table = "UPDATE kuali_table SET asset_received_time = :time";
    $update_stmt = $dbh->prepare($insert_into_kuali_table);
    $update_stmt->execute([":time" => $highest_time]);
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

