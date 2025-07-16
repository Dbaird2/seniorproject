<?php
include_once("../config.php");
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
if (!isset($_SESSION['role'])) {
    header("Location: https://dataworks-7b7x.onrender.com/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    $excel_sheet = false;
    $csv = false;

    $file_type_check = substr($fileName, strlen($fileName) - 4);
    if ($file_type_check == 'xlsx' || $file_type_check == '.xls') {
        $excel_sheet = true;
    } 
    if ($file_type_check == '.csv') {
        $csv = true;
    }

    $uploadDir = 'uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($fileName);

    if (move_uploaded_file($fileTmpPath, $filePath)) {
    } else {
    }
    if ($excel_sheet) {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        //$data = $worksheet->toArray();
        $data = $worksheet->toArray();
        $highest_row= $worksheet->getHighestRow();
        $highest_col = $worksheet->getHighestColumn();
        if ($data[0][2] == NULL) {
            unset($data[0]);
        }
        if (count($data)>1) {
            $_SESSION['saved_tags'] = [];
            $_SESSION['data'] = array_values($data);
            $_SESSION['info'] = [$highest_row, $highest_col, $filePath];
            header('Location: auditing.php');
            exit();
        } else {
            unset($_SESSION['saved_tags']);
            unset($_SESSION['data']);
            unset($_SESSION['info']);
            unset($_SESSION['max_rows']);
            echo "<h1>Blank File given</h1>";
        }
    } 
    if ($csv) {
        if (($handle = fopen($fileName, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                echo "<p> $num fields in line $row: <br /></p>\n";
                $row++;
                for ($c=0; $c < $num; $c++) {
                    echo $data[$c] . "<br />\n";
                }
            }
            fclose($handle);
        }
    }

        
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
.drop-container {
  position: relative;
  display: flex;
  gap: 10px;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 200px;
  padding: 20px;
  border-radius: 10px;
  border: 2px dashed #555;
  color: #444;
  cursor: pointer;
  transition: background .2s ease-in-out, border .2s ease-in-out;
}

.drop-container:hover {
  background: #eee;
  border-color: #111;
}

.drop-container:hover .drop-title {
  color: #222;
}

.drop-title {
  color: #444;
  font-size: 20px;
  font-weight: bold;
  text-align: center;
  transition: color .2s ease-in-out;
}
<body>
<?php include_once("../navbar.php"); ?>
<form id="sheet" name="form" action="upload.php" method="POST" enctype="multipart/form-data">
<label for="file" class="drop-container" id="dropcontiner">
    <span class="drop-title" id="dropcontainer" class="drop-container">Drop file here</span>
    or
    <input type="file" name="file" id="filePath" accept="image/*">
    <button type="submit">Submit</button>
</label>
</form>
</body>
