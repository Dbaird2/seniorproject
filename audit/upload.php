<?php
include_once("../config.php");
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    $file_type_check = substr($fileName, strlen($fileName) - 4);
    if ($file_type_check != 'xlsx' && $file_type_check != '.xls') {
        echo "<h3'>File type not allowed</h3>";
        return;
    }

    $uploadDir = 'uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($fileName);

    if (move_uploaded_file($fileTmpPath, $filePath)) {
    } else {
        echo "Error uploading file.";
    }
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
            $_SESSION['data'] = array_values($data);
            $_SESSION['info'] = [$highest_row, $highest_col, $filePath];
            header('Location: auditing.php');
            exit();
        } else {
            unset($_SESSION['data']);
            unset($_SESSION['info']);
            unset($_SESSION['max_rows']);
            echo "<h1>Blank File given</h1>";
        }
        
        
}
include_once("../navbar.php");
?>
<br><br><br>
<form id="sheet" name="form" action="upload.php" method="POST" enctype="multipart/form-data">
    <label for="file">Enter File:</label>
    <input type="file" name="file" id="filePath">
    <button type="submit">Submit</button>
</form>
