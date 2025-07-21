<?php
error_reporting(0);
include_once("../config.php");
check_auth('high');
$bldg_msg = $bldg_color = '';
$room_msg = [[]];
if (isset($_GET['bldg-id'])) {
    $bldg_name = isset($_GET['bldg-name']) ? trim(strtoupper($_GET['bldg-name'])) : exit('Missing building name.');
    $bldg_id = (int)$_GET['bldg-id'];
    $check_bldg_name = "SELECT bldg_name, bldg_id from bldg_table where bldg_name = :bldg_name OR bldg_id = :bldg_id";

    $statement = $dbh->prepare($check_bldg_name);
    $statement->execute([":bldg_name"=>$bldg_name, ":bldg_id"=>$bldg_id]);
    $already_in_db = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$already_in_db) {
        $insert_bldg = "INSERT INTO bldg_table (bldg_name, bldg_id) VALUES (?, ?)";
        $statement = $dbh->prepare($insert_bldg);
        $statement->execute([$bldg_name, $bldg_id]);
        $bldg_msg = "Building: " . $bldg_id . " ". $bldg_name . " inserted into database";
        $bldg_color = 'green';

    } else {
        $bldg_msg = "Building with id of " . $bldg_id . " or name of " .$bldg_name . " already exists";
        $bldg_color = 'red';
    }

}
if (isset($_GET['room-num'])) {
    $bldg_name = isset($_GET['bldg-name']) ? trim(strtoupper($_GET['bldg-name'])) : exit('Missing building name.');
    $bldg_id = (int)$_GET['bldg-id2'];
    $room_nums = trim($_GET['room-num']);

    $seen = [];
    $new_room_nums = [];
    foreach ($room_nums as $index=>$room) {
        if (!isset($seen[$room]) && $room !== '') {
            $seen[$room] = true;
            $new_room_nums[] = $room;
        }
    }
    $check_room_avail = "SELECT * FROM room_table WHERE room_loc = :room_loc AND bldg_id = :bldg_id";
    $insert_room = "INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)";
    try {
        foreach ($new_room_nums as $index=>$room) {

            $room_stmt = $dbh->prepare($check_room_avail);
            $room_stmt->execute([":room_loc"=>$room, ":bldg_id"=>$bldg_id]);
            $room_check = $room_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$room_check) {
                $insert_stmt = $dbh->prepare($insert_room);
                $insert_stmt->execute([$room, $bldg_id]);
                
                $room_msg[$index][0] = 'green';
                $room_msg[$index][1] = "Room number: " . $room . " successfully added."; 
                
            } else {
                $room_msg[$index][0] = 'red';
                $room_msg[$index][1] = "Room Number: " . $room . " failed to add.";
             }
                
          
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

include_once("../navbar.php");


$select = "SELECT * FROM bldg_table ORDER BY bldg_name";
$stmt = $dbh->prepare($select);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<link rel="stylesheet" href="bldg.css">
<body>
<div class="is-bldg">
    <div class="sub-body">
        <div class="form1">
            <h2 class='form-header'>Building Addition Form</h2>
            <form action="add-bldg.php" id="add-bldg" method="get">
                <div class="form-group">
                <label class="form-label" for="bldg-id">Building Number<br></label>
                <input class="form-input" name="bldg-id" type="number" required>
                </div>

                <div class="form-group">
                <label class="form-label" for="bldg-name">Building Name<br></label>
                <input class="form-input" name="bldg-name" type="text" required>
                </div>
                <button class="submit-btn" type="submit" id="submit1">Submit</button>
                <?php echo "<br><p style='color:$bldg_color;'>" . $bldg_msg . "<br>";?>
            </form>
        </div>



        <div class="form2">
            <div class="form-header">
            <h2 >Room Addition Form</h2>
            </div>
            <form action="add-bldg.php" id="add-room" method="get">
                <div class="form-group">
                <label class="form-label" for="bldg-name">Building Name<br></label>
                <select class="form-input" name="bldg-name" id="bldg-name2" required>
<?php foreach ($result as $row) {
$bldg = htmlspecialchars($row['bldg_name'], ENT_QUOTES, 'UTF-8');
echo "<option value='$bldg'>".$bldg."</option>";
                }
?>
            </select>
            </div>

            <div class="form-group">
            <label class="form-label" for="bldg-id2">Building ID<br></label>
            <input class="form-input" type="number" id="bldg-id2" name="bldg-id2" readonly>
            </div>


            <div class="form-group">
            <label class="form-label" for="room-num">Room Number<br></label>
                <input class="form-input" type="text" name="room-num[]" id="room-num" placeholder="Enter Room Number" required>
            </div>

            <div id="extra-rooms"></div>
            <button class="submit-btn" type="submit" id="submit2">Submit</button>
            </form>
            <button class="submit-btn" onclick="addNewRoom()">Add Another Room</button>

        <?php 
$color = '';
    foreach ($room_msg as $msg) {
        $color = $msg[0];
        echo "<p style='color:$color;text-align:left;'>" . $msg[1] . "</p>";
    }
        ?>
        </div>
    </div>
</div>
<script>
result = <?php echo json_encode($result); ?>;
document.addEventListener("DOMContentLoaded", (e) => {
    const bldg_id = document.getElementById("bldg-id2");
    const option_val = document.getElementById("bldg-name2");
    option_val.addEventListener("change", function() {
        result.forEach((item,index) => {
            if (item['bldg_name'] == option_val.value) {
                bldg_id.value = result[index]['bldg_id'];
                return true;
            }
        });
    });
    result.forEach((item,index) => {
        if (item['bldg_name'] == option_val.value) {
            bldg_id.value = result[index]['bldg_id'];
        }
    });
});

function addNewRoom() {
    const room_form = document.getElementById('extra-rooms');
    const br = document.createElement('br');
    const div = document.createElement("div");
    const new_room = document.createElement("input");
    new_room.setAttribute('type', 'text');
    new_room.setAttribute('name', 'room-num[]');
    new_room.setAttribute('placeholder', 'Enter Room Number');
    new_room.classList.add("form-input");
    div.appendChild(new_room);
    div.appendChild(br);
    room_form.appendChild(div);

}
</script>
</body>
