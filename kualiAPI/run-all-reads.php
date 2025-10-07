<?php
set_time_limit(300);

$base = 'https://dataworks-7b7x.onrender.com/kualiAPI/';
    // TRANSFER & DW-TRANSFER TO BE ADDED
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
    try {
        $url = $base . $file;
        echo "Fetching: $url\n";

        $response = @file_get_contents($url);

        if ($response === false) {
            echo "❌ Failed: $url\n";
        } else {
            echo "✅ Success: $url\n";
        }

        usleep(200000); // 0.2s
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

echo "All done!\n";
exit;
