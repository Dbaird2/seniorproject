<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (isset($_GET['file'])) {
    $filePath = urldecode($_GET['file']);

    if (file_exists($filePath)) {
        // Set headers for file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . basename($filePath) . '"');
        header('Cache-Control: max-age=0');

        echo $filePath;
        //readfile($filePath);

        // Optionally, delete the file after sending (if you don't need it anymore)
        // unlink($filePath);
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified.";
}
?>
