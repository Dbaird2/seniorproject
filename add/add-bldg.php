<?php
//error_reporting(0);
include_once("../config.php");
check_auth('high');
$bldg_msg = $bldg_color = '';
$room_msg = [[]];
if (isset($_GET['bldg-id'])) {
    $bldg_name = isset($_GET['bldg-name']) ? trim($_GET['bldg-name']) : exit('Missing building name.');
    $bldg_id = (int)$_GET['bldg-id'];
    $check_bldg_name = "SELECT bldg_name, bldg_id from bldg_table where bldg_name = :bldg_name OR bldg_id = :bldg_id";

    $statement = $dbh->prepare($check_bldg_name);
    $statement->execute([":bldg_name" => $bldg_name, ":bldg_id" => $bldg_id]);
    $already_in_db = $statement->fetch(PDO::FETCH_ASSOC);

    try {
        if ($_GET['add-remove'] === 'add') {
            if (!$already_in_db) {
                $insert_bldg = "INSERT INTO bldg_table (bldg_name, bldg_id) VALUES (?, ?)";

                $statement = $dbh->prepare($insert_bldg);
                $statement->execute([$bldg_name, $bldg_id]);

                $bldg_msg = "Building: " . $bldg_id . " " . $bldg_name . " inserted into database";
                $bldg_color = 'green';
            } else {
                $bldg_msg = "Building with id of " . $bldg_id . " or name of " . $bldg_name . " already exists";
                $bldg_color = 'red';
            }
        } else if ($_GET['add-remove'] === 'remove') {
            if ($already_in_db) {
                $delete_bldg = "DELETE FROM bldg_table WHERE bldg_name = :bldg_name AND bldg_id = :bldg_id";
                $delete_stmt = $dbh->prepare($delete_bldg);
                if ($delete_stmt->execute([":bldg_name" => $bldg_name, "bldg_id" => $bldg_id])) {
                    $bldg_msg = "Deleted " . $bldg_name . " from the database";
                }
            }
        }
    } catch (PDOException $e) {
        $bldg_msg = "Error adding or removing from database" . $e->getMessage();
    } catch (Exception $e) {
        $bldg_msg = "Error " . $e->getMessage();
    }
}
if (isset($_GET['room-num'])) {
    $bldg_name = isset($_GET['bldg-name']) ? $_GET['bldg-name'] : exit('Missing building name.');
    $bldg_id = (int)$_GET['bldg-id2'];
    $room_nums = $_GET['room-num'];

    $seen = [];
    $new_room_nums = [];
    foreach ($room_nums as $index => $room) {
        if (!isset($seen[trim($room)]) && $room !== '') {
            $seen[trim($room)] = true;
            $new_room_nums[] = trim($room);
        }
    }

    $check_room_avail = "SELECT * FROM room_table WHERE room_loc = :room_loc AND bldg_id = :bldg_id";
    $insert_room = "INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)";
    $delete_room = "DELETE FROM room_table WHERE room_loc = :room_loc AND bldg_id = :bldg_id";

    try {
        foreach ($new_room_nums as $index => $room) {

            $room_stmt = $dbh->prepare($check_room_avail);
            $room_stmt->execute([":room_loc" => $room, ":bldg_id" => $bldg_id]);
            $room_check = $room_stmt->fetch(PDO::FETCH_ASSOC);
            if ($_GET['add-remove-room'] === 'add') {
                if (!$room_check) {
                    $insert_stmt = $dbh->prepare($insert_room);
                    $insert_stmt->execute([$room, $bldg_id]);

                    echo "Adding " . $room;

                    $room_msg[$index][0] = 'green';
                    $room_msg[$index][1] = "Room number: " . $room . " successfully added.";
                } else {
                    $room_msg[$index][0] = 'red';
                    $room_msg[$index][1] = "Room Number: " . $room . " failed to add.";
                }
            } else if ($_GET['add-remove-room'] === 'remove') {
                if ($room_check) {
                    $delete_stmt = $dbh->prepare($delete_room);
                    $delete_stmt->execute([":room_loc" => $room, ":bldg_id" => $bldg_id]);
                    echo "Deleting " . $room;

                    $room_msg[$index][0] = 'green';
                    $room_msg[$index][1] = "Room number: " . $room . " successfully deleted.";
                }
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
                <h2 class='form-header'>Building Form</h2>
                <form action="add-bldg.php" id="add-bldg" method="get">
                    <div class="form-group">
                        <label class="form-label" for="bldg-name">Building Name<br></label>
                        <input class="form-input" name="bldg-name" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bldg-id">Building ID<br></label>
                        <input class="form-input" name="bldg-id" type="number" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add-remove">Add or Remove<br></label>
                        <select class="form-input" name="add-remove" id="add-remove">
                            <option value="add" selected>Add Building</option>
                            <option value="remove">Remove Building</option>
                        </select>
                    </div>
                    <button class="submit-btn" type="submit" id="submit1">Submit</button>
                    <?php echo "<br><p style='color:$bldg_color;'>" . $bldg_msg . "<br>"; ?>
                </form>
            </div>

            <div class="form2">
                <div class="form-header">
                    <h2>Room Form</h2>
                </div>
                <form action="add-bldg.php" id="add-room" method="get">
                    <div class="form-group">
                        <label class="form-label" for="bldg-name">Building Name<br></label>
                        <input class="form-input" type="search" name="bldg-name" id="bldg-name2" list="bldg-names" autocomplete="off">
                        <datalist id="bldg-names">
                            <?php foreach ($result as $bldg) { ?>
                                <option value="<?= $bldg["bldg_name"] ?>"><?= $bldg["bldg_name"] ?></option>
                            <?php } ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bldg-id2">Building ID<br></label>
                        <input class="form-input" type="number" id="bldg-id2" name="bldg-id2" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="add-remove-room">Add or Remove<br></label>
                        <select class="form-input" name="add-remove-room" id="add-remove-room">
                            <option value="add">Add Room(s)</option>
                            <option value="remove">Remove Room(s)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="room-num">Room Number<br></label>
                        <input class="form-input" type="text" name="room-num[]" id="room-num" placeholder="Enter Room Number" required>
                    </div>

                    <div class="form-group">
                        <div id="extra-rooms"></div>
                    </div>

                    <button class="submit-btn" type="submit" id="submit2">Submit</button>
                </form>
                <button class="submit-btn" onclick="addNewRoom()">Add Another Room</button>

                <?php
                $color = '';
                if (isset($room_msg[0][0])) {
                    foreach ($room_msg as $msg) {
                        $color = $msg[0];
                        echo "<p style='color:$color;text-align:left;'>" . $msg[1] . "</p>";
                    }
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
                result.forEach((item, index) => {
                    if (item['bldg_name'] == option_val.value) {
                        bldg_id.value = result[index]['bldg_id'];
                        return true;
                    }
                });
            });
            result.forEach((item, index) => {
                if (item['bldg_name'] == option_val.value) {
                    bldg_id.value = result[index]['bldg_id'];
                }
            });
        });

        function addNewRoom() {
            const room_form = document.getElementById('extra-rooms');
            const div = document.createElement("div");
            const new_room = document.createElement("input");
            new_room.setAttribute('type', 'text');
            new_room.setAttribute('name', 'room-num[]');
            new_room.setAttribute('placeholder', 'Enter Room Number');
            new_room.classList.add("form-input");


            div.appendChild(new_room);
            room_form.appendChild(div);
        }
    </script>
</body>