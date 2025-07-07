<?php
include_once("../config.php");

if ($_GET['bldg-id']) {
    $bldg_name = strtoupper($_GET['bldg-name']) ?? "Something went wrong";
    $bldg_id = (int)$_GET['bldg-id'];
    $check_bldg_name = "SELECT bldg_name, bldg_id from bldg_table where bldg_name = :bldg_name OR bldg_id = :bldg_id";

    $statement = $dbh->prepare($check_bldg_name);
    $statement->execute([":bldg_name"=>$bldg_name, ":bldg_id"=>$bldg_id]);
    $already_in_db = $statement->fecth(PDO::FETCH_ASSOC);
    if (!$already_in_db) {
        $insert_bldg = "INSERT INTO bldg_table (bldg_name, bldg_id) VALUES (?, ?)";
        $statement = $dbh->prepare($insert_bldg);
        $statemen->execute([":bldg_name"=>$bldg_name, ":bldg_id"=>$bldg_id]);
        $msg = "Building : " . $bldg_id . " ". $bldg_name . " inserted into database";
    }

} 
if ($_GET['room-tag']) {

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
                <label class="form-label" for="bldg-id">Building Number<br></label>
                <input class="form-input" name="bldg-id" type="text" required>
                </div>

                <div class="form-group">
                <label class="form-label" for="bldg-name">Building Name<br></label>
                <input class="form-input" name="bldg-name" type="text" required>
                </div>
                <button class="submit-btn" type="submit" id="submit1">Submit</button>
                <?php echo "<br>" . $msg . "<br>";?>
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
            <input class="form-input" type="text" id="bldg-id2" name="bldg-id2" readonly>
            </div>


            <div class="form-group">
            <label class="form-label" for="room-num">Room Number<br></label>
                <input class="form-input" type="text" name="room-num[]" id="room-num" placeholder="Enter Room Number" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="room-tag">Room Tag Number<br></label>
                <input class="form-input" type="text" name="room-tag[]" id="room-tag" placeholder="Enter Room Tag Number" required>
            </div>
            <div id="extra-rooms"></div>
            <button class="submit-btn" type="submit" id="submit2">Submit</button>
            </form>
            <button class="submit-btn" onclick="addNewRoom()">Add Another Room</button>

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
    div.appendChild(br);

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
    div2.appendChild(label2);
    div2.appendChild(br2);

    const new_room_tag = document.createElement("input");
    new_room_tag.setAttribute('type', 'text');
    new_room_tag.setAttribute('name', 'room-tag[]');
    new_room_tag.setAttribute('placeholder', 'Enter Room Tag Number');
    new_room_tag.classList.add("form-input");

    div2.appendChild(new_room_tag);

    room_form.appendChild(div2);

}
</script>
