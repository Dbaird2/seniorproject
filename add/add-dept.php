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
        $custodian = $f_name . " " . $l_name;
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
            $check_stmt->execute([":dept_name" => $dept_name, ":dept_id" => $dept_id, ":cust" => $custodian]);
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
        try {
            $delete_msg = "Deleted";
            $delete_q = "DELETE FROM department WHERE dept_id = :dept_id AND dept_name = :dept_name";
            $delete_stmt = $dbh->prepare($delete_q);
            $delete_msg = $delete_stmt->execute([":dept_id" => $dept_id, ":dept_name" => $dept_name]) ? "Successfully deleted " . $dept_name : "Failed to delete " . $dept_name;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
                <h2 class='form-header'>Department Removal Form</h2>

                <form action="add-dept.php" id="remove-dept" method="get" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="dept-name">Department Name<br></label>
                        <input class="form-input" placeholder="Department Name" type="search" name="dept-name" id="dept-name2" list="dept-names" autocomplete="off">
                        <datalist id="dept-names">
                            <?php foreach ($result as $dept) { ?>
                                <option value="<?= $dept["dept_name"] ?>"><?= $dept["dept_name"] ?></option>
                            <?php } ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept-id">Department ID<br></label>
                        <input class="form-input" type="text" id="dept-id2" name="dept-id" readonly>
                    </div>
                    <h2 class='form-label'><?= $delete_msg ?></h2>

                    <button class="submit-btn" type="submit" name="remove" id="submit2">Submit</button>
                </form>
            </div>
        </div>
        <script>
            result = <?php echo json_encode($result); ?>;
            document.addEventListener("DOMContentLoaded", (e) => {
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
