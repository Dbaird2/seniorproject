<?php
require_once '../config.php';
check_auth();
$result = NULL;
if (isset($_POST)) {
    $profile = $_POST['profile_name'];
    $email = $_SESSION['email'];

    $select_q = "SELECT p.asset_tag, a.asset_name,
    a.room_tag, r.room_loc, b.bldg_name, a.dept_id, a.po, p.asset_note
    FROM user_asset_profile p JOIN asset_info a ON p.asset_tag = a.asset_tag
    JOIN room_table r ON a.room_tag = r.room_tag
    JOIN bldg_table b ON r.bldg_id = b.bldg_id
    WHERE p.profile_name = :profile_name AND p.email = :email";
    try {
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":profile_name" => $profile, ":email" => $email]);
        $result = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
    }
}
if (isset($_POST['pdf'])) {
    require_once ("../vendor/autoload.php");
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script type="text/javascript" src="asset-ajax.js"></script>
    <link rel="stylesheet" href="manager.css">
</head>

<body class="is-ajax">

<?php if ($result) { ?>
    <table class="table">
        <thead>
            <tr>
                <th>Row</th>
                <th>Asset Tag</th>
                <th>Asset Name</th>
                <th>Room Tag</th>
                <th>Room Location</th>
                <th>Building</th>
                <th>Department ID</th>
                <th>PO</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($result as $index => $row) { ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $row['asset_tag'] ?></td>
                    <td><?= $row['asset_name'] ?></td>
                    <td><?= $row['room_tag'] ?></td>
                    <td><?= $row['room_loc'] ?></td>
                    <td><?= $row['bldg_name'] ?></td>
                    <td><?= $row['dept_id'] ?></td>
                    <td><?= $row['po'] ?></td>
                    <td><button id="delete-asset" class='asset-row' value="<?= $row['asset_tag'] ?>">Delete</button></td>
                    <td><textarea name="notes" class='asset-note' id="<?=$row['asset_tag']?>">Notes</textarea></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<? } else { ?>
    <h3>No assets in profile.</h3>
<?php } ?>
</body>

</html>
<?php 
                if (isset($_POST['pdf'])) {
                    $html = ob_get_clean();
                    $mpdf = new \Mpdf\Mpdf();
                    $mpdf->WriteHTML($html);
                    $mpdf->SetDisplayMode('fullpage');
                    $mpdf->Output('Profile: '.htmlspecialchars((string) $_POST['profile_name']).'pdf',' D');
                    unset($_POST['pdf']);
                }
?>
