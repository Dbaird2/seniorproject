<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

function failPdfRequest(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

function html(mixed $value): string
{
    if ($value === null || trim((string)$value) === '') {
        return '--';
    }

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function encodeStoragePath(string $path): string
{
    return implode(
        '/',
        array_map(
            'rawurlencode',
            explode('/', ltrim($path, '/'))
        )
    );
}

function imageUrlToDataUri(?string $url): ?string
{
    if ($url === null || trim($url) === '') {
        return null;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: image/*'
        ]
    ]);

    $imageBytes = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if (
        $imageBytes === false ||
        $status < 200 ||
        $status >= 300
    ) {
        error_log(
            'PDF image download failed. HTTP ' .
                $status .
                '. cURL error: ' .
                $curlError .
                '. URL: ' .
                $url
        );

        return null;
    }

    if (
        $contentType === null ||
        !str_starts_with($contentType, 'image/')
    ) {
        error_log(
            'PDF image returned invalid content type: ' .
                (string)$contentType
        );

        return null;
    }

    return sprintf(
        'data:%s;base64,%s',
        $contentType,
        base64_encode($imageBytes)
    );
}

function createSignedStorageUrl(
    string $bucket,
    ?string $objectPath,
    int $expiresIn = 900
): ?string {
    if ($objectPath === null || trim($objectPath) === '') {
        return null;
    }

    $baseUrl = rtrim((string)getenv('SB_URL'), '/');
    $secretKey = (string)getenv('SB_SECRET_KEY');

    if ($baseUrl === '' || $secretKey === '') {
        throw new RuntimeException(
            'Supabase environment variables are missing.'
        );
    }

    $endpoint =
        $baseUrl .
        '/storage/v1/object/sign/' .
        rawurlencode($bucket) .
        '/' .
        encodeStoragePath($objectPath);

    $curl = curl_init($endpoint);

    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $secretKey,
            'apikey: ' . $secretKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'expiresIn' => $expiresIn
        ], JSON_THROW_ON_ERROR)
    ]);

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);

    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException(
            'Supabase request failed: ' . $curlError
        );
    }

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException(
            'Supabase signed URL request failed. HTTP ' .
                $status .
                ': ' .
                $response
        );
    }

    $responseData = json_decode(
        $response,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $signedPath =
        $responseData['signedURL'] ??
        $responseData['signedUrl'] ??
        null;

    if (!$signedPath) {
        throw new RuntimeException(
            'Supabase did not return a signed URL.'
        );
    }

    if (str_starts_with($signedPath, 'http')) {
        return $signedPath;
    }

    return $baseUrl . $signedPath;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    failPdfRequest(405, 'Method Not Allowed');
}

$barcode = trim($_GET['barcode'] ?? '');

if ($barcode === '') {
    failPdfRequest(400, 'Barcode parameter is required.');
}

try {
    $query = '
        SELECT
            barcode,
            delivered_date,
            delivered_time,
            delivered_by,
            delivered_to,
            comments,
            delivered_status,
            signature_path,
            photo_path,
            latitude,
            longitude
        FROM packages
        WHERE barcode = ?
        LIMIT 1
    ';

    $statement = $dbh->prepare($query);
    $statement->execute([$barcode]);

    $package = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        failPdfRequest(404, 'Package not found.');
    }

    $signatureUrl = createSignedStorageUrl(
        'signatures-api',
        $package['signature_path']
    );

    $photoUrl = createSignedStorageUrl(
        'photos-api',
        $package['photo_path']
    );

    $signatureDataUri = imageUrlToDataUri($signatureUrl);
    $photoDataUri = imageUrlToDataUri($photoUrl);

    $deliveredDate = '--';

    if (!empty($package['delivered_date'])) {
        $timestamp = strtotime($package['delivered_date']);

        if ($timestamp !== false) {
            $deliveredDate = date('F j, Y', $timestamp);
        }
    }

    $deliveredTime = '--';

    if (!empty($package['delivered_time'])) {
        $timestamp = strtotime($package['delivered_time']);

        if ($timestamp !== false) {
            $deliveredTime = date('h:i A T', $timestamp);
        }
    }

    $signatureHtml = $signatureDataUri
        ? '<img
            class="signature-image"
            src="' . $signatureDataUri . '"
            alt="Delivery signature"
       >'
        : '<span>--</span>';

    $photoHtml = $photoDataUri
        ? '<img
            class="photo-image"
            src="' . $photoDataUri . '"
            alt="Delivery photo"
       >'
        : '<span>--</span>';

    $documentHtml = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">

            <style>
                @page {
                    margin: 38px;
                }

                body {
                    color: #161616;
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 11px;
                }

                .heading {
                    margin: 0 0 5px;
                    font-size: 25px;
                    font-weight: normal;
                }

                .status {
                    display: inline-block;
                    margin-top: 10px;
                    padding: 5px 9px;
                    border-radius: 3px;
                    color: #ffffff;
                    background: #3f8d47;
                    font-size: 10px;
                }

                .details-table {
                    width: 100%;
                    margin-top: 28px;
                    border-collapse: collapse;
                }

                .details-table td {
                    width: 25%;
                    padding: 10px 12px 10px 0;
                    vertical-align: top;
                }

                .label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }

                .section {
                    margin-top: 24px;
                    padding-top: 14px;
                    border-top: 1px solid #dddddd;
                }

                .media-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .media-table td {
                    width: 50%;
                    padding-right: 22px;
                    vertical-align: top;
                }

                .signature-image {
                    display: block;
                    width: 250px;
                    max-height: 125px;
                    margin-top: 8px;
                    object-fit: contain;
                }

                .photo-image {
                    display: block;
                    width: 300px;
                    max-height: 230px;
                    margin-top: 8px;
                    object-fit: contain;
                }

                .signature-image {
                    width: 250px;
                    max-height: 125px;
                }

                .photo-image {
                    width: 300px;
                    max-height: 230px;
                }
            </style>
        </head>

        <body>
            <h1 class="heading">' . html($package['barcode']) . '</h1>

            <div>Package</div>

            <span class="status">Delivered</span>

            <table class="details-table">
                <tr>
                    <td>
                        <span class="label">Delivered By:</span>
                        ' . html($package['delivered_by']) . '
                    </td>

                    <td>
                        <span class="label">Date Delivered:</span>
                        ' . html($deliveredDate) . '
                    </td>

                    <td>
                        <span class="label">Time Delivered:</span>
                        ' . html($deliveredTime) . '
                    </td>

                    <td>
                        <span class="label">Note:</span>
                        ' . html($package['comments']) . '
                    </td>
                </tr>

                <tr>
                    <td>
                        <span class="label">To:</span>
                        ' . html($package['delivered_to']) . '
                    </td>

                    <td>
                        <span class="label">Email Status:</span>
                        --
                    </td>

                </tr>
            </table>

            <div class="section">
                <table class="media-table">
                    <tr>
                        <td>
                            <span class="label">
                                Delivery Signature:
                            </span>

                            ' . $signatureHtml . '
                        </td>

                        <td>
                            <span class="label">Photo:</span>

                            ' . $photoHtml . '
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
    ';

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($documentHtml, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    $safeBarcode = preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $package['barcode']
    );

    $dompdf->stream(
        'Package-Details_' . $safeBarcode . '.pdf',
        [
            'Attachment' => true
        ]
    );
} catch (Throwable $error) {
    error_log($error->__toString());

    failPdfRequest(
        500,
        'Unable to generate the package PDF.'
    );
}
