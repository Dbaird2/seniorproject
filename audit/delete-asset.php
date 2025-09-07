<?php
include_once "../config.php";
check_auth();

if (isset($_POST['delete'])) {
    $tag = $_POST['delete'];
    foreach ($_SESSION['data'] as $key => $row) {
        if ($row["Tag Number"] === $tag) {
            unset($_SESSION['data'][$key]);
        }
    }
    $_SESSION['data'] = array_values($_SESSION['data']);
    echo json_encode(["status"=>"Successfully deleted asset"]);
    exit;
}
echo json_encode(["status"=>"Failed to delete asset"]);
exit;

