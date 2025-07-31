<?php
require_once '../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    $email = $_SESSION['email'];
    /* $email = 'dbaird2@csub.edu'; */
    $select = "SELECT distinct profile_name FROM user_asset_profile WHERE email = :email";
    $stmt = $dbh->prepare($select);
    $stmt->execute([":email"=>$email]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
    /* mock data 
    $result = [
        ['profile_name' => 'D21560'],
        ['profile_name' => 'D21220']
    ];
    */
    ?>
    <table>
        <thead>
            <tr>
                <th>Profile Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row) { ?>
                <tr>
                    <td><input type="text" id="<?= $row['profile_name'] ?>" value="<?= $row['profile_name'] ?>"></td>
                    <td><button class="rename" value="<?= $row['profile_name'] ?>">Rename</button></td>
                    <td><button class="delete-profile" value="<?= $row['profile_name'] ?>">Delete</button></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>
