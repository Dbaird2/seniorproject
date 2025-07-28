<?php
error_reporting(0);
require_once("../config.php");
check_auth("high");
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include_once("../navbar.php");
/* The point of this page is to read a CSV or Excel file then update,add,delete
 * the databases assets */
/*
 *  * Bulk Asset Import Script
 *   * ------------------------
 *    * This script handles uploading a spreadsheet (XLSX) containing asset
 *    data
 *     * and inserts new assets into the database (`asset_info`) if they:
 *      * - Are not already in `asset_info` or `complete_asset_view`
 *       * - Have a valid tag format
 *        * - Map to a known room/bldg in the `room_table`
 *         *
 *          * It also handles tag format validation, location ID mapping, and
 *          profile ID normalization.
 *           * Uses PHPSpreadsheet and PDO for file parsing and DB handling.
 *            */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $spreadsheet = new Spreadsheet();

    $filePath = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();
    $highest_row = $sheet->getHighestRow();
    $highest_column = $sheet->getHighestColumn();
    $header_row = 0;
    for ($r = 0; $r < 3; $r++) {
        if (in_array('Tag Number', $data[$r]) || in_array('Tag', $data[$r])) {
            $header_row = $r;
            break;
        }
    }
    $column_map = [
        'tag_col'        => ['Tag Number', 'Tag'],
        'descr_col'      => ['Description', 'Descr'],
        'serial_col'     => ['Serial ID', 'SN'],
        'vin_col'        => ['VIN', 'Vehicle ID'],
        'custodian_col'  => ['Custodian', 'Custodian Name'],
        'dept_col'       => ['Dept', 'Department'],
        'acq_date_col'   => ['Acq Date', 'Acquisition Date'],
        'cost_col'       => ['COST Total Cost', 'Price'],
        'po_col'         => ['PO No.', 'Purchase Order'],
        'location_col'   => ['Location'],
        'model_col'      => ['Model', 'Model Number'],
        'profile_id_col' => ['Profile ID', 'Profile'],
        'asset_type_col' => ['Asset Type'],
    ];

    /*
     *  NOTE FOR $$key.
     *  $key = 'tag_col'
     *  $$key = -1 -> means $tag_col = -1
     *  makes the value of the variable $key its own
     *  variable with the value of -1
     *  */
    foreach (array_keys($column_map) as $key) {
        $$key = -1;
    }
    $useless_columns = [];

    foreach ($data[$header_row] as $i => $header) {
        $found = false;
        foreach ($column_map as $varName => $aliases) {
            if (in_array($header, $aliases, true)) {
                $$varName = $i;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $useless_columns[] = $i;
        }
    }
    foreach ($data as &$row) {
        foreach ($useless_columns as $useless) {
            unset($row[$useless]);
        }
        unset($row);
    }
    $location_array = [];
    $j = 1;
    $ASI = "/^A[SI]?\d+$/";
    $STU = "/^S[RC]?[TU]?\d+$/";
    $CMP = "/^\d+/";
    $FDN = "/^F[DN]?\d+$/";
    $SPA = "/^SPA\d+$/";
    $location_map = [
        'AVCMP'  => 140,
        'AVC'    => 140,
        'LUC'    => 140,
        'AVCMP'  => 140,
        'CAF'    => 38,
        'CENT'   => 11,
        'CORP'   => 37,
        'DDH'    => 32,
        'DT'     => 39,
        'EDU'    => 34,
        'GYM'    => 33,
        'HC'     => 35,
        'LIB'    => 43,
        'MUSC'   => 112,
        '39A'    => 39,
        'CONNEX STORAG' => 146,
        '44B'    => 44,
        '44A'    => 44,
        '44C'    => 44,
        '44D'    => 44,
        '44E'    => 44
    ];
    $profile_map = [
        'EQUIP-10'  => 10,
        'NONCAPCOMP' => 10,
        'EQUIP-20'  => 20,
        'EQUIP-05'  => 5
    ];
    try {
        $dbh->beginTransaction();
        for ($i = $header_row; $i < $highest_row; $i++) {
            $location_array[$i] = [];
            echo $data[$i][$location_col] ?? 'N/A';
            if (searchstr($data[$i][$location_col] ?? '', "-")) {
                $location_array[$i] = explode("-", $data[$i][$location_col]);
            } else if (searchstr($data[$i][$location_col] ?? '', "_")) {
                $location_array[$i] = explode("_", $data[$i][$location_col]);
            }

            if ($data[$i][$tag_col] === 'Tag Number' || $data[$i][$tag_col] === '' || $data[$i][$profile_id_col] === 'BLDGIMP') {
                continue;
            }

            echo "Checking tag: " . $data[$i][$tag_col] . "<br>";
            if (
                preg_match($ASI, $data[$i][$tag_col]) || preg_match($STU, $data[$i][$tag_col]) ||
                preg_match($CMP, $data[$i][$tag_col]) || preg_match($FDN, $data[$i][$tag_col]) ||
                preg_match($SPA, $data[$i][$tag_col])
            ) {
                echo preg_match($ASI, $data[$i][$tag_col]) ? "ASI match found. " : "";
                echo preg_match($STU, $data[$i][$tag_col]) ? "STU match found. " : "";
                echo preg_match($CMP, $data[$i][$tag_col]) ? "CMP match found. " : "";
                echo preg_match($FDN, $data[$i][$tag_col]) ? "FDN match found. " : "";
                echo preg_match($SPA, $data[$i][$tag_col]) ? "SPA match found. " : "";
            } else continue;
            echo "Valid tag: " . $data[$i][$tag_col] . "<br>";

            if ($location_array[$i][0] === '' || !isset($location_array[$i][1])) {
                $location_array[$i][0] = 150;
                $location_array[$i][1] = '000';
            } else if ($location_array[$i][0] === 'OUT') {
                $location_array[$i][0] = 'OUTSIDE';
            } else if ($location_array[$i][0] === 'LOB') {
                $location_array[$i][0] = 'LOBBY';
            } else if ($location_array[$i][0] === '54' && $location_array[$i][1] === 'CLASSRO') {
                $location_array[$i][1] = 'CLASSRM';
            }


            $key = $location_array[$i][0];
            if (isset($location_map[$key])) {
                if ($key === '39A') {
                    $location_array[$i][1] = 'A' . $location_array[$i][1];
                } else if ($key === 'CONNEX STORAG') {
                    $location_array[$i][1] = 'STORAGE';
                } else if ($key === '44A') {
                    $location_array[$i][1] = 'A' . $location_array[$i][1];
                } else if ($key === '44B') {
                    $location_array[$i][1] = 'B' . $location_array[$i][1];
                } else if ($key === '44C') {
                    $location_array[$i][1] = 'C' . $location_array[$i][1];
                } else if ($key === '44D') {
                    $location_array[$i][1] = 'D' . $location_array[$i][1];
                } else if ($key === '44E') {
                    $location_array[$i][1] = 'E' . $location_array[$i][1];
                }
                $location_array[$i][0] = (int)$location_map[$key];
            }
            echo $location_array[$i][0] ?? 'N/A';
            echo " ";
            echo $location_array[$i][1] ?? 'N/A';
            echo "<br>";

            $key = $data[$i][$profile_id_col];
            if (isset($profile_map[$key])) {
                $data[$i][$profile_id_col] = $profile_map[$key];
            }

            $loc_change = false;
            $set_array = [];
            $check_tag_query = "SELECT 1 FROM asset_info WHERE asset_tag = :tag UNION SELECT 1 FROM complete_asset_view WHERE asset_tag = :tag OR new_tag = :tag";
            $stmt = $dbh->prepare($check_tag_query);
            $stmt->execute([':tag' => $data[$i][$tag_col]]);
            $existing_asset = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_asset) {
                echo "Asset with tag " . $data[$i][$tag_col] . " already exists. Skipping.<br>";
                continue;
            }
            $get_room_tag_query = "SELECT room_tag FROM room_table WHERE room_loc = :room_loc AND bldg_id = :bldg_id";
            $stmt = $dbh->prepare($get_room_tag_query);
            $stmt->execute([':room_loc' => $location_array[$i][1], ':bldg_id' => $location_array[$i][0]]);
            $room_tag = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($room_tag) {
                $room_tag = $room_tag['room_tag'];
                $insert_query = "INSERT INTO asset_info (asset_tag, asset_model, room_tag, asset_name, serial_num, po, asset_price, asset_type, lifecycle, dept_id) VALUES (:asset_tag, :asset_model, :room_tag, :asset_name, :serial_num, :po, :asset_price, :asset_type, :lifecycle, :dept_id)";

                echo "Added " . $j++ . " " . $data[$i][$tag_col] . " ";
                echo $location_array[$i][0] . " ";
                if (isset($location_array[$i][1])) {
                    echo $location_array[$i][1] . " ";
                } else {
                    echo "N/A ";
                }
                echo $data[$i][$descr_col] . " ";
                echo $data[$i][$serial_col] . " ";
                echo $data[$i][$model_col] . " ";
                echo $data[$i][$vin_col] . " ";
                echo $data[$i][$custodian_col] . " ";
                echo $data[$i][$dept_col] . " ";
                echo $data[$i][$acq_date_col] . " ";
                echo $data[$i][$cost_col] . " ";
                echo $data[$i][$po_col] . " ";
                echo $data[$i][$profile_id_col] . " ";
                echo "<br><br>";
            } else {
                echo "Room tag not found for location " . $location_array[$i][1] . " in building " . $location_array[$i][0] . ". Skipping asset " . $data[$i][$tag_col] . ".<br>";
                $room_tag = 'N/A';
                continue;
            }
        }
        $dbh->commit();
    } catch (Exception $e) {
        $dbh->rollBack();
        echo "Failed to add assets: " . $e->getMessage();
        echo "<br>";
        echo "Rolling back changes...";
    }
}
function searchstr($string, $char)
{
    foreach (str_split($string) as $var) {
        if ($char === $var) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulk Asset Upload</title>
    <link rel="stylesheet" href="styles.css">
<style>
* {
    margin:0;
}
</style>
</head>

<body>
    <form action="bulk_add.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xlsx, .xls, .csv" required>
        <button type="submit">Upload</button>
    </form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addAssetsButton = document.getElementById('add-assets');
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    addAssetsButton.addEventListener('click', function() {
        let selectedAssets = [];
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedAssets.push(checkbox.value);
            }
        });
        if (selectedAssets.length > 0) {
            alert('Selected Assets: ' + selectedAssets.join(', '));
        } else {
            alert('No assets selected.');
        }
    });
});
</script>
        </body>

</html>
