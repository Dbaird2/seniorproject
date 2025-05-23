<!DOCTYPE html>
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

<body>
    <form id="sheet" name="form" action="index.php" method="POST" enctype="multipart/form-data">
        <label for="filePath"> Enter File: </label>
        <input type="file" name="filePath" id="filePath">
<br>
        <button type="submit" >Submit</button>
    </form>
<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
    /*
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
     */
$worksheet = NULL;
if (isset($_POST['create'])) {
    try {
        $saveDir = __DIR__ . '/exports/';

        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1'];
        //echo "<pre>";
        //var_dump($_POST);
        //echo"</pre>";

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
        $filePath = $_FILES['filePath']['tmp_name'];
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
                #$sheet->setCellValue('E' . $row_index, $sn[$i]);
                $sheet->setCellValue('F' . $row_index, $loc[$i]);
                #$sheet->setCellValue('G' . $row_index, $po[$i]);

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
                echo "<br>";
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
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Location: download.php?file=' . urlencode($filePath));
    } catch (Exception $e) {
        echo "Something went wrong trying to parse before downloading ". $e;
    }
    exit();
}


#if (isset($_POST['filePath'])) {
if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
    if (isset($_POST['fileContent'])) {
    $encodedFileContent = $_POST['fileContent'];
    $fileName = $_POST['fileName'];

    // Decode the Base64 content back to its original binary form
    $fileContent = base64_decode($encodedFileContent);

    $stream = fopen('php://memory', 'r+');

    // Write the decoded file content to the memory stream
    fwrite($stream, $fileContent);

    // Rewind the stream to the beginning before passing it to IOFactory
    rewind($stream);
    // Now you can process the file content as needed
    echo "Received file content for file: $fileName";
    }
    $tmpPath = $_FILES['filePath']['tmp_name'];
    try {
        $filePath = $tmpPath;
        $spreadsheet = IOFactory::load($tmpPath);


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

        echo $filePath . "<br>";
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
    }

$tmpPath = $_FILES['filePath']['tmp_name'] ?? NULL;
try {
    $filePath = $tmpPath;
    $spreadsheet = IOFactory::load($tmpPath);


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

    echo $filePath . "<br>";
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

}
?>


<?php
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
if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
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
        echo "<div style=background-color:$color;height:110%;margin-top:-1em;margin-bottom:-1em; class='inner-text'>";
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
        $sn_arr[] = $sn;
        if ($sn != NULL) {
            echo "<b>SN: </b>" . $sn . " <b>|</b>  ";
        }
        $loc = $cellJ->getValue();
        $loc_arr[] = $loc;
        if ($loc != NULL) {
            echo "<b>Location: </b>" . $loc;
        }
        $po = $cellN->getValue();
        $po_arr[] = $sn;
        if ($po != NULL) {
            echo "<b> |</b>  ";
            echo "<b>PO:</b> " . $po;
        }
        echo "</div>";
        echo "</div>";
        $row_number++;

    }
}


$i = 0;
echo "<div class='show-tags'>";
echo "<h3 style=margin-bottom:-1vh;margin-left:0.6vw;>Tags Scanned</h3>";
if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
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
}
echo "</div>";
if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
?>
    <div id="additionalInputs"></div>
    <form id="dynamicForm" method='POST' action='index.php' onLoad="addNewInput()" enctype="multipart/form-data">
        <label for="inputContainer"> Enter Tags: </label>
        <div id="inputContainer">
            <!-- Input fields will appear here -->
            <input type="text" name="dynamicInput[]" placeholder="Enter Tag" onfocus="addNewInput()">

        </div>
<?php


if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
    $fileTmpPath = $_FILES['filePath']['tmp_name'];
    $fileName = $_FILES['filePath']['name'];

    // Read the file into memory
    $fileContent = file_get_contents($fileTmpPath);

    // Base64 encode the file content
    $encodedFileContent = base64_encode($fileContent);
foreach ($array as $value) {
    echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
}
foreach ($time_array as $time) {
    echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time) . "'>";
}
echo "<input type='hidden' name='fileContent' value='$encodedFileContent'>";
echo "<input type='hidden' name='filePath' value='$filePath'>";
}
?>

        <button type="button" id="addInputButton" onClick="addNewInput()" onLoad="addNewInput()">Add Field</button>
        <button type="submit" id='dynamicSubmit'>Submit</button>
    </form>
<?php
}
?>

    <form id="makeSheet" method='POST' action='index.php' enctype="multipart/form-data">
<?php

if (isset($_FILES['filePath'])
    /*&& $_FILE['filePath']['error'] === UPLOAD_ERR_OK*/) {
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

}
echo "<input type='hidden' name='filePath' value='$filePath'>";
?>
        <button type='submit' id='create' name='create'>Export Excel File</button>
    </form>


<script>
$(document).ready(function () {
    $('dynamicSubmit').click(function () {
        $.post($this.attr("action"), $("#dynamicForm").serialize(), function (response) {
            alert(response)
        });
    });
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
    newInput.classList.add('dynamic-input');

    newInput.addEventListener("focus", addNewInput, false)

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
window.addEventListener("load", function () {
    addNewInput();
});


</script>
</body>
