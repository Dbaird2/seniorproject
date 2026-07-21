<?php

declare(strict_types=1);
include("../config.php");

header('Content-Type: application/json');

//check_api_auth($dbh, 'low');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$isDevelopmentMode = true;

if (!$isDevelopmentMode) {
    // Call your new function that checks the DB for user tokens
    check_api_auth($dbh, 'low');
}

try {
    $deliveredBy = $_POST['user'] ?? '';
    $barcode = $_POST['barcode'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $comments = isset($_POST['comment']) ? strip_tags(trim($_POST['comment'])) : '';
    $deliveredTo = isset($_POST['lastName']) ? strip_tags(trim($_POST['lastName'])) : '';
    $latitude = $_POST['latitude'] ?? NULL;
    $longitude = $_POST['longitude'] ?? NULL;
    $sigURL = 'jfso';
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
        $photo = $_FILES['photo'];

        if ($photo['size'] > 5 * 1024 * 1024) { //5 MB Limit
            throw new Exception('File size exceeds limit.');
        }

        $tmpFile = $photo['tmp_name'];
        $fileName = $photo['name'];

        $finfo = new finfo(FILEINFO_MIME_TYPE); // Security
        $mimeType = $finfo->file($tmpFile);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WebP are allowed.');
        }

        $objectPath = "delivery-photos/" . $barcode . ".jpg";
        $fileContents = file_get_contents($tmpFile);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim(getenv('SB_URL'), '/') . "/storage/v1/object/photos-api/" . $objectPath,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('SB_SECRET_KEY'),
                "apikey: " . getenv('SB_SECRET_KEY'),
                "Content-Type: image/jpeg"
            ],
            CURLOPT_POSTFIELDS => $fileContents,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception('Photo cURL error: ' . $curlError);
        }

        if ($status < 200 || $status >= 300) {
            throw new Exception(
                'Photo upload failed. HTTP ' . $status . ': ' . $response
            );
        }

        $photoURL = $objectPath;
    } else {
        $photoURL = NULL;
    }

    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $sig = $_FILES['signature'];
        $tempFile = $sig['tmp_name'];
        $fileName = $sig['name'];

        $sigPath = "delivery-signature/" . $barcode . "_" . time() . ".jpg";
        $fileContent = file_get_contents($tempFile);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim(getenv('SB_URL'), '/') . "/storage/v1/object/signatures-api/" . $sigPath,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('SB_SECRET_KEY'),
                "apikey: " . getenv('SB_SECRET_KEY'),
                "Content-Type: image/jpeg"
            ],
            CURLOPT_POSTFIELDS => $fileContent,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);


        if ($response === false) {
            throw new Exception('Photo cURL error: ' . $curlError);
        }

        if ($status < 200 || $status >= 300) {
            throw new Exception(
                'Photo upload failed. HTTP ' . $status . ': ' . $response
            );
        }

        $sigURL = $sigPath; // change to url path
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
    /*
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
    */
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    error_log($e->getMessage());
    /*
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
    */
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
