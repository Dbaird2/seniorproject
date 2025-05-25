<?php

require __DIR__ . '/vender/autoload.php'

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$db_host = $_ENV['DB_HOST'] ?? NULL;
$db_port = $_ENV['DB_PORT'] ?? NULL;
$db_name = $_ENV['DB_NAME'] ?? NULL;
$db_user = $_ENV['DB_USER'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;

try {
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $dbu_pass, array());
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("location: /ConnectionFailed");
    error_log($e->getMessage());
    die('Connection Failed');
} 
?>


