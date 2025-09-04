<?php
include_once "../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT check_out_time, kuali_key FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['check_out_time'] ?? 1744307816063;
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
        "appId" => "677d53d969ef4601572b80ae",
        "skip" => 0,
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
$count = 1;
try {
    foreach ($edges as $index => $edge) {
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

                $update_kuali = "UPDATE kuali_table SET check_out_time = :time";
                $update_stmt = $dbh->prepare($update_kuali);
                $update_stmt->execute([":time" => $update_time]);
            }
        }
        echo "<br>" . $count++;
        echo "<br>Updating<br>Tag " . $tag . "<br>Time " . $update_time . "<br>";
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;

