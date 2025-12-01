<?php
include_once "../config.php";
header('Content-Type: application/json');
check_auth();

$select = 'SELECT room_tag FROM room_table ORDER BY room_tag ASC';
$stmt = $dbh->query($select);
$room_tags = $stmt->fetchAll();
$count = 0;

foreach ($room_tags as $tag) {
    $count++;
    $room = (int)$tag['room_tag'];
    if ((int)$room === $count) {
        continue;
    }
    break;
}
echo json_encode(['room_tag'=>$count]);
exit;


