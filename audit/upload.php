<?php
include_once("../config.php");
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

?>
<link href="https://dataworks-7b7x.onrender.com/tailwind/output.css" rel="stylesheet">

<?php

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
<label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file">Upload file</label>
<input id="filePath" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="file">
<button type="submit" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">
  Submit
</button>
</form>
