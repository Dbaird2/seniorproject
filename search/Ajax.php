<?php
require_once ("../config.php");

error_reporting(0);
if (isset($_POST['audit'])) {
    $where_dept = $where_price = '';
    $location_from = '';
    $search = $_POST['search'];
    $params = ['search'=>"%$search%"];
    if ($_POST['dept_id_search'] !== '') {
        $params['dept_id'] = $_POST['dept_id_search'];
        $where_dept = ' AND dept_id = :dept_id ';
    }
    if ($_POST['asset_price'] !== '') {
        $params['price'] = $_POST['asset_price'];
        $where_price = ' AND asset_price ' . $_POST['price_operation'] . ' :price ';
    }
    $audit_query = "SELECT a.asset_tag, a.serial_num, a.po, 
        a.asset_name, a.asset_price, a.room_tag, a.dept_id, b.bldg_name, r.room_loc FROM asset_info AS a 
        JOIN room_table AS r ON a.room_tag = r.room_tag 
        JOIN bldg_table AS b ON r.bldg_id = b.bldg_id 
        WHERE (asset_tag LIKE :search OR serial_num LIKE :search OR asset_name LIKE :search 
        OR CAST(po AS TEXT) LIKE :search) " . $where_dept . $where_price;
    $stmt = $dbh->prepare($audit_query);
    $stmt->execute($params);
    $data_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = json_encode($data_from_db);
    $_SESSION['audit_data'] = $data_from_db;
    header('Content-Type: application/json');
    echo $data;
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
body {
            margin: 0;
            height: 100vh;
            font-size: calc(0.5vw + 0.4vh);
        }
        body::-webkit-scrollbar {
            width: 1em;
        }

        body::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        }

        body::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            outline: 1px solid slategrey;
        }
        .row {
            display: flex;
            text-align: center;
        }


        #showExcel {
            display: flex;
            flex-wrap: wrap;
            margin:auto;
            text-align:center;
            justify-content: left;
            max-width: 80%;
        }

        .excel-info {
            min-height: 4vh;
            max-height: 4vh;
            min-width: 8vw;
            max-width: 15vw;
            flex: 1;
            justify-content: center;
            border: 0.1vh solid #cce0ff;
            text-align: center;
            border-radius: 0px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .inner-text {
            font-size: calc(0.5vw + 0.4vh);
            padding: 0.5vh 0.5vw;
            font-family: Arial, sans-serif;
        }
         .row-even {
            background-color: #f0f8ff;
        }

        .row-odd {
            background-color: #ffffff;
        }
        .asset-table {
            font-size: calc(0.7vw + 0.4vh);

            margin: auto; border: 1px solid #c0d6e4;
            border-collapse: collapse;
        }
        th {
            font-size: calc(0.7vw + 0.4vh);
            color:#2c3e50;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        td {
            font-size: calc(0.6vw + 0.4vh);
            font-weight: 500;
            color:#2c3e50;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;

        }
        .table-div {
            display:flex;
            justify-content: center;
        }
        #button-9 {
            appearance: button;
            backface-visibility: hidden;
            background-color: #405cf5;
            border-radius: 6px;
            border-width: 0;
            box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset,rgba(50, 50, 93, .1) 0 2px 5px 0,rgba(0, 0, 0, .07) 0 1px 1px 0;
            box-sizing: border-box;
            color: #fff;
            cursor: pointer;
            font-family: -apple-system,system-ui,"Segoe UI",Roboto,"Helvetica Neue",Ubuntu,sans-serif;
            font-size: calc(0.6vw + 0.4vh);
            height: 44px;
            line-height: 1.15;
            margin: 12px 0 0;
            outline: none;
            overflow: hidden;
            padding: 0 25px;
            position: relative;
            text-align: center;
            text-transform: none;
            transform: translateZ(0);
            transition: all .2s,box-shadow .08s ease-in;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
        }

        #button-9:disabled {
            cursor: default;
        }
        #button-9:focus {
            box-shadow: rgba(50, 50, 93, .1) 0 0 0 1px inset, rgba(50, 50, 93, .2) 0 6px 15px 0, rgba(0, 0, 0, .1) 0 2px 2px 0, rgba(50, 151, 211, .3) 0 0 0 4px;
        }
        </style>
<?php
if (isset($_POST['search']) || isset($_GET['search'])) {

//-------------------------------------------------------------------------
//  POST TO GET FROM SCRIPT.JS SEARCH FORM
    $location_from = '';
    $tag = strtoupper(htmlspecialchars($_POST['search']));
    $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 1;
    $category = $_POST['categories'];
    $status = $_POST['statusFilter'] ?? 'In Service';
    $dept_id = $_POST['dept_id'] ;
    $dept_id_search = strtoupper($_POST['dept_id_search']);
    $room_tag = $_POST['room_tag'] ;
    $room_loc = $_POST['room_loc'] ;
    $asset_sn = $_POST['asset_sn'] ;
    $asset_price = ($_POST['asset_price'] !== '') ? (int)$_POST['asset_price'] : -1;
    $asset_price_check = $_POST['asset_price_check'];
    $asset_price_operation = $_POST['price_operation'] ;
    $asset_po = $_POST['asset_po'] ;
    $bldg_id = $_POST['bldg_id'] ;
    $bldg_id_val = $_POST['bldg_id_val'];
    $bldg_name = $_POST['bldg_name'] ;
    $box_name = $_POST['box_name'] ;
    $params = [':search'=>"%$tag%"];
    $params2 = [':search'=>"%$tag%"];
    $q_all_params = [];
//-------------------------------------------------------------------------
    

//-------------------------------------------------------------------------
//  GET QUERY OFFSET
    $query_offset = max(0, (int)($offset - 1)) * 50;
    $result = [];
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
//  DYNAMIC SQL QUERIES
    $query_start = "SELECT ";
    $query_asset_from = " FROM asset_info AS a ";
    $query_end = " LIMIT 50 OFFSET :offset";
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
//  RESET ARRAYS
    $header_true = [];
    $column_array = [];
    $where_array = [];
//-------------------------------------------------------------------------


    if ($category === 'assets') {
        $dept_id_search_active = $price_search_active = 0;
        $where_price = $where_dept = $location = '';
//-------------------------------------------------------------------------
//      SET COLUMNS WITH WHERE CONDITIONING
        $column_array[] = "a.asset_tag";
        $where_array[] = "a.asset_tag LIKE :search";
        if ($room_tag === 'true') {
            // Might be wasted, potentially will get rid of
            $header_true['room_tag'] = 'true';
            // FOR QUERYING
            $column_array[] = "a.room_tag";
            $where_array[] = "CAST(a.room_tag AS TEXT) LIKE :search";
        }
        if ($box_name === 'true') {
            $header_true['asset_name'] = 'true';
            $column_array[] = "a.asset_name";
            $where_array[] = "a.asset_name LIKE :search";
        } 
        if ($asset_sn === 'true') {
            $header_true['asset_sn'] = 'true';
            $column_array[] = "a.serial_num";
            $where_array[] = "a.serial_num LIKE :search";
        } 
        if (isset($asset_price_operation)) {
            $params[':price'] = $asset_price;
            $params2[':price'] = $asset_price;
            $where_price = " AND a.asset_price " . $asset_price_operation . " :price";
        }
        if ($asset_price_check === 'true') {
            $header_true['asset_price'] = 'true';
            $column_array[] = "a.asset_price";
        } 
        if ($asset_po === 'true') {
            $header_true['asset_po'] = 'true';
            $column_array[] = 'a.po';
            $where_array[] = "CAST(a.po AS TEXT) LIKE :search";
        }
        if ($dept_id === 'true') {
            $header_true['dept_id'] = 'true';
            $column_array[] = "a.dept_id";
        }
        if (isset($dept_id_search) && $dept_id_search !== '') {
            $params[':dept_id'] = $dept_id_search;
            $params2[':dept_id'] = $dept_id_search;
            $where_dept = " AND a.dept_id = :dept_id";
        }
        if ($room_loc === 'true') {
            $header_true['room_loc'] = 'true';
            $header_true['bldg_name'] = 'true';
            $location_from = " JOIN room_table AS r on a.room_tag = r.room_tag JOIN bldg_table AS b on r.bldg_id = b.bldg_id ";
            $column_array[] = "b.bldg_name";
            $column_array[] = "r.room_loc";
        }
        $column_array = implode(', ', $column_array);
        $where_array = implode(' OR ', $where_array);
        $query = $query_start . $column_array . " " . $query_asset_from . $location_from . " WHERE (" . $where_array . ") " . $where_dept . $where_price . $query_end;
        $query_count = "SELECT COUNT(*) as Rows FROM asset_info AS a WHERE (" . $where_array . ") " . $where_dept . $where_price;
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
//      SHOW CHECKBOXES & INPUT FOR ASSET FILTER
        echo "<script>addCheckboxes('.filter-assets');</script>";

//-------------------------------------------------------------------------
//      HIDE CHECKBOXES FOR BLDG & INPUT FOR BLDG FILTER
        echo "<script>removeCheckbox('.filter-bldg');</script>";
        if ($tag === 'all' || $tag === '') {
            $where = $and = '';
            $where_dept = $where_price = '';
            $count = 0;
            if (isset($_POST['dept_id_search']) && $_POST['dept_id_search'] !== '') {
                $where_dept = " a.dept_id = :dept_id ";
                $q_all_params[':dept_id'] = $dept_id_search;
                $q_c_params[':dept_id'] = $dept_id_search;
                $count++;
            }
            if (isset($_POST['asset_price']) && $_POST['asset_price'] !== '') {
                $q_all_params[':price'] = $asset_price;
                $q_c_params[':price'] = $asset_price;
                $where_price = " a.asset_price " . $asset_price_operation . " :price ";
                $count++;
            }
            $where = ($count > 0) ? ' WHERE ' : '';
            if ($where_dept && $where_price) {
                $and = ' AND ';
            }
            $query = $query_start . $column_array . " " . $query_asset_from . $location_from . " " . $where . $where_price . $and . $where_dept . $query_end;

            $query_count = "SELECT COUNT(*) as Rows FROM asset_info AS a JOIN room_table AS r ON a.room_tag = r.room_tag JOIN bldg_table AS b ON r.bldg_id = b.bldg_id " . $where . $where_price . $and . $where_dept;
            $q_all_params['offset'] = $query_offset;

            $exec_query = $dbh->prepare($query);
            $exec_query->execute($q_all_params);
            $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);

            $exec_count = $dbh->prepare($query_count);
            $exec_count->execute($q_c_params);
            $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
        } else {
            $exec_query = $dbh->prepare($query);
            $params['offset'] = $query_offset;
            $exec_query->execute($params);
            $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);

            $exec_count = $dbh->prepare($query_count);
            $exec_count->execute($params2);
            $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
        }
            echo "<br>" . $query ."<br>";
        $row_count = (int)$total_rows['rows'];

        $row_num = isset($query_offset) ? $query_offset + 1 : 1;

        if ($result) {
            $color_class = ($row_num % 2 === 0) ? 'row-odd' : 'row-even';
?>
<div class="table-div">
  <table id="asset-table">
    <thead>
        <tr>
            <th class='row-even'>Row</th>
        <th class='row-even'>Asset Tag</th>
        <?php if(array_key_exists('asset_name', $header_true)){echo "<th class='row-even'>Description</th>";}
        if(array_key_exists('dept_id', $header_true)) {echo "<th class='row-even'>Department</th>";} 
        if(array_key_exists('room_tag', $header_true)) {echo "<th class='row-even'>Room Tag</th>";}
        if(array_key_exists('room_loc', $header_true)) {echo "<th class='row-even'>Room Number</th>";}
        if(array_key_exists('room_loc', $header_true)) {echo "<th class='row-even'>Building Name</th>";}
        if(array_key_exists('asset_sn', $header_true)) {echo "<th class='row-even'>Serial Number</th>";}
        if(array_key_exists('asset_price', $header_true)) {echo "<th class='row-even'>Price</th>";}
        if(array_key_exists('asset_po', $header_true)) {echo "<th class='row-even'>Purchase Order</th>";}?>
        </tr>

    </thead>
    <tbody><?php 
        foreach ($result as $row) {
            $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

            // Escape values for safety
            $safe_tag = htmlspecialchars($row['asset_tag'], ENT_QUOTES);
            $safe_name = htmlspecialchars($row['asset_name'], ENT_QUOTES);
            $safe_deptid = htmlspecialchars($row['dept_id'], ENT_QUOTES);
            $safe_price = htmlspecialchars($row['asset_price'], ENT_QUOTES);
            $safe_po = htmlspecialchars($row['po'], ENT_QUOTES);
            $safe_room = htmlspecialchars($row['room_tag'], ENT_QUOTES);
            $safe_serial = htmlspecialchars($row['serial_num'], ENT_QUOTES);
            $bldg_name = htmlspecialchars($row['bldg_name'], ENT_QUOTES);
            $room_loc = htmlspecialchars($row['room_loc'], ENT_QUOTES);
            
            ?>
            <td class=<?=$color_class?>><?=$row_num++?></td>
            <td class=<?=$color_class?>>
                    <button id="button-9"  data-toggle="modal" data-target="#modal<?= $safe_tag?>"><?= $safe_tag?></button>
                </td>
                <?php if(array_key_exists('asset_name', $header_true)) {echo "<td class=".$color_class.">".$safe_name."</td>";}?>
                <?php if(array_key_exists('dept_id', $header_true)) {echo "<td class=".$color_class.">".$safe_deptid."</td>";}?>
                <?php if(array_key_exists('room_tag', $header_true)) {echo "<td class=".$color_class.">".$safe_room."</td>";}?>
                <?php if(array_key_exists('room_loc', $header_true)) {echo "<td class=".$color_class.">".$room_loc."</td>";}?>
                <?php if(array_key_exists('room_loc', $header_true)) {echo "<td class=".$color_class.">".$bldg_name."</td>";}?>
                <?php if(array_key_exists('asset_sn', $header_true)) {echo "<td class=".$color_class.">".$safe_serial."</td>";}?>
                <?php if(array_key_exists('asset_price', $header_true)) {echo "<td class=".$color_class.">".$safe_price."</td>";}?>
                <?php if(array_key_exists('asset_po', $header_true)) {echo "<td class=".$color_class.">".$safe_po."</td>";}?>
            </tbody>
            <div id="modal<?=$safe_tag?>" class="modal" tabindex="-1" role="dialog" ria-labelledby="modalLabel<?= $safe_tag; ?>" aria-hidden="true">
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
                                <input type="text" id="asset_tag" name="asset_tag" value="<?= $safe_tag ?>" >
                                <br>
                                <label for="name">Asset Name:</label>
                                <input type="text" id="name" name="name" value="<?= $safe_name ?>" >
                                <br>

                                <label for="deptid">Department ID:</label>
                                <input type="text" id="deptid" name="deptid" value="<?= $safe_deptid ?>" >
                                <br>
                                <label for="location">Room Tag:</label>
                                <input type="text" id="location" name="location" value="<?= $safe_room ?>" >
                                <br>
                                <label for="serial">Serial Number:</label>
                                <input type="text" id="serial" name="serial" value="<?= $safe_serial ?>" >
                                <br>
                                <label for="price">Price:</label>
                                <input type="number" id="price" name="price" value="<?= $safe_price ?>">
                                <br>
                                <label for="po">Purchase Order:</label>
                                <input type="text" id="po" name="po" value="<?= $safe_po ?>" >
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
  <?php
        }
    } else if ($category === 'buildings') {
        $and = $bldg_id_where = '';
//      SHOW CHECKBOXES & INPUT FOR BLDG FILTER
        echo "<script>addCheckboxes('.filter-bldg');</script>";

//      HIDE CHECKBOXES & INPUT FOR ASSET FILTER
        echo "<script>removeCheckbox('.filter-assets');</script>";

        $params_bldg = [":offset"=>$offset];
        $params_count = [];
        $column_array[] = 'room_tag';
        $where_array[] = 'CAST(room_tag AS TEXT) LIKE :search';
        if ($bldg_id_val !== NULL  && $bldg_id_val !== '') {
            $params_bldg[":bldg_id"] = $bldg_id_val;
            $params_count[":bldg_id"] = $bldg_id_val;
            $and = ' AND ';
            $where = ' WHERE ';
            $bldg_id_where = 'bldg_id = :bldg_id';
        }
        if ($bldg_id === 'true') {
            $header_true['bldg_id'] = 'true';
            $column_array[] = 'bldg_id';
        }
        if ($bldg_name === 'true') {
            $header_true['bldg_name'] = 'true';
            $column_array[] = 'bldg_name';
            $where_array[] = 'bldg_name LIKE :search';
        }
        if ($room_loc === 'true') {
            $header_true['room_loc'] = 'true';
            $column_array[] = 'room_loc';
            $where_array[] = 'room_loc LIKE :search';
        }
        $column_array = implode(', ', $column_array);
        $where_array = implode(' OR ', $where_array);
        $query_bldg_from = " FROM bldg_table NATURAL JOIN room_table ";
        if ($tag === 'all' || $tag === '') {
            $query = "SELECT " .$column_array.$query_bldg_from.$where.$bldg_id_where.$query_end;
            $bldg_count = "SELECT COUNT(*) as Rows ".$query_bldg_from.$where.$bldg_id_where;

            $bldg_e = $dbh->prepare($query);
            $bldg_e->execute($params_bldg);
            $result = $bldg_e->fetchAll(PDO::FETCH_ASSOC);

            $exec_count = $dbh->prepare($bldg_count);
            if ($params_count[0] === '' || $params_count[0] === NULL) {
                $exec_count->execute();
            } else {
                $exec_count->execute($params_count);
            }
            $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
        } else {
            $params_bldg[":search"] = "%$tag%";
            $params_count[":search"] = "%$tag%";
            $query = "SELECT " . $column_array . ' ' . $query_bldg_from . ' WHERE (' . $where_array .')'.$and.$bldg_id_where.$query_end;

            $bldg_count = "SELECT COUNT(*) as Rows
                FROM bldg_table NATURAL JOIN room_table 
                WHERE
                (bldg_name like :search OR
                room_loc like :search OR
                CAST(room_tag as TEXT) like :search)".$and.$bldg_id_where;
            $bldg_e = $dbh->prepare($query);
            $bldg_e->execute($params_bldg);
            $result = $bldg_e->fetchAll(PDO::FETCH_ASSOC);

            $exec_count = $dbh->prepare($bldg_count);
            $exec_count->execute($params_count);
            $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
        }
        $row_count = (int)$total_rows['rows'];

        $row_num = isset($query_offset) ? $query_offset + 1 : 1;

        if ($result) {
            $color_class = ($row_num % 2 === 0) ? 'row-odd' : 'row-even';
            echo "<section id='showExcel'>";
            echo "<div class='row'>";
            echo "<div id='showExcel'  class='search-results'>";
?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$row_num\")'>
                    <strong>Row</strong>
                </div>
        <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_tag\")'>
                    <strong>Room Tag Number</strong>
                </div>
<?php if (array_key_exists('bldg_name', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>Building Name</strong>
                </div>
<?php }
 if (array_key_exists('room_loc', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <strong>Room Number/Name</strong>
                </div>
<?php }
 if (array_key_exists('bldg_id', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                   <strong>Building ID</strong>
                </div>
<?php
 }
            foreach ($result as $row) {
                $color_class = ($row_num % 2 === 0) ? 'row-even' : 'row-odd';

                // Escape values for safety
                $bldg_id = htmlspecialchars($row['bldg_id'], ENT_QUOTES);
                $bldg_name = htmlspecialchars($row['bldg_name'], ENT_QUOTES);
                $room_num = htmlspecialchars($row['room_loc'], ENT_QUOTES);    
                $room_tag = htmlspecialchars($row['room_tag'], ENT_QUOTES);  
?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>
                    <?= $row_num++ ?>
                    </strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_tag\")'>
                <strong>
                    <button data-toggle="modal" data-target="#modal<?= $room_tag?>"><?= $room_tag?></button>
                </strong>
                </div>
<?php 
 if (array_key_exists('bldg_name', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <?= $bldg_name ?>
                </div>
<?php }
 if (array_key_exists('room_loc', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <?= $room_num ?>
                </div>
<?php }
 if (array_key_exists('bldg_id', $header_true)) { ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                    <?= $bldg_id ?>
                </div>
<?php } ?>
<div id="modal<?=$room_tag?>" class="modal" tabindex="-1" role="dialog" ria-labelledby="modalLabel<?= $room_tag; ?>" aria-hidden="true">
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
                                <input type="text" id="asset_tag" name="asset_tag" value="<?= $bldg_id ?>" >
                                <br>
                                <label for="name">Building Name:</label>
                                <input type="text" id="name" name="name" value="<?= $bldg_name ?>" >
                                <br>

                                <label for="room_loc">Room Number/Name:</label>
                                <input type="text" id="room_loc" name="room_loc" value="<?= $room_num ?>" >
                                <br>
                                <label for="location">Room Tag:</label>
                                <input type="text" id="location" name="location" value="<?= $room_tag ?>" >
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
<?php
            }
            echo "</div></div>";
            echo "</section>";
        }
    }
?>

        <nav aria-label="Page navigation example">
  <ul class="pagination d-flex justify-content-center">
<?php
        $total_pages = $row_count / 50;
        if (($offset === '1' || $offset === 1) && $total_pages > 1) {
?>
<li class="page-item disabled">
      <a class="page-link" href="#" tabindex="-1">Previous</a>
    </li>
    <li class="page-item active">
      <span class="page-link">
        <?=$offset?>
        <span class="sr-only">(current)</span>
      </span>
    </li>
 <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+1?></a></li>
<?php if ($total_pages > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+2?></a></li>
<?php }
if ($total_pages > 3) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+3?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+3?></a></li>
<?php }
if ($total_pages > 4) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+4?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+4?></a></li>

<?php }
if ($total_pages <= 2 & $offset = 2)  { ?>

<?php } else { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>">Next</a></li>

<?php }

        } else if ($total_pages < $offset && $total_pages > 1) {
?>
<li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>">Previous</a></li>
    <?php if ($total_pages > 4) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-4?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-4?></a></li>
    <?php }
if ($total_pages > 3) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-3?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-3?></a></li>
<?php }
if ($total_pages > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-2?></a></li>
<?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-1?></a></li>
    <li class="page-item active">
      <span class="page-link">
        <?=$offset?>
        <span class="sr-only">(current)</span>
      </span>
    </li>
    <li class="page-item disabled">
      <a class="page-link" href="#" tabindex="-1">Next</a>
      </li>    <?php
        } else if ($total_pages > 1) {
?>
<li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>">Previous</a></li>
    <?php if ($offset > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-2?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset-1?></a></li>
    <li class="page-item active">
      <span class="page-link">
        <?=$offset?>
        <span class="sr-only">(current)</span>
      </span>
    </li>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+1?></a></li>
    <?php if ($total_pages > $offset + 1) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>"><?=$offset+2?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>&box_name=<?= urlencode($box_name)?>&dept_id=<?= urlencode($dept_id)?>&room_tag=<?= urlencode($room_tag)?>&room_loc=<?=urlencode($room_loc)?>&asset_sn=<?=urlencode($asset_sn)?>&bldg_name=<?=urlencode($bldg_name)?>&asset_price=<?=urlencode($asset_price)?>&asset_po=<?=urlencode($asset_po)?>&bldg_id=<?=urlencode($bldg_id)?>">Next</a></li>
    <?php
        }
?>
  </ul>
</nav>
<?php 
}
?>
<script> 
function changeBoxSize(box_size) {
    var resize = document.querySelectorAll('.excel-info');

    resize.forEach(el => {
        el.style.minWidth = box_size;
    });
}
</script>
<?php 
$page_size = '0vw';
$column_count = 2;
foreach ($header_true as $count) {
    $column_count++;
}
if ($column_count === 10) {$page_size = '6vw';}
if ($column_count === 9) {$page_size = '7vw';}
if ($column_count === 8) {$page_size = '7.5vw';}
if ($column_count === 7) {$page_size = '9vw';}
if ($column_count === 6) {$page_size = '10vw';}
if ($column_count === 5) {$page_size = '12vw';}
if ($column_count === 4) {$page_size = '15vw';}
if ($column_count === 3) {$page_size = '20vw';}
if ($column_count === 2)  {$page_size = '25vw';}

# 2 columns = 25vw, 3 = 20, 4 = 15, 5 = 12, 6 = 10, 7 = 8, 8 = 7.5
echo "<script>changeBoxSize('$page_size');</script>";
