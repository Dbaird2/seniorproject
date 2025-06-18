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
        $assets = json_decode($_POST['assets'], true);
        var_dump($assets);

        $filePath = __DIR__ . $_POST['filePath'];

        $saveDir = __DIR__ . '/exports/';
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        # START SPREADSHEET
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        # LOCATION FOR EXCEL HEADERS
        $column_letters = ['A1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1'];
        $row_index = 2;

        # GET POST DATA
        $previous_times = $assets['previousTime'] ?? NULL;
        $previous_notes = $assets['previousNote'] ?? NULL;
        $previous_inputs = $assets['previousInputContainer'] ?? NULL;
        $headers = $assets['headers'];
        $loc = $assets['loc'];
        $sn = $assets['serial'];
        $po = $assets['po_num'];
        $old_tags = $assets['old_tag'];
        $desc = $assets['description'];
        $cost = $assets['cost'];
        $dept = $assets['dept'];

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
            $sheet->setCellValue($column_letters[$i], $headers[$i]);
        }
        $sheet->setCellValue('B1', 'Tags Matched');
        $sheet->setCellValue('C1', 'Notes');
        $sheet->setCellValue('D1', 'Timestamp');
        $sheet->setCellValue('L1', 'Extra Tags');
        $sheet->setCellValue('M1', 'Notes');
        $sheet->setCellValue('N1', 'Timestamps');
        $i = 0;
        if (!$file_empty) {
            foreach ($old_tags as $row) {
                $sheet->setCellValue('A' . $row_index, $old_tags[$i]);
                $sheet->setCellValue('E' . $row_index, $desc[$i]);
                $sheet->setCellValue('F' . $row_index, $sn[$i]);
                $sheet->setCellValue('G' . $row_index, $loc[$i]);
                $sheet->setCellValue('H' . $row_index, $dept[$i]);
                $sheet->setCellValue('I' . $row_index, $cost[$i]);
                $sheet->setCellValue('J' . $row_index, $po[$i]);
                $i++;
                $row_index++;
            }
            $h_row = 2;
            if (!$empty_scan) {
            for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                for ($i = 0; $i < sizeof($old_tags); $i++) {
                    if ($previous_inputs[$j] == $old_tags[$i]) {
                        $sheet->setCellValue('B' . $i + 2, $previous_inputs[$j]);
                        $sheet->setCellValue('C' . $i + 2, $previous_notes[$j]);
                        $sheet->setCellValue('D' . $i + 2, $previous_times[$j]);
                        break;
                    } else if ($i == sizeof($old_tags) - 1) {
                        $sheet->setCellValue('L' . $h_row, $previous_inputs[$j]);
                        $sheet->setCellValue('M' . $h_row, $previous_notes[$j]);
                        $sheet->setCellValue('N' . $h_row++, $previous_times[$j]);
                    }
                }
            }
        }
        } else {
            $sheet->setCellValue('A2', 'No Assets Found');
            if (!$empty_scan) {
                $h_row=2;
                for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                    $sheet->setCellValue('L' . $h_row, $previous_inputs[$j]);
                    $sheet->setCellValue('M' . $h_row, $previous_notes[$j]);
                    $sheet->setCellValue('N' . $h_row++, $previous_times[$j]);
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
        body::-webkit-scrollbar {
            width: 1em;
        }

        body::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        }

        body::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            outline: 1px solid slategrey;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            text-align: center;
        }


        #showExcel {

            margin:auto;
            text-align:center;
            justify-content: left;
            max-width: 55%;
        }

        .excel-info {
            min-height: 4vh;
            max-height: 4vh;
            min-width: 7vw;
            max-width: 20vw;
            flex: 1;
            justify-content: center;
            border: 0.1vh solid #cce0ff;
            text-align: center;
            border-radius: 0px;
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
                        list-style-type: none;

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

        #makeSheet button {
            font-size: calc(0.5vw + 0.4vh);

            width: 6vw;
            background-color: #007BFF;
            color: #fff;
            padding: 0.2vw 0.3vh;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #dynamicForm button,
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
            max-height: 65vh;
            overflow-y: auto;
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
            max-width: 18%;
            width:15rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .show-tags::-webkit-scrollbar {
            width: 1em;
        }

        .show-tags::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        }

        .show-tags::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            outline: 1px solid slategrey;
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

        .row{
            display: flex;
            flex-wrap: wrap;
            text-align: center;
        }
        .row-4rols {

        }
    </style>

    <title>Asset Management Excel</title>
</head>
<?php

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
    } else {
        echo "Error uploading file.";
    }
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
        $dept_arr = [];
        $tag_array = [];
        $time_array = [];
        $note_array = [];
        $column_headers = [];

    } catch (Exception $e) {
        echo "Error uploading file";
    } catch (\TypeError $e) {
        echo "TypeError";
    }

    // Load the spreadsheet
    $assets = json_decode($_POST['assets'], true);
    var_dump($assets);
    if (isset($assets['dynamicInput'])) {
        $previous_times = $assets['previousTime'] ?? NULL;
        $previous_inputs = $assets['previousInputContainer'] ?? NULL;
        $previous_notes = $assets['previousNote'] ?? NULL;

        $inputs = $assets['dynamicInput'];
        $timeInputs = $assets['dynamicTime'];
        $noteInputs = $assets['dynamicNote'];

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

    // GET HEADERS FROM WORKSHEET
    if (!is_null($worksheet)){
        // SKIPS FIRST ROW
        #$worksheet->getRowIterator(1);
        // GET HEADERS STARTING AT ROW 2
        // TAG NUMBER
        $cell_array = [];
        $headers = ['Tag Number','Descr', 'Serial ID', 'Location', 'Dept', 'COST Total Cost', 'PO No.'];
        $count = 0;

        for ($row = 1; $row <= 4; $row++) {
            foreach (range('A', 'ZZ') as $columnID) {
                $cell = $columnID . $row;
                $cell_value = $worksheet->getCell($columnID . $row)->getValue();

                // If this cell value is in your $headers array
                if (in_array($cell_value, $headers)) {
                    $column_headers[] = $cell_value;
                    // Add the cell reference (like "A1") to the result array
                    $cell_array[$cell_value] = $columnID . $row;
                    $count++;
                }
                foreach (range( 'A', 'H') as $columnID2) {
                    $cell2 = $columnID . $columnID2 . $row;
                    $cell_value2 = $worksheet->getCell($cell2)->getValue();

                    if (in_array($cell_value2, $headers)) {
                        $columns_headers[] = $cell_value2;
                        $cell_array[$cell_value2] = $cell2;
                        $count++;
                    }
                if ($count == 7) {
                    $column_headers = ["Tag Number", "Description", "Serial Number", "Location", "Dept ID", "Cost", "PO"];
                    break;
                }
                if ($count == 7) break;
                }
            }
            if ($count == 7) break;
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
        echo "<section id='showExcel'>";
        echo "<div class='row'>";
        $offset = substr($cell_array['Tag Number'], 1 ,1) + 1;

        $column_count = 0;
        foreach ($cell_array as $key => $values) {
            $sizes[$key] = strlen($values)-1;
            $column_count++;
        }



        foreach ($worksheet->getRowIterator($offset) as $row) {
            if ($row_number == 1) {
                if (!is_null($cell_array['Tag Number'])) {
                    echo "<div class='excel-info'><strong>Tag Number</strong> </div>";
                }
                if (!is_null($cell_array['Descr'])) {
                    echo "<div class='excel-info'><strong>Description</strong> </div>";
                }
                if (!is_null($cell_array['Serial ID'])) {
                    echo "<div class='excel-info'><strong>Serial ID</strong> </div>";
                }
                if (!is_null($cell_array['Location'])) {
                    echo "<div class='excel-info'><strong>Location</strong> </div>";
                }
                if (!is_null($cell_array['Dept'])) {
                    echo "<div class='excel-info'><strong>Dept ID</strong> </div>";
                }
                if (!is_null($cell_array['COST Total Cost'])) {
                    echo "<div class='excel-info'><strong>Cost</strong> </div>";
                }
                if (!is_null($cell_array['PO No.'])) {
                    echo "<div class='excel-info'><strong>PO</strong> </div>";
                }
            }

            if (!is_null($cell_array['Tag Number'])) {

                $cellB = $worksheet->getCell(substr($cell_array['Tag Number'], 0, $sizes['Tag Number']) . $row->getRowIndex());
                $color_class = ($row_number % 2 === 0) ? 'row-odd' : 'row-even';

                $tag_array[] = $cellB->getValue();


                $match = in_array($cellB->getValue(), $array);
                $tagClass = $match ? "match-tag" : "miss-tag";
                $descClass = $match ? "match-desc" : "miss-desc";
                echo "<div class='$tagClass excel-info $color_class'>" . $row_number . " . &nbsp; " . $cellB->getValue() . "</div>";
            }
            if (!is_null($cell_array['Descr'])) {
                $cellH = $worksheet->getCell(substr($cell_array['Descr'], 0, $sizes['Descr']) . $row->getRowIndex());
                $desc = $cellH->getValue();
                $disc_arr[] = $desc;
                echo "<div class='$descClass excel-info $color_class'>" . $desc . "</div>";
            }
            if (!is_null($cell_array['Serial ID'])) {
                $cellI = $worksheet->getCell(substr($cell_array['Serial ID'], 0, $sizes['Serial ID']) . $row->getRowIndex());
                $sn = $cellI->getValue() ?? "EMPTY";
                $sn_arr[] = $sn;
                echo "<div class='excel-info $color_class'>" . $sn . "</div>";
            }
            if (!is_null($cell_array['Location'])) {
                $cellJ = $worksheet->getCell(substr($cell_array['Location'], 0, $sizes['Location']) . $row->getRowIndex());
                $loc = $cellJ->getValue() ?? "EMPTY";
                $loc_arr[] = $loc;
                echo "<div class='excel-info $color_class'>" . $loc . "</div>";
            }
            if (!is_null($cell_array['Dept'])) {
                $cellN = $worksheet->getCell(substr($cell_array['Dept'], 0, $sizes['Dept']) . $row->getRowIndex());
                $dept = $cellN->getValue() ?? "EMPTY";
                $dept_arr[] = $dept;
                echo "<div class='excel-info $color_class'>" . $dept . "</div>";
            }
            if (!is_null($cell_array['COST Total Cost'])) {
                $cellAA = $worksheet->getCell(substr($cell_array['COST Total Cost'], 0, $sizes['COST Total Cost']) . $row->getRowIndex());
                $cost = $cellAA->getValue() ?? "EMPTY";
                $cost_arr[] = $cost;
                echo "<div class='excel-info $color_class'>$" . $cost . "</div>";

            }
            if (!is_null($cell_array['PO No.'])) {
                $po_cell = $worksheet->getCell(substr($cell_array['PO No.'], 0, $sizes['PO No.']) . $row->getRowIndex());
                $po = $po_cell->getValue() ?? "EMPTY";
                $po_arr[] = $po;
                echo "<div class='excel-info $color_class'>" . $po . "</div>";

            }
            $row_number++;
        }
        echo "</div></section>";


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

#-----------------------------------------------------------------------------------------------------
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
#-----------------------------------------------------------------------------------------------------
    ?>
    <div class='formId'>
    <form id="makeSheet" method='POST' action='auditing.php' enctype="multipart/form-data">
<?php
    echo "<input type='hidden' name='assets' id='assets'>";

    echo "<input type='hidden' name='filePath' value='$filePath'>";
?>
        <button type='submit' id='create' name='create'>Export Excel</button>
    </form>
</div>
<?php
    echo "</div>";
#-----------------------------------------------------------------------------------------------------
    foreach ($array as $value) {
        echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
    }
    foreach ($time_array as $time) {
        echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time) . "'>";
    }
    foreach ($note_array as $note) {
        echo "<input type='hidden' name='previousNote[]' value='" . htmlspecialchars($note) . "'>";
    }
#-----------------------------------------------------------------------------------------------------
?>
    <div id="additionalInputs"></div>
    <form class="dyncamic-form" id="dynamicForm" method='POST' action='auditing.php' onLoad="addNewInput()" enctype="multipart/form-data">
        <div id="inputContainer">
            <!-- Input fields will appear here -->
            <input class="dynamicId" type="text" name="dynamicInput[]" placeholder="Enter Tag" onchange="addNewInput()">
            <input class="dynamicId" type="text" name="dynamicNote[]" value="none" placeholder="Notes">
        </div>
<?php


    echo "<input type='hidden' name='filePath' value='$filePath'>";
?>

        <button type="button" id="addInputButton" onClick="addNewInput()" onLoad="addNewInput()">Add Field</button>
        <button type="submit" id='dynamicSubmit'>Submit</button>
    </form>
<?php
?>


<?php
}
?>
<?php

?>
   <script>
document.querySelect('.dyncamic-form').addEventListener('submit', function(e) {

    const assets = [];

    // however you're storing the parallel arrays:
    const previousInputContainer = document.getElementsByName('previousInputContainer[]');
    const previousTime = document.getElementsByName('previousTime[]');
    const previousNote = document.getElementsByName('previousNote[]');
    for (let i = 0; i < tags.length; i++) {
        assets.push({
            previousInputContainer: previousInputContainer[i].value,
            previousTime: previousTime[i].value,
            previousNote: previousNote[i].value,
        });
    }

    // Convert to JSON and set the hidden input
    document.getElementById('assets').value = JSON.stringify({ assets: assets });
});
document.querySelect('.create-form').addEventListener('submit', function(e) {
    const assets = [];

    // however you're storing the parallel arrays:
    const previousInputContainer = document.getElementsByName('previousInputContainer[]');
    const old_tag = document.getElementsByName('old_tag[]');
    const previousTime = document.getElementsByName('previousTime[]');
    const previousNote = document.getElementsByName('previousNote[]');
    const headers = document.getElementsByName('headers[]');
    const description = document.getElementsByName('description[]');
    const serial = document.getElementsByName('serial[]');
    const po_num = document.getElementsByName('po_num[]');
    const loc = document.getElementsByName('loc[]');
    const cost = document.getElementsByName('cost[]');
    const dept = document.getElementsByName('dept[]');


    for (let i = 0; i < tags.length; i++) {
        assets.push({
            previousInputContainer: previousInputContainer[i].value,
            old_tag: old_tag[i].value,
            previousTime: previousTime[i].value,
            previousNote: previousNote[i].value,
            headers: headers[i].value,
            description: description[i].value,
            serial: serial[i].value,
            po_num: po_num[i].value,
            loc: loc[i].value,
            cost: cost[i].value,
            dept: dept[i].value
        });
    }

    // Convert to JSON and set the hidden input
    document.getElementById('assets').value = JSON.stringify({ assets: assets });

});

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
    noteInput.value = 'none';
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
function changeBoxSize(box_size) {
    var resize = document.querySelectorAll('.excel-info');
    console.log(box_size, resize.length);

    resize.forEach(el => {
        el.style.minWidth = box_size;
        console.log(el, "sefaef", box_size)
    });
}
/*
    window.addEventListener("load", function () {
    addNewInput();
});
 */

</script>
<?php
    if ($count == 4) {
        echo "<script>changeBoxSize('11vw');</script>";
        echo "<h1>".$column_count."</h1>";

    } else if ($count == 5) {
        echo "<script>changeBoxSize('10vw');</script>";
    } else if ($count === 6) {

        echo "<script>changeBoxSize('9vw');</script>";
    }

        ?>
</body>
