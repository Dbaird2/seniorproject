<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="ajax.css">

<?php
function asset_layout($result, $header_true, $row_num)
{
?>
    <section class="is-ajax">
        <div class="table-div">
            <table id="asset-table">
                <thead>
                    <tr>
                        <th class='row-even'>Row</th>
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
                        if (array_key_exists('asset_price', $header_true)) {
                            echo "<th class='row-even'>Price</th>";
                        }
                        if (array_key_exists('asset_po', $header_true)) {
                            echo "<th class='row-even'>Purchase Order</th>";
                        } ?>
                    </tr>

                </thead>
                <tbody><?php
                        foreach ($result as $row) {
                            $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

                            // Escape values for safety
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
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
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
                        <?php if (array_key_exists('asset_price', $header_true)) {
                                echo "<td class=" . $color_class . ">" . $safe_price . "</td>";
                            } ?>
                        <?php if (array_key_exists('asset_po', $header_true)) {
                                echo "<td class=" . $color_class . ">" . $safe_po . "</td>";
                            } ?>
                </tbody>
                <div id="modal<?= $safe_tag ?>" class="modal" tabindex="-1" role="dialog" ria-labelledby="modalLabel<?= $safe_tag; ?>" aria-hidden="true">
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
                                <form action="change_asset_info.php" method="post">
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
            </table>
        </div>
    </section>
<?php
}


function bldg_layout($result, $header_true, $row_num)
{
?>
    <section class="is-ajax">
        <div class="table-div">
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
                <tbody><?php
                        foreach ($result as $row) {
                            $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

                            // Escape values for safety
                            $bldg_id = htmlspecialchars($row['bldg_id'], ENT_QUOTES);
                            $bldg_name = htmlspecialchars($row['bldg_name'], ENT_QUOTES);
                            $room_num = htmlspecialchars($row['room_loc'], ENT_QUOTES);
                            $room_tag = htmlspecialchars($row['room_tag'], ENT_QUOTES);

                        ?>
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
                </tbody>
                <div id="modal<?= $room_tag ?>" class="modal" tabindex="-1" role="dialog" ria-labelledby="modalLabel<?= $room_tag; ?>" aria-hidden="true">
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
                                <form action="change_asset_info.php" method="post">
                                    <label for="asset_tag">Building ID:</label>
                                    <input type="text" id="asset_tag" name="asset_tag" value="<?= $bldg_id ?>">
                                    <br>
                                    <label for="name">Building Name:</label>
                                    <input type="text" id="name" name="name" value="<?= $bldg_name ?>">
                                    <br>

                                    <label for="room_loc">Room Number/Name:</label>
                                    <input type="text" id="room_loc" name="room_loc" value="<?= $room_num ?>">
                                    <br>
                                    <label for="location">Room Tag:</label>
                                    <input type="text" id="location" name="location" value="<?= $room_tag ?>">
                                    <br>
                                    <button type="submit">Update Room</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </table>
        </div>
    </section>
<?php
}

function dept_layout($result, $row_num)
{ ?>
    <section class="is-ajax">
        <div class="table-div">
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
                <tbody><?php
                        foreach ($result as $row) {
                            $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

                            // Escape values for safety
                            $dept_id = htmlspecialchars($row['dept_id'], ENT_QUOTES);
                            $dept_name = htmlspecialchars($row['dept_name'], ENT_QUOTES);
                            $custodian = htmlspecialchars($row['custodian'], ENT_QUOTES);
                            $manager = htmlspecialchars($row['manager'], ENT_QUOTES);

                        ?>
                        <td class=<?= $color_class ?>><?= $row_num++ ?></td>
                        <td class=<?= $color_class ?>>
                            <button id="button-9" data-toggle="modal" data-target="#modal<?= $dept_id ?>"><?= $dept_id ?></button>
                        </td>

                        <td class=<?= $color_class ?>><?= $dept_name ?></td>

                        <td class=<?= $color_class ?>><?= $custodian ?></td>
                        <td class=<?= $color_class ?>> <?= $manager ?></td>
                </tbody>
                <div id="modal<?= $dept_id ?>" class="modal" tabindex="-1" role="dialog" ria-labelledby="modalLabel<?= $dept_id; ?>" aria-hidden="true">
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
                                <form action="change_asset_info.php" method="post">
                                    <label for="asset_tag">Department ID:</label>
                                    <input type="text" id="asset_tag" name="asset_tag" value="<?= $dept_id ?>">
                                    <br>
                                    <label for="name">Department Name:</label>
                                    <input type="text" id="name" name="name" value="<?= $dept_name ?>">
                                    <br>

                                    <label for="room_loc">Custodian:</label>
                                    <input type="text" id="room_loc" name="room_loc" value="<?= $custodian ?>">
                                    <br>
                                    <label for="location">Manager:</label>
                                    <input type="text" id="location" name="location" value="<?= $manager ?>">
                                    <br>
                                    <button type="submit">Update Room</button>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </table>
        </div>
    </section>
<?php
}
?>
