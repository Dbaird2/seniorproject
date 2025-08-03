<?php
include_once("../config.php");
check_auth();
if (!isset($_SESSION['data']) && !isset($_POST)) {
    header("Location: https://dataworks-7b7x.onrender.com/audit/upload.php");
    exit;
}
if (empty($_SESSION['info']) && empty($_SESSION['data'])) {
    header("Location: https://dataworks-7b7x.onrender.com/audit/upload.php?msg=NoRecentAudit");
    exit;
}


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$filePath = $count = NULL;

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$data = $_SESSION['data'];
$highest_row = (int)$_SESSION['info'][0];
$keys = array_keys($_SESSION['data'][0]);

$file_path = $_SESSION['info'][2];
$file_name = $_SESSION['info'][4];
if (isset($_POST['download'])) {
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1', 'P1', 'Q1', 'R1', 'S1'];
        $row_index = 2;


        $fileNameOnly = basename($file_path);
        $filePath = $fileNameOnly;

        $filePath = str_replace(".xlsx", "_AUDIT", $filePath);
        $filePath = str_replace(".xls", "_AUDIT", $filePath);
        $filePath = $filePath . ".xlsx";
        foreach ($keys as $index => $key) {
            $sheet->setCellValue($column_letters[$index], $key);
        }

        $sheet->fromArray($_SESSION['data'], NULL, 'A2');

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
    <?php include_once("../navbar.php"); ?>
    <style>
    </style>
    <link rel="stylesheet" href="auditing.css" media="all">
    <title>Asset Management Excel</title>
</head>
<?php
$worksheet = NULL;
$array = $time_array = $note_array = $room_array = [];
$total_count = count($_SESSION['data']);
if (isset($_POST['data']) && isset($_POST['dynamicInput']) && ($_POST['dynamicInput'][0] !== '' || $_POST['dynamicInput'][1] !== '')) {
    foreach ($_POST['dynamicInput'] as $index => $row) {
        if ($row === '') {
            continue;
        }
        $scanned_tags[] = $row;
        $scanned_notes[] = 'None';
        $scanned_times[] = $_POST['dynamicTime'][$index];
        $scanned_rooms[] = $_POST['room-tag'];
    }



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
            foreach ($array as $index => $tag) {
                $found = 0;
                $in_session = 0;

                foreach ($_SESSION['data'] as $data_index => $row) {
                    if ($tag === $row["Tag Number"] && $row["Tag Status"] !== 'Extra') {
                        $_SESSION['data'][$data_index]['Tag Status'] = 'Found';
                        $_SESSION['data'][$data_index]['Found Room Tag'] = $room_array[$index];
                        $_SESSION['data'][$data_index]['Found Note'] = $note_array[$index];
                        $_SESSION['data'][$data_index]['Found Timestamp'] = $time_array[$index];
                        $found = 1;
                    } else if ($tag === $row["Tag Number"]) {
                        $found = 1;
                    }
                }
                if ($found === 0) {
                    $select_q = "SELECT a.asset_name, a.serial_num, r.room_loc, b.bldg_id, a.dept_id, a.asset_price, a.po, a.bus_unit, d.custodian, a.date_added
                        FROM asset_info AS a JOIN room_table AS r ON r.room_tag = a.room_tag JOIN bldg_table AS b ON b.bldg_id = r.bldg_id
                        WHERE a.asset_tag = :tag";
                    $select_stmt = $dbh->prepare($select_q);
                    $select_stmt->execute([":tag" => $tag]);
                    $result = $select_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $_SESSION['data'][$total_count]["Unit"] =  $result['bus_unit'];
                        $_SESSION['data'][$total_count]["Tag Number"] = $tag;
                        $_SESSION['data'][$total_count]["Descr"] = $result['asset_name'];
                        $_SESSION['data'][$total_count]["Serial ID"] = $result['serial_num'];
                        $_SESSION['data'][$total_count]["Location"] =  $result['bldg_id'] . '-' . $result['room_loc'];
                        $_SESSION['data'][$total_count]["VIN"] =  '';
                        $_SESSION['data'][$total_count]["Custodian"] =  /*trim($result['custodian'], '{}')*/'';
                        $_SESSION['data'][$total_count]["Dept"] = $result['dept_id'];
                        $_SESSION['data'][$total_count]["PO No."] =  $result['po'];
                        $_SESSION['data'][$total_count]["Acq Date"] =  $result['date_added'];
                        $_SESSION['data'][$total_count]["COST Total Cost"] =  $result['asset_price'];
                    } else {
                        $_SESSION['data'][$total_count]["Unit"] =  '';
                        $_SESSION['data'][$total_count]["Tag Number"] = $tag;
                        $_SESSION['data'][$total_count]["Descr"] = '';
                        $_SESSION['data'][$total_count]["Serial ID"] = '';
                        $_SESSION['data'][$total_count]["Location"] =  '';
                        $_SESSION['data'][$total_count]["VIN"] =  '';
                        $_SESSION['data'][$total_count]["Custodian"] =  '';
                        $_SESSION['data'][$total_count]["Dept"] =  '';
                        $_SESSION['data'][$total_count]["PO No."] =  '';
                        $_SESSION['data'][$total_count]["Acq Date"] =  '';
                        $_SESSION['data'][$total_count]["COST Total Cost"] =  '';
                    }
                    $_SESSION['data'][$total_count]['Tag Status'] = 'Extra';
                    $_SESSION['data'][$total_count]['Found Room Tag'] = $room_array[$index];
                    $_SESSION['data'][$total_count]['Found Note'] = $note_array[$index];
                    $_SESSION['data'][$total_count]['Found Timestamp'] = $time_array[$index];
                    $total_count++;
                }
            }
        }
    }
}
?>

<body>
    <div class='formId'>
        <form id="makeSheet" method='POST' action='auditing.php' enctype="multipart/form-data">

            <input type="hidden" name="download" id="download">

            <button type='submit' id='create' name='create'>Export</button>
        </form>
        <div class="wrapper">
            <?php if ($_SESSION['role'] === 'admin') { ?>
                <button type="submit" id="complete-audit" name="complete-audit">Complete Audit</button>
            <?php } ?>
        </div>
    </div>
    <div class="is-search">
        <div class="div-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Tags</th>
                        <th>Found</th>
                        <th>Description</th>
                        <th>Serial ID</th>
                        <th>Location</th>
                        <th>Department</th>
                        <th>Cost</th>
                        <th>Purchase Order</th>
                        <th>Room Tag</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="contentArea" class="clusterize-content" style="width:10vw;">
<?php
$max_rows = 5000;
$total_rows = count($data);
$j = 1;
$data = array_slice($data, $j, $max_rows);
$i = 0;
foreach ($_SESSION['data'] as $index => $row) {
    $color_class = ($i % 2 === 0) ? 'row-odd' : 'row-even';
    $i++;
    $j = $index + 1;

    $file_name = $data[0]["Dept"] ?? $file_name;
    $tag = htmlspecialchars($row["Tag Number"]);
    $descr = htmlspecialchars($row["Descr"] ?? "");
    $match = (isset($row['Tag Status']) && $row['Tag Status'] === 'Found') ? "found" : "not-found";
    $match = (isset($row['Tag Status']) && $row['Tag Status'] === 'Extra') ? "extra" : $match;

    $found_tag = (isset($row['Tag Status']) && $row['Tag Status'] !== '') ? "X" : "";
    $found_room = isset($row["Found Room Tag"]) ? $row["Found Room Tag"] : "";
    $found_note = isset($row["Found Note"]) ? $row["Found Note"] : "";
    $serial = htmlspecialchars($row["Serial ID"] ?? "");
    $location = htmlspecialchars($row["Location"] ?? "");
    $department = htmlspecialchars($row["Dept"] ?? "");
    $cost = htmlspecialchars($row["COST Total Cost"] ?? "");
    $po = htmlspecialchars($row["PO No."] ?? "");
    echo "<tr class='{$color_class}'>
        <td class='{$match}'> {$j}. </td>
        <td class='{$match}'> {$tag}</td>
        <td class='{$match}'>{$found_tag}</td>
        <td class='{$match}'>{$descr}</td>
        <td>{$serial}</td>
        <td>{$location}</td>
        <td>{$department}</td>
        <td>{$cost}</td>
        <td>{$po}</td>
        <td><input class='room' name='previousRms[]' id='{$tag}' value='" . htmlspecialchars($found_room) . "'></td>
        <td><input class='note' name='previousNote[]' id='{$tag}' value='" . htmlspecialchars($found_note) . "'></td>
        </tr>";
}
?>
                </tbody>
            </table>
        </div>

        <div id="insert-tags-div">
            <form id="dynamicForm" method='POST' action='auditing.php' onLoad="addNewInput()" enctype="multipart/form-data">
                <label for="room-tag" class="room-label">Room Tag<br></label>
                <input type="text" name="room-tag" id="room-tag" placeholder="Scan room tag">
                <label for="inputContainer" class="room-label">Asset Tags<br></label>
                <div id="inputContainer"></div>

                <input type="hidden" name="data" id="data">
                <button type="submit" id='dynamicSubmit'>Submit</button>

            </form>
        </div>
    </div>
<script>
var botmanWidget = {
frameEndpoint: 'https://dataworks-7b7x.onrender.com/chat/botman-widget.html',
    chatServer: 'https://dataworks-7b7x.onrender.com/chat/chatbot.php',
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
document.addEventListener("DOMContentLoaded", () => {
const complete_audit_btn = document.getElementById('complete-audit');
complete_audit_btn.addEventListener("click", (e) => {
url = "https://dataworks-7b7x.onrender.com/audit/complete/complete_api.php";
fetch(url, {
method: 'POST',
    headers: {
    'Content-Type': 'application/json'
}
})
    .then(res => res.json())
    .then(res => console.log(res))
})
});


const room = document.querySelectorAll('.room');
room.forEach(element => {
element.addEventListener('change', function(e) {

    const params = new URLSearchParams({
    tag: this.id,
        room: this.value
});
url = "http://localhost:3000/audit/save-data.php";
fetch(url, {
method: 'POST',
    body: params,
    headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
}

})
    .then(res => res.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
});
});

const note = document.querySelectorAll('.note');
note.forEach(element => {
element.addEventListener('change', function(e) {
    const params = new URLSearchParams({
    tag: this.id,
        note: this.value
});
url = "http://localhost:3000/audit/save-data.php";
fetch(url, {
method: 'POST',
    body: params,
    headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
}

})
    .then(res => res.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
});
})

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
</script>
<script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
                        </div>
                            </body>
