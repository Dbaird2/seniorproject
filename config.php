<?php
use Dotenv\Dotenv;
require __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/etc/secrets/DB_HOST')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
$db_host = $_ENV['DB_HOST'] ?? NULL;
$db_port = $_ENV['DB_PORT'] ?? NULL;
$db_name = $_ENV['DB_NAME'] ?? NULL;
$db_user = $_ENV['DB_USER'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;


try {
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, array());
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start();
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} catch (PDOException $e) {
    error_log($e->getMessage());
} 
?>
