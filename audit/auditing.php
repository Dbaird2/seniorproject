<?php
$filePath = $count = NULL;
error_reporting(0);
include_once("../config.php");
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
if (isset($_POST['download'])) {


    try {
        $download_data = $_POST['download'];
        list($sn, $po, $loc, $cost, $dept, $headers, $old_tags, $desc, $previous_inputs, $previous_notes, $previous_times, $previous_rooms, $filePath) = explode('|', $download_data);
        $sn = explode('`', $sn);
        $po = explode('`', $po);
        $loc = explode('`', $loc);
        $cost = explode('`', $cost);
        $dept = explode('`', $dept);
        $headers = explode('`', $headers);
        $old_tags = explode('`', $old_tags);
        $desc = explode('`', $desc);

        $previous_inputs = explode('`', $previous_inputs);
        $previous_times = explode('`', $previous_times);
        $previous_notes = explode('`', $previous_notes);
        $previous_rooms = explode('`', $previous_rooms);

        $saveDir = __DIR__ . '/exports/';
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $column_letters = ['A1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1'];
        $row_index = 2;


        $fileNameOnly = basename($filePath);
        $filePath = $fileNameOnly;

        $empty_scan = is_null($previous_inputs[0]) ? true : false;
        $file_empty = is_null($old_tags[0]) ? true : false;

        $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
        $filePath = str_replace(".xls", "_AUDIT", $filePath);
        $filePath = $filePath . ".xlsx";

        $sheet->setCellValue($column_letters[0], 'Tag Number');
        $sheet->setCellValue($column_letters[1], 'Description');
        $sheet->setCellValue($column_letters[2], 'Serial ID');
        $sheet->setCellValue($column_letters[3], 'Location');
        $sheet->setCellValue($column_letters[4], 'Department');
        $sheet->setCellValue($column_letters[5], 'Cost');
        $sheet->setCellValue($column_letters[6], 'Purchase Order');


        $sheet->setCellValue('B1', 'Tags Found');
        $sheet->setCellValue('C1', 'Found in Room');
        $sheet->setCellValue('D1', 'Notes');
        $sheet->setCellValue('E1', 'Timestamp');
        $i = 0;
        if (!$file_empty) {
            foreach ($old_tags as $row) {
                $sheet->setCellValue('A' . $row_index, $old_tags[$i]);
                $sheet->setCellValue('F' . $row_index, $desc[$i]);
                $sheet->setCellValue('G' . $row_index, $sn[$i]);
                $sheet->setCellValue('H' . $row_index, $loc[$i]);
                $sheet->setCellValue('I' . $row_index, $dept[$i]);
                $sheet->setCellValue('J' . $row_index, $cost[$i]);
                $sheet->setCellValue('K' . $row_index, $po[$i]);
                $i++;
                $row_index++;
            }
            $h_row = 2;
            if (!$empty_scan) {
                for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                    for ($i = 0; $i < sizeof($old_tags); $i++) {
                        if ($previous_inputs[$j] == $old_tags[$i]) {
                            $sheet->setCellValue('B' . $i + 2, $previous_inputs[$j]);
                            $sheet->setCellValue('C' . $i + 2, $previous_rooms[$j]);
                            $sheet->setCellValue('D' . $i + 2, $previous_notes[$j]);
                            $sheet->setCellValue('E' . $i + 2, $previous_times[$j]);
                            break;
                        } else if ($i == sizeof($old_tags) - 1) {
                            $sheet->setCellValue('B' . $h_row + sizeof($old_tags), $previous_inputs[$j]);
                            $sheet->setCellValue('C' . $h_row + sizeof($old_tags), $previous_rooms[$j]);
                            $sheet->setCellValue('D' . $h_row + sizeof($old_tags), $previous_notes[$j]);
                            $sheet->setCellValue('E' . $h_row + sizeof($old_tags), $previous_times[$j]);
                            $h_row++;
                        }
                    }
                }
            }
        } else {
            $sheet->setCellValue('A2', 'No Assets Found');
            if (!$empty_scan) {
                $h_row=2;
                for ($j = sizeof($old_tags); $j < sizeof($previous_inputs); $j++) {
                    $sheet->setCellValue('B' . $h_row, $previous_inputs[$j]);
                    $sheet->setCellValue('C' . $h_row, $previous_notes[$j]);
                    $sheet->setCellValue('D' . $h_row++, $previous_times[$j]);
                }
            }
        }
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
include_once("../navbar.php");

?>
 <style>
  body { font-family: sans-serif; margin: 0; }
  .show-tags { background: #fff; padding: 1rem; font-size: 1rem; }
  .row, .clusterize-scroll { max-height: 100vh; overflow-y: auto; }
</style>
<link rel="stylesheet" href="auditing.css" media="print" onload="this.media='all'">
    <title>Asset Management Excel</title>
</head>
<?php

$worksheet = NULL;
?>
<body>

<?php
$array = $time_array = $note_array = $room_array = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];

    $file_type_check = substr($fileName, strlen($fileName) - 4);
    if ($file_type_check != 'xlsx' && $file_type_check != '.xls') {
        echo "<h3'>File type not allowed</h3>";
        return;
    }

    $uploadDir = 'uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($fileName);

    if (move_uploaded_file($fileTmpPath, $filePath)) {
    } else {
        echo "Error uploading file.";
    }
}

if (isset($_POST['data'])) {
    $scanned_data = $_POST['data'];
    list($scanned_tags, $notes, $times, $rooms, $previous_tags, $previous_notes,$previous_times, $previous_rms, $filePath) = explode('|', $scanned_data);
    $scanned_tags = explode('`', $scanned_tags);
    $notes = explode('`', $notes);
    $times = explode('`', $times);
    $rooms = explode('`', $rooms);
    $previous_tags = explode('`', $previous_tags);
    $previous_times = explode('`', $previous_times);
    $previous_notes = explode('`', $previous_notes);
    $previous_rms = explode('`', $previous_rms);

    $scanned_tags = array_merge($scanned_tags, $previous_tags);
    $scanned_notes = array_merge($notes, $previous_notes);
    $scanned_times = array_merge($times, $previous_times);
    $scanned_rooms = array_merge($rooms, $previous_rms);



    $seen_tags = [];
    $filtered_tags = [];
    $filtered_notes = [];
    $filtered_times = [];
    $filtered_rooms = [];
    foreach($scanned_tags as $i=>$value) {
        if (!isset($seen_tags[$value]) && $value !== '') {
            $seen_tags[$value] = true;
            $filtered_tags[] = $value;
            $filtered_notes[] = $scanned_notes[$i];
            $filtered_times[] = $scanned_times[$i];
            $filtered_rooms[] = $scanned_rooms[$i];
        }
    }
    $array = $filtered_tags;
    $note_array = $filtered_notes;
    $time_array = $filtered_times;
    $room_array = $filtered_rooms;
}
if (!isset($filePath) && $filePath !== '') { ?>
<form id="sheet" name="form" action="auditing.php" method="POST" enctype="multipart/form-data">
    <label for="file">Enter File:</label>
    <input type="file" name="file" id="filePath">
    <button type="submit">Submit</button>
</form>
<?php
}

if (isset($filePath)) {
    try {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        //$data = $worksheet->toArray();
        $data = $worksheet->rangeToArray('A1:AB5000');
        $highest_row= $worksheet->getHighestRow();
        $highest_col = $worksheet->getHighestColumn();

        $row_number = 1;
        $old_tags = [];
        $disc_arr = [];
        $sn_arr = [];
        $loc_arr = [];
        $po_arr = [];
        $cost_arr = [];
        $dept_arr = [];
        $column_headers = [];

    } catch (Exception $e) {
        echo "Error uploading file";
    } catch (\TypeError $e) {
        echo "TypeError";
    }

    if (!is_null($worksheet)){
        $cell_array = [];
        $headers = ['Tag Number','Descr', 'Serial ID', 'Location', 'Dept', 'COST Total Cost', 'PO No.'];
        $header_row = 0;
        $header_row = in_array('Tag Number', $data[0]) ? 0 : $header_row;
        $header_row = in_array('Tag Number', $data[1]) ? 1 : $header_row;
        $header_row = in_array('Tag Number', $data[2]) ? 2 : $header_row;
        $header_row = in_array('Tag Number', $data[3]) ? 3 : $header_row;


        $count = 0;
        $tag_col = $descr_col = $serial_col = $model_col = $vin_col = $custodian_col = $dept_col = $acq_date_col = $cost_col = $po_col = $location_col = $asset_type_col = $profile_id_col = -1;
        $column_headers = [];
        for ($i = $header_row; $i < count($data[$header_row]); $i++) {
            if ($data[$header_row][$i] === 'Tag Number' || $data[$header_row][$i] === 'Tag') {
                $column_headers[] = "Tag Number";
                $tag_col = $i;
            }
            if ($data[$header_row][$i] === 'Description' || $data[$header_row][$i] === 'Descr') {
                $column_headers[] = "Description";
                $descr_col = $i;
            }
            if ($data[$header_row][$i] === 'Serial ID' || $data[$header_row][$i] === 'SN') {
                $column_headers[] = "Serial ID";
                $serial_col = $i;
            }
            if ($data[$header_row][$i] === 'Model' || $data[$header_row][$i] === 'Model Number') {
                $model_col = $i;
            }
            if ($data[$header_row][$i] === 'VIN' || $data[$header_row][$i] === 'Vehicle ID') {
                $vin_col = $i;
            }
            if ($data[$header_row][$i] === 'Custodian' || $data[$header_row][$i] === 'Custodian Name') {
                $column_headers[] = "Custodian";
                $custodian_col = $i;
            }
            if ($data[$header_row][$i] === 'Dept' || $data[$header_row][$i] === 'Department') {
                $column_headers[] = "Department";
                $dept_col = $i;
            }
            if ($data[$header_row][$i] === 'Acq Date' || $data[$header_row][$i] === 'Acquisition Date') {
                $column_headers[] = "Acquisition Date";
                $acq_date_col = $i;
            }
            if ($data[$header_row][$i] === 'COST Total Cost' || $data[$header_row][$i] === 'Price') {
                $column_headers[] = "Cost";
                $cost_col = $i;
            }
            if ($data[$header_row][$i] === 'PO No.' || $data[$header_row][$i] === 'Purchase Order') {
                $column_headers[] = "Purchase Order";
                $po_col = $i;
            }
            if ($data[$header_row][$i] === 'Profile ID' || $data[$header_row][$i] === 'Profile') {
                $profile_id_col =  $i;
            }
            if ($data[$header_row][$i] === 'Location') {
                $column_headers[] = "Location";
                $location_col = $i;
            }
            if ($data[$header_row][$i] === 'Asset Type') {
                $asset_type_col = $i;
            }
        }
    }

    $colors = ['lightblue', 'white'];
    $empty = false;

    $highest_row = $worksheet->getHighestRow();
    $highest_col = $worksheet->getHighestColumn();
    $j = 0;
?>
    <section id='showExcel'>
    <div class='row'>
<div class="clusterize">
  <div id="scrollArea" class="clusterize-scroll">
    <table>
        <thead>
      <tr>
        <th>Row</th><th>Tags</th><th>Descriptions</th><th>Serial IDs</th><th>Locations</th><th>Departments</th><th>Costs</th><th>Purchase Orders</th>
      </tr>
    </thead>
      <tbody id="contentArea" class="clusterize-content" style="width:10vw;">
<?php
    for ($i = $header_row + 1; $i < $header_row + 11; $i++) {
        $rowNum = $i - $header_row;
        $tag = htmlspecialchars($data[$i][$tag_col]);
        $descr = htmlspecialchars($data[$i][$descr_col]);
        $serial = htmlspecialchars($data[$i][$serial_col]);
        $location = htmlspecialchars($data[$i][$location_col]);
        $department = htmlspecialchars($data[$i][$dept_col]);
        $cost = htmlspecialchars($data[$i][$cost_col]);
        $po = htmlspecialchars($data[$i][$po_col]);

        echo "<tr>
            <td>$rowNum</td>
            <td>$tag</td>
            <td>$descr</td>
            <td>$serial</td>
            <td>$location</td>
            <td>$department</td>
            <td>$cost</td>
            <td>$po</td>
            </tr>";
    }
?>
        <tr class="clusterize-no-data">
          <td>Loading data…</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</div>
</section>

<link rel="preload" as="script" href="https://cdn.jsdelivr.net/npm/clusterize.js@0.18.1/clusterize.min.js">
<script src="https://cdn.jsdelivr.net/npm/clusterize.js@0.18.1/clusterize.min.js" defer></script>
<?php 

    $rows = [];
    $flipped_array = array_flip(array_filter($data, 'is_string'));
    for ($i = $header_row + 1; $i < $highest_row; $i++) {
        $color_class = ($i % 2 === 0) ? 'row_odd' : 'row-even';
        $j++;
        $match = in_array($data[$i][$tag_col], $array, true) ? "match-tag" : "miss-tag";
        $tag = htmlspecialchars($data[$i][$tag_col]);
        $descr = htmlspecialchars($data[$i][$descr_col]);
        $serial = htmlspecialchars($data[$i][$serial_col]);
        $location = htmlspecialchars($data[$i][$location_col]);
        $department = htmlspecialchars($data[$i][$dept_col]);
        $cost = htmlspecialchars($data[$i][$cost_col]);
        $po = htmlspecialchars($data[$i][$po_col]);
        $tag_array[] = $data[$i][$tag_col];
        $disc_arr[] = $data[$i][$descr_col];
        $sn_arr[] = $data[$i][$serial_col];
        $loc_arr[] = $data[$i][$location_col];
        $dept_arr[] = $data[$i][$dept_col];
        $cost_arr[] = $data[$i][$cost_col];            
        $po_arr[] = $data[$i][$po_col];
        echo "<input type='hidden' name='old_tag[]' value='" . htmlspecialchars($tag_array[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='description[]' value='" . htmlspecialchars($disc_arr[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='serial[]' value='" . htmlspecialchars($sn_arr[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='po_num[]' value='" . htmlspecialchars($po_arr[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='loc[]' value='" . htmlspecialchars($loc_arr[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='cost[]' value='" . htmlspecialchars($cost_arr[$i-$header_row-1]) . "'>";
        echo "<input type='hidden' name='dept[]' value='" . htmlspecialchars($dept_arr[$i-$header_row-1]) . "'>";
        $rows[] = "<tr class='{$color_class}'>
            <td class='{$match}'> {$j}. </td>
            <td class='{$match}'> {$tag}</td>
            <td>{$descr}</td>
            <td>{$serial}</td>
            <td>{$location}</td>
            <td>{$department}</td>
            <td>{$cost}</td>
            <td>{$po}</td>
            </tr>";
    }
?>
    <script>

    const rows = <?php echo json_encode($rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    window.requestIdleCallback(() => {
    var botmanWidget = {
        frameEndpoint: '../chat/botman-widget.html',
        chatServer: '../chat/chatbot.php',
        introMessage: "👋 Hello! I'm Chatbot. Ask me anything!",
        title: "Chatbot",
        mainColor: "#ADD8E6",
        bubbleBackground: "#ADD8E6",
        placeholderText: "Type your question here..."
    };
    clusterize = new Clusterize({
    rows: rows,
        scrollId: 'scrollArea',
        contentId: 'contentArea'
    });
    });
    </script>
<?php


    $i = 0;
    $tag_lookup = array_flip($tag_array);
    echo "<div class='show-tags' id='showTags'>";
    echo "<h4>Tags Scanned</h4>";
    echo "<ul>";
    foreach ($array as $row) {
        $colorClass = isset($tag_lookup[$row]) ? "tag-match" : "tag-miss";
        echo "<li class='$colorClass'>
            <strong>$row</strong> &mdash; 
        <br>Room:<input name='previousRms[]' value='" .htmlspecialchars($room_array[$i]) . "'>
            <br>Notes:<input name='previousNote[]' value='".htmlspecialchars($note_array[$i])."'></li>";
        $i++;
    }
?> 
    <div class='formId'>
    <form id="makeSheet" method='POST' action='auditing.php' enctype="multipart/form-data">

        <input type="hidden" name="download" id="download">

        <button type='submit' id='create' name='create'>Export Excel</button>
    </form>
</div>
<?php
    echo "</ul>";

    echo "</div>";
    $i = 0;
    foreach ($array as $value) {
        echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
        echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time_array[$i]) . "'>";
        echo "<input type='hidden' name='previousNote[]' value='" . htmlspecialchars($note_array[$i]) . "'>";
        echo "<input type='hidden' name='previousRms[]' value='" . htmlspecialchars($room_array[$i]) . "'>";
        $i++;
    }
    foreach ($column_headers as $header) {
        echo "<input type='hidden' name='headers[]' value='" . htmlspecialchars($header) . "'>";
    }
    echo "<input type='hidden' id='filePath2' name='filePath' value='$filePath'>";

?>

<div id="insert-tags-div">
        <div id="inputContainer"></div>
    <form id="dynamicForm" method='POST' action='auditing.php' onLoad="addNewInput()" enctype="multipart/form-data">

        <input type="hidden" name="data" id="data">
        <button type="submit" id='dynamicSubmit'>Submit</button>
        <button type="submit" id="complete-audit" name="complete-audit">Complete Audit</button>

    </form>
</div>

<?php
}

?>

<script>
window.requestIdleCallback(() => {
addNewInput();
addNewInput();
});
document.addEventListener("DOMContentLoaded", () => {
document.getElementById("showTags").style.opacity = "1";
});
//document.addEventListener('DOMContentLoaded', () => {
document.getElementById('makeSheet').addEventListener('submit', (e)=> {
e.preventDefault();
const previousInputContainer = Array.from(document.getElementsByName('previousInputContainer[]')).map(i=>i.value);
const previousTime = Array.from(document.getElementsByName('previousTime[]')).map(i=>i.value);
const previousNote = Array.from(document.getElementsByName('previousNote[]')).map(i=>i.value);
const previousRm = Array.from(document.getElementsByName('previousRms[]')).map(i=>i.value);

const serial = Array.from(document.getElementsByName('serial[]')).map(i=>i.value);
const po_num = Array.from(document.getElementsByName('po_num[]')).map(i=>i.value);
const loc = Array.from(document.getElementsByName('loc[]')).map(i=>i.value);
const cost = Array.from(document.getElementsByName('cost[]')).map(i=>i.value);
const dept = Array.from(document.getElementsByName('dept[]')).map(i=>i.value);
const old_tag = Array.from(document.getElementsByName('old_tag[]')).map(i=>i.value);
const headers = Array.from(document.getElementsByName('headers[]')).map(i=>i.value);
const description = Array.from(document.getElementsByName('description[]')).map(i=>i.value);

const filePath = document.getElementById('filePath2').value;

console.log(filePath, previousInputContainer, old_tag, filePath);


const download = [
    serial.join('`'),
    po_num.join('`'),
    loc.join('`'),
    cost.join('`'),
    dept.join('`'),
    headers.join('`'),
    old_tag.join('`'),
    description.join('`'),
    previousInputContainer.join('`'),
    previousNote.join('`'),
    previousTime.join('`'),
    previousRm.join('`'),
    filePath
].join('|');
document.querySelector('input[name="download"]').value = download;
document.getElementById('makeSheet').submit();
});

document.getElementById('dynamicForm').addEventListener('submit', (e)=> {
e.preventDefault();
const dynamicInputs = Array.from(document.getElementsByName('dynamicInput[]')).map(i=>i.value);
const dynamicNotes = Array.from(document.getElementsByName('dynamicNote[]')).map(i=>i.value);
const dynamicTimes = Array.from(document.getElementsByName('dynamicTime[]')).map(i=>i.value);
const dynamicRms = Array.from(document.getElementsByName('dynamicRm[]')).map(i=>i.value);

const previousInputs = Array.from(document.getElementsByName('previousInputContainer[]')).map(i=>i.value);
const previousTimes = Array.from(document.getElementsByName('previousTime[]')).map(i=>i.value);
const previousNotes = Array.from(document.getElementsByName('previousNote[]')).map(i=>i.value);
const previousRms = Array.from(document.getElementsByName('previousRms[]')).map(i=>i.value);

let empty_tag = [];
const filePath = document.getElementById('filePath2').value;
dynamicInputs.forEach(function(tag, index) {
    dynamicNotes[index] = dynamicNotes[index] === '' ? 'No Notes' :dynamicNotes[index];
    dynamicRms[index] = dynamicRms[index] === '' ? '000' :dynamicRms[index];
    if (tag === '') {
        empty_tag.push(index);
    }
})
    empty_tag.reverse().forEach(function(value) {
        dynamicInputs.splice(value, 1);
        dynamicNotes.splice(value, 1);
        dynamicTimes.splice(value, 1);
        dynamicRms.splice(value, 1);
    });

const data = [
    dynamicInputs.join('`'),
    dynamicNotes.join('`'),
    dynamicTimes.join('`'),
    dynamicRms.join('`'),
    previousInputs.join('`'),
    previousNotes.join('`'),
    previousTimes.join('`'),
    previousRms.join('`'),
    filePath
].join('|');
console.log(dynamicRms);
document.querySelector('input[name="data"]').value = data;
document.getElementById('dynamicForm').submit();
});
//});
function addNewInput() {
    const inputDiv = document.createElement('div');
    inputDiv.classList.add('input-container');

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'dynamicInput[]';
    newInput.autocomplete = "off";
    newInput.placeholder = 'Enter tag';
    inputDiv.appendChild(newInput);

    newInput.classList.add('dynamicId');

    newInput.addEventListener("change", addNewInput, false);

    const timeInput = document.createElement('input');
    timeInput.type = 'hidden';
    timeInput.name = 'dynamicTime[]';
    timeInput.value = getFormattedDateTime();
    inputDiv.appendChild(timeInput);

    const rmInput = document.createElement('input');
    rmInput.type = 'hidden';
    rmInput.name = 'dynamicRm[]';
    rmInput.placeholder = 'Room Num';
    rmInput.classList.add('dynamicId');
    inputDiv.appendChild(rmInput);

    const noteInput = document.createElement('input');
    noteInput.type = 'hidden';
    noteInput.name = 'dynamicNote[]';
    noteInput.placeholder = 'Notes';
    inputDiv.appendChild(noteInput);
    noteInput.classList.add('dynamicId');

    const inputContainer = document.getElementById('inputContainer');
    inputContainer.appendChild(inputDiv);

}

function getFormattedDateTime() {
    const currentDate = new Date();

    let month = currentDate.getMonth() + 1;
    let day = currentDate.getDate();
    let year = currentDate.getFullYear();

    month = month.toString().padStart(2, '0');
    day = day.toString().padStart(2, '0');
    let formattedDate = `${year}-${month}-${day}`;

    let formattedTime = currentDate.toLocaleTimeString('en-US', {
    hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    let formattedDateTime = `${formattedDate} ${formattedTime}`;


    return formattedDateTime;
}

function changeBoxSize(box_size) {
    var resize = document.querySelectorAll('.excel-info');

    resize.forEach(el => {
    el.style.minWidth = box_size;
    });
}


</script>
</body>
