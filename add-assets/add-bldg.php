<?php
include_once("../config.php");
if ($_SESSION['role'] !=='admin' || !isset($_SESSION['role'])) {
    header("Location: https://dataworks-7b7x.onrender.com");
    exit;
}

$bldg_msg = '';
$room_msg = [[]];
if (isset($_GET['bldg-id'])) {
    $bldg_name = strtoupper($_GET['bldg-name']) ?? "Something went wrong";
    $bldg_id = (int)$_GET['bldg-id'];
    $check_bldg_name = "SELECT bldg_name, bldg_id from bldg_table where bldg_name = :bldg_name OR bldg_id = :bldg_id";
    $statement = $dbh->prepare($check_bldg_name);
    $statement->execute([":bldg_name"=>$bldg_name, ":bldg_id"=>$bldg_id]);
    $already_in_db = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$already_in_db) {
        $insert_bldg = "INSERT INTO bldg_table (bldg_name, bldg_id) VALUES (?, ?)";
        $statement = $dbh->prepare($insert_bldg);
        $statemen->execute([":bldg_name"=>$bldg_name, ":bldg_id"=>$bldg_id]);
        $bldg_msg = "Building: " . $bldg_id . " ". $bldg_name . " inserted into database";

    } else {
        $bldg_msg = "Building with id of " . $bldg_id . " or name of " .$bldg_name . " already exists";
    }
}
if (isset($_GET['room-tag'])) {
    $bldg_name = strtoupper($_GET['bldg-name']) ?? "Soemthing went wrong";
    $bldg_id = (int)$_GET['bldg-id2'];
    $room_nums = $_GET['room-num'];
    $room_tags = $_GET['room-tag'];
    $merge_array = array_merge($room_tags, $room_nums);

    $seen = [];
    $new_room_tags = [];
    $new_room_nums = [];
    foreach ($room_tags as $index=>$tag) {
        if (!isset($seen[$tag]) && !isset($seen[$room_nums[$index]]) && $tag !== '' && $room_nums[$index] !== '') {
            $seen[$tag] = true;
            $seen[$room_nums[$index]] = true;
            $new_room_tags[] = $tag;
            $new_room_nums[] = $room_nums[$index];
        }
    }

    $check_room_avail = "SELECT * FROM room_table WHERE room_loc = :room_loc OR room_tag = :room_tag WHERE bldg_id = :bldg_id";
    $insert_room = "INSERT INTO room_table SET room_loc = :room_loc, room_tag = :room_tag WHERE bldg_id = :bldg_id";
    try {
        foreach ($new_room_nums as $index=>$room) {
            $room_stmt = $dbh->prepare($check_room_avail);
            $room_stmt->execute([":room_loc"=>$room, ":room_tag"=>$room_tags[$index], ":bldg_id"=>$bldg_id]);
            $room_check = $room_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$room_check) {
                $insert_stmt = $dbh->prepare($insert_room);
                $insert_stmt->execute([":room_loc"=>$room, ":room_tag"=>$room_tags[$index], ":bldg_id"=>$bldg_id]);
                
                $room_msg[$index][0] = 'green';
                $room_msg[$index][1] = "Room number: " . $room . " room tag: " . $new_room_tags[$index] . " successfully added."; 
                
            } else {
                $room_msg[$index][0] = 'red';
                $room_msg[$index][1] = "Room Number: " . $room . " room tag: " . $new_room_tags[$index] . " failed to add.";
             }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

include_once("../navbar.php");


$select = "SELECT * FROM bldg_table";
$stmt = $dbh->prepare($select);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<link rel="stylesheet" href="bldg.css">
<div class="body">
    <div class="sub-body">
        <div class="form1">
            <h2 class='form-header'>Building Addition Form</h2>
            <form action="add-bldg.php" id="add-bldg" method="get">
                <div class="form-group">
                <label class="form-label" for="bldg-id">Building ID<br></label>
                <input class="form-input" name="bldg-id" type="number" required>
                </div>

                <div class="form-group">
                <label class="form-label" for="bldg-name">Building Name<br></label>
                <input class="form-input" name="bldg-name" type="text" required>
                </div>
                <button class="submit-btn" type="submit" id="submit1">Submit</button>
                <?php echo "<br><p style='color:green;'>" . $bldg_msg . "<br>";?>
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
$bldg = $row['bldg_name'];
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

            <div class="form-group">
                <label class="form-label" for="room-tag">Room Tag Number<br></label>
                <input class="form-input" type="number" name="room-tag[]" id="room-tag" placeholder="Enter Room Tag Number" required>
            </div>
            <div id="extra-rooms"></div>
            <button class="submit-btn" type="submit" id="submit2">Submit</button>
            </form>
            <button class="submit-btn" onclick="addNewRoom()">Add Another Room</button>

        <?php 
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
    const br2 = document.createElement('br');

    const div = document.createElement("div");
    const label1 = document.createElement("label");
    label1.textContent = "Room Number";
    label1.classList.add("form-label");
    div.appendChild(label1);
    //div.appendChild(br);

    const new_room = document.createElement("input");
    new_room.setAttribute('type', 'text');
    new_room.setAttribute('name', 'room-num[]');
    new_room.setAttribute('placeholder', 'Enter Room Number');
    new_room.classList.add("form-input");
    div.appendChild(new_room);
    room_form.appendChild(div);

    const div2 = document.createElement("div");
    const label2 = document.createElement("label");
    label2.textContent = "Room Tag Number";
    label2.classList.add("form-label");
    //div2.appendChild(br2);
    div2.appendChild(label2);

    const new_room_tag = document.createElement("input");
    new_room_tag.setAttribute('type', 'number');
    new_room_tag.setAttribute('name', 'room-tag[]');
    new_room_tag.setAttribute('placeholder', 'Enter Room Tag Number');
    new_room_tag.classList.add("form-input");

    div2.appendChild(new_room_tag);

    room_form.appendChild(div2);

}
</script>
