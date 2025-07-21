<?php
include_once("../config.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$success = [];
check_auth('high');
include_once("../navbar.php");
if (isset($_GET['dept-name'])) {

    $dept_name = strtoupper($_GET['dept-name']);
    $dept_id = strtoupper($_GET['dept-id']);
    $mailstop = $_GET['dept-stop'];
    $f_name = $_GET['dept-cust-f'];
    $l_name = $_GET['dept-cust-l'];
    $custodian = $f_name . " " .$l_name;
    $manager = $_GET['dept-mana-f']. " " . $_GET['dept-mana-l'];

    $check_cust_uid = "SELECT id FROM user_table WHERE f_name ILIKE :f_name AND l_name ILIKE :l_name";
    $dept_availibility = "SELECT * FROM department WHERE dept_name = :dept_name OR dept_id = :dept_id";
    $dept_insert = "INSERT INTO department (dept_id, dept_name, custodian, dept_manager, mail_stop, uid) VALUES (?, ?, ?, ?, ?, ?)";
    try {
        $get_id_stmt = $dbh->prepare($check_cust_uid);
        $get_id_stmt->execute([":f_name"=>$f_name, ":l_name"=>$l_name]);
        $id = $get_id_stmt->fetch(PDO::FETCH_ASSOC);

        $uid = (isset($id['id']) && is_array[$id] && $id['id'] !== '') ? (int)$id['id'] :  -1;
        $mailstop = ($mailstop !== '') ? (int)$mailstop : -1; 

        $check_stmt = $dbh->prepare($dept_availibility);
        $check_stmt->execute([":dept_name"=>$dept_name, ":dept_id"=>$dept_id]);
        $is_avail = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$is_avail) {
            $insert_stmt = $dbh->prepare($dept_insert);
            $insert_stmt->execute([$dept_id, $dept_name, $custodian, $manager, $mailstop, $uid]);
        }


        $success[] = $dept_name;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

}

?>

<style>
* {
    margin:0;
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
.container > div {
  padding: 10px;
}
.container > div.left {
  grid-area: left;
}
.container > div.right {
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
                    <input class="form-input"  name="dept-mana-l" placeholder="Last Name" type="text">
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
<?php foreach ($success as $name) {
echo "<p style='color:green;'>Successfully added " . $name . "<br></p>";
} ?>
        </div>
    </div>
</div>
</body>

