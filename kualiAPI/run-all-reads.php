<?php
// Minimal Kuali API runner (no JSON headers, no browser JS)

// Optional: limit max runtime in seconds
set_time_limit(300);

$base = 'https://dataworks-7b7x.onrender.com/kualiAPI/';
$files = [
    'bulk-transfer.php',
    'add-kuali-info.php',
    'asset-addition-form.php',
    'asset-received.php',
    'bulk-psr.php',
    'check-out.php',
    'equip-loss-stole.php',
    'psr.php'
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

        // Small delay between requests (optional)
        usleep(200000); // 0.2s
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

echo "All done!\n";
exit;
