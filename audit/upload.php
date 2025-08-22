<?php
include_once("../config.php");
require '../vendor/autoload.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

use PhpOffice\PhpSpreadsheet\IOFactory;


$blank_msg = '';
check_auth();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file_tmp_path = $_FILES['file']['tmp_name'];
  $file_name = $_FILES['file']['name'];
  $file_size = $_FILES['file']['size'];
  $file_type = $_FILES['file']['type'];

  $excel_sheet = false;
  $csv = false;

  $file_type_check = substr($file_name, strlen($file_name) - 4);
  if ($file_type_check == 'xlsx' || $file_type_check == '.xls') {
    $excel_sheet = true;
  }
  if ($file_type_check == '.csv') {
    $csv = true;
  }

  $upload_dir = 'uploads/';

  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  $file_path = $upload_dir . basename($file_name);

  if (move_uploaded_file($file_tmp_path, $file_path)) {
  }
  if ($excel_sheet) {
    $spreadsheet = IOFactory::load($file_path);
    $worksheet = $spreadsheet->getActiveSheet();
    $data = $worksheet->toArray();
    $highest_row = $worksheet->getHighestRow();
    $highest_col = $worksheet->getHighestColumn();
    unset($_SESSION['data']);
    $continue = false;
    if ($data[0][11] === 'Tag Status') {
      $continue = true;
    }
    if (count($data) >= 2) {
      $header_index = 0;
      if (empty($data[0][5])) {
        $header_index = 1;
        unset($data[0]);
      }
      /* SHEET HAS 2 ROW HEADERS */
      if (!isset($data[0])) {
        $skipped = 2;
        foreach ($data as $index => $row) {

          if ($index === 0) {
            continue;
          }

          if (in_array('Tag Number', $row) || $row[1] === '' || $row[1] === NULL) {
            $skipped++;
            continue;
          }
          foreach ($row as $r_index => $r_row) {
            if (
              trim($data[1][$r_index]) === 'Fund' || trim($data[1][$r_index]) === 'Asset ID' || trim($data[1][$r_index]) === 'Asset Type' || trim($data[1][$r_index]) === 'Model' ||
              trim($data[1][$r_index]) === 'Manufacturer' || trim($data[1][$r_index]) === 'Project' || trim($data[1][$r_index]) === 'Class' || trim($data[1][$r_index]) === 'Profile ID'
            ) {
              continue;
            }
            if ($data[1][$r_index] === 'Tag Number') {
                $select_q = "SELECT asset_notes FROM asset_info WHERE asset_tag = :tag";
                $select_stmt = $dbh->prepare($select_q);
                $select_stmt->execute([":tag"=>$r_row]);
                $asset_notes = $select_stmt->fetchColumn();
                if (!empty($asset_notes)) {
                    $info = explode(",", $asset_notes);
                    $_SESSION['data'][$index - $skipped]['Tag Status'] = 'Found';
                    $_SESSION['data'][$index - $skipped]['Found Room Tag'] = $info[0];
                    $_SESSION['data'][$index - $skipped]['Found Note'] = $info[1];
                    $_SESSION['data'][$index - $skipped]['Found Timestamp'] = '';
                } else {
                    $_SESSION['data'][$index - $skipped]['Tag Status'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Room Tag'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Note'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Timestamp'] = '';
                }
            }
            $_SESSION['data'][$index - $skipped][$data[1][$r_index]] = $r_row;
          }
        }
      /* SHEET HAS 1 ROW HEADER */
      } else if ($continue === false) {
        $skipped = 1;
        foreach ($data as $index => $row) {
          if (in_array('Tag Number', $row) || $row[1] === '' || $row[1] === NULL) {
            $skipped++;
            continue;
          }
          foreach ($row as $r_index => $r_row) {
            if (
              trim($data[0][$r_index]) === 'Fund' || trim($data[0][$r_index]) === 'Asset ID' || trim($data[0][$r_index]) === 'Asset Type' || trim($data[0][$r_index]) === 'Model' ||
              trim($data[0][$r_index]) === 'Manufacturer' || trim($data[0][$r_index]) === 'Project' || trim($data[0][$r_index]) === 'Class' || trim($data[0][$r_index]) === 'Profile ID'
            ) {
              continue;
            }
            if ($data[0][$r_index] === 'Tag Number') {
                $select_q = "SELECT asset_notes FROM asset_info WHERE asset_tag = :tag";
                $select_stmt = $dbh->prepare($select_q);
                $select_stmt->execute([":tag"=>$r_row]);
                $asset_notes = $select_stmt->fetchColumn();
                if (!empty($asset_notes)) {
                    $info = explode(",", $asset_notes);
                    $_SESSION['data'][$index - $skipped]['Tag Status'] = 'Found';
                    $_SESSION['data'][$index - $skipped]['Found Room Tag'] = $info[0];
                    $_SESSION['data'][$index - $skipped]['Found Note'] = $info[1];
                    $_SESSION['data'][$index - $skipped]['Found Timestamp'] = '';
                } else {
                    $_SESSION['data'][$index - $skipped]['Tag Status'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Room Tag'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Note'] = '';
                    $_SESSION['data'][$index - $skipped]['Found Timestamp'] = '';
                }
            }

            $_SESSION['data'][$index - $skipped][$data[0][$r_index]] = $r_row;
          }
        }
        /* CONTINUE FROM SHEET */
      } else if ($continue === true) {
        foreach ($data as $index => $row) {
          if (in_array('Tag Number', $row) || $row[1] === '' || $row[1] === NULL) {
            continue;
          }
          foreach ($row as $r_index => $r_row) {
            if ($data[0][$r_index] !== '' && $data[0][$r_index] !== null) {
              $_SESSION['data'][$index][$data[0][$r_index]] = $r_row;
            }
          }
        }
      }


      $_SESSION['info'] = [$highest_row, $highest_col, $file_path, $_POST['audit-type'], $file_name];
      if (isset($_SESSION['data'][-1]) || isset($_SESSION['data'][0])) {
          try {
              $keys = array_keys($_SESSION['data'][-1]);
          } catch (Exception $e) {
              $keys = array_keys($_SESSION['data'][0]);
          }
        if (!in_array("Tag Number", $keys)) {
          $blank_msg = "Headers cannot be found";
        } else {
          header('Location: auditing.php');
          exit();
        }
      } else {
        $blank_msg = "File cannot be empty 1";
        echo "<pre>";
        var_dump($_SESSION['data']);
        echo "</pre>";
      }
    } else {
      unset($_SESSION['data']);
      unset($_SESSION['info']);
      unset($_SESSION['max_rows']);
      $blank_msg = "File cannot be empty 2";
    }
  }
/*  if ($csv) {
    if (($handle = fopen($file_name, 'r')) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c = 0; $c < $num; $c++) {
          echo $data[$c] . "<br />\n";
        }
      }
      fclose($handle);
    }
  }*/
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit File Upload</title>
</head>
<style>
  * {
    margin: 0;
  }

  .is-upload {
    display: flex;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    min-height: 100vh;

  }

  .container {
    --transition: 300ms;
    --folder-W: 120px;
    --folder-H: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    padding: 10px;
    background: linear-gradient(135deg, #6dd5ed, #2193b0);
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    height: calc(var(--folder-H) * 2.2);
    position: relative;
    width: 100%;
  }

  .folder {
    position: absolute;
    top: -25px;
    left: calc(50% - 60px);
    animation: float 2.5s infinite ease-in-out;
    transition: transform var(--transition) ease;
  }

  .folder:hover {
    transform: scale(1.05);
  }

  .folder .front-side,
  .folder .back-side {
    position: absolute;
    transition: transform var(--transition);
    transform-origin: bottom center;
  }

  .folder .back-side::before,
  .folder .back-side::after {
    content: "";
    display: block;
    background-color: white;
    opacity: 0.5;
    z-index: 0;
    width: var(--folder-W);
    height: var(--folder-H);
    position: absolute;
    transform-origin: bottom center;
    border-radius: 15px;
    transition: transform 350ms;
    z-index: 0;
  }

  .container:hover .back-side::before {
    transform: rotateX(-5deg) skewX(5deg);
  }

  .container:hover .back-side::after {
    transform: rotateX(-15deg) skewX(12deg);
  }

  .folder .front-side {
    z-index: 1;
  }

  .container:hover .front-side {
    transform: rotateX(-40deg) skewX(15deg);
  }

  .folder .tip {
    background: linear-gradient(135deg, #ff9a56, #ff6f56);
    width: 80px;
    height: 20px;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    position: absolute;
    top: -10px;
    z-index: 2;
  }

  .folder .cover {
    background: linear-gradient(135deg, #ffe563, #ffc663);
    width: var(--folder-W);
    height: var(--folder-H);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
  }

  .custom-file-upload {
    font-size: 1.1em;
    color: #ffffff;
    text-align: center;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: background var(--transition) ease;
    display: inline-block;
    padding: 10px 35px;
    position: relative;
  }

  .custom-file-upload:hover {
    background: rgba(255, 255, 255, 0.4);
  }

  .custom-file-upload input[type="file"] {
    display: none;
  }

  @keyframes float {
    0% {
      transform: translateY(0px);
    }

    50% {
      transform: translateY(-5px);
    }

    100% {
      transform: translateY(0px);
    }
  }

  .button-9 {
    appearance: button;
    backface-visibility: hidden;
    background-color: #405cf5;
    border-radius: 6px;
    border-width: 0;
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .1) 0 2px 5px 0, rgba(0, 0, 0, .07) 0 1px 1px 0;
    box-sizing: border-box;
    color: #fff;
    cursor: pointer;
    font-size: 100%;
    height: 44px;
    line-height: 1.15;
    margin: 12px 0 0;
    outline: none;
    overflow: hidden;
    padding: 0 25px;
    position: relative;
    text-align: center;
    text-transform: none;
    transform: translateZ(0);
    transition: all .2s, box-shadow .08s ease-in;
    -webkit-user-select: none;
    touch-action: manipulation;
    width: 50%;
  }

  .button-9:disabled {
    cursor: default;
  }

  .button-9:focus {
    box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
  }

  .is-upload .header {
    margin-bottom: 100px;
  }

  .form-input {
    padding: 14px 16px;
    border: 2px solid #e3f2fd;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background-color: #fafafa;
    color: #333;
  }
</style>
<?php include_once("../navbar.php"); ?>

<body>

  <div class="is-upload">
    <form id="sheet" name="form" action="upload.php" method="POST" enctype="multipart/form-data">
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
          <select class="form-input" name="audit-type" id="audit-type">
            <option value="cust">Self Audit</option>
            <option value="ocust">Old Self Audit</option>
<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') { ?>
            <option value="mgmt">Management</option>
            <option value="omgmt">Old Management</option>
            <option value="SPA">SPA</option>
            <option value="oSPA">Old SPA audit</option>
        <?php } ?>
          </select>
        <button class="button-9" type="submit" role="button">Submit</button>

      </div>
      <h2 style="color:red;justify-self:center;"><?php echo $blank_msg; ?></h2>
    </form>
  </div>
</body>
