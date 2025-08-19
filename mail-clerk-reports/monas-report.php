<?php
ini_set('memory_limit', '512M');
include_once "../config.php";
check_auth("high");
register_shutdown_function(function () {
    error_log('peak MB=' . (memory_get_peak_usage(true) / 1048576));
});
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$file_path = $file_name = $count = NULL;

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function bubbleSort($array)
{
    $count = count($array);

    for ($i = 0; $i < $count - 1; $i++) {
        $swapped = false;
        for ($j = 0; $j < $count - $i - 1; $j++) {
            if ($array[$j][0] > $array[$j + 1][0]) {
                $temp = $array[$j][0];
                $array[$j][0] = $array[$j + 1][0];
                $array[$j + 1][0] = $temp;
                $swapped = true;
            }
        }
        if ($swapped === false) {
            break;
        }
    }
    return $array;
}

if (isset($_POST['submit'])) {
    // while (ob_get_level() > 0) {
    //     ob_end_clean();
    // }
    // @ini_set('zlib.output_compression', 'Off');
    $file_name = $_FILES['file']['name'];
    $file_tmp_path = $_FILES['file']['tmp_name'];
    $ref_file_name = $_FILES['ref-file']['name'];
    $ref_file_tmp_path = $_FILES['ref-file']['tmp_name'];
    $postage_fee = (float)$_POST['postage-fee'];

    $upload_dir = 'uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_path = $upload_dir . basename($file_name);
    $ref_file = $upload_dir . basename($ref_file_name);

    move_uploaded_file($file_tmp_path, $file_path);
    move_uploaded_file($ref_file_tmp_path, $ref_file);

    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    $sheet1 = $sheet->toArray(null, false, false, false);
    $sheet = $spreadsheet->setActiveSheetIndex(1);
    $sheet2 = $sheet->toArray(null, false, false, false);
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet, $sheet);

    $ref_spread_sheet = IOFactory::load($ref_file);
    $ref_sheet = $ref_spread_sheet->getActiveSheet();
    $ref_sheet1 = $ref_sheet->rangeToArray('A1:I200', false, false, false);  // ['A'=>..., 'B'=>..., ...]

    $ref_spread_sheet->disconnectWorksheets();
    unset($ref_spread_sheet, $ref_sheet);
    //gc_collect_cycles();
    //gc_collect_cycles();
    $create_sheet = new SpreadSheet();
    $postage_sheet = $create_sheet->getActiveSheet();
    $final_spread_sheet = new SpreadSheet();
    $new_ref_sheet = $final_spread_sheet->getActiveSheet();

    foreach (range('A', 'T') as $columnID) {
        $postage_sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    foreach (range('A', 'T') as $columnID) {
        $new_ref_sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $postage_sheet->mergeCells('A1:H1');
    $postage_sheet->setCellValue('A1', 'Transaction Log Detail Report');
    $postage_sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $postage_sheet->getStyle('A1')->getFont()->setBold(true);

    $postage_sheet->getStyle('A2')->getFont()->setBold(true);
    $first_day = date('M-01-Y', strtotime('first day of last month'));
    $last_day = date('M-t-Y', strtotime('last day of last month'));
    $date = 'Date Range: ' . $first_day . ' to ' . $last_day;
    $postage_sheet->setCellValue('A2', $date);

    $new_ref_sheet->setCellValue('A1', 'Account');
    $new_ref_sheet->setCellValue('B1', 'Pieces');
    $new_ref_sheet->setCellValue('C1', 'Postage');
    $new_ref_sheet->setCellValue('D1', 'Fee Amount');
    $new_ref_sheet->setCellValue('E1', 'Surcharge');
    $new_ref_sheet->setCellValue('F1', 'BRM');
    $new_ref_sheet->setCellValue('G1', 'BULK');
    $new_ref_sheet->setCellValue('H1', 'Total Charged');
    $new_ref_sheet->setCellValue('K1', 'Mailcode');
    $new_ref_sheet->setCellValue('L1', 'Fund');
    $new_ref_sheet->setCellValue('M1', 'Dept');
    $new_ref_sheet->setCellValue('N1', 'ACCT');
    $new_ref_sheet->setCellValue('O1', 'Program');
    $new_ref_sheet->setCellValue('P1', 'Proj');
    $new_ref_sheet->setCellValue('Q1', 'Class');
    $new_ref_sheet->setCellValue('R1', 'TOTAL');

    foreach (range('A', 'H') as $column) {
        $postage_sheet->getStyle($column . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $postage_sheet->getStyle($column . '3')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD3D3D3');
    }
    $postage_sheet->setCellValue('A3', "Account");
    $postage_sheet->setCellValue('B3', "Date");
    $postage_sheet->setCellValue('C3', "Class of Mail");
    $postage_sheet->setCellValue('D3', "Total Postage Pieces");
    $postage_sheet->setCellValue('E3', "Postage");
    $postage_sheet->setCellValue('F3', "Fees");
    $postage_sheet->setCellValue('G3', "Surcharge Amount");
    $postage_sheet->setCellValue('H3', "Total Charged");

    $postage_pieces = [];
    $rows = [];
    $data_to_write = [];

    for ($i = 7; $i < count($sheet1); $i++) {
        if ((int)$sheet1[$i][0] === 9000) {
            continue;
        }
        if (!empty($sheet1[$i][0])) {
            //$header_index += $count;
            $account = trim((int) $sheet1[$i][0]);
            $start = $i + 1;
        } else {
            if (!empty($account)) {
                if (empty($postage_pieces[$account])) {
                    $postage_pieces[$account] = 0.00;
                }
                if ($sheet1[$i][6] === 'No Class') {
                    continue;
                }
                $data_to_write[$account][] = [$sheet1[$i][1], $sheet1[$i][6], $sheet1[$i][11], $sheet1[$i][12], $sheet1[$i][13], $sheet1[$i][14], $sheet1[$i][15]];

                $g = $sheet1[$i][6];
                if (!preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
                    $postage_pieces[$account] += ((float) $sheet1[$i][11] * $postage_fee);
                }
            }
        }
    }
    for ($i = 8; $i < count($sheet2); $i++) {
        if ((int)$sheet2[$i][0] === 9000) {
            continue;
        }
        if (!empty($sheet2[$i][0])) {
            $account = trim((int) $sheet2[$i][0]);
            $start = $i + 1;
        } else {
            if (!empty($account)) {
                if (empty($postage_pieces[$account])) {
                    $postage_pieces[$account] = 0.00;
                }
                if ($sheet2[$i][6] === 'No Class') {
                    continue;
                }
                $data_to_write[$account][] = [$sheet2[$i][1], $sheet2[$i][6], $sheet2[$i][11], $sheet2[$i][12], $sheet2[$i][13], $sheet2[$i][14], $sheet2[$i][15]];

                $g = $sheet2[$i][6];
                if (!preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
                    $postage_pieces[$account] += ((float) $sheet2[$i][11] * $postage_fee);
                }
            }
        }
    }
    ksort($postage_pieces);
    $keys = array_keys($postage_pieces);
    $start_of_data = 4;
    foreach ($keys as $index => $row) {
        if (empty($data_to_write[$row])) {
            continue;
        }
        $postage_sheet->setCellValue("A" . $start_of_data, $row);
        $postage_sheet->setCellValue("B" . $start_of_data, $postage_pieces[$row]);
        $postage_sheet->getStyle('A' . $start_of_data)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('000000');
        $postage_sheet->getStyle('A' . $start_of_data)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        $postage_sheet->getStyle('B' . $start_of_data)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('000000');
        $postage_sheet->getStyle('B' . $start_of_data)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        
        $start_of_data++;

        foreach ($data_to_write[$row] as $info) {
            echo $info[0] . ' ' . $row . "<br>";
            $postage_sheet->setCellValue("B" . $start_of_data, $info[0]);
            foreach (range('A', 'H') as $column) {
                $postage_sheet
                    ->getStyle($column . $start_of_data)
                    ->getBorders()
                    ->getOutline()
                    ->setBorderStyle(Border::BORDER_THICK)
                    ->setColor(new Color('FFD3D3D3'));
            }
            $postage_sheet->setCellValue("C" . $start_of_data, $info[1]);
            $postage_sheet->setCellValue("D" . $start_of_data, $info[2]);
            $postage_sheet->setCellValue("E" . $start_of_data, $info[3]);
            $postage_sheet->setCellValue("F" . $start_of_data, $info[4]);
            $postage_sheet->setCellValue("G" . $start_of_data, $info[5]);
            $postage_sheet->setCellValue("H" . $start_of_data, $info[6]);
            $start_of_data++;
        }
    }

    $ref_index = 2;
    $last_index = count($postage_pieces) + 1;
    $new_ref_sheet->setCellValue('A' . $last_index, 'Grand Total');
    $new_ref_sheet->setCellValue('B' . $last_index, 0);
    $new_ref_sheet->setCellValue('C' . $last_index, '-');
    $new_ref_sheet->setCellValue('D' . $last_index, '-');
    $new_ref_sheet->setCellValue('E' . $last_index, '-');
    $new_ref_sheet->setCellValue('F' . $last_index, '-');
    $new_ref_sheet->setCellValue('G' . $last_index, '-');
    $new_ref_sheet->setCellValue('H' . $last_index, '-');
    $new_ref_sheet->setCellValue('L' . $last_index, 'BK001');
    $new_ref_sheet->setCellValue('N' . $last_index, '107800');
    $new_ref_sheet->setCellValue('Q' . $last_index, 'C1060');
    foreach (range('B', 'H') as $column) {
        $postage_sheet
            ->getStyle($column . $last_index)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THICK)
            ->setColor(new Color('FFD3D3D3'));
    }
    $range   = "A1:H{$last_index}";
    $new_ref_sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF000000'], // ARGB (FF = opaque)
            ],
        ],
    ]);
    $range   = "K1:R{$last_index}";
    $new_ref_sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF000000'], // ARGB (FF = opaque)
            ],
        ],
    ]);
    foreach ($keys as $index => $key) {
        if (empty($data_to_write[$key])) {
            continue;
        }
        foreach ($ref_sheet1 as $ref) {
            if ($ref[0] === $key) {
                $new_ref_sheet->setCellValue('A' . $ref_index, $key);
                $new_ref_sheet->setCellValue('L' . $ref_index, $ref[2]);
                $new_ref_sheet->setCellValue('M' . $ref_index, $ref[3]);
                $new_ref_sheet->setCellValue('N' . $ref_index, $ref[4]);
                $ref_index++;
            }
        }
    }
    $last_month = date('M-Y', strtotime("-1 Month"));
    $t1 = tempnam(sys_get_temp_dir(), 'rpt1_');
    $t2 = tempnam(sys_get_temp_dir(), 'rpt2_');

    $w1 = new Xlsx($create_sheet);
    $w1->setPreCalculateFormulas(false);
    $w1->save($t1);
    $create_sheet->disconnectWorksheets();
    unset($w1, $create_sheet);

    $w2 = new Xlsx($final_spread_sheet);
    $w2->setPreCalculateFormulas(false);
    $w2->save($t2);
    $final_spread_sheet->disconnectWorksheets();
    unset($w2, $final_spread_sheet);

    $zip_path = tempnam(sys_get_temp_dir(), 'zip_');
    $zip = new ZipArchive();
    $opened = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($opened !== true) {
        http_response_code(500);
        die('Zip open failed: ' . $opened);
    }


    if (!$zip->addFile($t1, "Transaction Detail Report {$last_month}.xlsx")) {
        http_response_code(500);
        die('Failed to add t1');
    }
    if (!$zip->addFile($t2, "Postage Report {$last_month}.xlsx")) {
        http_response_code(500);
        die('Failed to add t2');
    }
    if (!$zip->close()) {
        http_response_code(500);
        die('Zip close failed');
    }

    // 3) stream it cleanly
    $size = filesize($zip_path);
    if (!$size) {
        http_response_code(500);
        die('Empty zip');
    }

    // kill any prior output/buffers and disable compression
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ini_set('zlib.output_compression', 'Off');

    // headers
    $zip_name = "Reports_{$last_month}.zip";
    header('Content-Type: application/zip');
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="' . $zip_name . '"');
    header('Content-Length: ' . $size);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: public');

    // stream
    $fp = fopen($zip_path, 'rb');
    fpassthru($fp);
    fclose($fp);

    // cleanup
    @unlink($t1);
    @unlink($t2);
    @unlink($zip_path);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mona's Excel Insert</title>
    <?php include_once "../navbar.php"; ?>
</head>
<style>
    * {
        margin: 0;
    }

    .mona-report {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 50%, #90caf9 100%);
        min-height: 100vh;

    }

    .form {
        color: #333;
        place-self: center;
    }
</style>

<body class="mona-report">
    <div class="form">
        <form action="monas-report.php" enctype="multipart/form-data" method="POST">
            <label for="file">Upload Scanner Report</label><br>
            <input type="file" name="file" id="file_name" accept=".xlsx, .xls" required><br>
            <label for="ref-file">Upload Reference File</label><br>
            <input type="file" name="ref-file" id="ref_file_name" accept=".xlsm" required><br>
            <input type="number" name="postage-fee" step="0.001" required>
            <button name="submit" value="good">Submit</button>
        </form>
    </div>
</body>

</html>
