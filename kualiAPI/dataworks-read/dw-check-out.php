<?php
include_once __DIR__ .  "/../../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT dw_check_time, kuali_key FROM kuali_table";
$result = $query_repo->fetchOne($select);

$raw_ms = (int)$result['dw_check_time'] ?? 0;

$decode_true = $kuali->baseReads("68bf09aaadec5e027fe35187", $raw_ms);
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
        $select_q = "SELECT 1 FROM asset_info WHERE asset_tag = ?";
        $asset_info_check = $query_repo->fetchOne($select_q, $tag);
        if ($asset_info_check) {
            if ($check_out) {
                $update_q = "UPDATE asset_info SET asset_notes = ? WHERE asset_tag = ?";
                $query_repo->execute($update_q, $info, $tag);

                $update_kuali = "UPDATE kuali_table SET check_out_time = ?";
                $query_repo->execute($update_kuali, $update_time);
            } else if ($check_in) {
                $update_q = "UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = ?";
                $query_repo->execute($update_q, $tag);

                $update_kuali = "UPDATE kuali_table SET dw_check_time = ?";
                $query_repo->execute($update_kuali, $count2);
            }
        }
        echo "<br>" . $count++;
        echo "<br>Updating<br>Tag " . $tag . "<br>Time " . $update_time . "<br>";
    }
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode($decode_true, JSON_PRETTY_PRINT) . '</pre>';
exit;
