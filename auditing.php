<?php
error_reporting(0);
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

        foreach (range('A', 'HH') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        # LOCATION FOR EXCEL HEADERS
        $column_letters = ['A1', 'E1', 'F1', 'G1', 'H1', 'I1'];
        $row_index = 2;

        # GET POST DATA
        $previous_times = $_POST['previousTime'] ?? NULL;
        $previous_notes = $_POST['previousNote'] ?? NULL;
        $previous_inputs = $_POST['previousInputContainer'] ?? NULL;
        $headers = $_POST['headers'];
        $loc = $_POST['loc'];
        $sn = $_POST['serial'];
        $po = $_POST['po_num'];
        $old_tags = $_POST['old_tag'];
        $desc = $_POST['description'];
        $cost = $_POST['cost'];
        $dept = $_POST['dept'];

        # GET DOWNLOAD PATH READY
        $fileNameOnly = basename($filePath);
        $filePath = $saveDir . $fileNameOnly;

        # CHECK IF EMPTY
        $empty_scan = is_null($previous_inputs[0]) ? true : false;
        $file_empty = is_null($old_tags[0]) ? true : false;

        # CHANGE FILE NAME
        $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
        $filePath = str_replace(".xls", "_AUDIT", $filePath);
        $filePath = $filePath . ".xlsx";

        # SET HEADERS IN SHEET
        for ($i = 0; $i < count($column_letters); $i++) {
            if ($i == 1) {
                $i = $i + 3;
            }
            $sheet->setCellValue($column_letters[$i], $headers[$i]);
        }
        $sheet->setCellValue('B1', 'Tags Matched');
        $sheet->setCellValue('C1', 'Notes');
        $sheet->setCellValue('D1', 'Timestamp');
        $sheet->setCellValue('J1', 'Extra Tags');
        $sheet->setCellValue('K1', 'Notes');
        $sheet->setCellValue('L1', 'Timestamps');
        $i = 0;
        if (!$file_empty) {
            foreach ($old_tags as $row) {
                $sheet->setCellValue('A' . $row_index, $old_tags[$i]);
                $sheet->setCellValue('E' . $row_index, $desc[$i]);
                $sheet->setCellValue('F' . $row_index, $sn[$i]);
                $sheet->setCellValue('G' . $row_index, $loc[$i]);
                $sheet->setCellValue('H' . $row_index, $dept[$i]);
                $sheet->setCellValue('I' . $row_index, $cost[$i]);
                $i++;
                $row_index++;
            }
            $h_row = 2;
            for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                for ($i = 0; $i < sizeof($old_tags); $i++) {
                    if ($previous_inputs[$j] == $old_tags[$i]) {
                        $sheet->setCellValue('B' . $i + 2, $previous_inputs[$j]);
                        $sheet->setCellValue('C' . $i + 2, $previous_notes[$j]);
                        $sheet->setCellValue('D' . $i + 2, $previous_times[$j]);
                        break;
                    } else if ($i == sizeof($old_tags) - 1) {
                        $sheet->setCellValue('I' . $h_row, $previous_inputs[$j]);
                        $sheet->setCellValue('J' . $h_row++, $previous_notes[$j]);
                        $sheet->setCellValue('K' . $h_row++, $previous_times[$j]);
                    }
                }
            }
        } else {
            $sheet->setCellValue('A2', 'No Assets Found');
            if (!$empty_scan) {
                $h_row=2;
                for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                    $sheet->setCellValue('I' . $h_row, $previous_inputs[$j]);
                    $sheet->setCellValue('J' . $h_row++, $previous_notes[$j]);
                    $sheet->setCellValue('K' . $h_row++, $previous_times[$j]);
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
            max-width: 20%;
            width:15rem;
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
            justify-content: left;
            margin-bottom: 0.1rem;
            margin-left: 0.5rem;
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
    <title>Asset Management Excel</title>
</head>
<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);
 */


/*
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
  */   
$worksheet = NULL;
?>
<body>
<form id="sheet" name="form" action="auditing.php" method="POST" enctype="multipart/form-data">
            <label for="file">Enter File:</label>
            <input type="file" name="file" id="filePath">
            <button type="submit">Submit</button>
        </form>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Get file info
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    # CHECK IF XLSX OR XLS
    $file_type_check = substr($fileName, strlen($fileName) - 4);
    if ($file_type_check != 'xlsx' && $file_type_check != '.xls') {
        echo "<h3'>File type not allowed</h3>";
        return;
    }

    // Define the target directory to save the uploaded file
    $uploadDir = 'uploads/';

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
    }

    // Define the path where the file will be moved
    $filePath = $uploadDir . basename($fileName);

    // Move the uploaded file to the specified path
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        // Return the file path after successful upload
        echo "File uploaded successfully.";
    } else {
        echo "Error uploading file.";
    }
} else {
}

if (isset($_POST['filePath'])) {
    $filePath = $_POST['filePath'];
}

if (isset($filePath)) {

    try {
        $spreadsheet = IOFactory::load($filePath);


        // Get the worksheet
        $worksheet = $spreadsheet->getActiveSheet();

        $row_number = 1;
        $array = [];
        $old_tags = [];
        $disc_arr = [];
        $sn_arr = [];
        $loc_arr = [];
        $po_arr = [];
        $cost_arr = [];
        $tag_array = [];
        $time_array = [];
        $note_array = [];
        $column_headers = [];

        //$tag = $worksheet->getCell('B2')->getValue() . ":";
        /*
        // Loop through the rows and columns
        foreach ($worksheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $coordinate = $cell->getCoordinate();
                # HORIZONTAL
                $worksheet->getStyle($coordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                # VERTICAL
                $worksheet->getStyle($coordinate)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
        }
         */
    } catch (Exception $e) {
        echo "Error uploading file";
    } catch (\TypeError $e) {
        echo "TypeError";
    }

    // Load the spreadsheet
    if (isset($_POST['dynamicInput'])) {
        $previous_times = $_POST['previousTime'] ?? NULL;
        $previous_inputs = $_POST['previousInputContainer'] ?? NULL;
        $previous_notes = $_POST['previousNote'] ?? NULL;

        $inputs = $_POST['dynamicInput'];
        $timeInputs = $_POST['dynamicTime'];
        $noteInputs = $_POST['dynamicNote'];

        # CHECK FOR DUPES IN NEW TAG INPUTS
        $seen = [];
        $newTimes = [];
        $newInputs = [];
        $newNotes = [];
        foreach ($inputs as $key => $input) {
            if (!isset($seen[$input])) {
                $seen[$input] = true;
                $newInputs[] = $input;
                $newTimes[] = $timeInputs[$key];
                $newNotes[] = $noteInputs[$key];
            }
        }

        # CHECK FOR DUPES IN OLD TAG INPUTS
        if (!is_null($previous_inputs)) {
            $pSeen = [];
            $pNewTimes = [];
            $pNewInputs = [];
            $pNewNotes = [];
            foreach ($previous_inputs as $key => $input) {
                if (!in_array($input, $pSeen)) {
                    $pSeen[] = $input;
                    $pNewInputs[] = $input;
                    $pNewTimes[] = $previous_times[$key];
                    $pNewNotes[] = $previous_notes[$key];
                }
            }


            # previous_inputs -> pNewInputs
            /*
             * OLD CODE WORKS
            $dupes = [];
            foreach ($pNewInputs as $key => $previousInput) {
                foreach ($newInputs as $newKey => $newInput) {
                    if ($previousInput == $newInput) {
                        $dupes[] = $newKey;
                    }
                }
            }
             */
            $dupes = [];
            $new_input_set = [];
            foreach ($newInputs as $input) {
                $newInputSet[$input] = true;
            }

            foreach ($previous_inputs as $key => $input) {
                if (isset($newInputSet[$input])) {
                    unset($pNewInputs[$key]);
                    unset($pNewTimes[$key]);
                    unset($pNewNotes[$key]);
                }
            }
            /*
             * OLD UNOPTIMIZED
            foreach ($newInputs as $key => $previousInput) {
                foreach ($pNewInputs as $newKey => $newInput) {
                    if ($previousInput == $newInput) {
                        $dupes[] = $newKey;
                        break;
                    }
                }
            }

            # newTimes -> pNewTimes, newInputs -> pNewInputs
            foreach ($dupes as $newKey) {
                unset($pNewTimes[$newKey]);
                unset($pNewInputs[$newKey]);
            }
             */
            $pNewInputs = array_values($pNewInputs);
            $pNewTimes = array_values($pNewTimes);
            $pNewNotes = array_values($pNewNotes);
        }

        # GET OLD TAGS READY
        foreach ($pNewInputs as $index => $value) {
            if ($value != NULL) {
                $array[] = htmlspecialchars($value);
            }
        }

        # GET OLD NOTES READY
        foreach ($pNewNotes as $index => $value) {
            if ($value != NULL) {
                $note_array[] = htmlspecialchars($value);
            }
        }

        # GET OLD TIMESTAMPS READY
        foreach ($pNewTimes as $index => $time) {
            if ($time != NULL) {
                $time_array[] = htmlspecialchars($time);
            }
        }
        # GET NEW TIMESTAMPS READY
        foreach ($newTimes as $index => $time) {
            if ($time != NULL) {
                $time_array[] = htmlspecialchars($time);
            }
        }
        # GET NEW TAGS READY
        foreach ($newInputs as $index => $value) {
            if ($value != NULL) {
                $array[] = htmlspecialchars($value);
            }
        }

        # GET NEW NOTES READY
        foreach ($newNotes as $index => $value) {
            if ($value != NULL) {
                $note_array[] = htmlspecialchars($value);
            }
        }
    }
    if (!is_null($worksheet)){
        // SKIPS FIRST ROW
        #$worksheet->getRowIterator(1);
        // GET HEADERS STARTING AT ROW 2
        // TAG NUMBER
        $cell_array = [];
        $headers = ['Tag Number','Descr', 'Serial ID', 'Location','Custodian Deptid', 'COST Total Cost'];
        $count = 0;

        for ($row = 1; $row <= 4; $row++) {
            foreach (range('A', 'Z') as $columnID) {
                $cell = $columnID . $row;
                $cell_value = $worksheet->getCell($columnID . $row)->getValue();
                // If this cell value is in your $headers array
                if (in_array($cell_value, $headers)) {
                    $column_headers[] = $cell_value;
                    // Add the cell reference (like "A1") to the result array
                    $cell_array[] = $columnID . $row;
                    $count++;
                }
                //echo "<h1>$count</h1>";
                if ($count == 6) {
                    $column_header = ["Tag Number", "Description", "Serial Number", "Location", "Dept ID", "Cost"];
                    break;
                }
            }
            if ($count == 6) break;
        }
        foreach ($column_headers as $key => $header) {
            if ($header == 'Custodian Deptid') {
                $column_headers[$key] = 'Dept ID';
            } else if ($header == 'COST Total Cost') {
                $column_headers[$key] = 'Total Cost';
            }
        }
    }
        
    $first_char = substr($string, 0, 1);
        

    $colors = ['lightblue', 'white'];
    $empty = false;
    try {
        if ($worksheet->getRowIterator(3) == NULL) {
            throw new Exception('File Messed up');

        }
    } catch (Exception $e) {
        $empty = TRUE;
    } catch (\Throwable $e) {
        $empty = TRUE;
    }
    if (!$empty) {
        foreach ($worksheet->getRowIterator(3) as $row) {
            $cellB = $worksheet->getCell(substr($cell_array[0], 0, 1) . $row->getRowIndex());
            $cellH = $worksheet->getCell(substr($cell_array[1], 0, 1) . $row->getRowIndex());
            $cellI = $worksheet->getCell(substr($cell_array[2], 0, 1) . $row->getRowIndex());
            $cellJ = $worksheet->getCell(substr($cell_array[3], 0, 1) . $row->getRowIndex());
            $cellN = $worksheet->getCell(substr($cell_array[4], 0, 1) . $row->getRowIndex());
            $cellAA = $worksheet->getCell(substr($cell_array[5], 0, 1) . $row->getRowIndex());

            $color_class = ($row_number % 2 === 0) ? 'row-even' : 'row-odd';

            echo "<section id='showExcel'>";
            $tag_array[] = $cellB->getValue();

            echo "<div class='excel-info'>";
            echo "<div class='inner-text $color_class'>";
            echo "<ul>";
            echo "<li class='row-number'><strong>$row_number &nbsp;  </strong>";

            $match = in_array($cellB->getValue(), $array);

            if ($cellB->getValue() == 'Tag Number') {
                echo "<strong class='neutral-tag'>" . $cellB->getValue() . "</strong>";
            } else {
                $tagClass = $match ? "match-tag" : "miss-tag";
                $descClass = $match ? "match-desc" : "miss-desc";

                echo "<strong>$column_headers[0]: </strong>";
                echo "<strong class='$tagClass'>" . $cellB->getValue() . "</strong> | ";

                $desc = $cellH->getValue();
                $disc_arr[] = $desc;
                echo "<strong>$column_headers[1]:</strong> <span class='$descClass'>" . $desc . "</span> | ";

                $sn = $cellI->getValue() ?? "EMPTY";
                $sn_arr[] = $sn;
                echo "<strong>$column_headers[2]:</strong> $sn | ";

                $loc = $cellJ->getValue() ?? "EMPTY";
                $loc_arr[] = $loc;
                echo "<strong>$column_headers[3]</strong> $loc | ";

                $po = $cellN->getValue() ?? "EMPTY";
                $po_arr[] = $po;
                echo "<strong>$column_headers[4] </strong> $po</li>";

                $cost = $cellAA->getValue() ?? "EMPTY";
                $cost_arr[] = $cost;
                echo "<strong>$column_headers[5] </strong>$$cost</li>";
            }

            echo "</ul>";
            echo "</div></div></section>";

            $row_number++;
        }
            
    }
        


    $i = 0;
    echo "<div class='show-tags'>";
    echo "<h4 >Tags Scanned</h4>";
    echo "<ul>";
    foreach ($array as $row) {
        $match2 = in_array($row, $tag_array) ? 1 : 0;
        $colorClass = $match2 ? "tag-match" : "tag-miss";
        echo "<li class='$colorClass'><strong>$row</strong> &mdash; {$note_array[$i]} <br> {$time_array[$i]}  </li>";
        $i++;
    }
    echo "</ul>";
    echo "</div>";
?>
    <div id="additionalInputs"></div>
    <form id="dynamicForm" method='POST' action='auditing.php' onLoad="addNewInput()" enctype="multipart/form-data">
        <div id="inputContainer">
            <!-- Input fields will appear here -->
            <input class="dynamicId" type="text" name="dynamicInput[]" placeholder="Enter Tag" onchange="addNewInput()">
            <input class="dynamicId" type="text" name="dynamicNote[]" placeholder="Notes">
        </div>
<?php


    foreach ($array as $value) {
        echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
    }
    foreach ($time_array as $time) {
        echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time) . "'>";
    }
    foreach ($note_array as $note) {
        echo "<input type='hidden' name='previousNote[]' value='" . htmlspecialchars($note) . "'>";
    }
    echo "<input type='hidden' name='filePath' value='$filePath'>";
?>

        <button type="button" id="addInputButton" onClick="addNewInput()" onLoad="addNewInput()">Add Field</button>
        <button type="submit" id='dynamicSubmit'>Submit</button>
    </form>
<?php
?>
<div class='formId'>
    <form id="makeSheet" method='POST' action='auditing.php' enctype="multipart/form-data">
<?php

    foreach ($array as $value) {
        echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
    }
    foreach ($tag_array as $old_tag) {
        echo "<input type='hidden' name='old_tag[]' value='" . htmlspecialchars($old_tag) . "'>";
    }
    foreach ($time_array as $time) {
        echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time) . "'>";
    }
    foreach ($note_array as $note) {
        echo "<input type='hidden' name='previousNote[]' value='" . htmlspecialchars($note) . "'>";
    }
    foreach ($column_headers as $header) {
        echo "<input type='hidden' name='headers[]' value='" . htmlspecialchars($header) . "'>";
    }
    foreach ($disc_arr as $description) {
        echo "<input type='hidden' name='description[]' value='" . htmlspecialchars($description) . "'>";
    }
    foreach ($sn_arr as $serial) {
        echo "<input type='hidden' name='serial[]' value='" . htmlspecialchars($serial) . "'>";
    }
    foreach ($po_arr as $po_num) {
        echo "<input type='hidden' name='po_num[]' value='" . htmlspecialchars($po_num) . "'>";
    }
    foreach ($loc_arr as $location) {
        echo "<input type='hidden' name='loc[]' value='" . htmlspecialchars($location) . "'>";
    }
    foreach ($cost_arr as $cost) {
        echo "<input type='hidden' name='cost[]' value='" . htmlspecialchars($cost) . "'>";
    }
    foreach ($dept_arr as $dept) {
        echo "<input type='hidden' name='dept[]' value='" . htmlspecialchars($dept) . "'>";
    }

    echo "<input type='hidden' name='filePath' value='$filePath'>";
?>
        <button type='submit' id='create' name='create'>Export Excel File</button>
    </form>
</div>

<?php
}
?>
<script>

function addNewInput() {
    // Create the div and input
    const inputDiv = document.createElement('div');
    inputDiv.classList.add('input-container');

    

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'dynamicInput[]';
    //newInput.onfocus = 'addNewInput()';
    //newInput.id = 'addInputButton';
    newInput.placeholder = 'Enter tag';
    inputDiv.appendChild(newInput);

    newInput.classList.add('dynamicId');

    newInput.addEventListener("change", addNewInput, false);

    const timeInput = document.createElement('input');
    timeInput.type = 'hidden';
    timeInput.name = 'dynamicTime[]';
    timeInput.value = getFormattedDateTime();
    // Append input to the div and the div to the container
    inputDiv.appendChild(timeInput);

    const noteInput = document.createElement('input');
    noteInput.type = 'text';
    noteInput.name = 'dynamicNote[]';
    noteInput.value = '';
    noteInput.placeholder = 'Notes';
    // Append input to the div and the div to the container
    inputDiv.appendChild(noteInput);

    noteInput.classList.add('dynamicId');

    const inputContainer = document.getElementById('inputContainer');
    inputContainer.appendChild(inputDiv);
}

function getFormattedDateTime() {
    const currentDate = new Date();

    // Get the date in m:d:Y format (Month:Day:Year)
    let month = currentDate.getMonth() + 1; // Months are 0-indexed, so add 1
    let day = currentDate.getDate();
    let year = currentDate.getFullYear();

    // Format the date as MM:DD:YYYY
    month = month.toString().padStart(2, '0');
    day = day.toString().padStart(2, '0');
    let formattedDate = `${month}:${day}:${year}`;

    // Get the time in 12-hour format with AM/PM
    let formattedTime = currentDate.toLocaleTimeString('en-US', {
    hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });

    // Combine date and time
    let formattedDateTime = `${formattedDate} ${formattedTime}`;

    return formattedDateTime;
}



function doNotReload(event) {
    event.preventDefault();

    var filePath = $('filePath').val();

    $.ajax({
    url: 'index.php',
        type: 'POST',
        data: {
        filePath: filePath,
            array: array,
            time_array: time_array
    }

    })
}
/*
    window.addEventListener("load", function () {
    addNewInput();
});
 */

</script>
</body>
