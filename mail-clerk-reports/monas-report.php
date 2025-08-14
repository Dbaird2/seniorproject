<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');
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

                $temp = $array[$j][1];
                $array[$j][1] = $array[$j + 1][1];
                $array[$j + 1][1] = $temp;

                $temp = $array[$j][2];
                $array[$j][2] = $array[$j + 1][2];
                $array[$j + 1][2] = $temp;

                $temp = $array[$j][3];
                $array[$j][3] = $array[$j + 1][3];
                $array[$j + 1][3] = $temp;
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
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    @ini_set('zlib.output_compression', 'Off');
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

    $create_sheet = new SpreadSheet();
    $new_sheet = $create_sheet->getActiveSheet();

    $ref_spread_sheet = IOFactory::load($ref_file);
    $ref_sheet = $ref_spread_sheet->getActiveSheet();

    $final_spread_sheet = new SpreadSheet();
    $final_sheet = $final_spread_sheet->getActiveSheet();

    foreach (range('A', 'Z') as $columnID) {
        $new_sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    foreach (range('A', 'Z') as $columnID) {
        $final_sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    /* NEW SHEET HEADER */
    $new_sheet->mergeCells('A1:H1');
    $new_sheet->setCellValue('A1', 'Transaction Log Detail Report');
    $new_sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $new_sheet->getStyle('A1')->getFont()->setBold(true);

    $new_sheet->getStyle('A2')->getFont()->setBold(true);
    $first_day = date('M-01-Y', strtotime('first day of last month'));
    $last_day = date('M-t-Y', strtotime('last day of last month'));
    $date = 'Date Range: ' . $first_day . ' to ' . $last_day;
    $new_sheet->setCellValue('A2', $date);

    $final_sheet->setCellValue('A1', 'Account');
    $final_sheet->setCellValue('B1', 'Pieces');
    $final_sheet->setCellValue('C1', 'Postage');
    $final_sheet->setCellValue('D1', 'Fee Amount');
    $final_sheet->setCellValue('E1', 'Surcharge');
    $final_sheet->setCellValue('F1', 'BRM');
    $final_sheet->setCellValue('G1', 'BULK');
    $final_sheet->setCellValue('H1', 'Total Charged');
    $final_sheet->setCellValue('K1', 'Mailcode');
    $final_sheet->setCellValue('L1', 'Fund');
    $final_sheet->setCellValue('M1', 'Dept');
    $final_sheet->setCellValue('N1', 'ACCT');
    $final_sheet->setCellValue('O1', 'Program');
    $final_sheet->setCellValue('P1', 'Proj');
    $final_sheet->setCellValue('Q1', 'Class');
    $final_sheet->setCellValue('R1', 'TOTAL');



    $highest_row = $sheet->getHighestRow();
    $new = null;
    $index = 4;
    $header_index = 4;
    $count = 0;
    $class_of_mail_sums = [];
    $ref_sheet_array = [];

    foreach (range('A', 'H') as $column) {
        $new_sheet->getStyle($column . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $new_sheet->getStyle($column . '3')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD3D3D3');
    }
    $new_sheet->setCellValue('A3', "Account");
    $new_sheet->setCellValue('B3', "Date");
    $new_sheet->setCellValue('C3', "Class of Mail");
    $new_sheet->setCellValue('D3', "Total Postage Pieces");
    $new_sheet->setCellValue('E3', "Postage");
    $new_sheet->setCellValue('F3', "Fees");
    $new_sheet->setCellValue('G3', "Surcharge Amount");
    $new_sheet->setCellValue('H3', "Total Charged");

    $start_of_data = 4;
    $ref_highest_row = $ref_sheet->getHighestRow();
    $final_index = 2;
    for ($i = 8; $i < $highest_row; $i++) {
        $a = (int)substr(
            $sheet->getCell('A' . $i + 1)->getValue(),
            0,
            strlen($sheet->getCell('A' . $i + 1)->getValue())
        );



        if ($new !== $a && ($a !== '' || $a !== null) && $a !== 0 && (int)$a !== 9000) {
            $header_index += $count;
            $count = 0;
            $new_sheet->setCellValue('A' . $index, $a);
            $new_sheet->getStyle('A' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            $new_sheet->getStyle('A' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

            $new_sheet->getStyle('B' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            $new_sheet->getStyle('B' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

            $new_sheet->mergeCells('C' . $index . ':H' . $index);
            $new_sheet->getStyle('C' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            for ($j = 0; $j < $ref_highest_row; $j++) {
                if ($a === (int)$ref_sheet->getCell('A' . $j + 1)->getValue()) {
                    $ref_c = $ref_sheet->getCell('C' . $j + 1)->getValue();
                    $ref_d = $ref_sheet->getCell('D' . $j + 1)->getValue();
                    $ref_e = $ref_sheet->getCell('E' . $j + 1)->getValue();
                    //echo $a . ' ' . $ref_c . ' ' . $ref_d . ' ' . $ref_e . '<br>';
                    // $final_sheet->setCellValue('A' . $final_index, $a);
                    // $final_sheet->setCellValue('L' . $final_index, $ref_c);
                    // $final_sheet->setCellValue('M' . $final_index, $ref_d);
                    // $final_sheet->setCellValue('N' . $final_index, $ref_e);
                    // $final_index++;

                    $ref_sheet_array[] = [$a, $ref_c, $ref_d, $ref_e];
                    break;
                }
            }
            $new = $a;
            $acc_nums[$index] = $new;
            $index++;
            $count++;
        } else {
            if ((int)$a === 9000) {
                continue;
            }
        }
        $b = $sheet->getCell('B' . $i + 1)->getValue();
        $g = $sheet->getCell('G' . $i + 1)->getValue();
        if ($b === '' || $b === NULL || $g === 'No Class') {
            continue;
        }
        foreach (range('B', 'H') as $column) {
            $new_sheet
                ->getStyle($column . $index)
                ->getBorders()
                ->getOutline()
                ->setBorderStyle(Border::BORDER_THICK)
                ->setColor(new Color('FFD3D3D3'));
        }
        $new_sheet->setCellValue('B' . $index, $b);

        $new_sheet->setCellValue('C' . $index, $g);

        $l = $sheet->getCell('L' . $i + 1)->getValue();
        $new_sheet->setCellValue('D' . $index, $l);


        $m = (float)substr($sheet->getCell('M' . $i + 1)->getValue(), 0, strlen($sheet->getCell('M' . $i + 1)->getValue()));
        if (!preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
            $class_of_mail_sums[$new] += $l;
        }
        $new_sheet->setCellValue('B' . $header_index, "Postage Pieces Charge: $" . $class_of_mail_sums[$new] * $postage_fee);
        $new_sheet->setCellValue('E' . $index, "$" . $m);

        $n = $sheet->getCell('N' . $i + 1)->getValue();
        $new_sheet->setCellValue('F' . $index, "$" . $n);

        $o = $sheet->getCell('O' . $i + 1)->getValue();
        $new_sheet->setCellValue('G' . $index, "$" . $o);

        $p = $sheet->getCell('P' . $i + 1)->getValue();
        $new_sheet->setCellValue('H' . $index, "$" . $p);
        $index++;
        $count++;
    }
    try {
        //$index = 4;
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $highest_row = $sheet->getHighestRow();

        for ($i = 8; $i < $highest_row; $i++) {
            $a = (int)substr(
                $sheet->getCell('A' . $i + 1)->getValue(),
                0,
                strlen($sheet->getCell('A' . $i + 1)->getValue())
            );



            if ($new !== $a && ($a !== '' || $a !== null) && $a !== 0 && (int)$a !== 9000) {
                $header_index += $count;
                $count = 0;
                $new_sheet->setCellValue('A' . $index, $a);
                $new_sheet->getStyle('A' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                $new_sheet->getStyle('A' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

                $new_sheet->getStyle('B' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                $new_sheet->getStyle('B' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

                $new_sheet->mergeCells('C' . $index . ':H' . $index);
                $new_sheet->getStyle('C' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                for ($j = 0; $j < $ref_highest_row; $j++) {
                    if ($a === (int)$ref_sheet->getCell('A' . $j + 1)->getValue()) {
                        $ref_c = $ref_sheet->getCell('C' . $j + 1)->getValue();
                        $ref_d = $ref_sheet->getCell('D' . $j + 1)->getValue();
                        $ref_e = $ref_sheet->getCell('E' . $j + 1)->getValue();
                        //echo $a . ' ' . $ref_c . ' ' . $ref_d . ' ' . $ref_e . '<br>';
                        // $final_sheet->setCellValue('L' . $final_index, $ref_c);
                        // $final_sheet->setCellValue('M' . $final_index, $ref_d);
                        // $final_sheet->setCellValue('N' . $final_index, $ref_e);
                        // $final_index++;

                        $ref_sheet_array[] = [$a, $ref_c, $ref_d, $ref_e];
                        break;
                    }
                }
                $new = $a;
                $acc_nums[$index] = $new;
                $index++;
                $count++;
            } else {
                if ((int)$a === 9000) {
                    continue;
                }
            }
            $b = $sheet->getCell('B' . $i + 1)->getValue();
            $g = $sheet->getCell('G' . $i + 1)->getValue();
            if ($b === '' || $b === NULL || $g === 'No Class') {
                continue;
            }
            foreach (range('B', 'H') as $column) {
                $new_sheet
                    ->getStyle($column . $index)
                    ->getBorders()
                    ->getOutline()
                    ->setBorderStyle(Border::BORDER_THICK)
                    ->setColor(new Color('FFD3D3D3'));
            }
            $new_sheet->setCellValue('B' . $index, $b);

            $new_sheet->setCellValue('C' . $index, $g);

            $l = $sheet->getCell('L' . $i + 1)->getValue();
            $new_sheet->setCellValue('D' . $index, $l);


            $m = (float)substr($sheet->getCell('M' . $i + 1)->getValue(), 0, strlen($sheet->getCell('M' . $i + 1)->getValue()));
            if (!preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
                $class_of_mail_sums[$new] += $l;
            }
            $new_sheet->setCellValue('B' . $header_index, "Postage Pieces Charge: $" . $class_of_mail_sums[$new] * $postage_fee);
            $new_sheet->setCellValue('E' . $index, "$" . $m);

            $n = $sheet->getCell('N' . $i + 1)->getValue();
            $new_sheet->setCellValue('F' . $index, "$" . $n);

            $o = $sheet->getCell('O' . $i + 1)->getValue();
            $new_sheet->setCellValue('G' . $index, "$" . $o);

            $p = $sheet->getCell('P' . $i + 1)->getValue();
            $new_sheet->setCellValue('H' . $index, "$" . $p);
            $index++;
            $count++;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
if (isset($_POST['submit'])) {
    $sorted_array = bubbleSort($ref_sheet_array);
    foreach (range('A', 'H') as $column) {
        foreach (range(1, count($sorted_array) + 1) as $count) {
            $final_sheet
                ->getStyle($column . $count)
                ->getBorders()
                ->getOutline()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('000000'));
        }
    }
    foreach (range('K', 'R') as $column) {
        foreach (range(1, count($sorted_array) + 1) as $count) {
            $final_sheet
                ->getStyle($column . $count)
                ->getBorders()
                ->getOutline()
                ->setBorderStyle(Border::BORDER_THIN)
                ->setColor(new Color('000000'));
        }
    }
    foreach ($sorted_array as $row) {
        $final_sheet->setCellValue('A' . $final_index, $row[0]);
        $final_sheet->setCellValue('L' . $final_index, $row[1]);
        $final_sheet->setCellValue('M' . $final_index, $row[2]);
        $final_sheet->setCellValue('N' . $final_index, $row[3]);
        $final_index++;
    }
    $final_sheet->setCellValue('A' . $final_index, 'Grand Total');
    $final_sheet->setCellValue('B' . $final_index, 0);
    $final_sheet->setCellValue('C' . $final_index, '-');
    $final_sheet->setCellValue('D' . $final_index, '-');
    $final_sheet->setCellValue('E' . $final_index, '-');
    $final_sheet->setCellValue('F' . $final_index, '-');
    $final_sheet->setCellValue('G' . $final_index, '-');
    $final_sheet->setCellValue('H' . $final_index, '-');
    $final_sheet->setCellValue('L' . $final_index, 'BK001');
    $final_sheet->setCellValue('N' . $final_index, '107800');
    $final_sheet->setCellValue('Q' . $final_index, 'C1060');


    foreach (range('A', 'H') as $column) {
        $final_sheet
            ->getStyle($column . $final_index)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('000000'));
        $final_sheet->getStyle($column . $final_index)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD3D3D3');
        $final_sheet->getStyle($column . $final_index)->getFont()->setBold(true);
    }
    foreach (range('K', 'R') as $column) {
        $final_sheet
            ->getStyle($column . $final_index)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new Color('000000'));
        $final_sheet->getStyle($column . $final_index)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD3D3D3');
    }

    if (headers_sent($file, $line)) {
        echo ("Headers already sent in $file on line $line — this will corrupt XLSX output.");
    }
    $last_month = date('M-Y', strtotime("-1 Month"));
    $t1 = tempnam(sys_get_temp_dir(), 'rpt1_');
    $t2 = tempnam(sys_get_temp_dir(), 'rpt2_');
    $w1 = new Xlsx($create_sheet);
    $w1->setPreCalculateFormulas(false);
    $w1->save($t1);

    $w2 = new Xlsx($final_spread_sheet);
    $w2->setPreCalculateFormulas(false);
    $w2->save($t2);

    $zip_path = tempnam(sys_get_temp_dir(), 'zip_');
    $zip = new ZipArchive();
    $zip->open($zip_path, ZipArchive::OVERWRITE);
    $last_month = date('M-Y', strtotime('-1 month'));
    $zip->addFile($t1, "Transaction Detail Report $last_month.xlsx");
    $zip->addFile($t2, "Postage Report $last_month.xlsx");
    $zip->close();

    // Stream the zip
    $zip_name = "Reports_$last_month.zip";
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_name . '"');
    header('Content-Length: ' . filesize($zip_path));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: public');
    readfile($zip_path);

    // Clean up
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
    <title>Mail Clerk Monthly Report</title>
    <?php include_once "../navbar.php"; ?>
</head>

<body>
    <form action="monas-report.php" enctype="multipart/form-data" method="POST">
        <input type="file" name="file" id="file_name" accept=".xlsx, .xls" required>
        <input type="file" name="ref-file" id="ref_file_name" accept=".xlsm" required>
        <input type="number" name="postage-fee" step="0.001">
        <button name="submit" value="good">Submit</button>
    </form>

</body>

</html>
