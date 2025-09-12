<?php
require_once '../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
</head>

<body>
    <?php
    $email = $_SESSION['email'];
    $select = "SELECT distinct profile_name FROM user_asset_profile WHERE email = :email";
    $stmt = $dbh->prepare($select);
    $stmt->execute([":email" => $email]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;

    if (in_array($_SESSION['role'], ['admin', 'management'], true)) {
        $select_q = "SELECT DISTINCT(email, profile_name) as profiles from user_asset_profile";
        $stmt = $dbh->prepare($select_q);
        $stmt->execute();
        $result2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

?>
    <section class="tables">

        <table>
            <thead>
                <tr>
                    <th>Your Profiles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) { ?>
                    <tr>
                        <td><input type="text" id="<?= $row['profile_name'] ?>" value="<?= $row['profile_name'] ?>"></td>
                        <td><button class="rename" value="<?= $email ?>">Rename</button></td>
                        <td><button class="delete-profile" value="<?= $row['profile_name'] ?>">Delete</button></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'management') { ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Other Users Profiles</th>
                        <th><input type="text" id="my-input" onchange="filterTable()" placeholder="Search for email..."</th>
                    </tr>
                </thead>
                <tbody>
<?php 
        foreach ($result2 as $row) { ?>
                        <tr>
<?php
            $row['profiles'] = trim($row['profiles'], "()");
        $row['profiles'] = explode(",", $row['profiles']);
        $email = $row['profiles'][0];
        $profile = trim(trim(trim($row['profiles'][1], '""')), '"'); ?>
        <td><p readonly><?= $email ?></p></td><br>
                            <td><input type="text" id="<?= $email ?>" value="<?= $profile ?>" readonly></td>
                            <td><button type="button" class="audit" data-email="<?= $email ?>" data-profile="<?= $profile ?>">Audit</button></td>
                            <td><button type="button" class="view" data-email="<?= $email ?>" data-profile="<?= $profile ?>">View</button></td>
                            <td><button type="button" data-email="<?= $email ?>" data-profile="<?= $profile ?>" class="admin-delete-profile" value="<?= $email . ' ' . $profile ?>">Delete</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
             </table>
<?php } ?>
        <div class="modal" id="myModal">
            <div id="modal-content">
                <span class="close">&times;</span>
                <div id="modal-view"></div>
            </div>
        </div>
      </section>
     </body>
        <script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];


// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
function filterTable() {
    var input, filter, table, tr, td, i, txt_value;
    input = document.getElementById("my-input");
    filter = input.value.toUpperCase();
    table = document.querySelector(".admin-table");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            txt_value = td.textContent || td.innerText;
            if (txt_value.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>
</html>
