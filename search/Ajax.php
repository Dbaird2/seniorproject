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
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scrolling if needed */
            background-color: rgb(0, 0, 0); /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4); /* Black with opacity */
            padding-top: 60px;
        }

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less depending on screen size */
        }

        /* Close button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        /* Style the tab */
        .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
        background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
        background-color: #ccc;
        }

        /* Style the tab content */
        .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
        }
        </style>
<?php
if (isset($_POST['search'])) {
    $tag = $_POST['search'];
    $result = [];
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') {
        $query = "SELECT a.asset_tag, a.asset_name, a.serial_num, a.asset_price, a.po, a.room_tag, r.dept_id FROM asset_info as a JOIN room_table as r ON r.room_tag = a.room_tag WHERE asset_tag LIKE :tag OR asset_name LIKE :tag OR serial_num LIKE :tag OR CAST(po as CHAR) LIKE :tag OR r.dept_id LIKE :tag";
    } else if ($_SESSION['role'] === 'custodian' || $_SESSION['role'] === 'user') {
    }

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
                    <strong><a href="#" id="openModalLink"><?= $safe_tag?></a></strong>
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
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span id="closeModalBtn" class="close">&times;</span>
            <div class="popup-tabs">
                <button class="tablinks" onclick="changeTabs(event, 'Display')">Item Display</button>
                <button class="tablinks" onclick="changeTabs(event, 'Change-info')">Change Asset Info</button>
            </div>
            <div id="Display" class="tabcontent">
                <h3>Asset Display</h3>
                <p>Asset Tag: <?php echo $safe_tag ?></p>
                <p>Name: <?= $safe_name ?></p>
                <p>Dept ID: <?= $safe_deptid ?></p>
                <p>PO: <?=  $safe_po ?></p>
                <p>Room Tag: <?=  $safe_room ?></p>
                <p>SN: <?= $safe_serial ?></p>
                <p>Price: <?=  $safe_price ?></p>

            </div>
            <div id="Change-info" class="tabcontent">
                <h3>Change Asset Info</h3>
                <form action="change_asset_info.php" method="post">
                    <label for="asset_tag">Asset Tag:</label>
                    <input type="text" id="asset_tag" name="asset_tag" value="<?= $safe_tag ?>" >
                    <br>
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="in_service">In Service</option>
                        <option value="disposed">Disposed</option>
                    </select>
                    <br>
                    <label for="name">Asset Name:</label>
                    <input type="text" id="name" name="name" value="<?= $safe_name ?>" >
                    <br>
                   
                    <label for="deptid">Department ID:</label>
                    <input type="text" id="deptid" name="deptid" value="<?= $safe_deptid ?>" >
                    <br>
                    <label for="po">Purchase Order:</label>
                    <input type="text" id="po" name="po" value="<?= $safe_po ?>" >
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
                    <button type="submit">Update Asset</button>
                </form>
        </div>
    </div> 
            <?php
        }
        echo "</div></div>";
        echo "</section>";

    }
    ?>
    <script>
    const modal = document.getElementById("myModal");

    const openModalLink = document.getElementById("openModalLink");

    const closeModalBtn = document.getElementById("closeModalBtn");

    openModalLink.onclick = function(event) {
            event.preventDefault(); 
                modal.style.display = "block"; 
    }

    closeModalBtn.onclick = function() {
            modal.style.display = "none"; 
    }

    window.onclick = function(event) {
            if (event.target == modal) {
                        modal.style.display = "none"; 
                            }
    }

    function changeTabs(evt, tab) {
          var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                      }

              tablinks = document.getElementsByClassName("tablinks");
              for (i = 0; i < tablinks.length; i++) {
                      tablinks[i].className = tablinks[i].className.replace(" active", "");
                        }

                document.getElementById(tab).style.display = "block";
                evt.currentTarget.className += " active";
    }
    </script>
    <?php
}
?>
