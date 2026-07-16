<?php
include_once("../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}


/*
echo json_encode([
    "status" => "success",
    "message" => "API is working!"
]);
*/

try {
    //$raw = file_get_contents('php://input');
    //$payload = json_decode($raw, true);

    $deliveredBy = $_POST['user'] ?? '';
    $barcode = $_POST['barcode'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $deliveredTo = $_POST['lastName'] ?? '';
    $comments = $_POST['comment'] ?? '';
    $latitude = $_POST['latitude'] ?? NULL;
    $longitude = $_POST['longitude'] ?? NULL;
    $sigURL = NULL;
    $photoURL = NULL;

    if ($barcode === '' || $deliveredTo === '' || $deliveredBy === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields'
        ]);
        exit;
    }

    if (isset($_FILES['photo'])  && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    }

    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
    }

    $insert = 'INSERT INTO packages (barcode, delivered_date, delivered_time, delivered_by, delivered_to, comments, delivered_status, signature_path, photo_path, latitude, longitude) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $dbh->prepare($insert);
    $stmt->execute([$barcode, $date, $time, $deliveredBy, $deliveredTo, $comments, true, $sigURL, $photoURL, $latitude, $longitude]);

    echo json_encode([
        'success' => true,
        'message' => 'Package info inserted successfully',
        'barcode' => $barcode
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
