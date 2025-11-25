<?php
include_once 'config.php';
include_once 'vendor/autoload.php';

/* THIS IS FOR EXCEL DOWNLOAD */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$auditor = $_GET['auditor'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';
$audit_id = $_GET['audit_id'] ?? '';


use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file'])) {
  $file_tmp_path = $_FILES['file']['tmp_name'];
  $file_name     = $_FILES['file']['name'];
  $file_size     = $_FILES['file']['size'];
  $file_type     = $_FILES['file']['type'];

  // Get extension safely
  $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
  $excel_sheet = in_array($ext, ['xls', 'xlsx'], true);
  $csv         = ($ext === 'csv');

  if (!$excel_sheet && !$csv) {
    die('Unsupported file type: ' . htmlspecialchars($ext));
  }

  // Save in same directory as script (you can change to /uploads if you want)
  $file_path = __DIR__ . '/' . basename($file_name);

  // Debug
  // var_dump([
  //     'tmp'        => $file_tmp_path,
  //     'dest'       => $file_path,
  //     'exists_tmp' => file_exists($file_tmp_path),
  // ]);

  if (!is_uploaded_file($file_tmp_path)) {
    die('No valid uploaded file found');
  }
  if (!move_uploaded_file($file_tmp_path, $file_path)) {
    die('Failed to move uploaded file to ' . $file_path);
  }


  if (!file_exists($file_path)) {
    die('File not found after move: ' . $file_path);
  }

  $spreadsheet = IOFactory::load($file_path);
  $worksheet = $spreadsheet->getActiveSheet();
  $data = $worksheet->toArray();
  $highest_row = $worksheet->getHighestRow();
  $highest_col = $worksheet->getHighestColumn();
  $continue = false;
  $it_regex = '/\b(LENOVO)|(APPLE)|(DELL)|(HP)|(CPU)|(MACBOOK)|(CHROMEBOOK)|(TABLET)|(SERVER)|(PRECISION\s\d*\sTOWER)|(iPAD)\b/i';
  foreach ($data as $row) {
    if (preg_match($it_regex, $row[4])) {
      $is_it = 1;
      echo '<br>is_it true<br>';
    } else {
      $is_it = 0;
    }
    if ($row[5] === 'I') {
      $status = 'In Service';
    } else if ($row[5] === 'D') {
      $status = 'Disposed';
    }
    echo 'Tag: ' . $row[3] . ' Descr ' . $row[4] . ' status ' . $row[5] . ' Serial ' . $row[6] . ' Location ' . $row[7] . ' Dept ' . $row[9] . ' Fund ' . $row[10] . ' Acq Date ' . $row[13] . ' Type ' . $row[14] . ' Deptid? ' . $row[15] . '<br>';
    $location_array = explode('-', $row[7]);
    if (count($location_array) === 2) {
      echo $location_array[0] . ' ' . $location_array[1];
    }

$regex = '/^(\d{1,3})$/';
    if (count($location_array) === 2) {
        if ($location_array[0] === '44A') {
            $location_array[0] = 44;
        }
        if ((int)$location_array[0] === 0 || !preg_match($regex, $location_array[0])) {
            echo 'Invalid Building ID<br>';
            $insert = 'INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_status, asset_type, dept_id, is_it, fund) VALUES
                (?,?,?,?,?,?,?,?,?,?) ON CONFLICT (asset_tag) DO UPDATE SET fund = EXCLUDED.fund, is_it = EXCLUDED.is_it';
            $stmt = $dbh->prepare($insert);
            $stmt->execute([$row[3], $row[4], $row[13], $row[6], 1.00, $status, $row[14], $row[9], $is_it, $row[10]]);
            echo 'Insert/Updated No Location<br>';
            continue;
        }
      $room_select = 'SELECT room_tag FROM room_table WHERE bldg_id = :bid AND room_loc = :loc';
      $stmt = $dbh->prepare($room_select);
      $stmt->execute([':bid' => $location_array[0], ':loc' => $location_array[1]]);
      $room_tag = $stmt->fetchColumn();
      if (!$room_tag) {
        $insert = 'INSERT INTO room_table (bldg_id, room_loc) VALUES (?, ?)';
        $stmt = $dbh->prepare($insert);
        $stmt->execute([$location_array[0], $location_array[1]]);
        $stmt = $dbh->prepare($room_select);
        $stmt->execute([':bid' => $location_array[0], ':loc' => $location_array[1]]);
        $room_tag = $stmt->fetchColumn();
      }
      $insert = 'INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_status, asset_type, room_tag, dept_id, is_it, fund) VALUES
      (?,?,?,?,?,?,?,?,?,?,?) ON CONFLICT (asset_tag) DO UPDATE SET fund = EXCLUDED.fund, is_it = EXCLUDED.is_it';
      $stmt = $dbh->prepare($insert);
      $stmt->execute([$row[3], $row[4], $row[13], $row[6], 1.00, $status, $row[14], $room_tag, $row[9], $is_it, $row[10]]);
      echo 'Inserted tag<br>';
    } else {
        echo 'Invalid Building ID<br>';
        $insert = 'INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_status, asset_type, dept_id, is_it, fund) VALUES
            (?,?,?,?,?,?,?,?,?,?) ON CONFLICT (asset_tag) DO UPDATE SET fund = EXCLUDED.fund, is_it = EXCLUDED.is_it';
        $stmt = $dbh->prepare($insert);
        $stmt->execute([$row[3], $row[4], $row[13], $row[6], 1.00, $status, $row[14], $row[9], $is_it, $row[10]]);
        echo 'Insert/Updated No Location<br>';
    }

  }
}

?>
<form id="sheet" name="form" action="insert_assets.php" method="POST" enctype="multipart/form-data">
  <h4 class="header">Concerned about Excel formatting? Check out our help page <a href="#">here</a></h4>
  <div class="container">
    <div class="folder">
      <div class="front-side">
        <div class="tip"></div>
        <div class="cover"></div>
      </div>
      <div class="back-side cover"></div>
    </div>
    <label class="custom-file-upload">
      <input class="title" type="file" name="file" id="filePath" accept=".xlsx, .xls" />
      Choose a file
    </label>
    <input class="form-input" list="dept-ids" type="search" name="list-type" placeholder="Search Dept Name">
    <datalist id="dept-ids" id="list" name="list">
      <option value="SPA Audit">SPA</option>

    </datalist>
    <button class="button-9" type="submit" role="button">Submit</button>

  </div>
</form>
