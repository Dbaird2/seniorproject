<?php
error_reporting(0);
include_once("../config.php");
if (!isset($_SESSION['data']) || $_SESSION['data'] === '') {
    header("Location: https://dataworks-7b7x.onrender.com/audit/upload.php");
    exit;
}
check_auth();


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";
$filePath = $_SESSION['data'][0]['dept_id'] . '_AUDIT';
$count = NULL;
$previous_inputs = [];
if (isset($_POST['download'])) {


    try {
        $download_data = $_POST['download'];
        list($previous_inputs, $previous_notes, $previous_times, $previous_rooms) = explode('|', $download_data);

        $previous_inputs = explode('`', $previous_inputs);
        $previous_times = explode('`', $previous_times);
        $previous_notes = explode('`', $previous_notes);
        $previous_rooms = explode('`', $previous_rooms);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($_SESSION['data'] as $index => $row) {
            $excel_array[0][] = $row['asset_tag'];
            $excel_array[1][] = $row['asset_name'];
            $excel_array[2][] = $row['serial_num'];
            $excel_array[3][] = $row['room_loc'];
            $excel_array[4][] = $row['dept_id'];
            $excel_array[5][] = $row['asset_price'];
            $excel_array[6][] = $row['po'];
        }
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }


        $column_letters = ['A1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1'];
        $row_index = 2;
        $sizeofarray = count($excel_array[0]);


        $fileNameOnly = basename($filePath);
        $filePath = $fileNameOnly;
        $empty_scan = is_null($previous_inputs[0]) ? true : false;
        $file_empty = is_null($excel_array[$header_row + 1][0]) ? true : false;

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
        if ($file_empty) {
            $sheet->setCellValue('A2', 'No Assets Found');
            if (!$empty_scan) {
                $h_row = 2;
                for ($j = sizeof($excel_array[0]); $j < sizeof($previous_inputs); $j++) {
                    $sheet->setCellValue('B' . $h_row, $previous_inputs[$j]);
                    $sheet->setCellValue('C' . $h_row, $previous_notes[$j]);
                    $sheet->setCellValue('D' . $h_row++, $previous_times[$j]);
                }
            }
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            header('Location: download.php?file=' . urlencode($filePath));
        }


        for ($i = 0; $i < count($excel_array[0]); $i++) {
            $sheet->setCellValue('A' . $row_index, $excel_array[0][$i]);
            $sheet->setCellValue('F' . $row_index, $excel_array[1][$i]);
            $sheet->setCellValue('G' . $row_index, $excel_array[2][$i]);
            $sheet->setCellValue('H' . $row_index, $excel_array[3][$i]);
            $sheet->setCellValue('I' . $row_index, $excel_array[4][$i]);
            $sheet->setCellValue('J' . $row_index, $excel_array[5][$i]);
            $sheet->setCellValue('K' . $row_index, $excel_array[6][$i]);
            $row_index++;
        }
        $h_row = 2;
        if (!$empty_scan) {
            for ($j = 0; $j < sizeof($previous_inputs); $j++) {
                for ($i = 0; $i < sizeof($excel_array[0]); $i++) {
                    if ($previous_inputs[$j] == $excel_array[0][$i]) {
                        $sheet->setCellValue('B' . $i + 2, $previous_inputs[$j]);
                        $sheet->setCellValue('C' . $i + 2, $previous_rooms[$j]);
                        $sheet->setCellValue('D' . $i + 2, $previous_notes[$j]);
                        $sheet->setCellValue('E' . $i + 2, $previous_times[$j]);
                        break;
                    } else if ($i == sizeof($excel_array[0]) - 1) {
                        $sheet->setCellValue('B' . $h_row + sizeof($excel_array[0]), $previous_inputs[$j]);
                        $sheet->setCellValue('C' . $h_row + sizeof($excel_array[0]), $previous_rooms[$j]);
                        $sheet->setCellValue('D' . $h_row + sizeof($excel_array[0]), $previous_notes[$j]);
                        $sheet->setCellValue('E' . $h_row + sizeof($excel_array[0]), $previous_times[$j]);
                        $h_row++;
                    }
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
        echo "Something went wrong trying to parse before downloading " . $e;
    }
}

?>

<html>

<head>
    <?php
    include_once("../navbar.php");

    ?>

    <style>
    </style>
    <link rel="stylesheet" href="auditing.css" media="all">
    <title>Asset Management Excel</title>
</head>

<?php
$data = $_SESSION['data'];

?>

<html>

<head>
    <?php
    include_once("../navbar.php");

    ?>

    <style>
    </style>
    <link rel="stylesheet" href="auditing.css" media="all">
    <title>Asset Management Excel</title>
</head>
<?php

$worksheet = NULL;
?>

<?php
$array = $time_array = $note_array = $room_array = [];

if (isset($_SESSION['saved_tags']) && count($_SESSION['saved_tags']) > 0) {
    foreach ($_SESSION['saved_tags'] as $info) {
        $array[] = $info[0];
        $note_array[] = $info[1];
        $time_array[] = $info[2];
        $room_array[] = $info[3];
    }
}
if (isset($_POST['data'])) {
    $scanned_data = $_POST['data'];
    list($scanned_tags, $notes, $times, $rooms, $previous_tags, $previous_notes, $previous_times, $previous_rms, $filePath) = explode('|', $scanned_data);
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
    foreach ($scanned_tags as $i => $value) {
        if (!isset($seen_tags[$value]) && $value !== '') {
            $seen_tags[$value] = true;
            $filtered_tags[] = $value;
            $filtered_notes[] = $scanned_notes[$i];
            $filtered_times[] = $scanned_times[$i];
            $filtered_rooms[] = $scanned_rooms[$i];
        }
    }
    if (count($filtered_tags) > 0) {
        $array = $filtered_tags;
        $note_array = $filtered_notes;
        $time_array = $filtered_times;
        $room_array = $filtered_rooms;
        if (count($array) > 0) {
            $_SESSION['saved_tags'] = array_map(null, $array, $note_array, $time_array, $room_array);
        }
    }
}


$row_number = 1;
$old_tags = [];
$disc_arr = [];
$sn_arr = [];
$loc_arr = [];
$po_arr = [];
$cost_arr = [];
$dept_arr = [];

$cell_array = [];
$headers = ['Tag Number', 'Descr', 'Serial ID', 'Location', 'Dept', 'COST Total Cost', 'PO No.'];



$colors = ['lightblue', 'white'];
$empty = false;

$j = 0;
?>

<body>
    <div class="is-search">
        <div class="div-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Tags</th>
                        <th>Description</th>
                        <th>Serial ID</th>
                        <th>Location</th>
                        <th>Department</th>
                        <th>Cost</th>
                        <th>Purchase Order</th>
                    </tr>
                </thead>
                <tbody id="contentArea" class="clusterize-content" style="width:10vw;">
                    <?php
                    $max_rows = 6000;
                    $total_rows = count($data);
                    $j = 0;
                    $data = array_slice($data, $j, $max_rows);
                    foreach ($data as $index => $row) {
                        $color_class = ($j % 2 === 0) ? 'row-odd' : 'row-even';

                        $row_num = $j + 1;
                        $j++;
                        $tag = htmlspecialchars($row['asset_tag']);
                        $descr = htmlspecialchars($row['asset_name']) ?? "";
                        $match = in_array($row['asset_tag'], $array, true) ? "match-tag" : "miss-tag";
                        $serial = htmlspecialchars($row['serial_num']) ?? "";
                        $location = htmlspecialchars($row['room_loc']) ?? "";
                        $department = htmlspecialchars($row['dept_id']) ?? "";
                        $cost = htmlspecialchars($row['asset_price']) ?? "";
                        $po = htmlspecialchars($row['po']) ?? "";
                        $match = in_array($tag, $array) ? 'found' : 'not-found';
                        echo "<tr class='{$color_class}'>
    <td class='{$match}'> {$row_num}. </td>
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
                </tbody>
            </table>

            <?php
            foreach ($data as $index => $row) {
                if (!isset($row['asset_tag']) || $row['asset_tag'] === NULL && $row['asset_name'] === NULL) {
                    break;
                }
                $tag_array[] = $row['asset_tag'];
                $disc_arr[] = $row['asset_name'];
                $sn_arr[] = $row['serial_num'];
                $loc_arr[] = $row['room_loc'];
                $dept_arr[] = $row['dept_id'];
                $cost_arr[] = (round((float)$row['asset_price'], 2));
                $po_arr[] = $row['po'];
            }

            $i = 0;
            $tag_lookup = array_flip($tag_array);
            ?>
        </div>

        <div id="insert-tags-div">
            <label for="room-tag" class="room-label">Room Tag<br></label>
            <input type="text" name="room-tag" id="room-tag" placeholder="Scan room tag">
            <label for="inputContainer" class="room-label">Asset Tags<br></label>
            <div id="inputContainer"></div>
            <form id="dynamicForm" method='POST' action='db_audit.php' onLoad="addNewInput()" enctype="multipart/form-data">

                <input type="hidden" name="data" id="data">
                <button type="submit" id='dynamicSubmit'>Submit</button>

            </form>
        </div>

        <div class="wrapper">
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') { ?>
                <button type="submit" id="complete-audit" name="complete-audit">Complete Audit</button>
            <?php } ?>

            <div class='show-tags' id='showTags'>
                <h3>Tags Scanned</h3>
                <ul>
                    <?php
                    foreach ($array as $row) {
                        $colorClass = isset($tag_lookup[$row]) ? "found" : "not-found";
                        echo "<li >
        <strong class='$colorClass'>$row</strong><br> 
        Room:<input name='previousRms[]' value='" . htmlspecialchars($room_array[$i]) . "'><br>Notes:<input name='previousNote[]' value='" . htmlspecialchars($note_array[$i]) . "'></li>";
                        $i++;
                    }

                    echo "</ul>";
                    ?>
            </div>
            <div class='formId'>
                <form id="makeSheet" method='POST' action='db_audit.php' enctype="multipart/form-data">

                    <input type="hidden" name="download" id="download">

                    <button type='submit' id='create' name='create'>Export</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    $i = 0;

    foreach ($array as $value) {
        if ($value !== '') {
            echo "<input type='hidden' name='previousInputContainer[]' value='" . htmlspecialchars($value) . "'>";
            echo "<input type='hidden' name='previousTime[]' value='" . htmlspecialchars($time_array[$i]) . "'>";
            echo "<input type='hidden' name='previousNote[]' value='" . htmlspecialchars($note_array[$i]) . "'>";
            echo "<input type='hidden' name='previousRms[]' value='" . htmlspecialchars($room_array[$i]) . "'>";
        }
        $i++;
    }
    $column_headers = [];
    $i = 0;
    foreach ($column_headers as $header) {
        echo "<input type='hidden' name='headers[]' value='" . htmlspecialchars($header) . "'>";
    }
    echo "<input type='hidden' id='dept-to-save' name='dept-to-save' value='{$dept_arr[1]}'>";
    echo "<input type='hidden' id='filePath2' name='filePath' value='$filePath'>";

    ?>
    <script>
        var botmanWidget = {
        frameEndpoint: 'https://dataworks-7b7x.onrender.com/chat/botman-widget.html',
            chatServer: 'http://dataworks-7b7x.onrender.com/chat/chatbot.php',
            introMessage: "👋 Hello! I'm Chatbot. Ask me anything!",
            title: "Chatbot",
            mainColor: "#ADD8E6",
            bubbleBackground: "#ADD8E6",
            placeholderText: "Type your question here..."
        };

        window.requestIdleCallback(() => {
            addNewInput();
            addNewInput();
        });
        window.addEventListener('beforeunload', function(event) {
            event.preventDefault();
            event.returnValue = ''; // Some browsers require this for the dialog to show.
        });
        document.addEventListener("DOMContentLoaded", () => {
            const complete_audit_btn = document.getElementById('complete-audit');
            complete_audit_btn.addEventListener("click", (e) => {
                const previousInput = Array.from(document.getElementsByName('previousInputContainer[]')).map(i => i.value);
                const previousTime = Array.from(document.getElementsByName('previousTime[]')).map(i => i.value);
                const previousNote = Array.from(document.getElementsByName('previousNote[]')).map(i => i.value);
                const previousRm = Array.from(document.getElementsByName('previousRms[]')).map(i => i.value);
                const dept_to_save = document.getElementById('dept-to-save').value;
                tag = [];
                room = [];
                time = [];
                note = [];
                previousInput.forEach((value, index) => {
                    tag.push(value);
                    note.push(previousNote[index]);
                    time.push(previousTime[index]);
                    room.push(previousRm[index]);
                })

                const complete_data = [
                    tag.join('`'),
                    note.join('`'),
                    time.join('`'),
                    room.join('`'),
                    dept_to_save
                ].join('|');
                console.log(complete_data);
                url = "https://dataworks-7b7x.onrender.com/audit/complete/complete-api.php";
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(
                            complete_data
                        )
                    })
                    .then(res => res.json())
                    .then(res => console.log(res))
            })
        });

        //document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('makeSheet').addEventListener('submit', (e) => {
            e.preventDefault();
            const previousInputContainer = Array.from(document.getElementsByName('previousInputContainer[]')).map(i => i.value);
            const previousTime = Array.from(document.getElementsByName('previousTime[]')).map(i => i.value);
            const previousNote = Array.from(document.getElementsByName('previousNote[]')).map(i => i.value);
            const previousRm = Array.from(document.getElementsByName('previousRms[]')).map(i => i.value);



            const download = [
                previousInputContainer.join('`'),
                previousNote.join('`'),
                previousTime.join('`'),
                previousRm.join('`')
            ].join('|');
            document.querySelector('input[name="download"]').value = download;
            document.getElementById('makeSheet').submit();
        });

        document.getElementById('dynamicForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const dynamicInputs = Array.from(document.getElementsByName('dynamicInput[]')).map(i => i.value);
            const dynamicNotes = Array.from(document.getElementsByName('dynamicNote[]')).map(i => i.value);
            const dynamicTimes = Array.from(document.getElementsByName('dynamicTime[]')).map(i => i.value);
            const dynamicRms = Array.from(document.getElementsByName('dynamicRm[]')).map(i => i.value);
            const room_tag = document.getElementById('room-tag').value;
            const previousInputs = Array.from(document.getElementsByName('previousInputContainer[]')).map(i => i.value);
            const previousTimes = Array.from(document.getElementsByName('previousTime[]')).map(i => i.value);
            const previousNotes = Array.from(document.getElementsByName('previousNote[]')).map(i => i.value);
            const previousRms = Array.from(document.getElementsByName('previousRms[]')).map(i => i.value);

            let empty_tag = [];
            const filePath = document.getElementById('filePath2').value;
            dynamicInputs.forEach(function(tag, index) {
                dynamicNotes[index] = dynamicNotes[index] === '' ? 'No Notes' : dynamicNotes[index];
                dynamicRms[index] = room_tag === '' ? '000' : room_tag;
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
    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
    </div>
    </div>
</body>
