<?php
  error_reporting(0);  

require_once("config.php");
 ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
  
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
include_once("navbar.php");
// The point of this page is to read a CSV or Excel file then update,add,delete the databases assets
?>
<style>
</style>
<form id="sheet" name="form" action="auditing.php" method="POST" enctype="multipart/form-data">
    <label for="file">Enter File:</label>
    <input type="file" name="file" id="filePath">
    <button type="submit">Submit</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $spreadsheet = new Spreadsheet();

    $filePath = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $dataArray = $sheet->toArray();
    $highest_row = $sheet->getHighestRow();
    $highest_column = $sheet->getHighestColumn();
    $header_row = in_array('Tag Number', $dataArray[2]) ? 1 : 0; 
    $header_row = in_array('Tag Number', $dataArray[1]) ? 1 : 0; 
    $header_row = in_array('Tag', $dataArray[0]) ? 1 : 0; 
    for ($i = $header_row+1; $i < count($dataArray[$header_row]); $i++) {
        if ($dataArray[$header_row][$i] === 'Tag Number' || $dataArray[$header_row][$i] === 'Tag') {
            $tag_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Description' || $dataArray[$header_row][$i] === 'Descr') {
            $descr_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Serial ID' || $dataArray[$header_row][$i] === 'SN') {
            $serial_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Model' || $dataArray[$header_row][$i] === 'Model Number') {
            $model_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'VIN' || $dataArray[$header_row][$i] === 'Vehicle ID') {
            $vin_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Custodian' || $dataArray[$header_row][$i] === 'Custodian Name') {
            $custodian_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Dept' || $dataArray[$header_row][$i] === 'Department') {
            $dept_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Acq Date' || $dataArray[$header_row][$i] === 'Acquisition Date') {
            $acq_date_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'COST Total Cost' || $dataArray[$header_row][$i] === 'Price') {
            $cost_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'PO No.' || $dataArray[$header_row][$i] === 'Purchase Order') {
            $po_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Profile ID' || $dataArray[$header_row][$i] === 'Profile') {
            $profile_id_col =  $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Location') {
            $location_col = $i; // Convert index to column letter 
        }
        if ($dataArray[$header_row][$i] === 'Asset Type') {
            $asset_type_col = $i; // Convert index to column letter 
        }
    }
    $location_array = [];
    $j = 1;
    $ASI = "/^A[SI]?\d+$/";
    $STU = "/^S[RC]?[TU]?\d+$/";
    $CMP = "/^\d+/";
    $FDN = "/^F[DN]?\d+$/";
    $SPA = "/^SPA\d+$/";
    ?>
            <button id="add-assets">Remove Selected Assets</button>
<?php
    echo "Added ASSETS from FILE " . $fileName . " : <br>";
    try {
        $dbh->beginTransaction();
        for ($i = 0; $i < $highest_row; $i++) {
            $location_array[] = explode("-", $dataArray[$i][$location_col]);

            if ($dataArray[$i][$tag_col] === 'Tag Number' || $dataArray[$i][$tag_col] === '' || $dataArray[$i][$profile_id_col] === 'BLDGIMP') {
                continue;
            }
            //echo $dataArray[$i][$location_col] . " ";
            if (preg_match($ASI, $dataArray[$i][$tag_col]) || preg_match($STU, $dataArray[$i][$tag_col]) || 
            preg_match($CMP, $dataArray[$i][$tag_col]) || preg_match($FDN, $dataArray[$i][$tag_col]) || 
            preg_match($SPA, $dataArray[$i][$tag_col])) {
            } else continue;
            
            if ($location_array[$i][0] === '' && !isset($location_array[$i][1])) {
                $location_array[$i][0] = 150;
                $location_array[$i][1] = '000';

            }
            if (($location_array[$i][0] === 'AVC' || $location_array[$i][0] === 'LUC') && isset($location_array[$i][1])) {
                $location_array[$i][0] = 90;
            }
            if ($dataArray[$i][$profile_id_col] === 'EQUIP-10' || $dataArray[$i][$profile_id_col] === 'NONCAPCOMP') {
                $dataArray[$i][$profile_id_col] = 10;
            } else if ($dataArray[$i][$profile_id_col] === 'EQUIP-20' || $dataArray[$i][$profile_id_col] === 'CAPCOMP') {
                $dataArray[$i][$profile_id_col] = 20;
            } else if ($dataArray[$i][$profile_id_col] === 'EQUIP-30' || $dataArray[$i][$profile_id_col] === 'CAPITAL') {
                $dataArray[$i][$profile_id_col] = 30;
            } else if ($dataArray[$i][$profile_id_col] === 'EQUIP-40' || $dataArray[$i][$profile_id_col] === 'NONCAPITAL') {
                $dataArray[$i][$profile_id_col] = 40;
            } else if ($dataArray[$i][$profile_id_col] === 'EQUIP-05') {
                $dataArray[$i][$profile_id_col] = 5;
            }
            
            // CHECK IF ASSET IS ALREADY IN SERVICE
            $loc_change = false;
            $set_array = [];
            $check_tag_query = "SELECT 1 FROM asset_info WHERE asset_tag = :tag UNION SELECT 1 FROM complete_asset_view WHERE asset_tag = :tag OR new_tag = :tag";
            $stmt = $dbh->prepare($check_tag_query);
            $stmt->execute([':tag' => $dataArray[$i][$tag_col]]);
            $existing_asset = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing_asset) {
                continue;
            } 
            // GET ROOM TAG FROM ROOM TABLE
            $get_room_tag_query = "SELECT room_tag FROM room_table WHERE room_loc = :room_loc AND bldg_id = :bldg_id";
            $stmt = $dbh->prepare($get_room_tag_query);
            $stmt->execute([':room_loc' => $location_array[$i][1], ':bldg_id' => $location_array[$i][0]]);
            $room_tag = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($room_tag) {
            // INSERT INTO ASSET_INFO TABLE
                $room_tag = $room_tag['room_tag'];
                $insert_query = "INSERT INTO asset_info (asset_tag, asset_model, room_tag, asset_name, serial_num, po, asset_price, asset_type, lifecycle, dept_id) VALUES (:asset_tag, :asset_model, :room_tag, :asset_name, :serial_num, :po, :asset_price, :asset_type, :lifecycle, :dept_id)";
                $stmt = $dbh->prepare($insert_query);
                $stmt->execute([
                    ':asset_tag' => $dataArray[$i][$tag_col],
                    ':asset_model' => $dataArray[$i][$model_col],
                    ':room_tag' => (int)$room_tag,
                    ':asset_name' => $dataArray[$i][$descr_col],
                    ':serial_num' => $dataArray[$i][$serial_col],
                    ':po' => $dataArray[$i][$po_col],
                    ':asset_price' => (float)$dataArray[$i][$cost_col],
                    ':asset_type' => $dataArray[$i][$asset_type_col],
                    ':lifecycle' => $dataArray[$i][$profile_id_col],
                    ':dept_id' => $dataArray[$i][$dept_col]
                ]);
                echo $j++ . " " . $dataArray[$i][$tag_col] . " ";
                echo $location_array[$i][0] . " ";
                if (isset($location_array[$i][1])) {
                    echo $location_array[$i][1] . " ";
                } else {
                    echo "N/A ";
                }
                echo $dataArray[$i][$descr_col] . " ";
                echo $dataArray[$i][$serial_col] . " ";
                echo $dataArray[$i][$model_col] . " ";
                echo $dataArray[$i][$vin_col] . " ";
                echo $dataArray[$i][$custodian_col] . " ";
                echo $dataArray[$i][$dept_col] . " ";
                echo $dataArray[$i][$acq_date_col] . " ";
                echo $dataArray[$i][$cost_col] . " ";
                echo $dataArray[$i][$po_col] . " ";
                echo $dataArray[$i][$profile_id_col] . " ";
                echo "<br>";
            } else {
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

?>
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
