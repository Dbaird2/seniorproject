<?php
include_once("../config.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$success = [];
$msg = $delete_msg = '';
check_auth('high');
include_once("../navbar.php");
if (isset($_GET['dept-name'])) {

    $dept_name = trim(strtoupper($_GET['dept-name']));
    $dept_id = trim(strtoupper($_GET['dept-id']));
    $dept_availibility = "SELECT * FROM department WHERE dept_name = :dept_name OR dept_id = :dept_id";
    if (isset($_GET['add'])) {
        $mailstop = $_GET['dept-stop'];
        $f_name = trim($_GET['dept-cust-f']);
        $l_name = trim($_GET['dept-cust-l']);
        $custodian = '{' . $f_name . " " . $l_name . '}';
        $manager = trim($_GET['dept-mana-f']) . " " . trim($_GET['dept-mana-l']);

        $check_cust_uid = "SELECT id FROM user_table WHERE f_name ILIKE :f_name AND l_name ILIKE :l_name";
        $dept_insert = "INSERT INTO department (dept_id, dept_name, custodian, dept_manager, mail_stop, uid) VALUES (?, ?, ?, ?, ?, ?)";
        try {
            $get_id_stmt = $dbh->prepare($check_cust_uid);
            $get_id_stmt->execute([":f_name" => $f_name, ":l_name" => $l_name]);
            $id = $get_id_stmt->fetch(PDO::FETCH_ASSOC);

            $uid = (isset($id['id']) && is_array($id) && $id['id'] !== '') ? (int)$id['id'] :  -1;
            $mailstop = ($mailstop !== '') ? (int)$mailstop : -1;

            $check_stmt = $dbh->prepare($dept_availibility);
            $check_stmt->execute([":dept_name" => $dept_name, ":dept_id" => $dept_id]);
            $is_avail = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$is_avail) {
                $insert_stmt = $dbh->prepare($dept_insert);
                $insert_stmt->execute([$dept_id, $dept_name, $custodian, $manager, $mailstop, $uid]);
                if ($insert_stmt) {
                    $msg = "Successfully added " . $dept_name;
                } else {
                    $msg = "Failed to add " . $dept_name;
                }
            } else {
                $msg = "Department with Custodian " . $custodian . " already exists";
            }


            $name = $dept_name;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else if (isset($_GET['remove'])) {
        $dept_id = $_GET['dept-id'];
        $dept_name = $_GET['dept-name2'];
        $dept_id = $_GET['dept-id'];
        $dept_name = $_GET['dept-name2'];

        if (isset($_GET['cust-f-name'], $_GET['cust-l-name'])) {
            $full_name = trim($_GET['cust-f-name']) . ' ' . trim($_GET['cust-l-name']);

            if (isset($_GET['option']) && $_GET['option'] === 'add-cust') {
                $update_q = "UPDATE department 
                    SET custodian = COALESCE(custodian, ARRAY[]::TEXT[]) || ARRAY[:name] 
                    WHERE dept_id = :dept_id";
            } else if ($_GET['option'] === 'remove-cust') {
                $update_q = "UPDATE department 
                    SET custodian = array_remove(custodian, :name) 
                    WHERE dept_id = :dept_id";
            }

            if (isset($update_q)) {
                $update_stmt = $dbh->prepare($update_q);
                $update_stmt->execute([
                    ":name" => $full_name,
                    ":dept_id" => $dept_id
                ]);
            }
        } else {
            try {
                $delete_q = "DELETE FROM department WHERE dept_name = :dept_name AND dept_id = :dept_id";
                $delete_stmt = $dbh->prepare($delete_q);
                $delete_msg = $delete_stmt->execute([":dept_id" => $dept_id, ":dept_name" => $dept_name]) ? "Successfully deleted " . $dept_name : "Failed to delete " . $dept_name;
            } catch (PDOException $e) {
                $msg = "Failed to delete " . $dept_name;
                echo "Error " . $e->getMessage();
            }
        }
    }
}

$select = "SELECT * FROM department ORDER BY dept_name";
$stmt = $dbh->prepare($select);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
/*
$result = [
    ["dept_name" => "DISTRIBUTION", "dept_id" => "D21560"],
    ["dept_name" => "GRASP", "dept_id" => "D10730"]
];
 */
?>

<style>
    * {
        margin: 0;
    }

    .container {
        display: grid;
        grid-template-areas:
            "left left"
            "right right";
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        padding: 5px;
    }

    .container>div {
        padding: 10px;
    }

    .container>div.left {
        grid-area: left;
    }

    .container>div.right {
        grid-area: right;
    }
</style>
<link rel="stylesheet" href="bldg.css">

<body>
    <div class="is-dept">
        <div class="sub-body">
            <div class="form1">
                <h2 class='form-header'>Department Addition Form</h2>
                <form action="add-dept.php" id="add-dept" method="get" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="dept-name">Department Name<br></label>
                        <input class="form-input" name="dept-name" placeholder="Department Name" type="text" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept-id">Department ID<br></label>
                        <input class="form-input" name="dept-id" placeholder="Deptartment ID" type="text" required>
                    </div>
                    <label style="margin-bottom:10px;padding:-20px;" class="form-label" for="dept-id">Custodian's Name<br></label>
                    <div class="container">
                        <div class="form-left form-group">
                            <input class="form-input" name="dept-cust-f" placeholder="First Name" type="text" required>
                        </div>
                        <div class="form-right form-group">
                            <input class="form-input" name="dept-cust-l" placeholder="Last Name" type="text" required>
                        </div>
                    </div>
                    <label class="form-label" for="dept-id">Manager's Name</label>
                    <div class="container">
                        <div class="form-left form-group">
                            <input class="form-input" name="dept-mana-f" placeholder="First Name" type="text">
                        </div>
                        <div class="form-right form-group">
                            <input class="form-input" name="dept-mana-l" placeholder="Last Name" type="text">
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept-id">Mailstop<br></label>
                        <input class="form-input" id="stop" name="dept-stop" placeholder="Deptartment Mailstop" type="number">
                    </div>

                    <div class="form-group">
                        <div id="extra-dept"></div>
                    </div>

                    <button class="submit-btn" type="submit" name="add" id="submit1">Submit</button>
                </form>
            </div>
            <div class="form2">
                <h2 class='form-header'>Department Removal/Custodian Addition Form</h2>

            <form action="add-dept.php" id="remove-dept" method="get" enctype="multipart/form-data">
                <div class="form-group">

                    <div class="form-group">
                        <label class="form-label" for="option-type">Option<br></label>
                        <select class="form-input" name="option" id="option-type">
                            <option value="delete-dept" selected>Delete Department</option>
                            <option value="add-cust">Add Custodian</option>
                            <option value="remove-cust">Remove Custodian</option>
                        </select>
                    </div>
                    <label class="form-label" for="dept-name2">Department Name<br></label>
                    <input class="form-input" placeholder="Department Name" type="search" name="dept-name2" id="dept-name2" list="dept-names" autocomplete="off">
                    <datalist id="dept-names">
                        <?php foreach ($result as $dept) { ?>
                            <option value="<?= $dept["dept_name"] ?>"><?= $dept["dept_name"] ?></option>
                        <?php } ?>
                    </datalist>

                    <div class="form-group">
                        <input class="form-input" type="text" id="dept-id2" name="dept-id" readonly>
                    </div>
                    <div class="" id="add-cust-section">
                        <label id="add-cust-label" class="form-label" for="dept-id">Custodian's Name</label>
                        <div class="container" id="add-cust"></div>
                    </div>
                    <!-- <input class="form-input" type="search" list="custodian-names" id="cust-search" name="cust-search" requied> -->
                    <button class="submit-btn" type="submit" name="remove" id="submit2">Submit</button>
            </form>
            </div>
        </div>
<script>
document.addEventListener("DOMContentLoaded", (e) => {
result = <?php echo json_encode($result); ?>;
const option_type = document.getElementById('option-type');
const display_input = document.getElementById('add-cust');
const add_cust_label = document.getElementById("add-cust-label");
add_cust_label.style.display = "none";
const add_cust_section = document.getElementById("add-cust-section");

const datalist = document.getElementById('custodian-names');
const dept_name2 = document.getElementById('dept-name2');


option_type.addEventListener('change', function() {
    type_value = option_type.value;
    if (type_value === "add-cust") {
        add_cust_label.style.display = "block";
        const f_name_check = document.getElementById('f_div_id');
        if (!f_name_check) {
            const f_name = document.createElement("input");
            const l_name = document.createElement("input");

            const f_div = document.createElement("div");
            f_div.setAttribute("id", "f_div_id");
            const l_div = document.createElement("div");
            l_div.setAttribute("id", "l_div_id");

            const label = document.createElement("label");
            label.textContent = "Custodian's Name";
            label.classList.add("form-label");
            label.setAttribute("id", "add-cust-label");

            f_name.setAttribute("type", "text");
            f_name.setAttribute("id", "add-f-name");
            f_name.setAttribute("placeholder", "First name");
            f_name.setAttribute("name", "cust-f-name");
            f_name.required = true;
            f_name.classList.add("form-input");
            f_div.classList.add('form-left');
            f_div.classList.add('form-group');

            l_name.setAttribute("type", "text");
            l_name.setAttribute("id", "add-l-name");
            l_name.setAttribute("placeholder", "Middle & Last name");
            l_name.setAttribute("name", "cust-l-name");
            l_name.required = true;
            l_div.classList.add('form-right');
            l_div.classList.add('form-group');
            l_name.classList.add("form-input");

            f_div.appendChild(f_name);
            l_div.appendChild(l_name);
            display_input.appendChild(f_div);
            display_input.appendChild(l_div);
        }
        const cust_search = document.getElementById('cust-search');
        if (cust_search) {
            cust_search.remove();
        }
    } else if (type_value === 'remove-cust') {
        add_cust_label.style.display = "block";

        const cust_search = document.createElement("input");
        cust_search.setAttribute("id", "cust-search");
        cust_search.setAttribute("type", "search");
        cust_search.setAttribute("list", "custodian-names");
        cust_search.setAttribute("name", "cust-search");
        cust_search.classList.add('form-input');
        cust_search.required = true;
        add_cust_section.appendChild(cust_search);
        dept_name2.addEventListener('change', function() {
            datalist.innerHTML = '';

            result.forEach(element => {
            if (element['dept_name'] === dept_name2.value) {
                element['custodian'].forEach(cust_name => {
                const option = document.createElement('option');
                option.value = cust_name;
                datalist.appendChild(option);
            });
            }
            });
        });
        const f_div = document.getElementById("f_div_id");
        const l_div = document.getElementById("l_div_id");
        const f_name = document.getElementById("add-f-name");
        const l_name = document.getElementById("add-l-name");

        if (f_div) {
            f_div.remove();
            l_div.remove();
            f_name.remove();
            l_name.remove();
        }

    } else {
        const cust_search = document.getElementById('cust-search');
        if (cust_search) {
            cust_search.remove();
        }

        const f_div = document.getElementById("f_div_id");
        const l_div = document.getElementById("l_div_id");
        const f_name = document.getElementById("add-f-name");
        const l_name = document.getElementById("add-l-name");

        if (f_div) {
            f_div.remove();
            l_div.remove();
            f_name.remove();
            l_name.remove();
            add_cust_label.style.display = "none";
        }
    }
});





const dept_id = document.getElementById("dept-id2");
const option_val = document.getElementById("dept-name2");
option_val.addEventListener("change", function() {
    found = false;
    result.forEach((item, index) => {
    if (item['dept_name'] == option_val.value) {
        dept_id.value = result[index]['dept_id'];
        found = true;
    }
    if (!found) {
        dept_id.value = '';
    }
    });
});
result.forEach((item, index) => {
if (item['dept_name'] == option_val.value) {
    bldg_id.value = result[index]['dept_id'];
}
});
});
        </script>
</body>
