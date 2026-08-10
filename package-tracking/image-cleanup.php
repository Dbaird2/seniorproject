<?php

declare(strict_types=1);

include('../../config.php');

header('Content-Type: application/json');

$baseUrl = rtrim(
    (string) getenv('SB_URL'),
    '/'
);

$secretKey =
    (string) getenv('SB_SECRET_KEY');

if (
    $baseUrl === '' ||
    $secretKey === ''
) {
    throw new Exception(
        'Supabase configuration is missing.'
    );
}

function deleteSupabaseObject(
    string $baseUrl,
    string $secretKey,
    string $bucket,
    string $objectPath
): bool {
    if (trim($objectPath) === '') {
        return true;
    }

    $encodedPath = implode(
        '/',
        array_map(
            'rawurlencode',
            explode('/', $objectPath)
        )
    );

    $url =
        $baseUrl .
        '/storage/v1/object/' .
        rawurlencode($bucket) .
        '/' .
        $encodedPath;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'DELETE',

        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' .
                $secretKey,

            'apikey: ' .
                $secretKey,
        ],

        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response =
        curl_exec($ch);

    $status =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

    $curlError =
        curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        error_log(
            'Supabase delete cURL error: ' .
            $curlError
        );

        return false;
    }

    if (
        $status < 200 ||
        $status >= 300
    ) {
        error_log(
            'Supabase delete failed. ' .
            'Bucket: ' .
            $bucket .
            ', Path: ' .
            $objectPath .
            ', HTTP: ' .
            $status .
            ', Response: ' .
            $response
        );

        return false;
    }

    return true;
}

function pathHasNewerReference(
    PDO $dbh,
    string $column,
    string $path
): bool {
    /*
      Column names cannot be bound as SQL parameters, so only allow the two known-safe column names.
     */
    $allowedColumns = [
        'photo_path',
        'signature_path',
    ];

    if (
        !in_array(
            $column,
            $allowedColumns,
            true
        )
    ) {
        throw new Exception(
            'Invalid path column.'
        );
    }

    $query = "
        SELECT COUNT(*)
        FROM packages
        WHERE {$column} = :path
          AND delivered_date >=
              CURRENT_DATE - INTERVAL '1 year'
    ";

    $stmt =
        $dbh->prepare($query);

    $stmt->execute([
        'path' => $path,
    ]);

    return (
        (int) $stmt->fetchColumn()
    ) > 0;
}

function clearPathFromOldRows(
    PDO $dbh,
    string $column,
    string $path
): void {
    $allowedColumns = [
        'photo_path',
        'signature_path',
    ];

    if (
        !in_array(
            $column,
            $allowedColumns,
            true
        )
    ) {
        throw new Exception(
            'Invalid path column.'
        );
    }

    $query = "
        UPDATE packages
        SET {$column} = NULL
        WHERE {$column} = :path
          AND delivered_date <
              CURRENT_DATE - INTERVAL '1 year'
    ";

    $stmt =
        $dbh->prepare($query);

    $stmt->execute([
        'path' => $path,
    ]);
}

/*
  Get unique old photo paths.
 */
$photoQuery = "
    SELECT DISTINCT photo_path
    FROM packages
    WHERE photo_path IS NOT NULL
      AND photo_path <> ''
      AND delivered_date <
          CURRENT_DATE - INTERVAL '1 year'
";

$photoStmt =
    $dbh->prepare($photoQuery);

$photoStmt->execute();

$photoPaths =
    $photoStmt->fetchAll(
        PDO::FETCH_COLUMN
    );

/*
  Get unique old signature paths.
 */
$signatureQuery = "
    SELECT DISTINCT signature_path
    FROM packages
    WHERE signature_path IS NOT NULL
      AND signature_path <> ''
      AND delivered_date <
          CURRENT_DATE - INTERVAL '1 year'
";

$signatureStmt =
    $dbh->prepare(
        $signatureQuery
    );

$signatureStmt->execute();

$signaturePaths =
    $signatureStmt->fetchAll(
        PDO::FETCH_COLUMN
    );

$photosDeleted = 0;
$photosSkipped = 0;

$signaturesDeleted = 0;
$signaturesSkipped = 0;

/*
  Process shared photos.
 */
foreach ($photoPaths as $photoPath) {
    if (
        pathHasNewerReference(
            $dbh,
            'photo_path',
            $photoPath
        )
    ) {
        /*
          At least one package using this same photo is less than one year old.
         */
        $photosSkipped++;

        continue;
    }

    $deleted =
        deleteSupabaseObject(
            $baseUrl,
            $secretKey,
            'photos-api',
            $photoPath
        );

    if ($deleted) {
        /*
          Every package using this object is old, so clear all old database references.
         */
        clearPathFromOldRows(
            $dbh,
            'photo_path',
            $photoPath
        );

        $photosDeleted++;
    }
}

/*
 Process shared signatures.
 */
foreach (
    $signaturePaths as
    $signaturePath
) {
    if (
        pathHasNewerReference(
            $dbh,
            'signature_path',
            $signaturePath
        )
    ) {
        $signaturesSkipped++;

        continue;
    }

    $deleted =
        deleteSupabaseObject(
            $baseUrl,
            $secretKey,
            'signatures-api',
            $signaturePath
        );

    if ($deleted) {
        clearPathFromOldRows(
            $dbh,
            'signature_path',
            $signaturePath
        );

        $signaturesDeleted++;
    }
}

echo json_encode([
    'success' => true,

    'photos' => [
        'deleted' =>
            $photosDeleted,

        'skipped_shared_with_newer_package' =>
            $photosSkipped,
    ],

    'signatures' => [
        'deleted' =>
            $signaturesDeleted,

        'skipped_shared_with_newer_package' =>
            $signaturesSkipped,
    ],
]);