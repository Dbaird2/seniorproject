<?php
include_once("../config.php");

$tag = $_POST['tag'];
if (isset($_POST['room'])) {
    $room = $_POST['room'];
    foreach ($_SESSION['data'] as $index => &$row) {
        if ($row['Tag Number'] === $tag) {
            $row['Found Room Tag'] = $room;
            echo json_encode(['success' => true, 'message' => $_POST]);
            exit;
        }
    }
} else if (isset($_POST['note'])) {
    $note = $_POST['note'];
    foreach ($_SESSION['data'] as $index => &$row) {
        if ($row['Tag Number'] === $tag) {
            $row['Found Note'] = $note;
            echo json_encode(['success' => true, 'message' => $_POST]);
            exit;
        }
    }
}

echo json_encode(['failure' => true, 'message' => 'tag not found']);
exit;

