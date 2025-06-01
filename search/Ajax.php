<?php
require_once ("../config.php");
error_reporting(0);
?>
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
if (isset($_POST['search'])) {
    $tag = $_POST['search'];
    $result = [];
    $query = "SELECT a.asset_tag, a.asset_name, a.serial_num, a.asset_price, a.po, a.room_tag, r.dept_id FROM asset_info as a JOIN room_table as r ON r.room_tag = a.room_tag WHERE asset_tag LIKE :tag OR asset_name LIKE :tag OR serial_num LIKE :tag OR CAST(po as CHAR) LIKE :tag";

    $exec_query = $dbh->prepare($query);
    $exec_query->execute(['tag' => "%$tag%"]);
    $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        $row_number = 1;
        $color_class = ($row_number % 2 === 0) ? 'row-odd' : 'row-even';
        echo "<section id='showExcel'>";
        echo "<div class='row'>";
        echo "<div id='showExcel'  class='search-results'>";
        ?>
        <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_tag\")'>
                    <strong>Asset Tag</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <strong>Asset Name</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <strong>Department ID</strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_po\")'>
                   <strong> Purchase Order</strong>
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
                <?php
        foreach ($result as $row) {
            $row_number++;
            $color_class = ($row_number % 2 === 0) ? 'row-odd' : 'row-even';

            // Escape values for safety
            $safe_tag = htmlspecialchars($row['asset_tag'], ENT_QUOTES);
            $safe_name = htmlspecialchars($row['asset_name'], ENT_QUOTES);
            $safe_deptid = htmlspecialchars($row['dept_id'], ENT_QUOTES);    
            $safe_price = htmlspecialchars($row['asset_price'], ENT_QUOTES);  
            $safe_po = htmlspecialchars($row['po'], ENT_QUOTES);
            $safe_room = htmlspecialchars($row['room_tag'], ENT_QUOTES);
            $safe_serial = htmlspecialchars($row['serial_num'], ENT_QUOTES);
            ?>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_tag\")'>
                    <strong><a href="#somewhere.php"><?= $safe_tag?></a></strong>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_name\")'>
                    <?= $safe_name ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_deptid\")'>
                    <?= $safe_deptid ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_po\")'>
                    <?= $safe_po?> 
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_room\")'>
                    <?= $safe_room ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    <?= $safe_serial ?>
                </div>
                <div class='<?=$color_class?> excel-info' onclick='fill(\"$safe_serial\")'>
                    <?= $safe_price ?>
                </div>
            <?php
        }
        echo "</div></div>";
        echo "</section>";

    }
}
?>
