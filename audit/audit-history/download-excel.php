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
$file_path = $audit_details['dept_id'] . '_AUDIT.xlsx';
$data = json_decode($audit_details['audit_data'], true);
$keys = [
    0 => 'Unit',
    1 => 'Tag Number',
    2 => 'Tag Status',
    3 => 'Found Room Tag',
    4 => 'Found Room Number',
    5 => 'Found Building Name',
    6 => 'Found Note',
    7 => 'Found Timestamp',
    8 => 'Description',
    9 => 'Location',
    10 => 'Dept',
    11 => 'Serial ID',
    12 => 'VIN',
    13 => 'Custodian'
]; 
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    foreach (range('A', 'Z') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1', 'P1', 'Q1', 'R1', 'S1', 'T1', 'U1', 'V1', 'W1', 'X1', 'Y1', 'Z1', 'AA1', 'AB1'];
    $row_index = 2;
    foreach ($keys as $index => $key) {
        $sheet->setCellValue($column_letters[$index], $key);
    } 
    $index = 2;
    foreach ($data as $row) {
        if ($row['Tag Number'] !== 'Tag Number' && $row['Tag Number'] !== '' && $row['Tag Number'] !== NULL) {
            $sheet->setCellValue('A'.$index, $row['Unit']);
            $sheet->setCellValue('B'.$index, $row['Tag Number']);
            $sheet->setCellValue('C'.$index, $row['Tag Status']);
            $sheet->setCellValue('D'.$index, $row['Found Room Tag']);
            if (!empty($row['Found Room Number'])) {
                $sheet->setCellValue('E'.$index, $row['Found Room Number']) ?? 'N/A';
                $sheet->setCellValue('F'.$index, $row['Found Building Name']) ?? 'N/A';
            }
            $sheet->setCellValue('G'.$index, $row['Found Note']);
            $sheet->setCellValue('H'.$index, $row['Found Timestamp']);
            $sheet->setCellValue('I'.$index, $row['Descr']);
            $sheet->setCellValue('J'.$index, $row['Location']);
            $sheet->setCellValue('K'.$index, $row['Dept']);
            $sheet->setCellValue('L'.$index, $row['Serial ID']);
            $sheet->setCellValue('M'.$index, $row['VIN']);
            $sheet->setCellValue('N'.$index++, $row['Custodian']);
        }
    }
    //$sheet->fromArray($data, NULL, 'A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // For .xlsx
    // For .xls: header('Content-Type: application/vnd.ms-excel');
    // For .csv: header('Content-Type: text/csv');

    header('Content-Disposition: attachment; filename="' . urlencode($file_path) . '"');
    header('Cache-Control: max-age=0'); // Optional, to prevent caching
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    echo "Something went wrong trying to parse before downloading " . $e;
}
?>
