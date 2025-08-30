<link rel="stylesheet" href="ajax.css">

<?php
function asset_layout($result, $header_true, $row_num)
{
?>
    <section class="is-ajax" id="is-ajax" style="opacity: 0;">
            <table id="asset-table">
                <thead>
                    <tr>
                        <th class='row-even'>Row</th>
                        <th class='row-even'>Unit</th>
                        <th class='row-even'>Asset Tag</th>
<?php if (array_key_exists('asset_name', $header_true)) {
echo "<th class='row-even'>Description</th>";
}
if (array_key_exists('dept_id', $header_true)) {
    echo "<th class='row-even'>Department</th>";
}
if (array_key_exists('room_tag', $header_true)) {
    echo "<th class='row-even'>Room Tag</th>";
}
if (array_key_exists('room_loc', $header_true)) {
    echo "<th class='row-even'>Room Number</th>";
}
if (array_key_exists('room_loc', $header_true)) {
    echo "<th class='row-even'>Building Name</th>";
}
if (array_key_exists('asset_sn', $header_true)) {
    echo "<th class='row-even'>Serial Number</th>";
}
    echo "<th class='row-even'>Status</th>";
if (array_key_exists('asset_price', $header_true)) {
    echo "<th class='row-even'>Price</th>";
}
if (array_key_exists('asset_po', $header_true)) {
    echo "<th class='row-even'>Purchase Order</th>";
} ?>
                    </tr>

                </thead>
                <tbody id="table-body"><?php
    foreach ($result as $row) {
        $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

        // Escape values for safety
        $safe_tag = htmlspecialchars($row['asset_tag'] ?? '', ENT_QUOTES);
        $asset_status = htmlspecialchars($row['asset_status'] ?? '', ENT_QUOTES);
        $bus_unit = htmlspecialchars($row['bus_unit'] ?? '', ENT_QUOTES);
        $safe_name = htmlspecialchars($row['asset_name'] ?? '', ENT_QUOTES);
        $safe_deptid = htmlspecialchars($row['dept_id'] ?? '', ENT_QUOTES);
        $safe_price = htmlspecialchars($row['asset_price'] ?? '', ENT_QUOTES);
        $safe_po = htmlspecialchars($row['po'] ?? '', ENT_QUOTES);
        $safe_room = htmlspecialchars($row['room_tag'] ?? '', ENT_QUOTES);
        $safe_serial = htmlspecialchars($row['serial_num'] ?? '', ENT_QUOTES);
        $bldg_name = htmlspecialchars($row['bldg_name'] ?? '', ENT_QUOTES);
        $room_loc = htmlspecialchars($row['room_loc'] ?? '', ENT_QUOTES);

?>
<tr>
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
                        <td class=<?= $color_class ?>><?= $bus_unit ?></td>
                        <td class=<?= $color_class ?>>
                            <button id="button-9" data-toggle="modal" data-target="#modal<?= $safe_tag ?>"><?= $safe_tag ?></button>
                        </td>
<?php if (array_key_exists('asset_name', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_name . "</td>";
        } ?>
<?php if (array_key_exists('dept_id', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_deptid . "</td>";
} ?>
<?php if (array_key_exists('room_tag', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_room . "</td>";
} ?>
<?php if (array_key_exists('room_loc', $header_true)) {
echo "<td class=" . $color_class . ">" . $room_loc . "</td>";
} ?>
<?php if (array_key_exists('room_loc', $header_true)) {
echo "<td class=" . $color_class . ">" . $bldg_name . "</td>";
} ?>
<?php if (array_key_exists('asset_sn', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_serial . "</td>";
} ?>
                        <td class=<?= $color_class ?>><?= $asset_status ?></td>
<?php if (array_key_exists('asset_price', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_price . "</td>";
} ?>
<?php if (array_key_exists('asset_po', $header_true)) {
echo "<td class=" . $color_class . ">" . $safe_po . "</td>";
} ?>
</tr>
            <?php } ?>
                </tbody>

            </table>
    </section>
<?php foreach ($result as $row) {
        $safe_tag = htmlspecialchars($row['asset_tag'] ?? '', ENT_QUOTES);
        $safe_name = htmlspecialchars($row['asset_name'] ?? '', ENT_QUOTES);
        $safe_deptid = htmlspecialchars($row['dept_id'] ?? '', ENT_QUOTES);
        $safe_price = htmlspecialchars($row['asset_price'] ?? '', ENT_QUOTES);
        $safe_po = htmlspecialchars($row['po'] ?? '', ENT_QUOTES);
        $safe_room = htmlspecialchars($row['room_tag'] ?? '', ENT_QUOTES);
        $safe_serial = htmlspecialchars($row['serial_num'] ?? '', ENT_QUOTES);
        $bldg_name = htmlspecialchars($row['bldg_name'] ?? '', ENT_QUOTES);
        $room_loc = htmlspecialchars($row['room_loc'] ?? '', ENT_QUOTES);
?>
                <div id="modal<?= $safe_tag ?>" class="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel<?= $safe_tag; ?>" aria-hidden="true">
                    <!-- Modal content -->
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel<?= $safe_tag; ?>">Asset Details for <?= $safe_tag ?></h5>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="crud/change_asset_info.php" method="post">
<input type="hidden" id="old_tag" name="old_tag">
<input type="hidden" id="old_name" name="old_name">
<input type="hidden" id="old_dept" name="old_dept">
<input type="hidden" id="old_room_tag" name="old_room_tag">
<input type="hidden" id="old_sn" name="old_sn">
<input type="hidden" id="old_price" name="old_price">
<input type="hidden" id="old_po" name="old_po">
<input type="hidden" id="old_status" name="old_status">
                                    <label for="asset_tag">Asset Tag:</label>
                                    <input type="text" id="asset_tag" name="asset_tag" value="<?= $safe_tag ?>">
                                    <br>
                                    <label for="name">Asset Name:</label>
                                    <input type="text" id="name" name="name" value="<?= $safe_name ?>">
                                    <br>

                                    <label for="deptid">Department ID:</label>
                                    <input type="text" id="deptid" name="deptid" value="<?= $safe_deptid ?>">
                                    <br>
                                    <label for="location">Room Tag:</label>
                                    <input type="text" id="location" name="location" value="<?= $safe_room ?>">
                                    <br>
                                    <label for="serial">Serial Number:</label>
                                    <input type="text" id="serial" name="serial" value="<?= $safe_serial ?>">
                                    <br>
                                    <label for="price">Price:</label>
                                    <input type="number" id="price" name="price" value="<?= $safe_price ?>">
                                    <br>
                                    <label for="po">Purchase Order:</label>
                                    <input type="text" id="po" name="po" value="<?= $safe_po ?>">
                                    <br>
                                    <label for="status">Status:</label>
                                    <select id="status" name="status">
                                        <option value="in_service">In Service</option>
                                        <option value="disposed">Disposed</option>
                                    </select>
                                    <br>
                                    <button type="submit" onclick="deleteUser(<?= $safe_tag ?>, 'asset')" name="delete">Delete Asset</button>
                                    <button type="submit">Update Asset</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
<?php } ?>

<?php
}


function bldg_layout($result, $header_true, $row_num)
{
?>
    <section class="is-ajax" id="is-ajax" style="opacity: 0;">
            <table id="asset-table">
                <thead>
                    <tr>
                        <th class='row-even'>Row</th>
                        <th class='row-even'>Room Tag</th>
<?php if (array_key_exists('bldg_name', $header_true)) {
echo "<th class='row-even'>Building Name</th>";
}
if (array_key_exists('room_loc', $header_true)) {
    echo "<th class='row-even'>Room Number</th>";
}
if (array_key_exists('bldg_id', $header_true)) {
    echo "<th class='row-even'>Building ID</th>";
}
?>
                    </tr>

                </thead>
                <tbody id="table-body"><?php
foreach ($result as $row) {
    $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

    // Escape values for safety
    $bldg_id = htmlspecialchars($row['bldg_id'] ?? '', ENT_QUOTES);
    $bldg_name = htmlspecialchars($row['bldg_name'] ?? '', ENT_QUOTES);
    $room_num = htmlspecialchars($row['room_loc'] ?? '', ENT_QUOTES);
    $room_tag = htmlspecialchars($row['room_tag'] ?? '', ENT_QUOTES);

?>
<tr>
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
                        <td class=<?= $color_class ?>>
                            <button id="button-9" data-toggle="modal" data-target="#modal<?= $room_tag ?>"><?= $room_tag ?></button>
                        </td>
<?php if (array_key_exists('bldg_name', $header_true)) {
echo "<td class=" . $color_class . ">" . $bldg_name . "</td>";
    } ?>
<?php if (array_key_exists('room_loc', $header_true)) {
echo "<td class=" . $color_class . ">" . $room_num . "</td>";
} ?>
<?php if (array_key_exists('bldg_id', $header_true)) {
echo "<td class=" . $color_class . ">" . $bldg_id . "</td>";
} ?>
</tr>
            <?php } ?>
                </tbody>

            </table>
    </section>
<?php foreach ($result as $row) {
$bldg_id = htmlspecialchars($row['bldg_id'] ?? '', ENT_QUOTES);
$bldg_name = htmlspecialchars($row['bldg_name'] ?? '', ENT_QUOTES);
$room_num = htmlspecialchars($row['room_loc'] ?? '', ENT_QUOTES);
$room_tag = htmlspecialchars($row['room_tag'] ?? '', ENT_QUOTES);
?>
                <div id="modal<?= $room_tag ?>" class="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel<?= $room_tag; ?>" aria-hidden="true">
                    <!-- Modal content -->
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel<?= $room_tag; ?>">Room Details for <?= $room_tag ?></h5>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="crud/change_bldg_info.php" method="post">
                                <input type="hidden" id="old_bldg_id" name="old_bldg_id" value="<?= $bldg_id ?>">
                                <input type="hidden" id="old_name" name="old_name" value="<?= $bldg_name ?>">
                                <input type="hidden" id="old_room_loc" name="old_room_loc" value="<?= $room_num ?>">
                                <input type="hidden" id="old_room_tag" name="old_room_tag" value="<?= $room_tag?>">
                                    <label for="asset_tag">Building ID:</label>
                                    <input type="number" id="bldg_id" name="bldg_id" value="<?= $bldg_id ?>">
                                    <br>
                                    <label for="name">Building Name:</label>
                                    <input type="text" id="name" name="name" value="<?= $bldg_name ?>">
                                    <br>

                                    <label for="room_loc">Room Number/Name:</label>
                                    <input type="text" id="room_loc" name="room_loc" value="<?= $room_num ?>">
                                    <br>
                                    <label for="location">Room Tag:</label>
                                    <input type="text" id="room_tag" name="room_tag" value="<?= $room_tag ?>">
                                    <br>
                                    <button type="submit" onclick="deleteUser(<?= $room_tag ?>, 'room')" name="delete">Delete Room</button>
                                    <button type="submit" name="bldg">Update Room</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>
<?php
}

function dept_layout($result, $row_num)
{ ?>
    <section class="is-ajax" id="is-ajax" style="opacity: 0;">
            <table id="asset-table">
                <thead>
                    <tr>
                        <th class='row-even'>Row</th>
                        <th class='row-even'>Department ID</th>
                        <th class='row-even'>Department Name</th>
                        <th class='row-even'>Custodian</th>
                        <th class='row-even'>Manager</th>
                    </tr>

                </thead>
                <tbody id="table-body"><?php
foreach ($result as $row) {
    $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

    // Escape values for safety
    $dept_id = htmlspecialchars($row['dept_id'] ?? '', ENT_QUOTES);
    $dept_name = htmlspecialchars($row['dept_name'] ?? '', ENT_QUOTES);
    //$custodian = htmlspecialchars($row['custodian'] ?? '', ENT_QUOTES);
    $custodian= str_getcsv(trim($row['custodian'], '{}'), ',', '"', '\\');
    $manager = htmlspecialchars($row['dept_manager'] ?? '', ENT_QUOTES);

?>
                       <tr>
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
                        <td class=<?= $color_class ?>>
                            <button id="button-9" data-toggle="modal" data-target="#modal<?= $dept_id ?>"><?= $dept_id ?></button>
                        </td>

                        <td class=<?= $color_class ?>><?= $dept_name ?></td>
                        <td class=<?= $color_class ?>>
<?php 
    $count = count($custodian);
    foreach ($custodian as $index=>$cust) { 
        if ($count-1 == $index) {
            echo $cust;
        } else {
            echo $cust . ',';
        } 
    }
?>
</td>
                        <td class=<?= $color_class ?>> <?= $manager ?></td>
                    </tr>
            <?php } ?>
                </tbody>

            </table>
    </section>
<?php
    foreach ($result as $row) {
        // Escape values for safety
        $dept_id = htmlspecialchars($row['dept_id'] ?? '', ENT_QUOTES);
        $dept_name = htmlspecialchars($row['dept_name'] ?? '', ENT_QUOTES);
        //$custodian = htmlspecialchars($row['custodian'] ?? '', ENT_QUOTES);
        $custodian= str_getcsv(trim($row['custodian'], '{}'), ',', '"', '\\');
        $manager = htmlspecialchars($row['dept_manager'] ?? '', ENT_QUOTES);
?>
                <div id="modal<?= $dept_id ?>" class="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel<?= $dept_id; ?>" aria-hidden="true">
                    <!-- Modal content -->
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel<?= $dept_id; ?>">Department Details for <?= $dept_id ?></h5>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="crud/change_dept_info.php" method="post">
<input type="hidden" id="old_dept" name="old_dept">
<input type="hidden" id="old_name" name="old_name">
<input type="hidden" id="old_cust" name="old_cust">
<input type="hidden" id="old_manager" name="old_manager">
                                    <label for="asset_tag">Department ID:</label>
                                    <input type="text" id="dept" name="dept" value="<?= $dept_id ?>">
                                    <br>
                                    <label for="name">Department Name:</label>
                                    <input type="text" id="name" name="name" value="<?= $dept_name ?>">
                                    <br>

<?php foreach ($custodian as $index=>$cust) { ?>
                                    <label for="room_loc">Custodian:</label>
                                    <input type="text" id="cust" name="cust[]" value="<?= $cust ?>">
                                    <br>
<?php } ?>

                                    <label for="location">Manager:</label>
                                    <input type="text" id="manager" name="manager" value="<?= $manager ?>">
                                    <br>
                                    <button type="submit" onclick="deleteUser(<?= $dept_id ?>, 'dept')" name="delete">Delete Department</button>
                                    <button type="submit" name="dept">Update Department</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
<?php
}

function user_layout($result, $row_num)
{ ?>
    <section class="is-ajax" id="is-ajax" style="opacity: 0;">
            <table id="asset-table">
                <thead>
                    <tr>
                        <th class='row-even'>Row</th>
                        <th class='row-even'>User Name</th>
                        <th class='row-even'>Email</th>
                        <th class='row-even'>Role</th>
                        <th class='row-even'>Last Login</th>
                        <th class='row-even'>First Name</th>
                        <th class='row-even'>Last Name</th>
                        <th class='row-even'>Department ID(s)</th>
                    </tr>

                </thead>
                <tbody id="table-body"><?php
foreach ($result as $row) {
    $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

    // Escape values for safety
    $username = htmlspecialchars($row['username'] ?? '', ENT_QUOTES);
    $email = htmlspecialchars($row['email'] ?? '', ENT_QUOTES);
    $u_role = htmlspecialchars($row['u_role'] ?? '', ENT_QUOTES);
    $last_login = htmlspecialchars($row['last_login'] ?? '', ENT_QUOTES);
    $f_name = htmlspecialchars($row['f_name'] ?? '', ENT_QUOTES);
    $l_name = htmlspecialchars($row['l_name'] ?? '', ENT_QUOTES);
    $dept = trim($row['dept_id'], '{}');

?>
<tr style="min-height:90px;">
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
                        <td class=<?= $color_class ?>>
                            <button id="button-9" data-toggle="modal" data-target="#modal<?= $username ?>"><?= $username ?></button>
                        </td>

                        <td class=<?= $color_class ?>><?= $email ?></td>

                        <td class=<?= $color_class ?>><?= $u_role ?></td>
                        <td class=<?= $color_class ?>> <?= $last_login ?></td>
                        <td class=<?= $color_class ?>><?= $f_name ?></td>
                        <td class=<?= $color_class ?>><?= $l_name ?></td>
                        <td class=<?= $color_class ?>><?= $dept ?></td>
</tr>
            <?php } ?>
                </tbody>

            </table>
    </section>
<?php
    foreach ($result as $row) {
        // Escape values for safety
        $username = htmlspecialchars($row['username'] ?? '', ENT_QUOTES);
        $email = htmlspecialchars($row['email'] ?? '', ENT_QUOTES);
        $u_role = htmlspecialchars($row['u_role'] ?? '', ENT_QUOTES);
        $last_login = htmlspecialchars($row['last_login'] ?? '', ENT_QUOTES);
        $f_name = htmlspecialchars($row['f_name'] ?? '', ENT_QUOTES);
        $l_name = htmlspecialchars($row['l_name'] ?? '', ENT_QUOTES);
        $dept = trim($row['dept_id'], '{}');
        $dept2 = explode(',', $dept);
        $dept = str_replace('"', '' ,$row['dept_id']);

?>
                <div id="modal<?= $username ?>" class="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel<?= $username; ?>" aria-hidden="true">
                    <!-- Modal content -->
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel<?= $username; ?>">Department Details for <?= $username ?></h5>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="crud/change_user_info.php" method="post">

                                    <?php foreach ($dept2 as $index=>$dept_row) { ?>
                                    <input type="hidden" id="old_dept" name="old_dept[]" value=<?= $dept_row ?>>
<?php } ?>
                                    <label for="asset_tag">Username:</label>
                                    <input type="text" id="username" name="username" value="<?= $username ?>" readonly>
                                    <br>
                                    <label for="name">Email:</label>
                                    <input type="text" id="email" name="email" value="<?= $email ?>">
                                    <br>

                                    <label for="room_loc">User Role:</label>
                                    <input type="text" id="old_role" name="old_role" value="<?= $u_role ?>" readonly>
<br>
                                    <label for="room_loc">Change Role to:</label>
                                    <select id="role" name="role">
                                        <option value="management">Management</option>
                                        <option value="admin">Admin</option>
                                        <option value="user" selected>User</option>
                                        <option value="custodian">Custodian</option>
</select>
                                    <br>
                                    <label for="location">Last Login:</label>
                                    <input type="text" id="last_login" name="last_login" value="<?= $last_login ?>" readonly>
                                    <br>
                                    <label for="location">First Name:</label>
                                    <input type="text" id="f_name" name="f_name" value="<?= $f_name ?>" readonly>
                                    <br>
                                    <label for="location">Last Name:</label>
                                    <input type="text" id="l_name" name="l_name" value="<?= $l_name ?>" readonly>
                                    <br>
                                    <label for="location">Department ID:</label>
                                    <input type="text" id="dept_ids" name="dept_ids[]" value="<?= $dept ?>">
                                    <br>
                                    <button type="submit" onclick="deleteUser(<?= htmlspecialchars($email, ENT_QUOTES) ?>, 'user')" name="delete">Delete User</button>
                                    <button type="submit" name="user">Update User</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
<?php
}
?>
<script>
async function deleteUser(data, type) {
    const API_URL = '/api/delete.php';
    const params = new URLSearchParams();
    const user = 'user-delete';
    if (type) params.set('type', type);
    if (data) params.set('data', data);

    try {
        const res = await fetch (`${API_URL}?${params.toString()}`, {
        headers: {
        'Accept': 'application/json';
    }
    });
        if (!res.ok) throw new Error (`HTTP ${res.status}`);
        const data = await res.json();
    } catch (err) {
        console.warn('Error deleting user:', err);
    }
}



</script>
