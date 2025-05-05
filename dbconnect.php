<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env only if it exists (Render will use real env vars)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Access variables safely
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_port = $_ENV['DB_PORT'] ?? '5432';
$db_name = $_ENV['DB_NAME'] ?? 'defaultdb';
$db_user = $_ENV['DB_USER'] ?? 'user';
$db_pass = $_ENV['DB_PASS'] ?? '';

?>
