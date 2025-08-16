<?php
include_once "../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT equip_lost_stol_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
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
            $update_q = "UPDATE asset_info SET asset_status = Disposed WHERE asset_tag = :tag";
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
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

