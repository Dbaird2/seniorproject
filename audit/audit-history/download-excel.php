<?php
include_once(__DIR__ . "/../../config.php");
require_once __DIR__ . '/../../vendor/autoload.php';
/* THIS IS FOR EXCEL DOWNLOAD */

check_auth();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$auditor = $_GET['auditor'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';
$audit_id = $_GET['audit_id'] ?? '';

$select_query = "SELECT * FROM audit_history WHERE dept_id = :dept_id AND auditor = :auditor AND audit_id = :audit_id ORDER BY finished_at DESC";
$stmt = $dbh->prepare($select_query);
$stmt->execute([':dept_id' => $dept_id, ':auditor' => $auditor, ':audit_id' => $audit_id]);
$audit_details = $stmt->fetch(PDO::FETCH_ASSOC);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$file_path = $audit_details['dept_id'] . '.xlsx';
$data = json_decode($audit_details['audit_data'], true);
try {
    //$keys = array_keys($data[0]);
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    foreach (range('A', 'Z') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1', 'P1', 'Q1', 'R1', 'S1', 'T1', 'U1', 'V1', 'W1', 'X1', 'Y1', 'Z1', 'AA1', 'AB1'];
    $row_index = 2;


    $fileNameOnly = basename($file_path);
    $filePath = $fileNameOnly;

    $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
    $filePath = str_replace(".xls", "_AUDIT", $filePath);
    $filePath = $filePath . ".xlsx";
    /* foreach ($keys as $index => $key) {
        $sheet->setCellValue($column_letters[$index], $key);
    } */

    $sheet->fromArray($data, NULL, 'A2');

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
    header('Location: https://dataworks-7b7x.onrender.com/audit/download.php?file=' . urlencode($filePath));
    error_reporting(1);
} catch (Exception $e) {
    echo "Something went wrong trying to parse before downloading " . $e;
}
?>
