<?php
error_reporting(0);
include_once("config.php");
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if (isset($_POST['create'])) {

    # This if statement will get the current directory +appended filename,
    # current direct + appended export directory, create the export directory
    # if it does not exist. Get all the info that was sent in array.
    # Format it according to how it is wanted for PHPSpreadsheet Excel
    # it will then create a excel sheet, save it to the sheet, then
    # encode it for file transfering to download
    try {
        $filePath = __DIR__ . $_POST['filePath'];

        $saveDir = __DIR__ . '/exports/';
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        # START SPREADSHEET
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        # LOCATION FOR EXCEL HEADERS
        $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1'];
        $row_index = 2;

        # GET POST DATA
        $previous_times = $_POST['previousTime'] ?? NULL;
        $previous_inputs = $_POST['previousInputContainer'] ?? NULL;
        $headers = $_POST['headers'];
        $loc = $_POST['loc'];
        $sn = $_POST['serial'];
        $po = $_POST['po_num'];
        $old_tags = $_POST['old_tag'];
        $desc = $_POST['description'];

        # GET DOWNLOAD PATH READY
        $fileNameOnly = basename($filePath);
        $filePath = $saveDir . $fileNameOnly;

        # CHECK IF EMPTY
        $empty_scan = is_null($previous_inputs[0]) ? true : false;
        $file_empty = is_null($old_tags[1]) ? true : false;

        # CHANGE FILE NAME
        $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
        $filePath = str_replace(".xls", "_AUDIT", $filePath);
        $filePath = $filePath . ".xlsx";

        # SET HEADERS IN SHEET
        for ($i = 0; $i < count($column_letters); $i++) {
            $sheet->setCellValue($column_letters[$i], $headers[$i]);
        }
        $sheet->setCellValue('I1', 'Extra Tags');
        $i = 0;
        if (!$file_empty) {
            foreach ($old_tags as $row) {
                $sheet->setCellValue('A' . $row_index, $old_tags[$i]);
                $sheet->setCellValue('D' . $row_index, $desc[$i]);
                $sheet->setCellValue('E' . $row_index, $sn[$i]);
                $sheet->setCellValue('F' . $row_index, $loc[$i]);
                $sheet->setCellValue('G' . $row_index, $po[$i]);
                $i++;
                $row_index++;
            }
            $h_row = 2;
            for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                for ($i = 0; $i < sizeof($old_tags); $i++) {
                    if ($previous_inputs[$j] == $old_tags[$i]) {
                        $sheet->setCellValue('B' . $i + 2, $previous_inputs[$j]);
                        $sheet->setCellValue('C' . $i + 2, $previous_times[$j]);
                        break;
                    } else if ($i == sizeof($old_tags) - 1) {
                        $sheet->setCellValue('I' . $h_row, $previous_inputs[$j]);
                        $sheet->setCellValue('J' . $h_row++, $previous_times[$j]);
                    }
                }
            }
        } else {
            $sheet->setCellValue('A2', 'No Assets Found');
            if (!$empty_scan) {
                $h_row=2;
                for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                    $sheet->setCellValue('I' . $h_row, $previous_inputs[$j]);
                    $sheet->setCellValue('J' . $h_row++, $previous_times[$j]);
                }
            }
        }
        // Use PhpSpreadsheet to save the file on the server
        if (ob_get_length()) {
            ob_end_clean();
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        header('Location: download.php?file=' . urlencode($filePath));
        error_reporting(1);
    } catch (Exception $e) {
        echo "Something went wrong trying to parse before downloading ". $e;
    }
}
?>

<html>
<head>
<?php
include_once("navbar.php");
?>
  <style>
body {
            margin: 0;
            height: 100vh;
            font-size: calc(0.5vw + 0.4vh);
            width: 100%;
            position: absolute;
            top: 8vh;
        }

        #makeSheet,
        #showExcel {
            display: flex;
            margin:auto;
            justify-content: left;
            align-items: left;
            max-width: 50%;
        }

        .excel-info {
            border: 0.1vh solid #cce0ff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .inner-text {
            font-size: calc(0.5vw + 0.4vh);
            padding: 0.5vh 0.5vw;
            font-family: Arial, sans-serif;
        }

        .row-even {
            background-color: #f0f8ff;
        }

        .row-odd {
            background-color: #ffffff;
        }

        .inner-text ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .inner-text li {
            margin: 0vw 0;
            margin-bottom: 0vh;
        }

        .row-number {
            margin-right: 0vw;
            margin-bottom: 0vh;

            color: #003366;
        }

        .match-tag {
            color: green;
        }

        .miss-tag {
            color: red;
        }

        .match-desc {
            color: green;
        }

        .miss-desc {
            color: red;
        }

        .neutral-tag {
            color: black;
        }

        #sheet {
            position: fixed;
            float: left;
            top: 4.5rem;
            background-color: #ffffff;
            padding: 0.6vw 1vw;
            border-radius: 1vw;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 12vw;
        }

        #dynamicForm label,
        #sheet label {
            display: block;
            margin-bottom: 0.2vw;
            color: #003366;
            font-weight: bold;
        }

        #sheet input[type="file"] {
            font-size: calc(0.5vw + 0.4vh);

            width: 80%;
            margin-bottom: 1vh;
            padding: 0.3vw;
            border: 1px solid #cce0ff;
            border-radius: 1vw;
            background-color: #f0f8ff;
        }

        #dynamicForm button,
        #makeSheet button,
        #sheet button {
            font-size: calc(0.5vw + 0.4vh);

            width: 4vw;
            background-color: #007BFF;
            color: #fff;
            padding: 0.2vw 0.3vw;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        #dynamicForm button:hover,
        #makeSheet button:hover,
        #sheet button:hover {
            background-color: #0056b3;
        }

        .show-tags {
            display: flex;
            position: fixed;
            flex-wrap: wrap;
            justify-content: center;
            left: 0vw;
            top: calc(15rem);
            background-color: #ffffff;
            margin-top: 0vh;
            margin-left: 0vw;
            padding: 1vh 0vw;
            border-radius: 8px;
            max-width: 15%;
            width:10rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .show-tags h4 {
            color: #003366;
            margin-top: 1vh;
            font-size: calc(0.5vw + 0.5vh);
        }

        .show-tags ul {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            list-style-type: none;
            padding-left: 0;
            justify-content: center;
            margin-bottom: 0.1rem;
            margin-right:-0.3rem;
        }

        .show-tags li {
            margin-bottom: 0.5vw;
            font-size: calc(0.5vw + 0.4vh);
        }

        .tag-match {
            color: green;
        }

        .tag-miss {
            color: red;
        }

        .formId {
            float: top;
            display: flex;
            justify-content: center;

        }

        #dynamicForm {
            position: fixed;
            top: 3rem;
            right:0;
            padding: 10px;
            background-color: white;
            border: 1px solid white;
        }

        .dynamicId {
            font-size: calc(0.5vw + 0.4vh);

            line-height: 28px;
            border: 2px solid transparent;
            border-bottom-color: #777;
            padding: .2rem 0;
            outline: none;
            background-color: transparent;
            color: #0d0c22;
            transition: .3s cubic-bezier(0.645, 0.045, 0.355, 1);
        }

        .dynamicId:focus,
        .dynamicId:hover {
            font-size: calc(0.5vw + 0.4vh);
            outline: none;
            padding: .2rem 1rem;
            border-radius: 1rem;
            border-color: #7a9cc6;
        }

        .dynamicId::placeholder {
            font-size: calc(0.5vw + 0.4vh);

            color: #777;
        }

        .dynamicId:focus::placeholder {
            font-size: calc(0.5vw + 0.4vh);

            opacity: 0;
            transition: opacity .3s;
        }
    </style>

<body>
<form id="sheet" name="form" action="auditing.php" method="POST" enctype="multipart/form-data">
            <label for="file">Enter File:</label>
            <input type="file" name="file" id="filePath">
            <button type="submit">Submit</button>
        </form>
