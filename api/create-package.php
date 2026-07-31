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

$currentUser = NULL;

$isDevelopmentMode = false;

if (!$isDevelopmentMode) {
    // Call your new function that checks the DB for user tokens
    $currentUser = check_api_auth($dbh, 'low');
}

try {
    $deliveredBy = $currentUser['f_name'] . ' ' . $currentUser['l_name'];
    $barcode = $_POST['barcode'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $comments = isset($_POST['comment']) ? strip_tags(trim($_POST['comment'])) : '';
    $deliveredTo = isset($_POST['lastName']) ? strip_tags(trim($_POST['lastName'])) : '';
    $latitude = $_POST['latitude'] ?? NULL;
    $longitude = $_POST['longitude'] ?? NULL;
    $sigURL = NULL;
    $photoURL = NULL;
    $carrier = strtolower(trim($_POST['carrier'] ?? 'unknown'));

    $safeBarcode = preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $barcode
    );

    if ($safeBarcode === null || $safeBarcode === '') {
        throw new Exception('Unable to create a safe barcode filename.');
    }

    if ($barcode === '' || $deliveredTo === '' || $deliveredBy === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields'
        ]);
        exit;
    }

    if (
        isset($_FILES['photo']) &&
        isset($_FILES['photo']['error']) &&
        $_FILES['photo']['error'] === UPLOAD_ERR_OK
    ) {
        $photo = $_FILES['photo'];

        if ($photo['size'] > 5 * 1024 * 1024) {
            throw new Exception('Photo size exceeds the 5 MB limit.');
        }

        $tmpFile = $photo['tmp_name'];

        if (!is_uploaded_file($tmpFile)) {
            throw new Exception('Invalid photo upload.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpFile);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (
            $mimeType === false ||
            !isset($allowedTypes[$mimeType])
        ) {
            throw new Exception(
                'Invalid photo type. Only JPG, PNG, and WebP are allowed.'
            );
        }

        $extension = $allowedTypes[$mimeType];

        $objectPath =
            'delivery-photos/' .
            $safeBarcode .
            '_' .
            time() .
            '_' .
            bin2hex(random_bytes(4)) .
            '.' .
            $extension;

        $fileContents = file_get_contents($tmpFile);

        if ($fileContents === false) {
            throw new Exception('Unable to read the uploaded photo.');
        }

        $encodedObjectPath = implode(
            '/',
            array_map(
                'rawurlencode',
                explode('/', $objectPath)
            )
        );

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL =>
            rtrim((string) getenv('SB_URL'), '/') .
                '/storage/v1/object/photos-api/' .
                $encodedObjectPath,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . getenv('SB_SECRET_KEY'),
                'apikey: ' . getenv('SB_SECRET_KEY'),
                'Content-Type: ' . $mimeType
            ],

            CURLOPT_POSTFIELDS => $fileContents,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception(
                'Photo cURL error: ' . $curlError
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new Exception(
                'Photo upload failed. HTTP ' .
                    $status .
                    ': ' .
                    $response
            );
        }

        $photoURL = $objectPath;
    } else {
        $photoURL = null;
    }

    if (
        isset($_FILES['signature']) &&
        isset($_FILES['signature']['error']) &&
        $_FILES['signature']['error'] === UPLOAD_ERR_OK
    ) {
        $signature = $_FILES['signature'];

        if ($signature['size'] > 5 * 1024 * 1024) {
            throw new Exception(
                'Signature size exceeds the 5 MB limit.'
            );
        }

        $tempFile = $signature['tmp_name'];

        if (!is_uploaded_file($tempFile)) {
            throw new Exception('Invalid signature upload.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $signatureMimeType = $finfo->file($tempFile);

        $allowedSignatureTypes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg'
        ];

        if (
            $signatureMimeType === false ||
            !isset(
                $allowedSignatureTypes[$signatureMimeType]
            )
        ) {
            throw new Exception(
                'Invalid signature type. Only PNG and JPG are allowed.'
            );
        }

        $signatureExtension =
            $allowedSignatureTypes[$signatureMimeType];

        $sigPath =
            'delivery-signature/' .
            $safeBarcode .
            '_' .
            time() .
            '_' .
            bin2hex(random_bytes(4)) .
            '.' .
            $signatureExtension;

        $fileContent = file_get_contents($tempFile);

        if ($fileContent === false) {
            throw new Exception(
                'Unable to read the uploaded signature.'
            );
        }

        $encodedSignaturePath = implode(
            '/',
            array_map(
                'rawurlencode',
                explode('/', $sigPath)
            )
        );

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL =>
            rtrim((string) getenv('SB_URL'), '/') .
                '/storage/v1/object/signatures-api/' .
                $encodedSignaturePath,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . getenv('SB_SECRET_KEY'),
                'apikey: ' . getenv('SB_SECRET_KEY'),
                'Content-Type: ' . $signatureMimeType
            ],

            CURLOPT_POSTFIELDS => $fileContent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception(
                'Signature cURL error: ' . $curlError
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new Exception(
                'Signature upload failed. HTTP ' .
                    $status .
                    ': ' .
                    $response
            );
        }

        $sigURL = $sigPath;
    } else {
        $sigURL = null;
    }

    $check = 'SELECT * FROM packages WHERE barcode = :barcode';
    $checkStmt = $dbh->prepare($check);
    $checkStmt->execute(['barcode' => $barcode]);

    $existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingRow) {
        $update = 'UPDATE packages SET delivered_date = :delivered_date, delivered_time = :delivered_time, delivered_by = :delivered_by, delivered_to = :delivered_to, comments = :comments, delivered_status = :delivered_status, signature_path = :signature_path, photo_path = :photo_path, latitude = :latitude, longitude = :longitude, carrier = :carrier WHERE barcode = :barcode';
        $updateStmt = $dbh->prepare($update);
        $updateStmt->execute([
            'delivered_date' => $date,
            'delivered_time' => $time,
            'delivered_by' => $deliveredBy,
            'delivered_to' => $deliveredTo,
            'comments' => $comments,
            'delivered_status' => true,
            'signature_path' => $sigURL,
            'photo_path' => $photoURL,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'carrier' => $carrier,
            'barcode' => $barcode
        ]);
    }

    if (!$existingRow) {
        $insert = 'INSERT INTO packages (barcode, delivered_date, delivered_time, delivered_by, delivered_to, comments, delivered_status, signature_path, photo_path, latitude, longitude, carrier) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
        $stmt = $dbh->prepare($insert);
        $stmt->execute([$barcode, $date, $time, $deliveredBy, $deliveredTo, $comments, true, $sigURL, $photoURL, $latitude, $longitude]);
    }
    
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
