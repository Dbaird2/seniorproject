<?php

declare(strict_types=1);
include("../config.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

//check_auth();

$isDevelopmentMode = false;

if (!$isDevelopmentMode) {
    // Call your new function that checks the DB for user tokens
    check_auth();
}

$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Barcode parameter is required']);
    exit;
}

try {
    $query = "SELECT barcode, delivered_date, delivered_time, delivered_by, delivered_to, comments, signature_path, photo_path, latitude, longitude 
              FROM packages WHERE barcode = ? LIMIT 1";
    $stmt = $dbh->prepare($query);
    $stmt->execute([$barcode]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Package not found']);
        exit;
    }

    $photoUrl = null;
    if (!empty($package['photo_path'])) {
        $baseUrl = rtrim(getenv('SB_URL'), '/');

        $encodedPhotoPath = implode(
            '/',
            array_map(
                'rawurlencode',
                explode('/', $package['photo_path'])
            )
        );
        // Endpoint structure for creating signed URLs in Supabase Storage
        $supabaseStorageUrl = $baseUrl . "/storage/v1/object/sign/photos-api/" . $encodedPhotoPath;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $supabaseStorageUrl,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('SB_SECRET_KEY'),
                "apikey: " . getenv('SB_SECRET_KEY'),
                "Content-Type: application/json"
            ],
            // Request a URL valid for 900 seconds (15 minutes)
            CURLOPT_POSTFIELDS => json_encode(['expiresIn' => 900]),
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $responseData = json_decode($response, true);
            // Supabase returns a relative path like /storage/v1/object/sign/...
            // We prepend the base project domain to make it a fully qualified URL
            $photoUrl = $baseUrl . $responseData['signedURL'];
        }
    }

    $sigUrl = null;
    if (!empty($package['signature_path'])) {
        $baseUrl = rtrim(getenv('SB_URL'), '/');

        $encodedSigPath = implode(
            '/',
            array_map(
                'rawurlencode',
                explode('/', $package['signature_path'])
            )
        );
        // Endpoint structure for creating signed URLs in Supabase Storage
        $supabaseStorageUrl = $baseUrl . "/storage/v1/object/sign/signatures-api/" . $encodedSigPath;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $supabaseStorageUrl,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('SB_SECRET_KEY'),
                "apikey: " . getenv('SB_SECRET_KEY'),
                "Content-Type: application/json"
            ],
            // Request a URL valid for 900 seconds (15 minutes)
            CURLOPT_POSTFIELDS => json_encode(['expiresIn' => 900]),
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $responseData = json_decode($response, true);
            // Supabase returns a relative path like /storage/v1/object/sign/...
            // We prepend the base project domain to make it a fully qualified URL
            $sigUrl = $baseUrl . $responseData['signedURL'];
        }
    }

    // 5. Return payload
    echo json_encode([
        'success' => true,
        'data' => [
            'barcode' => $package['barcode'],
            'delivered_date' => $package['delivered_date'],
            'delivered_time' => $package['delivered_time'],
            'delivered_by' => $package['delivered_by'],
            'delivered_to' => $package['delivered_to'],
            'comments' => $package['comments'],
            'latitude' => $package['latitude'],
            'longitude' => $package['longitude'],
            'photo_url' => $photoUrl, // The secure web-ready image link!
            'sig_url' => $sigUrl
        ]
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
