<?php
include_once __DIR__ . '/../config.php';
set_time_limit(300);

$files = [
    'bulk-transfer.php',
    'add-kuali-info.php',
    'asset-addition-form.php',
    'asset-received.php',
    'bulk-psr.php',
    'check-out.php',
    'equip-loss-stole.php',
    'psr.php',
    'dataworks-read/dw-psr.php',
    'dataworks-read/dw-equip-loss-stole.php',
    'dataworks-read/dw-check-out.php',
    'dataworks-read/dw-bulk-transfer.php'
];

foreach ($files as $file) {
    echo 'Starting: ' . $path . "<br>";
    $path = 'https://dataworks-7b7x.onrender.com/kualiAPI/' . $file; // adjust if needed
    $curl = curl_init($path);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($resp, true);
    echo "<pre><br>";
    var_dump($response);
    echo "</pre>";
    usleep(200000); // 0.2s pause
}

echo "All done!\n";
