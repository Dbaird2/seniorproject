<html>
<head>
    <style>
        .excel-info {
            border: 2px outset black;
            background-color: white;
            text-align: left;
            width: 60%;

            margin-bottom: -0.0em;
            display: inline-block;
        }

        li {
            list-style-type: none;
        }

        .inner-text {
            margin-top: -1vh;
            margin-bottom: -1vh;
            font-size: 0.8em;
        }

        .show-tags {
            position: absolute;
            /* Position the form relative to the viewport */
            top: 0;
            /* Align it to the top */
            right: 34vh;
            /* Align it to the right */
        }

        .show-tags li {
            padding-top: 1em;
            margin-top: -5%;
            font-size: 80%;
            margin-bottom: -2.5vh;
        }

        body {
            margin: 0;
            height: 100vh;
        }

        #dynamicForm {
            position: absolute;
            top: 1vh;
            right: 10.5vh;
            padding: 10px;
            background-color: white;
            border: 1px solid white;
        }
    </style>
    <title>Asset Management Excel</title>
</head>
<?php
error_reporting(0);
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if (isset($_POST['create'])) {
    try {
        $filePath = __DIR__ . $_POST['filePath'];
        try {
        } catch (Exception $e) {
        }
        $saveDir = __DIR__ . '/exports/';
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1'];
        /*
        echo "<pre>";
        var_dump($_POST);
        echo"</pre>";
         */
        $row_index = 2;
        $previous_times = $_POST['previousTime'] ?? NULL;
        $previous_inputs = $_POST['previousInputContainer'] ?? NULL;
        //var_dump($previous_inputs);
        //echo sizeof($previous_inputs);
        $headers = $_POST['headers'];
        $loc = $_POST['loc'];
        $sn = $_POST['serial'];
        $po = $_POST['po_num'];
        $old_tags = $_POST['old_tag'];
        $desc = $_POST['description'];
        //echo "Looking for: " .  $filePath . "<br>";
        //var_dump(file_exists($filePath));
        $fileNameOnly = basename($filePath);
        $filePath = $saveDir . $fileNameOnly;
        $empty_scan = is_null($previous_inputs[0]) ? true : false;
        $file_empty = is_null($old_tags[1]) ? true : false;
        $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
        $filePath = str_replace(".xls", "_AUDIT", $filePath);
        $filePath = $filePath . ".xlsx";
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
        /*
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . basename($filePath) . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');
         */
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        //$writer->save('php://output');
        //readfile($filePath);
        header('Location: download.php?file=' . urlencode($filePath));
        error_reporting(1);
    } catch (Exception $e) {
        echo "Something went wrong trying to parse before downloading ". $e;
    }
    //exit();
}
?>


<?php

    /*
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
     */
$worksheet = NULL;
?>
<body>
    <form id="sheet" name="form" action="index.php" method="POST" enctype="multipart/form-data">
        <label for="file"> Enter File: </label>
        <input type="file" name="file" id="filePath">
<br>
        <button type="submit" >Submit</button>
    </form>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Get file info
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

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

#if (isset($_POST['filePath'])) {
try {
    $spreadsheet = IOFactory::load($filePath);


    // Get the first worksheet
    $worksheet = $spreadsheet->getActiveSheet();

    $row_number = 1;
    $array = [];
    $old_tags = [];
    $disc_arr = [];
    $sn_arr = [];
    $loc_arr = [];
    $po_arr = [];
    $tag_array = [];
    $time_array = [];
    $column_headers = [];

    $tag = $worksheet->getCell('B2')->getValue() . ":";
    // Loop through the rows and columns
    foreach ($worksheet->getRowIterator() as $row) {
        foreach ($row->getCellIterator() as $cell) {
            $coordinate = $cell->getCoordinate();
            $worksheet->getStyle($coordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Horizontal alignment
            $worksheet->getStyle($coordinate)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); // Vertical alignment
        }
    }
} catch (Exception $e) {
    echo "Error uploading file";
} catch (\TypeError $e) {
    echo "TypeError";
}

// Load the spreadsheet
echo "<pre>";
//var_dump($_POST);
echo "</pre>";
if (isset($_POST['dynamicInput'])) {
    $previous_times = $_POST['previousTime'] ?? NULL;       
    $previous_inputs = $_POST['previousInputContainer'] ?? NULL;

    $inputs = $_POST['dynamicInput'];
    $timeInputs = $_POST['dynamicTime'];
    $seen = [];
    $pSeen = [];
    $pNewTimes = [];
    $pNewInputs = [];
    $newTimes = [];

    foreach ($inputs as $key => $input) {
        if (!isset($seen[$input])) {
            $seen[$input] = true; // Mark this input as seen
            $newInputs[] = $input;
            $newTimes[] = $timeInputs[$key];
        }
    }
        /*foreach ($inputs as $key => $input) {
            if (!in_array($input, $seen)) {
                $seen[] = $input;
                $newInputs[] = $input;
                $newTimes[] = $timeInputs[$key];
            }
        }
         */
    if (!is_null($previous_inputs)) {

        foreach ($previous_inputs as $key => $input) {
            if (!in_array($input, $pSeen)) {
                $pSeen[] = $input;
                $pNewInputs[] = $input;
                $pNewTimes[] = $previous_times[$key];
            }
        }

        $dupes = [];
        foreach ($previous_inputs as $key => $previousInput) {
            foreach ($newInputs as $newKey => $newInput) {
                if ($previousInput == $newInput) {
                    $dupes[] = $newKey;
                }
            }
        }

        foreach ($dupes as $newKey) {
            unset($newTimes[$newKey]);
            unset($newInputs[$newKey]);
        }
    }


    foreach ($pNewInputs as $index => $value) {
        if ($value != NULL) {
            $array[] = htmlspecialchars($value);
        }
    }

    foreach ($pNewTimes as $index => $time2) {
        if ($value != NULL) {
            $time_array[] = htmlspecialchars($time2);
        }
    }

    foreach ($newTimes as $index => $time) {
        if ($time != NULL) {
            $time_array[] = htmlspecialchars($time);
        }
    }

    foreach ($newInputs as $index => $value) {
        if ($value != NULL) {
            $array[] = htmlspecialchars($value);
        }
    }
}
    if (!is_null($worksheet)){
$worksheet->getRowIterator(1);
$cellB = $worksheet->getCell('B' . 2);
$cellH = $worksheet->getCell('H' . 2);
$cellI = $worksheet->getCell('I' . 2);
$cellJ = $worksheet->getCell('J' . 2);
$cellN = $worksheet->getCell('N' . 2);
$tags = $cellB->getValue('B2');
$H = $cellH->getValue('H2');
$I = $cellI->getValue('I2');
$J = $cellJ->getValue('J2');
$N = $cellN->getValue('N2');
$column_headers[] = $tags;
$column_headers[] = 'Audited Tags';
$column_headers[] = 'Timestamp';
$column_headers[] = $H;
$column_headers[] = $I;
$column_headers[] = $J;
$column_headers[] = $N;
}

$colors = ['lightgray', 'white'];
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

        $cellB = $worksheet->getCell('B' . $row->getRowIndex());
        $cellH = $worksheet->getCell('H' . $row->getRowIndex());
        $cellI = $worksheet->getCell('I' . $row->getRowIndex());
        $cellJ = $worksheet->getCell('J' . $row->getRowIndex());
        $cellN = $worksheet->getCell('N' . $row->getRowIndex());

        $color_change = $row_number % 2;
        $color = $colors[$color_change];

        $tag_array[] = $cellB->getValue();
        echo "<div class='excel-info' style=border-style: solid;margin-bottom:1em;>";
        echo "<div style='background-color:$color;margin-top:-1em;margin-bottom:-1em;' class='inner-text'>";
        echo "<ul>";
        echo "<li style=float:left;margin-left:-2em; tabindex='2'><b>" . $row_number . "</b></li>";
        $match = 0;
        foreach ($array as $row) {
            if ($cellB->getValue() == $row) {
                $match = 1;
                break;
            } else {
                $match = 0;
            }
        }
        if ($match) {
            echo $tag . str_repeat(' ', 4);
            echo "<b style=color:green;border:5vh solid black>" . $cellB->getValue() . " |</b> ";
            echo "<b>Description:</b> ";
            echo "<b style=color:green;>" . $cellH->getValue() . "</b>";
            echo "<b> |</b>  ";
            $disc_arr[] = $cellH->getValue();
        } else if ($cellB->getValue() == 'Tag Number') {
            echo "<b style=color:black;>" . $cellB->getValue() . "</b>  ";
        } else {
            echo $tag . str_repeat(' ', 4);
            echo "<b style=color:red;>" . $cellB->getValue() . "</b> |</b>  ";
            echo "<b>Description:</b> ";
            echo "<b style=color:red;>" . $cellH->getValue() . "</b>";
            echo "<b> |  " . "</b>";
            $disc_arr[] = $cellH->getValue();
        }
        $sn = $cellI->getValue();
        $sn = is_null($sn) ? "EMPTY" : $sn;
        echo "<b>SN: </b>" . $sn . " <b>|</b>  ";
        $sn_arr[] = $sn;

        $loc = $cellJ->getValue();
        $loc = is_null($loc) ? "EMPTY" : $loc;
        echo "<b>Location: </b>" . $loc;
        $loc_arr[] = $loc;

        $po = $cellN->getValue();
        $po = is_null($po) ? "EMPTY" : $po;
        $po_arr[] = $sn;
        echo "<b> |</b>  ";
        echo "<b>PO:</b> " . $po;
        echo "</div>";
        echo "</div>";
        $row_number++;

    }
}


$i = 0;
echo "<div class='show-tags'>";
echo "<h3 style=margin-bottom:-1vh;margin-left:0.6vw;>Tags Scanned</h3>";
foreach ($array as $row) {
    foreach ($tag_array as $tag_row) {
        $match2 = ($row == $tag_row) ? 1 : 0;
        if ($match2) break;
    }
    if ($match2) {
        echo "<b> <li style=color:green;>" . $row . "</b>  " . $time_array[$i] . "</li><br>";

    } else {
        echo "<b> <li style=color:red;>" . $row . "</b>  " . $time_array[$i] . "</li><br>";

    }
    $i++;
}
echo "</div>";
?>
    <div id="additionalInputs"></div>
    <form id="dynamicForm" method='POST' action='index.php' onLoad="addNewInput()" enctype="multipart/form-data">
        <label for="inputContainer"> Enter Tags: </label>
        <div id="inputContainer">
            <!-- Input fields will appear here -->
            <input type="text" name="dynamicInput[]" placeholder="Enter Tag" onchange="addNewInput()">

        </div>
<?php


foreach ($array as $value) {
    echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
}
foreach ($time_array as $time) {
    echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time) . "'>";
}
echo "<input type='hidden' name='filePath' value='$filePath'>";
?>

        <button type="button" id="addInputButton" onClick="addNewInput()" onLoad="addNewInput()">Add Field</button>
        <button type="submit" id='dynamicSubmit'>Submit</button>
    </form>
<?php
?>

    <form id="makeSheet" method='POST' action='index.php' enctype="multipart/form-data">
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

echo "<input type='hidden' name='filePath' value='$filePath'>";
?>
        <button type='submit' id='create' name='create'>Export Excel File</button>
    </form>

<?php
}
echo "<pre>";
//var_dump($_POST);
echo "</pre>";
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
    newInput.classList.add('dynamic-input');

    newInput.addEventListener("change", addNewInput, false)

        const timeInput = document.createElement('input');
    timeInput.type = 'hidden';
    timeInput.name = 'dynamicTime[]';
    timeInput.value = getFormattedDateTime();

    // Append input to the div and the div to the container
    inputDiv.appendChild(timeInput);

    inputDiv.appendChild(newInput);
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
