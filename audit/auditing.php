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

$highest_row = (int)$_SESSION['info'][0];
$key_index = -1;
$key_found = true;
while (!isset($_SESSION['data'][$key_index])) {
    $key_index++;
    if ($key_index >= 50) {
        $key_found = false;
        break;
    }
}
if ($key_found === false) {
    header("Location: https://dataworks-7b7x.onrender.com/audit/upload.php?error=key_fail");
    exit;
}
$keys = array_keys($_SESSION['data'][$key_index]);

$file_path = $_SESSION['info'][2];
$file_name = $_SESSION['info'][4];
if (isset($_POST['create'])) {
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1', 'L1', 'M1', 'N1', 'O1', 'P1', 'Q1', 'R1', 'S1', 'T1', 'U1', 'V1', 'W1', 'X1', 'Y1', 'Z1', 'AA1', 'AB1'];
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
<title>Auditing <?= $_SESSION['info'][2] ?></title>
    <?php include_once("../navbar.php"); ?>
    <style>
    </style>
    <link rel="stylesheet" href="auditing.css" media="all">
</head>
<?php
$worksheet = NULL;
$array = $time_array = $note_array = $room_array = [];
$total_count = count($_SESSION['data']);
if (isset($_POST['data']) && isset($_POST['dynamicInput']) && ($_POST['dynamicInput'][0] !== '' || $_POST['dynamicInput'][1] !== '')) {
    foreach ($_POST['dynamicInput'] as $index => $row) {
        $found = false;
        $found_at = -2;
        if ($row === '') {
            continue;
        }
        foreach ($_SESSION['data'] as $sesh_index => $sesh_row) {
            if ($sesh_row["Tag Number"] === $row) {
                $found = true;
                $found_at = $sesh_index;
                break;
            }
        }
        $scanned_tags[] = $row;
        $scanned_notes[] = ($found === true && $_SESSION['data'][$found_at]['Found Note'] !== '') ? $_SESSION['data'][$found_at]['Found Note'] : '';
        $scanned_times[] = ($found === true && $_SESSION['data'][$found_at]['Found Timestamp'] !== '') ? $_SESSION['data'][$found_at]['Found Timestamp'] : $_POST['dynamicTime'][$index];
        $scanned_rooms[] = ($found === true && $_SESSION['data'][$found_at]['Found Room Tag'] !== '') ? $_SESSION['data'][$found_at]['Found Room Tag'] : $_POST['room-tag'];
        $scanned_room_nums[] = ($found === true && $_SESSION['data'][$found_at]['Found Room Number'] !== '') ? $_SESSION['data'][$found_at]['Found Room Number'] : $_POST['room-number'];
        $scanned_bldg[] = ($found === true && $_SESSION['data'][$found_at]['Found Building Name'] !== '') ? $_SESSION['data'][$found_at]['Found Building Name'] : $_POST['bldg-name'];
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
            $filtered_room_nums[] = $scanned_room_nums[$i];
            $filtered_bldg[] = $scanned_bldg[$i];
        }
    }
    unset($scanned_tags);
    unset($scanned_notes);
    unset($scanned_times);
    unset($scanned_rooms);
    unset($scanned_room_nums);
    unset($scanned_bldg);
    if (count($filtered_tags) > 0) {
        $array = $filtered_tags;
        $note_array = $filtered_notes;
        $time_array = $filtered_times;
        $room_array = $filtered_rooms;
        $room_num_array = $filtered_room_nums;
        $bldg_array = $filtered_bldg;
        unset($filtered_tags);
        unset($filtered_notes);
        unset($filtered_times);
        unset($filtered_rooms);
        unset($filtered_room_nums);
        unset($filtered_bldg);
        if (count($array) > 0) {
            foreach ($array as $index => $tag) {
                $found = 0;
                $in_session = 0;

                foreach ($_SESSION['data'] as $data_index => $row) {
                    if ($tag === $row["Tag Number"] && $row["Tag Status"] !== 'Extra') {
                        $_SESSION['data'][$data_index]['Tag Status'] = 'Found';
                        $_SESSION['data'][$data_index]['Found Room Tag'] = $room_array[$index];
                        $_SESSION['data'][$data_index]['Found Room Number'] = $room_num_array[$index];
                        $_SESSION['data'][$data_index]['Found Building Name'] = $bldg_array[$index];
                        $_SESSION['data'][$data_index]['Found Note'] = $note_array[$index];
                        $_SESSION['data'][$data_index]['Found Timestamp'] = $time_array[$index];
                        $found = 1;
                    } else if ($tag === $row["Tag Number"]) {
                        $found = 1;
                    }
                }
                if ($found === 0) {
                    $select_q = "SELECT a.asset_name, a.serial_num, r.room_loc, b.bldg_id, a.dept_id, a.asset_price, a.po, a.bus_unit, d.custodian, a.date_added
                        FROM asset_info AS a LEFT JOIN room_table AS r ON r.room_tag = a.room_tag 
                        LEFT JOIN bldg_table AS b ON b.bldg_id = r.bldg_id 
                        LEFT JOIN department AS d ON a.dept_id = d.dept_id
                        WHERE a.asset_tag = :tag";
                    $select_stmt = $dbh->prepare($select_q);
                    $select_stmt->execute([":tag" => $tag]);
                    $result = $select_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $_SESSION['data'][$total_count]["Unit"] =  $result['bus_unit'];
                        $_SESSION['data'][$total_count]["Tag Number"] = $tag;
                        $_SESSION['data'][$total_count]['Tag Status'] = 'Extra';
                        $_SESSION['data'][$total_count]['Found Room Tag'] = $room_array[$index];
                        $_SESSION['data'][$total_count]['Found Room Number'] = $room_num_array[$index];
                        $_SESSION['data'][$total_count]['Found Building Name'] = $bldg_array[$index];
                        $_SESSION['data'][$total_count]['Found Note'] = $note_array[$index];
                        $_SESSION['data'][$total_count]['Found Timestamp'] = $time_array[$index];
                        $_SESSION['data'][$total_count]["Descr"] = $result['asset_name'];
                        $_SESSION['data'][$total_count]["Serial ID"] = $result['serial_num'];
                        $_SESSION['data'][$total_count]["Location"] =  $result['bldg_id'] . '-' . $result['room_loc'];
                        $_SESSION['data'][$total_count]["VIN"] =  '';
                        $_SESSION['data'][$total_count]["Custodian"] =  trim(trim($result['custodian'], '{}'), '"');
                        $_SESSION['data'][$total_count]["Dept"] = $result['dept_id'];
                        $_SESSION['data'][$total_count]["PO No."] =  $result['po'];
                        $_SESSION['data'][$total_count]["Acq Date"] =  $result['date_added'];
                        $_SESSION['data'][$total_count]["COST Total Cost"] =  $result['asset_price'];
                    } else {
                        $_SESSION['data'][$total_count]["Unit"] =  '';
                        $_SESSION['data'][$total_count]["Tag Number"] = $tag;
                        $_SESSION['data'][$total_count]['Tag Status'] = 'Extra';
                        $_SESSION['data'][$total_count]['Found Room Tag'] = $room_array[$index];
                        $_SESSION['data'][$total_count]['Found Room Number'] = $room_num_array[$index];
                        $_SESSION['data'][$total_count]['Found Building Name'] = $bldg_array[$index];
                        $_SESSION['data'][$total_count]['Found Note'] = $note_array[$index];
                        $_SESSION['data'][$total_count]['Found Timestamp'] = $time_array[$index];
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
                    $total_count++;
                }
            }
        }
    }
}
$select_bldgs = "SELECT * FROM bldg_table";
$select_stmt = $dbh->query($select_bldgs);
$bldgs = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body>
<div id="snackbar"></div>
    <div class=" is-search">
        <div class="wrapper">

            <button type="submit" id="complete-audit" name="complete-audit">Save Audit</button>
            <form id="makeSheet" method='POST' action='auditing.php' enctype="multipart/form-data">
                <button type='submit' id='create' name='create'>Export</button>
            </form>
            <h4 class="gradient-text">Auditing</h4>
        </div>
        <label class="switch">
            <input id="scanner-mode" type="checkbox" checked />
            <span class="slider"></span>
        </label>
        <div id="insert-tags-div">
            <form id="dynamicForm" method='POST' action='auditing.php' onLoad="addNewInput()" enctype="multipart/form-data">
                <input type="text" name="room-tag" id="room-tag" placeholder="Scan room tag" required>
                <input type="search" list="bldg-names" name="bldg-name" id="bldg-name" required>
                <datalist id="bldg-names">
                    <?php foreach($bldgs as $bldg) { ?>
                        <option value="<?= $bldg['bldg_name'] ?>">
                    <?php } ?>
                </datalist>
                <input type="text" name="room-number" id="room-number" placeholder="Room Number" required>
                <div id="inputContainer"></div>

                <input type="hidden" name="data" id="data">
                <button type="submit" id='dynamicSubmit'>Submit</button>

            </form>
        </div>


        <div class="filter-seciton">
            <input type="text" id="my-input" onchange="filterTable()" placeholder="Search for tags.." accesskey="c">
            <select type="text" id="my-status" onchange="filterAssetStatus()" placeholder="Search for tags.." accesskey="c">
                <option value="All">All</option>
                <option value="X">Found</option>
                <option value="O">Not Found</option>
            </select>
        </div>
        <div class="div-table">

            <table class="table">
                <thead>
                    <tr>
                        <th></th>
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
$data = $_SESSION['data'];
$max_rows = 300;
$total_rows = count($data);
$j = 1;
$data_slice = array_slice($data, 0, $max_rows);
$i = 0;

foreach ($data_slice as $index => $row) {
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
    $encoded_tag = json_encode($tag);
    echo "<tr class='{$color_class}'>
        <td><button onclick='deleteAsset({$encoded_tag});' name='delete'  class='delete' id='{$tag}' value='" . htmlspecialchars($tag) . "'>&#215;</button></td>
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


    </div>
<script>
const session_data = <?= json_encode(array_slice($_SESSION['data'], 300)) ?>;
let index = 0;
let offset = 0
    const chunk_size = 300;

function loadMoreRows() {

    const table = document.querySelector(".table");
    for (let i = offset; i < chunk_size && index < session_data.length; i++, index++) {
        color_class = (i % 2 === 0) ? 'row-odd' : 'row-even';

        const row = session_data[index];
        const tr = document.createElement("tr");
        tr.classList.add(color_class);
        match = (row['Tag Status'] !== 'undefined' && row['Tag Status'] === 'Found') ? "found" : "not-found";
        match = (row['Tag Status'] !== 'undefined' && row['Tag Status'] === 'Extra') ? "extra" : match;
        tr.innerHTML = `
            <td><button onclick="deleteAsset(${row["Tag Number"]})" class='delete' id=${row["Tag Number"]} value=${row["Tag Number"]} name='delete'>&#215;</button></td>
            <td class=${match}>${300 + index + 1}</td>
                <td class=${match}>${row["Tag Number"]}</td>
                <td class=${match}>${row["Tag Status"]}</td>
                <td class=${match}>${row["Descr"]}</td>
                <td>${row["Serial ID"]}</td>
                <td>${row["Location"]}</td>
                <td>${row["Dept"]}</td>
                <td>${row["COST Total Cost"]}</td>
                <td>${row["PO"] ?? ''}</td>
                <td><input class='room' name='previousRms[]' id=${row["Tag Number"]} value=${row["Found Room Tag"]}></td>
                <td><input class='note' name='previousNote[]' id=${row["Tag Number"]} value=${row["Found Note"]}></td>
                `;
        table.appendChild(tr);
    }

}
const loops = Math.ceil(session_data.length / 300);
console.log(session_data.length, loops);
for (let i = 0; i < loops; i++) {
    setTimeout(loadMoreRows, 1000);
}


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
    complete_audit_btn.addEventListener("click", async () => {
        let audited_with = prompt("Did anyone help with the audit?");
        audited_with = audited_with.trim();
        url = "https://dataworks-7b7x.onrender.com/audit/complete/complete_api.php";
        const res = await fetch(url, {
            method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify ({ audited_with })
            });
            if (!res.ok) {
                const text = await res.text();
                throw new Error (`HTTP ${res.status}: ${text}`);
            } else {
                console.log(res);
            }
        })
        const room_tag = document.getElementById("room-tag")
        room_tag.addEventListener("focusout", async () {
            url = "https://dataworks-7b7x.onrender.com/audit/get-bldg-info.php";
            const res2 = await fetch(url, {
            method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify ({ room_tag: room_tag })
            });
            if (!res.ok) {
                const text = await res.text();
                throw new Error (`HTTP ${res.status}: ${text}`);
            } else {
                console.log(res);
                const data = await res.json();
                if (data['bldg_name'] !== '' || data['bldg_name'] !== null) {
                    const room_number = document.getElementById('room-number');
                    room_number.value = data['room_number'];
                    const bldg_name = document.getElementById('bldg-name');
                    bldg_name.value = data['bldg_name'];
                } else {
                    toast("Room Tag Not Found in Database");
                }
            }
        });
});
function deleteAsset(tag) {
    const params = new URLSearchParams({
    tag: tag
});
url = "https://dataworks-7b7x.onrender.com/audit/delete-asset.php";
const response = confirm("Are you sure you want to delete this asset");
if (response) {
    fetch(url, {
    method: 'POST', 
        body: params,
        headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
}
})
    .then(res => res.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error ', error));
    //location.reload();
    setTimeout(() => {
    window.location.href = 'https://dataworks-7b7x.onrender.com/audit/auditing.php';
    }, 500);
} else {
    console.log("User declined");
}
};
document.querySelector('.table').addEventListener('change', function(e) {
    if (e.target.classList.contains('room')) {
        const params = new URLSearchParams({
        tag: e.target.id,
            room: e.target.value
    });
        url = "https://dataworks-7b7x.onrender.com/audit/save-data.php";
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
    } else {
        const params = new URLSearchParams({
        tag: e.target.id,
            note: e.target.value
    });
        url = "https://dataworks-7b7x.onrender.com/audit/save-data.php";
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
    }

});

document.addEventListener("input", function(e) {
    const scanner_mode = document.getElementById('scanner-mode').checked;
    if (!e.target.classList.contains("dynamicId") || scanner_mode === false) return;
    const value = e.target.value.trim();
    let tab = 5;
    if (value[0] === 'S' || value[0] === 'A' || value[0] === 'F') {
        tab++;
        if (value[1] === 'P' || value[1] === 'R') {
            tab++;
        } else if (value[1] === 'T') {
            tab++;
        }
        if (value[2] == 'U' || value[2] === 'C') {
            tab++;
        }
    }
    if (value.length >= tab) {
        const inputs = Array.from(document.querySelectorAll(".dynamicId"));
        const index = inputs.indexOf(e.target);
        if (index > -1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        } else {
            e.target.blur();
        }
    }
});

function addNewInput() {
    const inputDiv = document.createElement('div');
    inputDiv.classList.add('input-container');

    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'dynamicInput[]';
    newInput.autocomplete = "off";
    newInput.placeholder = 'Enter tag';
    newInput.setAttribute("accesskey", "s");
    newInput.classList.add('dynamicId');


    newInput.addEventListener("change", addNewInput, false);
    inputDiv.appendChild(newInput);

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

function filterTable() {
    var input, filter, table, tr, td, i, txt_value;
    input = document.getElementById("my-input");
    filter = input.value.toUpperCase();
    table = document.querySelector(".table");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[2];
        if (td) {
            txt_value = td.textContent || td.innerText;
            if (txt_value.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function filterAssetStatus() {
    var input, filter, table, tr, td, i, txt_value;
    input = document.getElementById("my-status");
    filter = input.value.toUpperCase();
    table = document.querySelector(".table");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[3];
        if (td && filter === 'X') {
            txt_value = td.textContent || td.innerText;
            if (txt_value.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        } else if (filter === 'ALL') {
            tr[i].style.display = "";
        } else if (td && filter === 'O') {
            var filter2 = 'X';
            txt_value = td.textContent || td.innerText;
            if (txt_value.toUpperCase().indexOf(filter2) > -1) {
                tr[i].style.display = "none";
            } else {
                tr[i].style.display = "";
            }
        }
    }
}
function toast(message) {
  var x = document.getElementById("snackbar");
  x.className = "show";
  x.textContent = message;
  setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}
      </script>
      <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
    </div>
</body>
