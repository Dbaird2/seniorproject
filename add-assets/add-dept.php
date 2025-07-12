<?php
include_once("../config.php");
include_once("../navbar.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$success = [];
if (isset($_GET['dept-name'])) {

    $dept_names = $_GET['dept-name'];
    $dept_ids = $_GET['dept-id'];
    $dept_custs = $_GET['dept-cust'];
    $seen = [];
    $filtered_dept_names = [];
    $filtered_dept_ids = [];
    foreach ($dept_names as $index=>$name) {
        if (!isset($seen[$name]) && $name !== '' && !isset($seen[$dept_ids[$index]])) {
            $seen[$name] = true;
            $seen[$dept_ids[$index]] = true;
            $filtered_dept_names[] = $name;
            $filtered_dept_ids[] = $dept_ids[$index];
            $filtered_custs[] = $dept_custs[$index];
        }
    }


    $dept_availibility = "SELECT * FROM dept_table WHERE dept_name = :dept_name OR dept_id = :dept_id";
    $dept_insert = "INSERT INTO dept_table (dept_id, dept_name, custodian) VALUES (?, ?, ?)";
    try {
        foreach ($filtered_dept_names as $index=>$name_lower) {
            $id = strtoupper($filtered_dept_ids[$index]);
            $cust = strtoupper($filtered_custs[$index]);
            $name = strtoupper($name_lower);

            $check_stmt = $dbh->prepare($dept_availibility);
            $check_stmt->execute([":dept_name"=>$name, ":dept_id"=>$id]);
            $is_avail = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$is_not_avail) {
                $insert_stmt = $dbh->prepare($dept_insert);
                $insert_tmt->execute([$id, $name]);
            }

            $success[] = $name;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

}



$select = "SELECT * FROM dept_table ORDER BY dept_name";
$stmt = $dbh->prepare($select);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="bldg.css">
<div class="body">
    <div class="sub-body">
        <div class="form1">
            <h2 class='form-header'>Department Addition Form</h2>
            <form action="add-dept.php" id="add-dept" method="get" enctype="multipart/form-data">
                <div class="form-group">
                <label class="form-label" for="dept-name">Department Name<br></label>
                <input class="form-input" name="dept-name[]" placeholder="Department Name" type="text" required>
                </div>

                <div class="form-group">
                <label class="form-label" for="dept-id">Department ID<br></label>
                <input class="form-input" name="dept-id[]" placeholder="Deptartment ID" type="text" required>
                </div>

                <div class="form-group">
                <label class="form-label" for="dept-id">Department Custodian<br></label>
                <input class="form-input" name="dept-cust[]" placeholder="Deptartment Custodian" type="text" required>
                </div>

                <div class="form-group">
                    <div id="extra-dept"></div>
                </div>

                <button class="submit-btn" type="submit" name="add" id="submit1">Submit</button>
            </form>
            <button class="submit-btn" onclick="addNewDept()">Add Department</button>
<?php foreach ($success as $name) {
echo "<p style='color:green;'>Successfully added " . $name . "<br></p>";
} ?>
        </div>
    </div>
</div>
    <script>
    let count = 2;
function addNewDept() {
    const room_form = document.getElementById('extra-dept');
    const br = document.createElement('br');
    const br2 = document.createElement('br');
    const br3 = document.createElement('br');
    const br4 = document.createElement('br');
    const div = document.createElement("div");

    const name_label = document.createElement("label");
    name_label.textContent = "Department Name " + count;
    name_label.classList.add("form-label");
    if (count > 2) {
        div.appendChild(br);
    }
    div.appendChild(name_label);

    const new_dept = document.createElement("input");
    new_dept.setAttribute('type', 'text');
    new_dept.setAttribute('name', 'dept-name[]');
    new_dept.setAttribute('placeholder', 'Deptartment ID');
    new_dept.classList.add("form-input");
    div.appendChild(new_dept);
    room_form.appendChild(div);

    const id_div = document.createElement("div");

    const id_label = document.createElement("label");  
    id_label.textContent = "Department ID "+ count;
    id_label.classList.add("form-label");
    id_div.appendChild(br3);
    id_div.appendChild(id_label);

    const new_dept_id = document.createElement("input");
    new_dept_id.setAttribute('type', 'text');
    new_dept_id.setAttribute('name', 'dept-id[]');
    new_dept_id.setAttribute('placeholder', 'Deptartment ID');
    new_dept_id.classList.add("form-input");
    id_div.appendChild(new_dept_id);
    room_form.appendChild(id_div);

    const cust_div = document.createElement("div");

    const cust_label = document.createElement("label");  
    cust_label.textContent = "Department Custodian "+ count;
    cust_label.classList.add("form-label");
    cust_div.appendChild(br4);
    cust_div.appendChild(cust_label);

    const new_dept_cust = document.createElement("input");
    new_dept_cust.setAttribute('type', 'text');
    new_dept_cust.setAttribute('name', 'dept-cust[]');
    new_dept_cust.setAttribute('placeholder', 'Deptartment Custodian');
    new_dept_cust.classList.add("form-input");
    cust_div.appendChild(new_dept_cust);

    room_form.appendChild(cust_div);
    count++;
}
</script>
