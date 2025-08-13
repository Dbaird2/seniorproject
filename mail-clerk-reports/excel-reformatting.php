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

if (isset($_POST['submit'])) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    @ini_set('zlib.output_compression', 'Off');
    $file_name = $_FILES['file']['name'];
    $file_tmp_path = $_FILES['file']['tmp_name'];
    $postage_fee = (float)$_POST['postage-fee'];

    $upload_dir = 'uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_path = $upload_dir . basename($file_name);
    move_uploaded_file($file_tmp_path, $file_path);

    $spreadsheet = IOFactory::load($file_path);
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    $create_sheet = new SpreadSheet();
    $new_sheet = $create_sheet->getActiveSheet();
    foreach (range('A', 'Z') as $columnID) {
        $new_sheet->getColumnDimension($columnID)->setAutoSize(true);
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



    $highest_row = $sheet->getHighestRow();
    $new = null;
    $index = 4;
    $header_index = 4;
    $count = 0;
    $sum_of_nums = [];
    $class_of_mail_sums = [];

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

            $new_sheet->setCellValue('B' . $index, 'Total Postage: $' . $sum_of_nums[$acc_nums[$index - 4]]);
            $new_sheet->getStyle('B' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            $new_sheet->getStyle('B' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

            $new_sheet->setCellValue('C' . $index, "Postage Pieces Charge: $" . $class_of_mail_sums[$acc_nums[$index - 4]] * $postage_fee);
            $new_sheet->getStyle('C' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            $new_sheet->getStyle('C' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            $new_sheet->mergeCells('D' . $index . ':H' . $index);
            $new_sheet->getStyle('D' . $index)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('000000');
            $new = $a;
            $same = false;
            $acc_nums[$index] = $new;
            $index++;
            $count++;
        } else {
            $same = true;
            if ((int)$a === 9000) {
                continue;
            }
        }
        $b = $sheet->getCell('B' . $i + 1)->getValue();
        $g = $sheet->getCell('G' . $i + 1)->getValue();
        if ($b === '' || $b === NULL || $g === 'No Class' || preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
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
        $sum_of_nums[$new] += $m;
        $class_of_mail_sums[$new] += $l;
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

                $new_sheet->setCellValue('B' . $index, 'Total Postage: $' . $sum_of_nums[$acc_nums[$index - 4]]);
                $new_sheet->getStyle('B' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                $new_sheet->getStyle('B' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

                $new_sheet->setCellValue('C' . $index, "Postage Pieces Charge: $" . $class_of_mail_sums[$acc_nums[$index - 4]] * $postage_fee);
                $new_sheet->getStyle('C' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                $new_sheet->getStyle('C' . $index)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
                $new_sheet->mergeCells('D' . $index . ':H' . $index);
                $new_sheet->getStyle('D' . $index)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('000000');
                $new = $a;
                $same = false;
                $acc_nums[$index] = $new;
                $index++;
                $count++;
            } else {
                $same = true;
                if ((int)$a === 9000) {
                    continue;
                }
            }
            $b = $sheet->getCell('B' . $i + 1)->getValue();
            $g = $sheet->getCell('G' . $i + 1)->getValue();
            if ($b === '' || $b === NULL || $g === 'No Class' || preg_match('/(Flat)/', $g, $matches, PREG_OFFSET_CAPTURE)) {
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
            $sum_of_nums[$new] += $m;
            $class_of_mail_sums[$new] += $l;
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



    if (headers_sent($file, $line)) {
        echo ("Headers already sent in $file on line $line — this will corrupt XLSX output.");
    }
    $last_month = date('M-Y', strtotime("-1 Month"));
    $filename = "Transaction Detail Report " . $last_month . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: public');
    header('Expires: 0');
    $writer = new Xlsx($create_sheet);
    $writer->setPreCalculateFormulas(false);

    $writer->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mona's Excel Insert</title>
</head>

<body>
    <form action="monas-report.php" enctype="multipart/form-data" method="POST">
        <input type="file" name="file" id="file_name" accept=".xlsx, .xls" required>
        <input type="number" name="postage-fee" step="0.001">
        <button name="submit" value="good">Submit</button>
    </form>

</body>

</html>
