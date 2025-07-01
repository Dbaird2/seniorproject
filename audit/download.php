<?php
error_reporting(0);
require __DIR__ . '/vendor/autoload.php';

if (isset($_GET['file'])) {
    $baseDir = __DIR__ . '/exports/';
    $fileName = basename(urldecode($_GET['file']));
    $filePath = $baseDir . $fileName;

    if (file_exists($filePath)) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filePath));
        flush();
        readfile($filePath);
        exit;
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified.";
}
?>
