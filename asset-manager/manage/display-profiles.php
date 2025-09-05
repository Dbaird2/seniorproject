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
            <table>
                <thead>
                    <tr>
                        <th>Other Users Profiles</th>
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
                            <td><button class="audit" value="<?= $email . ' ' . $profile ?>">Audit</button></td>
                            <td><button class="admin-delete-profile" value="<?= $email . ' ' . $profile ?>">Delete</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
             </table>
<?php } ?>
      </section>
     </body>

</html>
