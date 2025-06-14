<?php
require_once ("../config.php");
error_reporting(0);
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
            width: 100%;
            position: absolute;
            top: 8vh;
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
        </style>
<?php
if (isset($_POST['search']) || isset($_GET['search'])) {
    if (isset($tag)) {
        echo "<h1>$tag</h1>";
    }
    $tag = $_POST['search'];
    $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 1;
    $category = $_POST['categories'];
    $status = $_POST['statusFilter'];
    $query_offset = max(0, (int)($offset - 1)) * 50;
    $result = [];
    if ($category === 'assets') {
        $query = "SELECT a.asset_tag, a.asset_name, a.serial_num, a.asset_price, 
            a.po, a.room_tag, a.dept_id FROM asset_info as a 
            WHERE asset_tag LIKE :tag 
            OR asset_name LIKE :tag 
            OR serial_num LIKE :tag 
            OR CAST(po as CHAR) LIKE :tag 
            OR dept_id LIKE :tag LIMIT 50 OFFSET :offset";
        $query_count = "SELECT COUNT(*) as Rows
            FROM asset_info
            WHERE asset_tag LIKE :tag 
            OR asset_name LIKE :tag 
            OR serial_num LIKE :tag 
            OR CAST(po as CHAR) LIKE :tag 
            OR dept_id LIKE :tag";
        $exec_query = $dbh->prepare($query);
        $exec_query->execute(['tag' => "%$tag%",
            'offset' => $query_offset ]);
        $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);

        $exec_count = $dbh->prepare($query_count);
        $exec_count->execute(['tag' => "%$tag%"]);
        $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
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
                    <strong>Asset Tag</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>Asset Name</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <strong>Department ID</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                   <strong> Room Tag</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                   <strong> Serial Number</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    <strong>Price</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_po\")'>
                   <strong> Purchase Order</strong>
                </div>
<?php
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
?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>
                    <?= $row_num++ ?>
                    </strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_tag\")'>
                <strong>
                    <button  data-toggle="modal" data-target="#modal<?= $safe_tag?>"><?= $safe_tag?></button>
                </strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <?= $safe_name ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <?= $safe_deptid ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                    <?= $safe_room ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    <?= $safe_serial ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    $<?= $safe_price ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    <?= $safe_po ?>
                </div>
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
<?php
            }
            echo "</div></div>";
            echo "</section>";
        }
    } else if ($category === 'buildings') {
        $bldg_q = "SELECT bldg_id, bldg_name, room_loc, room_tag 
            FROM bldg_table NATURAL JOIN room_table 
            WHERE CAST(bldg_id as CHAR) like :search OR
            bldg_name like :search OR
            room_loc like :search OR
            CAST(room_tag as CHAR) like :search
            ORDER BY bldg_id
            LIMIT 50 OFFSET :offset";
        $bldg_count = "SELECT COUNT(*) as Rows
            FROM bldg_table NATURAL JOIN room_table 
            WHERE CAST(bldg_id as CHAR) like :search OR
            bldg_name like :search OR
            room_loc like :search OR
            CAST(room_tag as CHAR) like :search
            ";
        $bldg_e = $dbh->prepare($bldg_q);
        $bldg_e->execute(['search' => "%$tag%",
            'offset' => $query_offset ]);
        $result = $bldg_e->fetchAll(PDO::FETCH_ASSOC);

        $exec_count = $dbh->prepare($bldg_count);
        $exec_count->execute(['search' => "%$tag%"]);
        $total_rows = $exec_count->fetch(PDO::FETCH_ASSOC);
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
                    <strong>Building ID</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>Building Name</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <strong>Room Number/Name</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                   <strong>Room Tag Number</strong>
                </div>
<?php
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
                    <button  data-toggle="modal" data-target="#modal<?= $bldg_id?>"><?= $bldg_id?></button>
                </strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <?= $bldg_name ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <?= $room_num ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                    <?= $room_tag ?>
                </div>
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
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&statusFilter=<?=urlencode($status)?>"><?=$offset+1?></a></li>
<?php if ($total_pages > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset+2?></a></li>
<?php }
if ($total_pages > 3) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+3?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset+3?></a></li>
<?php }
if ($total_pages > 4) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+4?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset+4?></a></li>
<?php }
if ($total_pages <= 2 & $offset = 2)  { ?>

<?php } else { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>">Next</a></li>

<?php }

        } else if ($total_pages < $offset && $total_pages > 1) {
?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>">Previous</a></li>
    <?php if ($total_pages > 4) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-4?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-4?></a></li>
<?php }
if ($total_pages > 3) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-3?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-3?></a></li>
<?php }
if ($total_pages > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-2?></a></li>
<?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-1?></a></li>
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
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>">Previous</a></li>
    <?php if ($offset > 2) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-2?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset-1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset-1?></a></li>
    <li class="page-item active">
      <span class="page-link">
        <?=$offset?>
        <span class="sr-only">(current)</span>
      </span>
    </li>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset+1?></a></li>
    <?php if ($total_pages > $offset + 1) { ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-6b7x.onrender.com/search/search.php?offset=<?=$offset+2?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>"><?=$offset+2?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="https://dataworks-7b7x.onrender.com/search/search.php?offset=<?=$offset+1?>&search=<?=urlencode($tag)?>&categories=<?=urlencode($category)?>&st    atusFilter=<?=urlencode($status)?>">Next</a></li>
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
if ($category === 'assets') {
    echo "<script>changeBoxSize('8vw');</script>";
} else if ($category === 'buildings') {
    echo "<script>changeBoxSize('11vw');</script>";
} else if ($category === 'assets') {
    echo "<script>changeBoxSize('8vw');</script>";
}
