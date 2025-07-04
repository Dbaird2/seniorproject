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
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 43200);
        session_set_cookie_params(43200);
        session_start();
    }
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, array());
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set up session only once
} catch (PDOException $e) {
    error_log($e->getMessage());
} 
?>
