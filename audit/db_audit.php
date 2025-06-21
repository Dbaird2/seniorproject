<?php
include_once("../config.php");
include_once("../navbar.php");
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;


if (isset($_SESSION['audit_data'])) {
    $data = $_SESSION['audit_data'];
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
}

