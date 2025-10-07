<?php
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
    $path = __DIR__ . '/' . $file; // adjust if needed
    echo "Running: $path\n";

    // run the script using PHP CLI
    $cmd = 'php ' . escapeshellarg($path);
    exec($cmd . ' 2>&1', $output, $exitCode);

    if ($exitCode === 0) {
        echo "Success: $file\n<br>";
    } else {
        echo "Failed: $file (exit code $exitCode)\n<br>";
    }

    // optionally show partial output for debugging
    echo implode("\n", array_slice($output, 0, 5)) . "\n\n";
    usleep(200000); // 0.2s pause
}

echo "All done!\n";
